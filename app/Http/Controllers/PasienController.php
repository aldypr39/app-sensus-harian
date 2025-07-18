<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\TempatTidur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\KelasPerawatan;

/**
 * Controller untuk mengelola pasien.
 */

class PasienController extends Controller
{
    public function getActivePatients()
    {
        $user = Auth::user();

        // Query dasar untuk pasien aktif
        $query = Pasien::where('status', 'aktif');

        // Jika user bukan admin, filter berdasarkan ruangannya
        if ($user->role !== 'admin') {
            $query->where('ruangan_id', $user->ruangan_id);
        }

        // Ambil data dan urutkan berdasarkan tanggal masuk terbaru
        $pasiens = $query->orderBy('tgl_masuk', 'desc')->get();

        // Hitung lama dirawat untuk setiap pasien sebelum dikirim
        $pasiens->each(function ($pasien) {
        $tglMasuk = Carbon::parse($pasien->tgl_masuk)->startOfDay();
        $hariIni = Carbon::now()->startOfDay();

        $selisihHari = $tglMasuk->diffInDays($hariIni);

        // Terapkan aturan baru: jika selisih 0, jadikan 1. Jika tidak, gunakan selisihnya.
        $pasien->lama_dirawat = ($selisihHari == 0) ? 1 : $selisihHari;
    });

        // Kembalikan data sebagai JSON
        return response()->json($pasiens);
    }

    // Mendapatkan detail pasien untuk edit
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $validatedData = $request->validate([
            'no_rm' => 'required|string|unique:pasiens,no_rm',
            'nama_pasien' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tgl_masuk' => 'required|date',
            'asal_pasien' => 'required|string',
            'kelas' => 'required|string',
            'no_tt' => 'required|string',
        ]);

        // 2. Tambahkan ruangan_id dan status
        $validatedData['ruangan_id'] = Auth::user()->ruangan_id;
        $validatedData['status'] = 'aktif';

        // 3. Simpan ke database
        $pasien = Pasien::create($validatedData);

