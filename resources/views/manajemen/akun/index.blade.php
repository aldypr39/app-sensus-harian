@extends('layouts.admin')

@section('title', 'Manajemen Akun')

@section('content')
    <div class="section-header">
        <h1>Manajemen Akun Perawat</h1>
        <button id="btn-tambah-akun" class="header-action-btn">
            <i class="fas fa-plus"></i> Buat Akun Baru
        </button>
    </div>

    <div class="content-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama User</th>
                        <th>Username</th>
                        <th>Ruangan yang Ditugaskan</th>
                        <th style="width: 150px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($akuns as $akun)
                        <tr>
                            <td><strong>{{ $akun->name }}</strong></td>
                            <td>{{ $akun->username }}</td>
                            <td>{{ $akun->ruangan->nama_ruangan ?? 'Belum Ditugaskan' }}</td>
                            <td style="text-align: center;">
                                <div class="action-buttons">
                                    <button class="btn-action-icon edit" data-id="{{ $akun->id }}" title="Edit Akun">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn-action-icon delete" data-id="{{ $akun->id }}" title="Hapus Akun">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center;">Belum ada akun perawat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
            <div id="modal-akun" class="modal">
            <div class="modal-content glass-effect">
                <span class="close-btn">&times;</span>
                <h2 id="modal-akun-title">Buat Akun Baru</h2>
                <form id="form-akun">
                    <div class="form-group">
                        <label for="name">Nama User</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="ruangan_id">Tugaskan ke Ruangan</label>
                        <select id="ruangan_id" name="ruangan_id" required>
                            <option value="">Memuat...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn-submit">Simpan Akun</button>
                </form>
            </div>
        </div>
@endsection

@section('scripts')
    {{-- Kita akan gunakan file js terpisah agar lebih rapi --}}
    <script src="{{ asset('js/admin_akun.js') }}" type="module"></script>
@endsection