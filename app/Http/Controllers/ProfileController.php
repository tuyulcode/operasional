<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil saya (info akun read-only)
     */
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    /**
     * Update password user yang sedang login
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        // Cek password lama cocok dengan password_hash yang tersimpan
        if (! Hash::check($request->current_password, $user->password_hash)) {
            return redirect()->route('profile.index')
                ->with('error', 'Password lama yang kamu masukkan salah.');
        }

        $user->update([
            'password_hash' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Password berhasil diperbarui.');
    }
}