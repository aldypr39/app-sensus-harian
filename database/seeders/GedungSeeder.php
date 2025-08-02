<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gedung;

class GedungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gedung::create(['nama_gedung' => 'Gedung Tulip']);
        Gedung::create(['nama_gedung' => 'Gedung Aster']);
        Gedung::create(['nama_gedung' => 'Gedung Ulin Tower']);
    }
}
