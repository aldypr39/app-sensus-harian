<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // User pertama sebagai Administrator
        User::create([
            'name' => 'Admin Utama',
            'username' => 'admin', // <-- KITA PAKAI INI UNTUK LOGIN
            'email' => 'admin@dummy.com', // <-- Email palsu, hanya untuk mengisi kolom
            'role' => 'admin',
            'password' => Hash::make('password'),
            'ruangan_id' => null,
        ]);

        // User kedua sebagai Perawat Ruangan
        User::create([
            'name' => 'Ruangan Mata',
            'username' => 'ruanganmata', // <-- KITA PAKAI INI UNTUK LOGIN
            'email' => 'ruangan.mata@dummy.com', // <-- Email palsu, hanya untuk mengisi kolom
            'role' => 'ruangan',
            'password' => Hash::make('password'),
            'ruangan_id' => 1,
        ]);
    }
}