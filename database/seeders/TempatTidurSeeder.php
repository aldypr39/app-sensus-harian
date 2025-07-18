<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TempatTidur;
use Illuminate\Support\Facades\DB; // <-- Gunakan DB facade

class TempatTidurSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua data kelas perawatan yang sudah dibuat
        $kelasPerawatan = DB::table('kelas_perawatans')->get();

        foreach ($kelasPerawatan as $kelas) {
            // Loop sebanyak jumlah_tt untuk setiap kelas
            for ($i = 1; $i <= $kelas->jumlah_tt; $i++) {
                TempatTidur::create([
                    'ruangan_id' => $kelas->ruangan_id,
                    'nomor_tt' => $kelas->nama_kelas . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'kelas' => $kelas->nama_kelas,
                    'status' => 'tersedia',
                ]);
            }
        }
    }
}