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
        // Menggunakan Model Ruangan untuk insert data ke tabel yang benar ('ruangans')
        Ruangan::insert([
            [
                'nama_ruangan' => 'Ruang Mata',
                'gedung' => 'Gedung Tulip',
                'lantai' => '1a',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_ruangan' => 'Ruang Aster Lantai 3',
                'gedung' => 'Gedung Aster',
                'lantai' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_ruangan' => 'Ruang Wijaya Kusuma 4',
                'gedung' => 'Gedung Ulin Tower',
                'lantai' => '4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}