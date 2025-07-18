<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hapus semua kode contoh, ganti dengan ini.
        // Ini akan memanggil seeder kita secara berurutan.
        $this->call([
            RuanganSeeder::class,
            KelasPerawatanSeeder::class,
            TempatTidurSeeder::class,
            UserSeeder::class,
        ]);
    }
}