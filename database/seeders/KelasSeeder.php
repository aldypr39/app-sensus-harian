<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        Kelas::create(['nama_kelas' => 'VVIP']);
        Kelas::create(['nama_kelas' => 'VIP']);
        Kelas::create(['nama_kelas' => 'Kelas 1']);
        Kelas::create(['nama_kelas' => 'Kelas 2']);
        Kelas::create(['nama_kelas' => 'Kelas 3']);
    }
}