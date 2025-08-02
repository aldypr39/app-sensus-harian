<?php

namespace App\Http\Controllers;

use App\Models\Ruangan; // <-- Impor model Ruangan
use App\Models\KelasPerawatan; // <-- Impor model KelasPerawatan
use App\Models\Gedung;
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
        // 1. Validasi data yang masuk
        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangans,nama_ruangan',
            'gedung_id' => 'required|integer|exists:gedungs,id',
            'lantai' => 'required|string|max:50',
            'classes' => 'required|array|min:1',
            'classes.*.kelas_id' => 'required|integer|exists:kelas,id',
            'classes.*.jumlah_tt' => 'required|integer|min:1',
        ]);

        // 2. Gunakan transaction untuk memastikan semua data berhasil disimpan
        DB::transaction(function () use ($validated) {
            // 3. Buat ruangan baru
            $ruangan = Ruangan::create([
                'nama_ruangan' => $validated['nama_ruangan'],
                'gedung_id' => $validated['gedung_id'],
                'lantai' => $validated['lantai'],
            ]);

            // 4. Loop dan simpan setiap kelas perawatan yang terhubung
            foreach ($validated['classes'] as $kelasData) {
                $ruangan->kelasPerawatans()->create([
                    'kelas_id' => $kelasData['kelas_id'],
                    'jumlah_tt' => $kelasData['jumlah_tt'],
                ]);
            }
        });

        // 5. Kembalikan respon sukses
        return response()->json(['message' => 'Ruangan berhasil ditambahkan!'], 201);
    }



}