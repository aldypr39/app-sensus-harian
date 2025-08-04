<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user sudah login DAN perannya adalah 'admin'
        if (Auth::check() && Auth::user()->role == 'admin') {
            // Jika ya, izinkan akses ke halaman selanjutnya
            return $next($request);
        }

        // Jika tidak, tolak dan kembalikan ke halaman dashboard dengan pesan error
        return redirect('/')->with('error', 'Anda tidak memiliki hak akses ke halaman ini.');
    }
}