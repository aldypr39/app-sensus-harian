<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Menyimpan data kelas baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas',
        ]);

        $kelas = Kelas::create($validated);
        return response()->json($kelas, 201);
    }


    public function index()
    {
    return response()->json(Kelas::all());
    }
    /**
     * Menghapus data kelas.
     */
    public function destroy(Kelas $kelas)
    {
        try {
            $kelas->delete();
            return response()->json(['message' => 'Kelas berhasil dihapus.']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'message' => 'Gagal menghapus! Kelas ini masih digunakan oleh data ruangan.'
                ], 409);
            }
            return response()->json(['message' => 'Terjadi kesalahan pada database.'], 500);
        }
    }
}