<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

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
}