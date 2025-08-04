<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AkunController extends Controller
{
    // Mengambil semua data ruangan untuk dropdown
    public function getRuanganForDropdown()
    {
        return response()->json(Ruangan::orderBy('nama_ruangan')->get());
    }

    public function index()
    {
        // Ambil semua user yang role-nya 'ruangan' beserta data ruangannya
        $akuns = User::where('role', 'ruangan')->with('ruangan')->get();

        // Kirim data ke view
        return view('manajemen.akun.index', ['akuns' => $akuns]);
    }
    

    // Menyimpan akun baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'ruangan_id' => ['required', 'exists:ruangans,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'ruangan_id' => $request->ruangan_id,
            'password' => Hash::make($request->password),
            'email' => $request->username . '@sensus.local', // Email dummy
            'role' => 'ruangan',
        ]);

        return response()->json(['message' => 'Akun berhasil dibuat!', 'user' => $user], 201);
    }

    public function edit(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'ruangan_id' => ['required', 'exists:ruangans,id'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'ruangan_id' => $request->ruangan_id,
            'email' => $request->username . '@dummy.com',
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['message' => 'Akun berhasil diperbarui!']);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }
}