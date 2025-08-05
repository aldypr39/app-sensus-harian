<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\GedungController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\AkunController;

// Rute untuk tamu
Route::get('/login', fn() => view('login'))->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Rute untuk user yang sudah login
Route::middleware('auth')->group(function () {
    
    // Rute Aplikasi Utama (Perawat)
    Route::get('/', fn() => view('dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard-stats', [PasienController::class, 'getDashboardStats']);
    Route::get('/api/ruangan/kelas-tersedia', [PasienController::class, 'getKelasTersedia']);
    Route::get('/api/ruangan/tempat-tidur-tersedia', [PasienController::class, 'getTempatTidurTersedia']);
    Route::get('/api/ruangans', [AkunController::class, 'getRuanganForDropdown']);

    // Rute API Pasien
    Route::post('/pasien', [PasienController::class, 'store']);
    Route::get('/pasien/aktif', [PasienController::class, 'getActivePatients']);
    Route::get('/pasien/riwayat', [PasienController::class, 'getDischargedPatients']);
    Route::get('/pasien/{pasien}', [PasienController::class, 'show']);
    Route::put('/pasien/{pasien}', [PasienController::class, 'update']);
    Route::delete('/pasien/{pasien}', [PasienController::class, 'destroy']);
    Route::post('/pasien/{pasien}/keluar', [PasienController::class, 'discharge']);
    Route::post('/pasien/{id}/batalkan-pulang', [PasienController::class, 'batalkanPulang']);

    // Rute API Data Master (Admin)
    Route::middleware('admin')->prefix('api/master')->name('api.master.')->group(function() {
    // Tambahkan 'index' untuk mengizinkan GET /api/master/gedungs
    Route::apiResource('/gedungs', GedungController::class)->only(['index', 'store', 'destroy']);
    
    // Tambahkan 'index' untuk mengizinkan GET /api/master/kelas
    Route::apiResource('/kelas', KelasController::class)->only(['index', 'store', 'destroy']);
    });

    // Rute Halaman Panel Admin
    Route::middleware('admin')->prefix('manajemen')->name('manajemen.')->group(function() {
        Route::resource('/ruangan', RuanganController::class);
        Route::resource('/akun', AkunController::class)->parameters(['akun' => 'user']);
    });
});