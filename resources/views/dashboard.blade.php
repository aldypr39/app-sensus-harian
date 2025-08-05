<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dashboard Sensus Harian</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><link rel="stylesheet" href="css/base.css">
        <link rel="stylesheet" href="css/dashboard.css">
    <meta name="csrf-token" content="{{ csrf_token() }}"> ...
</head>
<body>
    <canvas id="weather-canvas" style="position:fixed;top:0;left:0;z-index:-1;"></canvas>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-info"><h1>Dashboard Ruangan: <span id="nama-ruangan">-</span></h1><P id="tanggal-hari-ini"></p></div>
            <div class="header-controls">
                <button id="btn-tambah-pasien" class="header-action-btn"><i class="fas fa-plus-circle"></i> Tambah Pasien Masuk</button>
                <a href="{{ route('manajemen.ruangan.index') }}" class="header-icon-btn" title="Panel Admin"><i class="fas fa-cogs" style="color: #e67e22;"></i></a>
                <div class="theme-switcher"><i class="fas fa-sun"></i><label class="switch"><input type="checkbox" id="theme-toggle"><span class="slider round"></span></label><i class="fas fa-moon"></i></div>
                <div class="user-profile"><span id="display-user-name">Perawat Ana</span><button class="logout-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></button></div>
            </div>
        </header>

        <nav class="main-nav">
            <ul>
                <li><a href="{{ url('/') }}" class="active">Dashboard</a></li>
                <li><a href="{{ url('/rekapitulasi') }}">Rekapitulasi</a></li>
                <li><a href="{{ url('/laporan-indikator') }}">Laporan Indikator</a></li>
            </ul>
        </nav>

        <main class="dashboard-content">
            <section class="summary-cards">
                <div class="card glass-effect"><div class="card-icon" style="background-color: var(--card-icon-bg-1);"><i class="fas fa-bed" style="color: var(--card-icon-color-1);"></i></div><div class="card-content"><h3>Tempat Tidur Tersedia</h3><p class="card-value">...</p></div></div>
                <div class="card glass-effect"><div class="card-icon" style="background-color: var(--card-icon-bg-2);"><i class="fas fa-users" style="color: var(--card-icon-color-2);"></i></div><div class="card-content"><h3>Pasien Sisa Kemarin</h3><p class="card-value">...</p></div></div>
                <div class="card glass-effect"><div class="card-icon" style="background-color: var(--card-icon-bg-3);"><i class="fas fa-user-plus" style="color: var(--card-icon-color-3);"></i></div><div class="card-content"><h3>Pasien Masuk Hari Ini</h3><p class="card-value">...</p></div></div>
                <div class="card glass-effect"><div class="card-icon" style="background-color: var(--card-icon-bg-4);"><i class="fas fa-user-minus" style="color: var(--card-icon-color-4);"></i></div><div class="card-content"><h3>Pasien Keluar Hari Ini</h3><p class="card-value">...</p></div></div>
                <div class="card glass-effect"><div class="card-icon" style="background-color: var(--card-icon-bg-5);"><i class="fas fa-hospital-user" style="color: var(--card-icon-color-5);"></i></div><div class="card-content"><h3>Jumlah Pasien Saat Ini</h3><p class="card-value">...</p></div></div>
            </section>
            
            <div class="tab-container">
                <div class="tab-nav">
                    <button class="tab-link active" data-tab="aktif"><i class="fas fa-user-clock"></i> Pasien Aktif</button>
                    <button class="tab-link" data-tab="pulang"><i class="fas fa-history"></i> Riwayat Pasien Pulang</button>
                </div>
                <div class="tab-content-wrapper">
                    <div id="aktif" class="tab-content active">
                        <div class="patient-table-section">
                            <div class="section-header">
                                <h2><i class="fas fa-user-clock"></i> Daftar Pasien yang Masih Dirawat</h2>
                            
                                <div class="table-search-wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="search-pasien-aktif" placeholder="Cari Nama / No. RM...">
                                </div>
                                <div class="kelas-filter">
                                    <label for="filter-kelas">Filter Kelas:</label>
                                    <select id="filter-kelas">
                                        <option value="">Semua</option>
                                        <option value="VVIP">VVIP</option>
                                        <option value="VIP">VIP</option>
                                        <option value="Kelas 1">Kelas 1</option>
                                        <option value="Kelas 2">Kelas 2</option>
                                        <option value="Kelas 3">Kelas 3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-container"></div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. RM</th>
                                            <th>Nama Pasien</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Tgl Masuk</th>
                                            <th>Lama Dirawat (Hari)</th>
                                            <th>Kelas Pasien</th>
                                            <th>No. TT</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabel-pasien-aktif"></tbody>                                      
                                </table>
                            </div>
                        </div>
                    <div id="pulang" class="tab-content">
                        <div class="patient-table-section">
                            <div class="section-header">
                                <h2><i class="fas fa-history"></i> Riwayat Pasien Pulang</h2>
                                <div class="header-controls-group">
                                    <div class="table-search-wrapper">
                                        <i class="fas fa-search"></i>
                                        
                                        <input type="text" id="search-riwayat-pulang" placeholder="Cari Nama / No. RM...">
                                    </div>
                                    <div class="tab-filter">
                                        <label>Tampilkan dari tgl:</label>
                                        <input type="date" id="filter-tanggal-awal">
                                        <label>sampai tgl:</label>
                                        <input type="date" id="filter-tanggal-akhir">
                                    </div>
                                </div>
                            </div>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. RM</th>
                                            <th>Nama Pasien</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Tgl Masuk</th>
                                            <th>Tgl Keluar</th>
                                            <th>Lama Dirawat (Hari)</th>
                                            <th>Kelas</th>
                                            <th>Keadaan Keluar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabel-riwayat-pasien"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div id="modal-tambah-pasien" class="modal">
        <div class="modal-content glass-effect">
            <span class="close-btn">&times;</span>
            <h2>Form Tambah Pasien Masuk</h2>
            <form id="form-pasien">
                <div class="form-group">
                    <label for="no_rm">No. RM</label>
                    <input type="text" id="no_rm" name="no_rm" required>
                </div>
                <div class="form-group">
                    <label for="nama_pasien">Nama Pasien</label>
                    <input type="text" id="nama_pasien" name="nama_pasien" required>
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="jenis_kelamin" value="L" checked> Laki-laki
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="jenis_kelamin" value="P"> Perempuan
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="tgl_masuk">Tgl & Jam Masuk</label>
                    <input type="datetime-local" id="tgl_masuk" name="tgl_masuk" required>
                </div>
                <div class="form-group">
                    <label for="asal_pasien">Asal Pasien</label>
                    <select id="asal_pasien" name="asal_pasien" required>
                        <option value="">Pilih Asal Pasien</option>
                        <option value="igd">IGD</option>
                        <option value="poli">Poli</option>
                        <option value="pindahan">Pindahan</option>
                    </select>
                    </div>
                    <div class="form-group">
                        <label for="kelas_pasien">Kelas Pasien</label>
                        <select id="kelas_pasien" name="kelas_id" required>
                            <option value="">Pilih Kelas</option>
                            <option value="VVIP">VVIP</option>
                            <option value="VIP">VIP</option>
                            <option value="Kelas 1">Kelas 1</option>
                            <option value="Kelas 2">Kelas 2</option>
                            <option value="Kelas 3">Kelas 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="no_tt">No. Tempat Tidur</label>
                    <select id="no_tt" name="tempat_tidur_id" required>
                        <option value="">Pilih Tempat Tidur</option>
                        <option value="M-02">M-02</option>
                        <option value="M-05">M-05</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Simpan</button>
            </form>
        </div>
    </div>
    <div id="modal-keluar-pasien" class="modal">
        <div class="modal-content glass-effect">
            <span class="close-btn">&times;</span>
            <h2>Form Pasien Keluar / Pindah</h2>
            <form id="form-keluar-pasien" name="form-keluar-pasien">
                <div class="form-group">
                    <label>Nama Pasien</label>
                    <p>Budi Santoso (No. RM: 123456)</p>
                </div>
                <div class="form-group">
                    <label for="tgl_keluar">Tanggal & Jam Keluar</label>
                    <input type="datetime-local" id="tgl_keluar" name="tgl_keluar">
                </div>
                <div class="form-group">
                    <label for="keadaan_keluar">Keadaan Keluar</label>
                    <select id="keadaan_keluar" name="keadaan_keluar">
                        <option value="pulang">Pulang</option>
                        <option value="aps">Pulang APS</option>
                        <option value="pindah">Pindah ke Ruang Lain</option>
                        <option value="dirujuk">Dirujuk</option>
                        <option value="meninggal">Meninggal Dunia</option>
                    </select>
                </div>
                <div class="form-group" id="tujuan_ruangan_group" style="display: none;">
                    <label for="tujuan_ruangan">Tujuan Ruangan</label>
                    <select id="tujuan_ruangan" name="tujuan_ruangan">
                        <option value="melati">Ruang Melati</option>
                        <option value="icu">Ruang ICU</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Lama Dirawat (LD)</label>
                    <p>4 Hari (Otomatis)</p>
                </div>
                <button type="submit" class="btn-submit">Simpan Data Keluar</button>
            </form>
        </div>
    </div>

    <div id="modal-konfirmasi-hapus" class="modal"><div class="modal-content small-modal glass-effect"><span class="close-btn">&times;</span><div class="modal-header"><i class="fas fa-exclamation-triangle"></i><h3>Konfirmasi Hapus</h3></div><p>Apakah Anda yakin ingin menghapus data pasien ini? Aksi ini tidak dapat dibatalkan.</p><div class="modal-actions"><button class="btn-secondary" id="btn-batal-hapus">Batal</button><button class="btn-danger" id="btn-konfirmasi-hapus">Ya, Hapus</button></div></div></div>
    
    <script src="js/main.js" type="module"></script>
    
</body>
</html>