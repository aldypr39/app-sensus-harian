<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        // 1. Validasi input
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Coba lakukan login
        if (Auth::attempt($credentials)) {
            // 3. Jika berhasil, kirim respon sukses
            $request->session()->regenerate();
            
            return response()->json([
                'message' => 'Login berhasil!',
                'user' => Auth::user() 
            ]);
        }

        // 4. Jika gagal, kirim respon error
        return response()->json([
            'message' => 'Username atau password salah.'
        ], 401); // 401 = Unauthorized
    }

    public function username()
    {
        return 'username';
    }

    public function logout(Request $request)
    {
        // 1. Lakukan logout pada user yang sedang aktif
        Auth::logout();

        // 2. Invalidate sesi untuk mencegah session fixation
        $request->session()->invalidate();

        // 3. Buat ulang token CSRF
        $request->session()->regenerateToken();

        // 4. Kirim respon sukses
        return response()->json(['message' => 'Logout berhasil.']);
    }
}