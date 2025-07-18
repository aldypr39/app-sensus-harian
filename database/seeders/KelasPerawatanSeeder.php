<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KelasPerawatan; // <-- Impor model

class KelasPerawatanSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan Model, yang otomatis akan merujuk ke tabel 'kelas_perawatans'
        KelasPerawatan::insert([
            [
                'ruangan_id' => 1,
                'nama_kelas' => 'Kelas 3',
                'jumlah_tt' => 8,
            ],
            [
                'ruangan_id' => 2,
                'nama_kelas' => 'VIP',
                'jumlah_tt' => 19,
            ],
            [
                'ruangan_id' => 3,
                'nama_kelas' => 'Kelas 1',
                'jumlah_tt' => 17,
            ],
        ]);
    }
}