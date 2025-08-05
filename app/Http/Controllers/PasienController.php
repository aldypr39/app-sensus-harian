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
        $query = Pasien::where('status', 'aktif')->with('tempatTidur.ruangan', 'tempatTidur.kelas');

        // Jika user bukan admin, filter berdasarkan ruangannya
        if ($user->role !== 'admin') {
            $ruanganId = $user->ruangan_id;
            // Gunakan whereHas untuk filter berdasarkan relasi
            $query->whereHas('tempatTidur', function ($q) use ($ruanganId) {
                $q->where('ruangan_id', $ruanganId);
            });
        }

    $pasiens = $query->latest('tgl_masuk')->get();

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
            'tempat_tidur_id' => 'required|integer|exists:tempat_tidurs,id',
        ]);

        $validatedData['status'] = 'aktif';
        $pasien = Pasien::create($validatedData);

        

        // 4. Update status tempat tidur yang dipilih menjadi 'terisi'
        if ($pasien) {
            TempatTidur::where('id', $pasien->tempat_tidur_id)
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
        'tgl_keluar' => 'required|date|after_or_equal:' . $pasien->tgl_masuk,
        'keadaan_keluar' => 'required|string',
         ]);

        // 2. Hitung lama dirawat final menggunakan fungsi yang sudah kita buat
        $tglMasuk = new Carbon($pasien->tgl_masuk);
        $tglKeluar = new Carbon($validated['tgl_keluar']);
        // Hitung selisih hari, jika hasilnya 0 maka dianggap 1 hari
        $lamaDirawat = $tglMasuk->startOfDay()->diffInDays($tglKeluar->startOfDay()) ?: 1;

        
        TempatTidur::where('id', $pasien->tempat_tidur_id)
                   ->update(['status' => 'tersedia']);
        
        // 3. Update data pasien tersebut di database
        $pasien->status = 'keluar';
        $pasien->tgl_keluar = $validated['tgl_keluar'];
        $pasien->keadaan_keluar = $validated['keadaan_keluar'];
        $pasien->lama_dirawat = $lamaDirawat;

        // 4. Simpan perubahan
        $pasien->save();

        // 4. Update status tempat tidur yang ditinggalkan menjadi 'tersedia'
        

        // 5. Kembalikan respon sukses
        return response()->json([
            'message' => 'Pasien berhasil dicatat keluar.',
            'pasien' => $pasien 
        ]);
    }

    // Batalkan status pulang pasien
    public function batalkanPulang($id)
    {
        DB::beginTransaction();

        try {
            $pasien = Pasien::findOrFail($id);

            // 1. Update status tempat tidur menjadi 'terisi'
            // 1. KITA TAMBAHKAN INI: Ambil data tempat tidur yang terkait dengan pasien
             $tempatTidur = TempatTidur::find($pasien->tempat_tidur_id);

            // 2. KITA TAMBAHKAN BLOK INI: Logika untuk memeriksa jika TT sudah terisi
            //    Jika tempat tidur ditemukan dan statusnya sudah 'terisi' oleh pasien lain
            if ($tempatTidur && $tempatTidur->status == 'terisi') {
                // Hentikan proses dan kirim pesan error
                DB::rollBack();
                return response()->json([
                    'message' => 'Gagal! Tempat tidur ' . $tempatTidur->nomor_tt . ' sudah ditempati oleh pasien lain.'
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

    // Mendapatkan riwayat pasien yang sudah keluar
    public function getDischargedPatients()
    {
        $user = Auth::user();
        $query = Pasien::where('status', 'keluar')->with('tempatTidur.ruangan', 'tempatTidur.kelas');

        if ($user->role !== 'admin') {
            $ruanganId = $user->ruangan_id;
            // Gunakan whereHas untuk filter berdasarkan relasi
            $query->whereHas('tempatTidur', function ($q) use ($ruanganId) {
                $q->where('ruangan_id', $ruanganId);
            });
        }

        $pasiens = $query->latest('tgl_keluar')->get();

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
        // 1. Validasi data yang masuk
        $validatedData = $request->validate([
            'no_rm' => 'required|string|unique:pasiens,no_rm,' . $pasien->id,
            'nama_pasien' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tgl_masuk' => 'required|date',
            'asal_pasien' => 'required|string',
            'tempat_tidur_id' => 'required|integer|exists:tempat_tidurs,id',
        ]);

        // 2. Cek apakah tempat tidur benar-benar berubah
        $tempatTidurBerubah = $validatedData['tempat_tidur_id'] != $pasien->tempat_tidur_id;

        if ($tempatTidurBerubah) {
            // Jika berubah, kosongkan tempat tidur LAMA
            TempatTidur::where('id', $pasien->tempat_tidur_id)
                        ->update(['status' => 'tersedia']);
            
            // Dan isi tempat tidur BARU
            TempatTidur::where('id', $validatedData['tempat_tidur_id'])
                        ->update(['status' => 'terisi']);
        }

        // 3. Update data pasien dengan data yang divalidasi
        $pasien->update($validatedData);

        // 4. Kembalikan respon sukses
        return response()->json([
            'message' => 'Data pasien berhasil diperbarui!',
            'pasien' => $pasien
        ]);
    }

    public function destroy(Pasien $pasien)
    {
        // Hapus data pasien yang ditemukan berdasarkan ID dari URL
         // 1. Simpan dulu ID tempat tidur yang akan ditinggalkan
        $tempatTidurId = $pasien->tempat_tidur_id;

        // 2. Hapus data pasien
        $pasien->delete();

        // 3. Update status tempat tidur yang ditinggalkan menjadi 'tersedia'
        if ($tempatTidurId) {
            TempatTidur::where('id', $tempatTidurId)
                    ->update(['status' => 'tersedia']);
        }

        // Kembalikan respon sukses
        return response()->json(['message' => 'Data pasien berhasil dihapus secara permanen.']);
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

            // Hitung statistik dengan filter melalui relasi
            $pasien_masuk_hari_ini = Pasien::whereHas('tempatTidur', function ($q) use ($ruangan_id) {
                $q->where('ruangan_id', $ruangan_id);
            })->whereDate('tgl_masuk', today())->count();

            $pasien_keluar_hari_ini = Pasien::whereHas('tempatTidur', function ($q) use ($ruangan_id) {
                $q->where('ruangan_id', $ruangan_id);
            })->whereDate('tgl_keluar', today())->count();

            $jumlah_pasien_saat_ini = Pasien::whereHas('tempatTidur', function ($q) use ($ruangan_id) {
                $q->where('ruangan_id', $ruangan_id);
            })->whereNull('tgl_keluar')->count();
            
            $pasien_sisa_kemarin = ($jumlah_pasien_saat_ini - $pasien_masuk_hari_ini) + $pasien_keluar_hari_ini;
            
            $total_tempat_tidur = TempatTidur::where('ruangan_id', $ruangan_id)->count();

            return response()->json([
                'nama_ruangan' => $ruangan->nama_ruangan,
                'tempat_tidur_tersedia' => $total_tempat_tidur - $jumlah_pasien_saat_ini,
                'total_tempat_tidur' => $total_tempat_tidur,
                'pasien_sisa_kemarin' => $pasien_sisa_kemarin,
                'pasien_masuk_hari_ini' => $pasien_masuk_hari_ini,
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
                                    ->where('kelas_id', $kelasId)
                                    ->where('status', 'tersedia')
                                    ->get(['id', 'nomor_tt']);

        return response()->json($tempatTidur);
    }
}