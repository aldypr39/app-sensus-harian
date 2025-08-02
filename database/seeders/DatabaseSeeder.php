<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Jalankan seeder data master dulu
            GedungSeeder::class,
            KelasSeeder::class,

            // 2. Baru jalankan seeder yang tergantung pada data master
            RuanganSeeder::class,
            KelasPerawatanSeeder::class,
            TempatTidurSeeder::class, 
            UserSeeder::class,
        ]);
    }
}