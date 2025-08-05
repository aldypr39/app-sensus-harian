<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TempatTidur;
use App\Models\Ruangan;
use App\Models\KelasPerawatan;
use App\Models\Kelas; // <-- Tambahkan ini

class TempatTidurSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua ruangan yang ada
        $semuaRuangan = Ruangan::all();

        foreach ($semuaRuangan as $ruangan) {
            // Atur ulang penghitung tempat tidur untuk setiap ruangan baru
            $penghitungTT = 1;
            
            // Ambil semua jenis kelas yang ada di ruangan ini
            $kelasDiRuangan = KelasPerawatan::where('ruangan_id', $ruangan->id)->get();

            foreach ($kelasDiRuangan as $kp) {
                // Loop sebanyak jumlah_tt untuk setiap kelas
                for ($i = 1; $i <= $kp->jumlah_tt; $i++) {
                    TempatTidur::create([
                        'ruangan_id' => $ruangan->id,
                        'kelas_id'   => $kp->kelas_id,
                        // Buat nomor urut sederhana (01, 02, ..., 12)
                        'nomor_tt'   => str_pad($penghitungTT, 2, '0', STR_PAD_LEFT),
                        'status'     => 'tersedia',
                    ]);
                    $penghitungTT++; // Naikkan penghitung untuk nomor TT berikutnya
                }
            }
        }
    }
}