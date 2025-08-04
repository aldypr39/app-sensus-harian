<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TempatTidur;
use App\Models\KelasPerawatan;
use Illuminate\Support\Facades\DB;

class TempatTidurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        

        // Ambil semua data kelas perawatan DAN gabungkan dengan tabel lain untuk dapat nama
        $semuaKelasPerawatan = DB::table('kelas_perawatans')
            ->join('ruangans', 'kelas_perawatans.ruangan_id', '=', 'ruangans.id')
            ->join('kelas', 'kelas_perawatans.kelas_id', '=', 'kelas.id')
            ->select(
                'kelas_perawatans.jumlah_tt',
                'ruangans.id as ruangan_id',
                'ruangans.nama_ruangan',
                'kelas.id as kelas_id',
                'kelas.nama_kelas'
            )
            ->get();

        foreach ($semuaKelasPerawatan as $kp) {
            $namaSingkatRuangan = strtok($kp->nama_ruangan, " "); // Ambil kata pertama dari nama ruangan
            for ($i = 1; $i <= $kp->jumlah_tt; $i++) {
                TempatTidur::create([
                    'ruangan_id' => $kp->ruangan_id,
                    'kelas_id'   => $kp->kelas_id,
                    'nomor_tt'   => $namaSingkatRuangan . '-' . $kp->nama_kelas . '-' . $i,
                    'status'     => 'tersedia',
                ]);
            }
        }
    }
}