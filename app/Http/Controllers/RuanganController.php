<?php

namespace App\Http\Controllers;

use App\Models\Ruangan; // <-- Impor model Ruangan
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index()
    {
        // 1. Ambil semua data ruangan dari database
        $ruangans = Ruangan::with('kelasPerawatans')->get();

        // 2. Kirim data tersebut ke view
        return view('manajemen.ruangan.index', ['ruangans' => $ruangans]);
    }
}