        // 4. Kembalikan respon sukses beserta data pasien yang baru dibuat
        return response()->json($pasien, 201); // 201 = Created
    }

    // Proses keluar pasien
    public function discharge(Request $request, Pasien $pasien)
    {
        // 1. Validasi data yang masuk dari form
        $validated = $request->validate([
            'tgl_keluar' => 'required|date',
            'keadaan_keluar' => 'required|string',
        ]);

        // 2. Hitung lama dirawat final menggunakan fungsi yang sudah kita buat
        $lamaDirawat = Pasien::hitungLamaDirawat($pasien->tgl_masuk, $validated['tgl_keluar']);

        // 3. Update data pasien tersebut di database
        $pasien->status = 'keluar';
        $pasien->tgl_keluar = $validated['tgl_keluar'];
        $pasien->keadaan_keluar = $validated['keadaan_keluar'];
        $pasien->lama_dirawat = $lamaDirawat;

        // 4. Simpan perubahan
        $pasien->save();

        // 5. Kembalikan respon sukses
        return response()->json([
            'message' => 'Pasien berhasil dicatat keluar.',
            'pasien' => $pasien 
        ]);
    }

    // Mendapatkan riwayat pasien yang sudah keluar
    public function getDischargedPatients()
    {
        $user = Auth::user();
        $query = Pasien::where('status', 'keluar');

        if ($user->role !== 'admin') {
            $query->where('ruangan_id', $user->ruangan_id);
        }

        // Ambil riwayat 30 hari terakhir sebagai default
        $pasiens = $query->where('tgl_keluar', '>=', Carbon::now()->subDays(30))
                        ->orderBy('tgl_keluar', 'desc')
                        ->get();

        return response()->json($pasiens);
    }

    // Mendapatkan detail pasien untuk edit
    // Kita gunakan Route-Model Binding untuk otomatis mencari pasien berdasarkan ID
    public function show(Pasien $pasien)
    {
        
        return response()->json($pasien);
    }

    // Menyimpan perubahan pasien (update)
    public function update(Request $request, Pasien $pasien)
    {
        // 1. Validasi data (mirip seperti store, tapi aturan 'unique' diubah)
        $validatedData = $request->validate([
            // no_rm harus unik, KECUALI untuk ID pasien ini sendiri
            'no_rm' => 'required|string|unique:pasiens,no_rm,' . $pasien->id,
            'nama_pasien' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tgl_masuk' => 'required|date',
            'asal_pasien' => 'required|string',
            'kelas' => 'required|string',
            'no_tt' => 'required|string',
        ]);

        // 2. Update data pasien dengan data yang divalidasi
        $pasien->update($validatedData);

        // 3. Kembalikan respon sukses
        return response()->json([
            'message' => 'Data pasien berhasil diperbarui!',
            'pasien' => $pasien
        ]);
    }

    public function destroy(Pasien $pasien)
    {
        // Hapus data pasien yang ditemukan berdasarkan ID dari URL
        $pasien->delete();

        // Kembalikan respon sukses
        return response()->json(['message' => 'Data pasien berhasil dihapus secara permanen.']);
    }

    public function batalkanPulang($id)
    {
        DB::beginTransaction();

        try {
            $pasien = Pasien::findOrFail($id);

            // 1. Update status tempat tidur menjadi 'terisi'
            if ($pasien->no_tt) {
                TempatTidur::where('nomor_tt', $pasien->no_tt)
                            ->where('ruangan_id', $pasien->ruangan_id)
                            ->update(['status' => 'terisi']);
            }

            // 2. Kosongkan data keluar dan ubah status pasien
            $pasien->status = 'aktif'; // <-- TAMBAHKAN BARIS INI
            $pasien->tgl_keluar = null;
            $pasien->keadaan_keluar = null;
            $pasien->lama_dirawat = null;
            $pasien->save();

            DB::commit();

            return response()->json(['message' => 'Status pulang pasien berhasil dibatalkan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan di server: ' . $e->getMessage()], 500);
        }
    }

    public function getDashboardStats()
    {
        $user = Auth::user();
        $ruangan = $user->ruangan; // Mengambil data ruangan dari relasi User

        // Jika user tidak terhubung ke ruangan (misal: admin)
        if (!$ruangan) {
            return response()->json([
                'nama_ruangan' => 'Dashboard Administrator',
                // Di sini Anda bisa menghitung total data dari semua ruangan
                'tempat_tidur_tersedia' => TempatTidur::where('status', 'tersedia')->count(),
                'total_tempat_tidur' => TempatTidur::count(),
                'pasien_sisa_kemarin' => 0, // Logika ini perlu dibuat nanti
                'pasien_masuk_hari_ini' => Pasien::whereDate('tgl_masuk', today())->count(),
                'pasien_keluar_hari_ini' => Pasien::whereDate('tgl_keluar', today())->count(),
                'jumlah_pasien_saat_ini' => Pasien::whereNull('tgl_keluar')->count(),
            ]);
        }

        // Jika user adalah perawat ruangan, filter berdasarkan ruangannya
        $ruangan_id = $ruangan->id;

        // Hitung statistik khusus untuk ruangan tersebut
        $total_tempat_tidur = TempatTidur::where('ruangan_id', $ruangan_id)->count();
        $jumlah_pasien_saat_ini = Pasien::where('ruangan_id', $ruangan_id)->whereNull('tgl_keluar')->count();

        return response()->json([
            'nama_ruangan' => $ruangan->nama_ruangan, // Mengirim nama ruangan yang benar
            'tempat_tidur_tersedia' => $total_tempat_tidur - $jumlah_pasien_saat_ini,
            'total_tempat_tidur' => $total_tempat_tidur,
            'pasien_sisa_kemarin' => 0, // Logika ini perlu dibuat nanti
            'pasien_masuk_hari_ini' => Pasien::where('ruangan_id', $ruangan_id)->whereDate('tgl_masuk', today())->count(),
            'pasien_keluar_hari_ini' => Pasien::where('ruangan_id', $ruangan_id)->whereDate('tgl_keluar', today())->count(),
            'jumlah_pasien_saat_ini' => $jumlah_pasien_saat_ini,
        ]);
    }

    public function getKelasTersedia()
    {
        $ruanganId = Auth::user()->ruangan_id;

        $kelas = KelasPerawatan::where('ruangan_id', $ruanganId)
                                ->distinct()
                                ->pluck('nama_kelas');

        return response()->json($kelas);
    }

    /**
     * Mengambil daftar tempat tidur yang tersedia berdasarkan ruangan dan kelas.
     */
    public function getTempatTidurTersedia(Request $request)
    {
        // Validasi input kelas
        $request->validate(['kelas' => 'required|string']);

        $ruanganId = Auth::user()->ruangan_id;
        $kelas = $request->input('kelas');

        $tempatTidur = TempatTidur::where('ruangan_id', $ruanganId)
                                    ->where('kelas', $kelas)
                                    ->where('status', 'tersedia')
                                    ->pluck('nomor_tt');

        return response()->json($tempatTidur);
    }
}