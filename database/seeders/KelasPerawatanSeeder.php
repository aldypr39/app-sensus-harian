<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KelasPerawatan;

class KelasPerawatanSeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan ID dari data yang dibuat oleh KelasSeeder
        // ID Ruangan: 1=Mawar, 2=Melati, 3=Anggrek
        // ID Kelas: 1=VVIP, 2=VIP, 3=Kelas 1, 4=Kelas 2, 5=Kelas 3

        KelasPerawatan::create(['ruangan_id' => 1, 'kelas_id' => 3, 'jumlah_tt' => 10]); // Ruangan Mawar, Kelas 1
        KelasPerawatan::create(['ruangan_id' => 2, 'kelas_id' => 2, 'jumlah_tt' => 5]);  // Ruangan Melati, VIP
        KelasPerawatan::create(['ruangan_id' => 3, 'kelas_id' => 5, 'jumlah_tt' => 15]); // Ruangan Anggrek, Kelas 3
    }
}