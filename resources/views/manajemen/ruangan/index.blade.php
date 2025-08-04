@extends('layouts.admin')

@section('title', 'Manajemen Ruangan')

@section('content')
    <div class="section-header">
        <h1>Manajemen Ruangan Perawatan</h1>
        <div class="header-actions">
            <button id="btn-pengaturan-master" class="header-icon-btn" title="Pengaturan Data Master">
                <i class="fas fa-cog"></i>
            </button>
            <button id="btn-tambah-ruangan" class="header-action-btn">
                <i class="fas fa-plus"></i> Tambah Ruangan Baru
            </button>
        </div>
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
                                <div class="action-buttons">
                                    <button class="btn-action-icon edit" data-id="{{ $ruangan->id }}" title="Edit Ruangan">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn-action-icon delete" data-id="{{ $ruangan->id }}" title="Hapus Ruangan">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
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

    <div id="modal-master" class="modal">
        <div class="modal-content glass-effect">
            <span class="close-btn">&times;</span>
            <h2>Pengaturan Data Master</h2>

            <div class="tab-container-master">
                <div class="tab-nav-master">
                    <button class="tab-link-master active" data-tab="master-gedung">Manajemen Gedung</button>
                    <button class="tab-link-master" data-tab="master-kelas">Manajemen Kelas</button>
                </div>

                <div class="tab-content-wrapper-master">
                    {{-- Tab untuk Gedung --}}
                    <div id="master-gedung" class="tab-content-master active">
                        <form id="form-tambah-gedung" class="master-form">
                            <input type="text" name="nama_gedung" placeholder="Ketik nama gedung baru..." required>
                            <button type="submit">Tambah Gedung</button>
                        </form>
                        <ul id="list-gedung" class="master-list">
                            {{-- Daftar gedung akan diisi oleh JavaScript --}}
                        </ul>
                    </div>

                    {{-- Tab untuk Kelas --}}
                    <div id="master-kelas" class="tab-content-master">
                        <form id="form-tambah-kelas" class="master-form">
                            <input type="text" name="nama_kelas" placeholder="Ketik nama kelas baru..." required>
                            <button type="submit">Tambah Kelas</button>
                        </form>
                        <ul id="list-kelas" class="master-list">
                            {{-- Daftar kelas akan diisi oleh JavaScript --}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{ asset('js/admin.js') }}" type="module"></script>
@endsection