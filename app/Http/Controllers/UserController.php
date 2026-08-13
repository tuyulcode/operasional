<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::latest()->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = User::findOrFail($request->query('edit'));
        }

        return view('users.index', compact('users', 'edit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas',
        ]);

        User::create([
            'username' => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,petugas',
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat mengubah role admin terakhir.');
        }

        $user->username = $validated['username'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
