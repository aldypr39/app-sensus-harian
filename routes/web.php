<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\GedungController;
use App\Http\Controllers\KelasController;

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

});

Route::middleware('auth')->group(function () {
    
    // Rute API untuk mengambil data master (dropdown)
    Route::get('/api/master/gedungs', [RuanganController::class, 'getAllGedungs']);
    Route::get('/api/master/kelas', [RuanganController::class, 'getAllKelas']);
    // Rute untuk Manajemen Gedung
    Route::post('/api/master/gedungs', [GedungController::class, 'store']);
    Route::delete('/api/master/gedungs/{gedung}', [GedungController::class, 'destroy']);
    // Rute untuk Manajemen Kelas
    Route::post('/api/master/kelas', [KelasController::class, 'store']);
    Route::delete('/api/master/kelas/{kelas}', [KelasController::class, 'destroy']);
    
    // Grup untuk semua halaman manajemen
    Route::prefix('manajemen')->group(function() {
        // Rute untuk Manajemen Ruangan
        Route::get('/ruangan', [RuanganController::class, 'index'])->name('ruangan.index');
        Route::post('/ruangan', [RuanganController::class, 'store'])->name('ruangan.store');
        Route::get('/ruangan/{ruangan}/edit', [RuanganController::class, 'edit'])->name('ruangan.edit');
        Route::put('/ruangan/{ruangan}', [RuanganController::class, 'update'])->name('ruangan.update');
        Route::delete('/ruangan/{ruangan}', [RuanganController::class, 'destroy'])->name('ruangan.destroy');
        
    });

});