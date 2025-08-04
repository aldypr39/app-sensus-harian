<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use App\Models\KelasPerawatan;
use App\Models\TempatTidur;
use App\Models\Gedung; 
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RuanganController extends Controller
{
    public function index()
    {
        // 1. Ambil semua data ruangan dari database
        $ruangans = Ruangan::with(['gedung', 'kelasPerawatans.kelas'])->get();

        // 2. Kirim data tersebut ke view
        return view('manajemen.ruangan.index', ['ruangans' => $ruangans]);
    }

    public function store(Request $request)
    {
        // 1. Validasi data (tidak berubah)
        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangans,nama_ruangan',
            'gedung_id' => 'required|integer|exists:gedungs,id',
            'lantai' => 'required|string|max:50',
            'classes' => 'required|array|min:1',
            'classes.*.kelas_id' => 'required|integer|exists:kelas,id',
            'classes.*.jumlah_tt' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            // 2. Buat ruangan baru (tidak berubah)
            $ruangan = Ruangan::create([
                'nama_ruangan' => $validated['nama_ruangan'],
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
            ]);

            // 3. Loop dan simpan setiap kelas perawatan (tidak berubah)
            foreach ($validated['classes'] as $kelasData) {
                $kelas = Kelas::find($kelasData['kelas_id']);
                
                $kp = $ruangan->kelasPerawatans()->create([
                    'kelas_id' => $kelasData['kelas_id'],
                    'jumlah_tt' => $kelasData['jumlah_tt'],
                ]);

                // --- TAMBAHAN PENTING ---
                // 4. Buat record tempat tidur fisik berdasarkan data kelas yang baru
                for ($i = 1; $i <= $kp->jumlah_tt; $i++) {
                    TempatTidur::create([
                        'ruangan_id' => $ruangan->id,
                        'kelas_id' => $kp->kelas_id,
                        'nomor_tt' => strtok($ruangan->nama_ruangan, " ") . '-' . $kelas->nama_kelas . '-' . $i,
                    ]);
                }
            }
        });

        // 5. Kembalikan respon sukses (tidak berubah)
        return response()->json(['message' => 'Ruangan berhasil ditambahkan!'], 201);
    }


    // Mendapatkan semua gedung dan kelas untuk dropdown
    public function getAllGedungs()
    {
        return response()->json(Gedung::all());
    }

    public function getAllKelas()
    {
        return response()->json(Kelas::all());
    }


    // Mendapatkan data ruangan untuk edit
    public function edit(Ruangan $ruangan)
    {
        // Muat relasi kelas perawatannya juga
        $ruangan->load('kelasPerawatans.kelas');
        return response()->json($ruangan);
    }

    /**
     * Menyimpan perubahan pada ruangan yang sudah ada.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangans,nama_ruangan,' . $ruangan->id,
            'gedung_id' => 'required|integer|exists:gedungs,id',
            'lantai' => 'required|string|max:50',
            'classes' => 'required|array|min:1',
            'classes.*.kelas_id' => 'required|integer|exists:kelas,id',
            'classes.*.jumlah_tt' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $ruangan) {
            // 1. Update data utama ruangan
            $ruangan->update([
                'nama_ruangan' => $validated['nama_ruangan'],
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
            ]);

            // --- AWAL KODE BARU ---
            // 2. Hapus semua data lama yang terkait
            TempatTidur::where('ruangan_id', $ruangan->id)->delete();
            $ruangan->kelasPerawatans()->delete();
            // --- AKHIR KODE BARU ---

            // 3. Buat ulang data kelas perawatan dengan data yang baru
            foreach ($validated['classes'] as $kelasData) {
                $kelas = Kelas::find($kelasData['kelas_id']);
                
                $kp = $ruangan->kelasPerawatans()->create([
                    'kelas_id' => $kelasData['kelas_id'],
                    'jumlah_tt' => $kelasData['jumlah_tt'],
                ]);

                // 4. Buat ulang data tempat tidur fisik berdasarkan data kelas yang baru
                for ($i = 1; $i <= $kp->jumlah_tt; $i++) {
                    TempatTidur::create([
                        'ruangan_id' => $ruangan->id,
                        'kelas_id' => $kp->kelas_id,
                        'nomor_tt' => $kelas->nama_kelas . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Ruangan berhasil diperbarui!']);
    }

    /**
     * Menghapus ruangan yang sudah ada.
     */
    public function destroy(Ruangan $ruangan)
    {
        // Karena kita sudah mengatur onDelete('cascade') di migrasi,
        // maka semua kelas perawatan, tempat tidur, dan pasien yang terkait
        // akan ikut terhapus secara otomatis.
        $ruangan->delete();

        return response()->json(['message' => 'Ruangan berhasil dihapus!']);
    }

}