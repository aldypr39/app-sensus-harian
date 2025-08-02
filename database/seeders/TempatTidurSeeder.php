<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TempatTidur;
use Illuminate\Support\Facades\DB; // <-- Gunakan DB facade

class TempatTidurSeeder extends Seeder
{
    public function run(): void
    {
    // --- UBAH QUERY INI ---
    // Ambil data kelas perawatan DAN gabungkan dengan tabel 'kelas' untuk dapat nama_kelas
    $semuaKelasPerawatan = DB::table('kelas_perawatans')
        ->join('kelas', 'kelas_perawatans.kelas_id', '=', 'kelas.id')
        ->select('kelas_perawatans.*', 'kelas.nama_kelas')
        ->get();

    foreach ($semuaKelasPerawatan as $kp) {
        // Loop sebanyak jumlah_tt untuk setiap kelas
        for ($i = 1; $i <= $kp->jumlah_tt; $i++) {
            TempatTidur::create([
                'ruangan_id' => $kp->ruangan_id,
                'kelas_id'   => $kp->kelas_id, // <-- Pastikan ini 'kelas_id'
                'nomor_tt'   => $kp->nama_kelas . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status'     => 'tersedia',
                ]);
            }
        }
    }
}