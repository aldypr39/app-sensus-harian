document.addEventListener('DOMContentLoaded', () => {
    console.log('MATA-MATA #1: DOMContentLoaded, script main.js mulai berjalan.');
    let allActivePatients = [];
    let allDischargedPatients = [];
    // --- AWAL BAGIAN LOGIN CHECK ---
    const userJSON = localStorage.getItem('sensus_harian_current_user');
    if (!userJSON) {
        window.location.href = '/login';
        return; // Hentikan eksekusi jika tidak ada user
    }
    const currentUser = JSON.parse(userJSON);
    console.log('Selamat datang,', currentUser.name);
    const userNameSpan = document.getElementById('display-user-name');
    if (userNameSpan) {
        userNameSpan.textContent = currentUser.name;
    }
    


    // --- AWAL BAGIAN MEMUAT DATA DASHBOARD ---
    async function loadDashboardData() {
        try {
            // Pastikan fetch sudah menyertakan 'credentials'
            const response = await fetch('/dashboard-stats', {
                credentials: 'same-origin'
            });

            if (!response.ok) {
                // Jika sesi habis atau tidak valid, server akan redirect, kita ikuti
                if (response.status === 401 || response.redirected) {
                    window.location.href = '/login';
                    return;
                }
                throw new Error('Gagal memuat data statistik');
            }

            const data = await response.json();
            updateDashboardUI(data);

        } catch (error) {
            console.error('Error memuat data dashboard:', error);
            // alert('Gagal memuat data dari server.');
        }
    }

    function updateDashboardUI(data) {
        const namaRuanganEl = document.getElementById('nama-ruangan');
        if(namaRuanganEl) {
            namaRuanganEl.textContent = data.nama_ruangan;
        }

        const cardValues = document.querySelectorAll('.card-value');
        if(cardValues.length >= 5) {
            cardValues[0].textContent = `${data.tempat_tidur_tersedia} / ${data.total_tempat_tidur}`;
            cardValues[1].textContent = data.pasien_sisa_kemarin;
            cardValues[2].textContent = data.pasien_masuk_hari_ini;
            cardValues[3].textContent = data.pasien_keluar_hari_ini;
            cardValues[4].textContent = data.jumlah_pasien_saat_ini;
        }
    }

    
    // --- AKHIR BAGIAN MEMUAT DATA DASHBOARD ---
    async function loadPasienAktifTable() {
        try {
            const response = await fetch('/pasien/aktif', { credentials: 'same-origin' });
            if (!response.ok) throw new Error('Gagal memuat data pasien');

            allActivePatients = await response.json(); // Simpan data ke variabel
            displayPasienTable(allActivePatients); // Tampilkan semua data saat pertama kali dimuat
        } catch (error) {
            console.error('Error memuat tabel pasien:', error);
        }
    }

    function displayPasienTable(pasienList) {
        const tbody = document.getElementById('tabel-pasien-aktif');
        if (!tbody) return;

        tbody.innerHTML = ''; // Kosongkan tabel terlebih dahulu

        if (pasienList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;">Tidak ada pasien aktif.</td></tr>`;
            return;
        }

        pasienList.forEach((pasien, index) => {
            const tglMasuk = new Date(pasien.tgl_masuk).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
            
            const namaKelas = pasien.tempat_tidur?.kelas?.nama_kelas ?? 'N/A';
            const noTT = pasien.tempat_tidur?.nomor_tt ?? 'N/A';

            const row = `
                <tr data-tgl-masuk="${pasien.tgl_masuk}">
                    <td>${index + 1}</td>
                    <td>${pasien.no_rm}</td>
                    <td>${pasien.nama_pasien}</td>
                    <td>${pasien.jenis_kelamin}</td>
                    <td>${tglMasuk}</td>
                    <td>${pasien.lama_dirawat} hari</td>
                    <td>${namaKelas}</td>
                    <td>${noTT}</td>
                    <td>
                        <div class="actions-cell">
                        <button class="btn-action-icon edit" title="Edit Data Pasien" data-id="${pasien.id}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn-action-icon delete" title="Hapus Data Pasien" data-id="${pasien.id}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <button class="btn-action-primary discharge" title="Keluar/Pindah Pasien" data-id="${pasien.id}">
                            Keluar/Pindah
                        </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function displayRiwayatTable(riwayatList) {
        const tbody = document.getElementById('tabel-riwayat-pasien');
        if (!tbody) return;

        tbody.innerHTML = ''; // Kosongkan tabel

        // Jika tidak ada riwayat, tampilkan pesan
        if (riwayatList.length === 0) {
            // Colspan diubah menjadi 10 karena ada tambahan kolom Aksi
            tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;">Tidak ada riwayat pasien pulang.</td></tr>`;
            return;
        }

        // Loop data riwayat dan buat baris tabel
        riwayatList.forEach((pasien, index) => {
            const tglMasuk = new Date(pasien.tgl_masuk).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const tglKeluar = new Date(pasien.tgl_keluar).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            
            const namaKelas = (pasien.tempat_tidur && pasien.tempat_tidur.kelas) ? pasien.tempat_tidur.kelas.nama_kelas : 'N/A';

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${pasien.no_rm}</td>
                    <td>${pasien.nama_pasien}</td>
                    <td>${pasien.jenis_kelamin}</td>
                    <td>${tglMasuk}</td>
                    <td>${tglKeluar}</td>
                    <td>${pasien.lama_dirawat}</td>
                    <td>${namaKelas}</td>
                    <td>${pasien.keadaan_keluar}</td>
                    <td>
                        <button class="btn-aksi btn-batal-pulang" data-id="${pasien.id}" title="Batalkan status pulang pasien ini">
                            <i class="fas fa-undo"></i> Batalkan
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    // --- FUNGSI UNTUK RIWAYAT PASIEN KELUAR ---
    async function loadRiwayatPasien() {
        try {
            const response = await fetch('/pasien/riwayat', { credentials: 'same-origin' });
            if (!response.ok) throw new Error('Gagal memuat riwayat pasien');

            allDischargedPatients = await response.json(); // Simpan data ke variabel
            displayRiwayatTable(allDischargedPatients); // Tampilkan semua data saat awal
        } catch (error) {
            console.error('Error memuat riwayat pasien:', error);
        }
    }

    
    // Panggil fungsi untuk memuat data saat halaman dibuka
    loadDashboardData();
    loadPasienAktifTable();
    loadRiwayatPasien();
    

    // Helper untuk membuka/menutup modal
    const openModal = (modal) => modal.classList.add('active');
    const closeModal = (modal) => modal.classList.remove('active');

    // Referensi ke elemen-elemen modal
    const modalTambahPasien = document.getElementById('modal-tambah-pasien');
    const modalKeluarPasien = document.getElementById('modal-keluar-pasien');
    const formPasien = document.getElementById('form-pasien');
    const formKeluarPasien = document.getElementById('form-keluar-pasien');
    const btnTambahPasien = document.getElementById('btn-tambah-pasien');
    const tabelPasienAktif = document.getElementById('tabel-pasien-aktif');
    const tabContainer = document.querySelector('.tab-container');
    const modalKonfirmasiHapus = document.getElementById('modal-konfirmasi-hapus');
    const tabelRiwayatPasien = document.getElementById('tabel-riwayat-pasien');
    const selectKelas = document.getElementById('kelas_pasien'); 
    const selectTT = document.getElementById('no_tt'); 
    


    // Event listener untuk tombol "Tambah Pasien Masuk"
    if (btnTambahPasien) {
        btnTambahPasien.addEventListener('click', async () => {
            // Reset form dan judul modal
            formPasien.reset();
            formPasien.removeAttribute('data-edit-id');
            modalTambahPasien.querySelector('h2').textContent = 'Form Tambah Pasien Masuk';

            // --- INI BAGIAN KUNCINYA ---
            // 1. Ambil elemen input tanggal
            const tglMasukInput = document.getElementById('tgl_masuk');
            const now = new Date();
            
            // 2. Format tanggal ke YYYY-MM-DDTHH:mm untuk waktu lokal saat ini
            const localDateTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000))
                                    .toISOString()
                                    .slice(0, 16);
            
            // 3. Atur nilai (value) DAN batas maksimal (max)
            tglMasukInput.value = localDateTime;
            tglMasukInput.max = localDateTime;
            // --- AKHIR BAGIAN KUNCI ---

            // Tampilkan kembali form yang mungkin tersembunyi saat mode edit
            formPasien.querySelector('#kelas_pasien').closest('.form-group').style.display = 'block';
            formPasien.querySelector('#no_tt').closest('.form-group').style.display = 'block';

            // Logika untuk mengisi dropdown (tidak berubah)
            populateDropdown(selectKelas, [], 'Memuat kelas...');
            populateDropdown(selectTT, [], 'Pilih kelas terlebih dahulu');
            selectTT.disabled = true;

            openModal(modalTambahPasien);

            // Ambil data kelas dari backend
            try {
                const response = await fetch('/api/ruangan/kelas-tersedia');
                if (!response.ok) throw new Error('Gagal memuat kelas');
                const kelasList = await response.json();
                populateDropdown(selectKelas, kelasList, 'Pilih Kelas', 'id', 'nama_kelas');
            } catch (error) {
                console.error(error);
                populateDropdown(selectKelas, [], 'Gagal memuat');
            }
        });
    }

    // Event listener untuk tombol close di modal
    if (modalTambahPasien) {
        modalTambahPasien.querySelector('.close-btn').addEventListener('click', () => closeModal(modalTambahPasien));
    }

    // Event listener untuk form submit
    if (formPasien) {
        formPasien.addEventListener('submit', async (e) => {
            e.preventDefault();

            const editId = formPasien.dataset.editId; // Cek apakah kita dalam mode edit
            const formData = new FormData(formPasien);
            const data = Object.fromEntries(formData.entries());

            // Tentukan URL dan Metode berdasarkan mode
            const url = editId ? `/pasien/${editId}` : '/pasien';
            const method = editId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method, // Gunakan metode yang sudah ditentukan
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        const errorMessages = Object.values(result.errors).join('\n');
                        alert('Error Validasi:\n' + errorMessages);
                    } else {
                        throw new Error(result.message || 'Gagal menyimpan data.');
                    }
                } else {
                    alert(editId ? 'Data pasien berhasil diperbarui!' : 'Pasien berhasil ditambahkan!');
                    closeModal(document.getElementById('modal-tambah-pasien'));
                    loadPasienAktifTable();
                    loadDashboardData();
                    formPasien.removeAttribute('data-edit-id'); // Hapus penanda edit mode
                }
            } catch (error) {
                console.error('Error saat menyimpan pasien:', error);
                alert(error.message);
            }
        });
    }

    // TODO: Nanti kita tambahkan lagi event listener untuk tabel pasien, modal, dll di sini.
    if (tabelPasienAktif) {
        tabelPasienAktif.addEventListener('click', async (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            const pasienId = button.dataset.id;

            // Logika untuk tombol EDIT
            if (button.classList.contains('edit')) {
                try {
                    const response = await fetch(`/pasien/${pasienId}`, { credentials: 'same-origin' });
                    if (!response.ok) throw new Error('Gagal mengambil data pasien.');
                    const pasien = await response.json();
                    
                    formPasien.querySelector('#no_rm').value = pasien.no_rm;
                    formPasien.querySelector('#nama_pasien').value = pasien.nama_pasien;
                    formPasien.querySelector(`input[name="jenis_kelamin"][value="${pasien.jenis_kelamin}"]`).checked = true;
                    formPasien.querySelector('#tgl_masuk').value = pasien.tgl_masuk.slice(0, 16);
                    formPasien.querySelector('#asal_pasien').value = pasien.asal_pasien;
                    formPasien.querySelector('#kelas_pasien').value = pasien.kelas;
                    formPasien.querySelector('#no_tt').value = pasien.no_tt;
                    
                    modalTambahPasien.querySelector('h2').textContent = 'Edit Data Pasien';
                    formPasien.dataset.editId = pasienId;
                    openModal(modalTambahPasien);
                } catch (error) {
                    console.error('Error saat edit pasien:', error);
                    alert(error.message);
                }
            }

            // Logika untuk tombol KELUAR/PINDAH
            else if (button.classList.contains('discharge')) {
                const row = button.closest('tr');
                const no_rm = row.cells[1].textContent;
                const nama = row.cells[2].textContent;
                const tglMasukString = row.dataset.tglMasuk;

                const tglMasuk = new Date(tglMasukString);
                tglMasuk.setHours(0, 0, 0, 0);
                const hariIni = new Date();
                hariIni.setHours(0, 0, 0, 0);

                const selisihHari = (hariIni - tglMasuk) / (1000 * 60 * 60 * 24);
                const lamaDirawat = (selisihHari === 0) ? 1 : selisihHari;
                
                modalKeluarPasien.querySelector('.form-group p').textContent = `${nama} (No. RM: ${no_rm})`;
                modalKeluarPasien.querySelectorAll('.form-group p')[1].textContent = `${lamaDirawat} Hari (Otomatis)`;
                modalKeluarPasien.dataset.pasienId = pasienId;
                document.getElementById('tgl_keluar').value = new Date().toISOString().slice(0, 16);
                openModal(modalKeluarPasien);

                const tglKeluarInput = document.getElementById('tgl_keluar');
                const now = new Date();
                const maxDateTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                
                tglKeluarInput.value = maxDateTime; // Mengisi input dengan waktu sekarang
                tglKeluarInput.max = maxDateTime;  // Menetapkan batas maksimal waktu
            }

            // Logika untuk tombol DELETE
            else if (button.classList.contains('delete')) {
                modalKonfirmasiHapus.querySelector('#btn-konfirmasi-hapus').dataset.pasienId = pasienId;
                openModal(modalKonfirmasiHapus);
            }
        });
    }
    // --- AKHIR BAGIAN BARU ---

    if (tabelRiwayatPasien) {
        tabelRiwayatPasien.addEventListener('click', async (e) => {
            // Cari tombol 'batalkan' yang paling dekat dengan target klik
            const tombolBatal = e.target.closest('.btn-batal-pulang');

            if (tombolBatal) {
                const pasienId = tombolBatal.dataset.id;
                
                // Tampilkan dialog konfirmasi ke pengguna
                const isConfirmed = confirm('Anda yakin ingin membatalkan status pulang pasien ini? Data akan dikembalikan ke daftar pasien aktif.');

                if (isConfirmed) {
                    try {
                        // Kirim permintaan ke server untuk membatalkan
                        const response = await fetch(`/pasien/${pasienId}/batalkan-pulang`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message || 'Gagal membatalkan status.');
                        }

                        alert(result.message); // Tampilkan pesan sukses dari server

                        // Muat ulang semua data agar UI terupdate
                        loadDashboardData();
                        loadPasienAktifTable();
                        loadRiwayatPasien();

                    } catch (error) {
                        console.error('Error saat membatalkan status pulang:', error);
                        alert(error.message);
                    }
                }
            }
        });
    }

    // Event listener untuk form keluar pasien submit
    if (formKeluarPasien) {
        formKeluarPasien.addEventListener('submit', async (e) => {
            e.preventDefault();
            const pasienId = modalKeluarPasien.dataset.pasienId;
            
            const formData = new FormData(formKeluarPasien);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(`/pasien/${pasienId}/keluar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Gagal memproses pasien keluar.');
                }
                
                alert(result.message);
                closeModal(modalKeluarPasien);
                loadDashboardData();      // Refresh kartu statistik
                loadPasienAktifTable(); // Refresh tabel pasien
                loadRiwayatPasien();
                
            } catch (error) {
                console.error('Error saat proses keluar pasien:', error);
                alert(error.message);
            }
        });
    }

    // Event listener untuk tombol "Hapus" di tabel pasien
    if (tabContainer) {
        const tabLinks = tabContainer.querySelectorAll('.tab-link');
        const tabContents = tabContainer.querySelectorAll('.tab-content');
        tabLinks.forEach(clickedLink => {
            clickedLink.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = clickedLink.dataset.tab;
                tabLinks.forEach(link => link.classList.remove('active'));
                clickedLink.classList.add('active');
                tabContents.forEach(content => content.classList.remove('active'));
                document.getElementById(targetId)?.classList.add('active');
            });
        });
    }
    // Event listener untuk tombol konfirmasi hapus
    const btnKonfirmasiHapus = document.getElementById('btn-konfirmasi-hapus');
    if (btnKonfirmasiHapus) {
        btnKonfirmasiHapus.addEventListener('click', async () => {
            const pasienId = btnKonfirmasiHapus.dataset.pasienId;
            if (!pasienId) return;

            try {
                const response = await fetch(`/pasien/${pasienId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Gagal menghapus pasien.');

                alert(result.message);
                closeModal(modalKonfirmasiHapus);
                loadDashboardData();
                loadPasienAktifTable();
            } catch (error) {
                alert(error.message);
                console.error('Error saat hapus pasien:', error);
            }
        });
    }
    // Event listener untuk tombol batal hapus
    const btnBatalHapus = document.getElementById('btn-batal-hapus');
    if(btnBatalHapus) {
        btnBatalHapus.addEventListener('click', () => closeModal(modalKonfirmasiHapus));
    }
    // Event listener untuk tombol close di semua modal
    document.querySelectorAll('.modal .close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal(btn.closest('.modal'));
        });
    });

    // Helper function untuk mengisi dropdown
    function populateDropdown(selectElement, options, placeholder, valueKey = null, textKey = null) {
    selectElement.innerHTML = `<option value="">${placeholder}</option>`;
    if (!Array.isArray(options)) return; // Pastikan options adalah array

    options.forEach(option => {
        // Jika options adalah array objek, gunakan key. Jika tidak, gunakan option itu sendiri.
        const value = valueKey ? option[valueKey] : option;
        const text = textKey ? option[textKey] : option;
        const optionElement = document.createElement('option');
        optionElement.value = value;
        optionElement.textContent = text;
        selectElement.appendChild(optionElement);
    });
    }


    

    // B. Saat Kelas di Dropdown Dipilih
    if (selectKelas) {
        selectKelas.addEventListener('change', async () => {
            const selectedKelasId = selectKelas.value; // Sekarang kita pakai ID
            
            populateDropdown(selectTT, [], 'Memuat tempat tidur...');
            selectTT.disabled = true;

            if (!selectedKelasId) {
                populateDropdown(selectTT, [], 'Pilih kelas terlebih dahulu');
                return;
            }

            // Ambil data tempat tidur dari backend
            try {
                // Kirim kelas_id ke server
                const response = await fetch(`/api/ruangan/tempat-tidur-tersedia?kelas_id=${selectedKelasId}`);
                if (!response.ok) throw new Error('Gagal memuat tempat tidur');
                const ttList = await response.json();
                
                populateDropdown(selectTT, ttList, 'Pilih Tempat Tidur', 'id', 'nomor_tt');
                selectTT.disabled = false;
                
                if(ttList.length === 0) {
                    populateDropdown(selectTT, [], 'Tidak ada TT tersedia');
                    selectTT.disabled = true;
                }

            } catch (error)
            {
                console.error(error);
                populateDropdown(selectTT, [], 'Gagal memuat');
            }
        });
    }


    // --- AWAL KODE TOGGLE TEMA ---

    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    const themeKey = 'theme_preference';

    // Fungsi untuk menerapkan tema
    const applyTheme = (theme) => {
        if (theme === 'dark') {
            // Menggunakan .dark-theme sesuai dengan CSS Anda
            body.classList.add('dark-theme'); 
            themeToggle.checked = true;
        } else {
            body.classList.remove('dark-theme');
            themeToggle.checked = false;
        }
    };

    // Saat halaman dimuat, cek tema yang tersimpan di localStorage
    const savedTheme = localStorage.getItem(themeKey);
    if (savedTheme) {
        applyTheme(savedTheme);
    } else {
        // Tema default jika tidak ada yang tersimpan
        applyTheme('light'); 
    }

    // Event listener saat tombol toggle di-klik
    themeToggle.addEventListener('change', () => {
        const newTheme = themeToggle.checked ? 'dark' : 'light';
        applyTheme(newTheme);
        // Simpan pilihan user agar diingat saat refresh halaman
        localStorage.setItem(themeKey, newTheme);
    });

    // --- AWAL KODE FILTER ---
    const searchInput = document.getElementById('search-pasien-aktif');
    const kelasFilter = document.getElementById('filter-kelas');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedKelas = kelasFilter.value;

        const filteredData = allActivePatients.filter(pasien => {
            // Filter berdasarkan nama atau no RM
            const matchesSearch = pasien.nama_pasien.toLowerCase().includes(searchTerm) || 
                                String(pasien.no_rm).toLowerCase().includes(searchTerm);

            // Filter berdasarkan kelas
            const namaKelasPasien = pasien.tempat_tidur?.kelas?.nama_kelas;
            const matchesKelas = selectedKelas ? namaKelasPasien === selectedKelas : true;

            return matchesSearch && matchesKelas;
        });

        displayPasienTable(filteredData);
    }

    // Tambahkan event listener untuk input pencarian dan dropdown kelas
    searchInput.addEventListener('input', applyFilters);
    kelasFilter.addEventListener('change', applyFilters);

    // --- FILTER RIWAYAT ---
    const searchRiwayatInput = document.getElementById('search-riwayat-pulang');
    const tglAwalFilter = document.getElementById('filter-tanggal-awal');
    const tglAkhirFilter = document.getElementById('filter-tanggal-akhir');

    function applyRiwayatFilters() {
        const searchTerm = searchRiwayatInput.value.toLowerCase();
        const tglAwal = tglAwalFilter.value ? new Date(tglAwalFilter.value) : null;
        const tglAkhir = tglAkhirFilter.value ? new Date(tglAkhirFilter.value) : null;

        // Atur jam ke awal dan akhir hari untuk perbandingan yang akurat
        if (tglAwal) tglAwal.setHours(0, 0, 0, 0);
        if (tglAkhir) tglAkhir.setHours(23, 59, 59, 999);

        const filteredData = allDischargedPatients.filter(pasien => {
            // Filter nama atau no RM
            const matchesSearch = pasien.nama_pasien.toLowerCase().includes(searchTerm) ||
                                String(pasien.no_rm).toLowerCase().includes(searchTerm);

            // Filter tanggal keluar
            const tglKeluar = new Date(pasien.tgl_keluar);
            const matchesTanggal = (!tglAwal || tglKeluar >= tglAwal) && 
                                (!tglAkhir || tglKeluar <= tglAkhir);

            return matchesSearch && matchesTanggal;
        });

        displayRiwayatTable(filteredData);
    }

    // Tambahkan event listener untuk semua input filter
    searchRiwayatInput.addEventListener('input', applyRiwayatFilters);
    tglAwalFilter.addEventListener('change', applyRiwayatFilters);
    tglAkhirFilter.addEventListener('change', applyRiwayatFilters);

    // --- AWAL KODE LOGOUT ---
    const logoutButton = document.querySelector('.logout-btn');

    if (logoutButton) {
        logoutButton.addEventListener('click', async (e) => {
            e.preventDefault();

            try {
                const response = await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        // Mengambil token CSRF dari meta tag di HTML
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                });

                if (response.ok) {
                    // Hapus data user dari localStorage
                    localStorage.removeItem('sensus_harian_current_user');
                    // Arahkan ke halaman login
                    window.location.href = '/login';
                } else {
                    throw new Error('Gagal melakukan logout.');
                }

            } catch (error) {
                console.error('Error saat logout:', error);
                alert(error.message);
            }
        });
    }


});

