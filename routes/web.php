<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\RuanganController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute untuk tamu (yang belum login)
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);


// Rute untuk user yang sudah login
Route::middleware('auth')->group(function () {
    
    // Halaman utama
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rute untuk proses logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- SEMUA RUTE APLIKASI ADA DI SINI ---

    // Rute untuk data statistik dashboard (hanya satu)
    Route::get('/dashboard-stats', [PasienController::class, 'getDashboardStats']);

    // Rute Pasien
    Route::get('/pasien/aktif', [PasienController::class, 'getActivePatients']);
    Route::get('/pasien/riwayat', [PasienController::class, 'getDischargedPatients']);
    Route::post('/pasien', [PasienController::class, 'store']);
    Route::get('/pasien/{pasien}', [PasienController::class, 'show']);
    Route::put('/pasien/{pasien}', [PasienController::class, 'update']);
    Route::delete('/pasien/{pasien}', [PasienController::class, 'destroy']);
    
    // Aksi spesifik pasien
    Route::post('/pasien/{pasien}/keluar', [PasienController::class, 'discharge']);
    Route::post('/pasien/{id}/batalkan-pulang', [PasienController::class, 'batalkanPulang']);

    // Rute untuk mendapatkan daftar kelas yang tersedia di ruangan user
    Route::get('/api/ruangan/kelas-tersedia', [PasienController::class, 'getKelasTersedia']);

    // Rute untuk mendapatkan tempat tidur yang tersedia berdasarkan kelas yang dipilih
    Route::get('/api/ruangan/tempat-tidur-tersedia', [PasienController::class, 'getTempatTidurTersedia']);


    // Rute untuk manajemen ruangan
    Route::get('/manajemen/ruangan', [RuanganController::class, 'index'])->name('ruangan.index')->middleware('auth');

    Route::post('/manajemen/ruangan', [RuanganController::class, 'store'])->name('ruangan.store');
});