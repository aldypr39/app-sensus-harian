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
                        <th>Kelas & Kapasitas</th>
                        <th style="width: 150px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ruangans as $ruangan)
                        <tr>
                            <td><strong>{{ $ruangan->nama_ruangan }}</strong></td>
                            <td>{{ $ruangan->gedung }}</td>
                            <td>
                                {{-- Loop melalui setiap kelas di dalam ruangan --}}
                                @foreach ($ruangan->kelasPerawatans as $kelas)
                                    <div class="kelas-item">
                                        <span class="nama-kelas">{{ $kelas->nama_kelas }}</span>
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
                            <td colspan="4" style="text-align: center;">Belum ada data ruangan.</td>
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
                <div class="form-group">
                    <label for="nama-ruangan">Nama Ruangan</label>
                    <input type="text" id="nama-ruangan" name="nama_ruangan" placeholder="Contoh: Ruang Anggrek" required>
                </div>
                <div id="kelas-container"></div>
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