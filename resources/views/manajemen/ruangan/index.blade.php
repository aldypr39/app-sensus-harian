@extends('layouts.admin')

@section('title', 'Manajemen Ruangan')

@section('content')
    <div class="section-header">
        <h1>Manajemen Ruangan Perawatan</h1>
        <button id="btn-tambah-ruangan" class="header-action-btn"><i class="fas fa-plus"></i> Tambah Ruangan Baru</button>
    </div>
    
    <div class="content-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Ruangan</th>
                        <th>Gedung</th>
                        <th>Lantai</th>
                        <th>Kelas & Kapasitas</th>
                        <th style="width: 150px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ruangans as $ruangan)
                        <tr>
                            <td><strong>{{ $ruangan->nama_ruangan }}</strong></td>
                            <td>{{ $ruangan->gedung->nama_gedung ?? 'N/A' }}</td>
                            <td>{{ $ruangan->lantai }}</td>
                            <td>
                                @foreach ($ruangan->kelasPerawatans as $kelas)
                                    <div class="kelas-item">
                                        <span class="nama-kelas">{{ $kelas->kelas->nama_kelas ?? 'N/A' }}</span>
                                        <span class="kapasitas">({{ $kelas->jumlah_tt }} TT)</span>
                                    </div>
                                @endforeach
                            </td>
                            <td style="text-align: center;">
                                <button class="btn-edit" data-id="{{ $ruangan->id }}">Edit</button>
                                <button class="btn-delete" data-id="{{ $ruangan->id }}">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">Belum ada data ruangan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal untuk Tambah/Edit Ruangan --}}
    <div id="modal-ruangan" class="modal">
        <div class="modal-content glass-effect">
            <span class="close-btn">&times;</span>
            <h2 id="modal-ruangan-title">Tambah Ruangan Baru</h2>
            <form id="form-ruangan">
                {{-- Dropdown untuk Gedung --}}
                <div class="form-group">
                    <label for="gedung_id">Gedung</label>
                    <select id="gedung_id" name="gedung_id" required>
                        <option value="">Memuat...</option>
                    </select>
                </div>

                {{-- Input untuk Lantai --}}
                <div class="form-group">
                    <label for="lantai">Lantai</label>
                    <input type="text" id="lantai" name="lantai" placeholder="Contoh: 1A, 3, Lobby" required>
                </div>
                
                {{-- Input untuk Nama Ruangan --}}
                <div class="form-group">
                    <label for="nama_ruangan">Nama Ruangan</label>
                    <input type="text" id="nama_ruangan" name="nama_ruangan" placeholder="Contoh: Ruang Anggrek" required>
                </div>

                <hr style="border: 1px solid var(--border-color); margin: 20px 0;">

                <label>Kelas Perawatan & Kapasitas</label>
                <div id="kelas-container" style="margin-top: 8px;"></div>
                <button type="button" id="btn-tambah-kelas" class="btn-tambah-kelas">
                    <i class="fas fa-plus"></i> Tambah Kelas Perawatan
                </button>
                <button type="submit" class="btn-submit">Simpan Ruangan</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/admin.js') }}" type="module"></script>
@endsection