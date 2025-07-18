<?php

namespace App\Http\Controllers;

use App\Models\KelasPerawatan;
use App\Models\Pasien;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mengambil data statistik untuk dashboard.
     */
    public function getStats()
    {
        $user = Auth::user();

        // JIKA YANG LOGIN ADALAH ADMIN
        if ($user->role === 'admin') {
            $pasienAktif = Pasien::where('status', 'aktif')->count();
            $pasienMasukHariIni = Pasien::whereDate('tgl_masuk', Carbon::today())->count();
            $pasienKeluarHariIni = Pasien::where('status', 'keluar')->whereDate('tgl_keluar', Carbon::today())->count();
            $totalTempatTidur = KelasPerawatan::sum('jumlah_tt');

            return response()->json([
                'nama_ruangan' => 'Dashboard Administrator',
                'tempat_tidur_tersedia' => $totalTempatTidur - $pasienAktif,
                'total_tempat_tidur' => (int)$totalTempatTidur,
                'pasien_sisa_kemarin' => 0, // Placeholder
                'pasien_masuk_hari_ini' => $pasienMasukHariIni,
                'pasien_keluar_hari_ini' => $pasienKeluarHariIni,
                'jumlah_pasien_saat_ini' => $pasienAktif,
            ]);
        }
        
        // JIKA YANG LOGIN ADALAH PERAWAT RUANGAN
        else {
            $ruanganId = $user->ruangan_id;
            if (!$ruanganId) {
                return response()->json(['error' => 'User perawat tidak terikat pada ruangan'], 403);
            }
            
            $pasienAktif = Pasien::where('ruangan_id', $ruanganId)->where('status', 'aktif')->count();
            $pasienMasukHariIni = Pasien::where('ruangan_id', $ruanganId)->whereDate('tgl_masuk', Carbon::today())->count();
            $pasienKeluarHariIni = Pasien::where('ruangan_id', $ruanganId)->where('status', 'keluar')->whereDate('tgl_keluar', Carbon::today())->count();
            $totalTempatTidur = KelasPerawatan::where('ruangan_id', $ruanganId)->sum('jumlah_tt');

            return response()->json([
                'nama_ruangan' => $user->ruangan->nama_ruangan,
                'tempat_tidur_tersedia' => $totalTempatTidur - $pasienAktif,
                'total_tempat_tidur' => (int)$totalTempatTidur,
                'pasien_sisa_kemarin' => 0, // Placeholder
                'pasien_masuk_hari_ini' => $pasienMasukHariIni,
                'pasien_keluar_hari_ini' => $pasienKeluarHariIni,
                'jumlah_pasien_saat_ini' => $pasienAktif,
            ]);
        }
    }
}