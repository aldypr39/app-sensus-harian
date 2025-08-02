<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruangan; // Penting: Impor model Ruangan

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menggunakan ID dari data yang akan dibuat oleh GedungSeeder
        // Gedung Tulip -> id 1
        // Gedung Aster -> id 2
        // Gedung Ulin Tower -> id 3
        Ruangan::create(['gedung_id' => 1, 'nama_ruangan' => 'Ruang Mawar', 'lantai' => '1A']);
        Ruangan::create(['gedung_id' => 1, 'nama_ruangan' => 'Ruang Melati', 'lantai' => '1B']);
        Ruangan::create(['gedung_id' => 2, 'nama_ruangan' => 'Ruang Anggrek', 'lantai' => '3']);
    }
}