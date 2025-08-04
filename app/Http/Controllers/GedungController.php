<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use Illuminate\Http\Request; // <-- Tambahkan ini

class GedungController extends Controller
{
    // --- TAMBAHKAN FUNGSI BARU DI BAWAH INI ---

    /**
     * Menyimpan data gedung baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'nama_gedung' => 'required|string|max:255|unique:gedungs,nama_gedung',
        ]);

        // 2. Simpan ke database
        $gedung = Gedung::create($validated);

        // 3. Kembalikan data gedung yang baru dibuat sebagai JSON
        return response()->json($gedung, 201);
    }
    /**
     * Menghapus gedung berdasarkan ID.
     */
    public function destroy(Gedung $gedung)
    {
        try {
            // Coba hapus gedung
            $gedung->delete();
            return response()->json(['message' => 'Gedung berhasil dihapus.']);

        } catch (\Illuminate\Database\QueryException $e) {
            // Jika terjadi error foreign key (gedung masih dipakai oleh ruangan)
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Gagal menghapus! Gedung ini masih digunakan oleh data ruangan lain.'
                ], 409); // 409 Conflict
            }
            // Untuk error lainnya
            return response()->json(['message' => 'Terjadi kesalahan pada database.'], 500);
        }
    }
}