<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pasien;
use App\Models\TempatTidur;
use App\Models\Ruangan;
use App\Models\Gedung;
use App\Models\Kelas;             
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

        // 4. Update status tempat tidur yang dipilih menjadi 'terisi'
        if ($pasien) {
            TempatTidur::where('ruangan_id', $pasien->ruangan_id)
                        ->where('nomor_tt', $pasien->no_tt)
                        ->update(['status' => 'terisi']);
        }

        // 5. Kembalikan respon sukses beserta data pasien yang baru dibuat
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

        // 4. Update status tempat tidur yang ditinggalkan menjadi 'tersedia'
        if ($pasien->wasChanged()) {
            TempatTidur::where('ruangan_id', $pasien->ruangan_id)
                        ->where('nomor_tt', $pasien->no_tt)
                        ->update(['status' => 'tersedia']);
        }

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

        // 2. Cek apakah tempat tidur berubah
        $tempatTidurBerubah = $validatedData['no_tt'] !== $pasien->no_tt;

        if ($tempatTidurBerubah) {
            // Jika berubah, kosongkan tempat tidur lama
            TempatTidur::where('ruangan_id', $pasien->ruangan_id)
                        ->where('nomor_tt', $pasien->no_tt)
                        ->update(['status' => 'tersedia']);
            
            // Dan isi tempat tidur yang baru
            TempatTidur::where('ruangan_id', $pasien->ruangan_id)
                        ->where('nomor_tt', $validatedData['no_tt'])
                        ->update(['status' => 'terisi']);
        }

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
            // 1. KITA TAMBAHKAN INI: Ambil data tempat tidur yang terkait dengan pasien
            $tempatTidur = TempatTidur::where('ruangan_id', $pasien->ruangan_id)
                                    ->where('nomor_tt', $pasien->no_tt)
                                    ->first();

            // 2. KITA TAMBAHKAN BLOK INI: Logika untuk memeriksa jika TT sudah terisi
            //    Jika tempat tidur ditemukan dan statusnya sudah 'terisi' oleh pasien lain
            if ($tempatTidur && $tempatTidur->status == 'terisi') {
                // Hentikan proses dan kirim pesan error
                DB::rollBack();
                return response()->json([
                    'message' => 'Gagal! Tempat tidur ' . $pasien->no_tt . ' sudah ditempati oleh pasien lain.'
                ], 409); // 409 Conflict
            }

            // 3. KITA UBAH BLOK INI: Jika tempat tidur tersedia, lanjutkan proses
            //    Kode lama Anda: if ($pasien->no_tt) { TempatTidur::where(...)->update(...); }
            if ($tempatTidur) {
                $tempatTidur->update(['status' => 'terisi']);
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
        $ruangan = $user->ruangan;

        // --- Blok untuk Admin ---
        if (!$ruangan) {
            $pasien_masuk_hari_ini = Pasien::whereDate('tgl_masuk', today())->count();
            $pasien_keluar_hari_ini = Pasien::whereDate('tgl_keluar', today())->count();
            $jumlah_pasien_saat_ini = Pasien::whereNull('tgl_keluar')->count();
            $pasien_sisa_kemarin = ($jumlah_pasien_saat_ini - $pasien_masuk_hari_ini) + $pasien_keluar_hari_ini;

            return response()->json([
                'nama_ruangan' => 'Dashboard Administrator',
                'tempat_tidur_tersedia' => TempatTidur::where('status', 'tersedia')->count(),
                'total_tempat_tidur' => TempatTidur::count(),
                'pasien_sisa_kemarin' => $pasien_sisa_kemarin,
                'pasien_masuk_hari_ini' => $pasien_masuk_hari_ini, // <-- Pastikan ini ada
                'pasien_keluar_hari_ini' => $pasien_keluar_hari_ini,
                'jumlah_pasien_saat_ini' => $jumlah_pasien_saat_ini,
            ]);
        }

        // --- Blok untuk Perawat Ruangan ---
        $ruangan_id = $ruangan->id;
        $total_tempat_tidur = TempatTidur::where('ruangan_id', $ruangan_id)->count();
        $jumlah_pasien_saat_ini = Pasien::where('ruangan_id', $ruangan_id)->whereNull('tgl_keluar')->count();
        $pasien_masuk_hari_ini = Pasien::where('ruangan_id', $ruangan_id)->whereDate('tgl_masuk', today())->count();
        $pasien_keluar_hari_ini = Pasien::where('ruangan_id', $ruangan_id)->whereDate('tgl_keluar', today())->count();
        $pasien_sisa_kemarin = ($jumlah_pasien_saat_ini - $pasien_masuk_hari_ini) + $pasien_keluar_hari_ini;

        return response()->json([
            'nama_ruangan' => $ruangan->nama_ruangan,
            'tempat_tidur_tersedia' => $total_tempat_tidur - $jumlah_pasien_saat_ini,
            'total_tempat_tidur' => $total_tempat_tidur,
            'pasien_sisa_kemarin' => $pasien_sisa_kemarin,
            'pasien_masuk_hari_ini' => $pasien_masuk_hari_ini, // <-- Pastikan ini ada
            'pasien_keluar_hari_ini' => $pasien_keluar_hari_ini,
            'jumlah_pasien_saat_ini' => $jumlah_pasien_saat_ini,
        ]);
    }

    public function getKelasTersedia()
    {
        $ruanganId = Auth::user()->ruangan_id;

        // Ambil ID kelas yang ada di ruangan ini dari tabel kelas_perawatans
        $kelasIds = KelasPerawatan::where('ruangan_id', $ruanganId)->pluck('kelas_id');
        
        // Ambil detail nama kelas dari tabel master 'kelas'
        $kelas = Kelas::whereIn('id', $kelasIds)->get(['id', 'nama_kelas']);

        return response()->json($kelas);
    }

    /**
     * Mengambil daftar tempat tidur yang tersedia berdasarkan ruangan dan kelas.
     */
    public function getTempatTidurTersedia(Request $request)
    {
        $request->validate(['kelas_id' => 'required|integer']); // Validasi input

        $ruanganId = Auth::user()->ruangan_id;
        $kelasId = $request->input('kelas_id');

        $tempatTidur = TempatTidur::where('ruangan_id', $ruanganId)
                                    ->where('kelas_id', $kelasId) // Filter berdasarkan kelas_id
                                    ->where('status', 'tersedia')
                                    ->pluck('nomor_tt');

        return response()->json($tempatTidur);
    }
}