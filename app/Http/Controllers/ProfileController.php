<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Update foto profil user yang sedang login
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'photo.required' => 'Foto profil wajib dipilih.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.mimes'    => 'Format foto harus jpg, jpeg, atau png.',
            'photo.max'      => 'Ukuran foto maksimal 2MB.',
        ]);

        $user = Auth::user();

        // Hapus foto lama kalau ada, biar tidak numpuk file yang tidak terpakai
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->update([
            'photo' => $path,
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Foto profil berhasil diperbarui.');
    }
}