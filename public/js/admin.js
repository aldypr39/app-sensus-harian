import { showConfirm, showNotification } from './modules/utils.js';

document.addEventListener('DOMContentLoaded', () => {
    // Pastikan skrip ini hanya berjalan di halaman manajemen ruangan
    const formRuangan = document.getElementById('form-ruangan');
    if (!formRuangan) return;

    // --- Referensi Elemen & Variabel Global ---
    const modalRuangan = document.getElementById('modal-ruangan');
    const btnTambahRuangan = document.getElementById('btn-tambah-ruangan');
    const btnTambahKelas = document.getElementById('btn-tambah-kelas');
    const kelasContainer = document.getElementById('kelas-container');
    const modalTitle = document.getElementById('modal-ruangan-title');
    const tableBody = document.querySelector('.table-container tbody');
    const closeBtn = modalRuangan.querySelector('.close-btn');
    
    let currentEditId = null; // Untuk membedakan mode Tambah vs Edit
    let masterGedung = [];
    let masterKelas = [];

    // --- Fungsi Helper ---
    const openModal = () => modalRuangan.classList.add('active');
    const closeModal = () => modalRuangan.classList.remove('active');

    function populateDropdown(selectElement, options, valueKey, textKey, selectedValue = null) {
        selectElement.innerHTML = '';
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option[valueKey];
            opt.textContent = option[textKey];
            if (option[valueKey] == selectedValue) {
                opt.selected = true;
            }
            selectElement.appendChild(opt);
        });
    }
    
    function createKelasRow(kelasId = null, jumlah = '') {
        const row = document.createElement('div');
        row.className = 'kelas-row';

        const select = document.createElement('select');
        select.name = 'kelas_id';
        populateDropdown(select, masterKelas, 'id', 'nama_kelas', kelasId);

        const inputJumlah = document.createElement('input');
        inputJumlah.type = 'number';
        inputJumlah.name = 'jumlah_tt';
        inputJumlah.placeholder = 'Jumlah TT';
        inputJumlah.min = 1;
        inputJumlah.required = true;
        inputJumlah.value = jumlah;

        const btnHapus = document.createElement('button');
        btnHapus.type = 'button';
        btnHapus.className = 'btn-hapus-kelas';
        btnHapus.textContent = '−';
        btnHapus.addEventListener('click', () => row.remove());

        row.appendChild(select);
        row.appendChild(inputJumlah);
        row.appendChild(btnHapus);
        return row;
    }

    // --- Logika Utama ---

    // Fungsi untuk memuat data master (gedung & kelas)
    async function loadMasterData(force = false) {
        if (masterGedung.length > 0 && masterKelas.length > 0 && !force) return; // Jangan muat ulang jika sudah ada
        try {
            const [gedungRes, kelasRes] = await Promise.all([
                fetch('/api/master/gedungs'),
                fetch('/api/master/kelas')
            ]);
            masterGedung = await gedungRes.json();
            masterKelas = await kelasRes.json();
        } catch (error) {
            console.error("Gagal memuat data master:", error);
            alert("Gagal memuat data master untuk form.");
        }
    }

    // 1. Event listener untuk tombol "Tambah Ruangan Baru"
    btnTambahRuangan.addEventListener('click', async () => {
        currentEditId = null; // Set mode ke "Tambah"
        modalTitle.textContent = 'Tambah Ruangan Baru';
        formRuangan.reset();
        await loadMasterData(); // Muat data master

        populateDropdown(formRuangan.querySelector('#gedung_id'), masterGedung, 'id', 'nama_gedung');
        kelasContainer.innerHTML = '';
        kelasContainer.appendChild(createKelasRow());
        openModal();
    });

    // 2. Event listener untuk Aksi di Tabel (Edit & Hapus)
    tableBody.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = btn.dataset.id;

        // Jika tombol EDIT diklik
        if (btn.classList.contains('edit')) {
            currentEditId = id; // Set mode ke "Edit"
            modalTitle.textContent = 'Edit Ruangan';
            formRuangan.reset();
            await loadMasterData();

            // Ambil data ruangan yang akan diedit dari server
            try {
                const response = await fetch(`/manajemen/ruangan/${id}/edit`);
                const ruanganData = await response.json();
                
                // Isi form dengan data yang ada
                populateDropdown(formRuangan.querySelector('#gedung_id'), masterGedung, 'id', 'nama_gedung', ruanganData.gedung_id);
                formRuangan.querySelector('#lantai').value = ruanganData.lantai;
                formRuangan.querySelector('#nama_ruangan').value = ruanganData.nama_ruangan;
                
                kelasContainer.innerHTML = '';
                ruanganData.kelas_perawatans.forEach(kp => {
                    kelasContainer.appendChild(createKelasRow(kp.kelas_id, kp.jumlah_tt));
                });

                openModal();
            } catch (error) {
                console.error('Gagal mengambil data ruangan:', error);
                alert('Gagal mengambil data ruangan.');
            }
        }
        
        // Jika tombol HAPUS diklik
        if (btn.classList.contains('delete')) {
            if (await showConfirm('Anda Yakin?', 'Ruangan dan semua data terkait akan dihapus permanen!')) {
                try {
                    const response = await fetch(`/manajemen/ruangan/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ '_method': 'DELETE' })
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message);
                    showNotification('Berhasil!', result.message, 'success');
                    btn.closest('tr').remove();
                } catch (error) {
                    showNotification('Gagal!', error.message, 'error');
                }
            }
        }
    });

    // 3. Event listener untuk "Tambah Kelas" di dalam modal
    btnTambahKelas.addEventListener('click', () => {
        if (masterKelas.length > 0) {
            kelasContainer.appendChild(createKelasRow());
        } else {
            alert('Data kelas belum termuat, silakan buka ulang form.');
        }
    });

    // 4. Event listener untuk SUBMIT FORM (bisa untuk Tambah & Edit)
    formRuangan.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(formRuangan);
        const ruanganData = {
            gedung_id: formData.get('gedung_id'),
            lantai: formData.get('lantai'),
            nama_ruangan: formData.get('nama_ruangan'),
            classes: []
        };
        
        const kelasRows = kelasContainer.querySelectorAll('.kelas-row');
        kelasRows.forEach(row => {
            ruanganData.classes.push({
                kelas_id: row.querySelector('[name="kelas_id"]').value,
                jumlah_tt: row.querySelector('[name="jumlah_tt"]').value
            });
        });

        // Tentukan URL dan method berdasarkan mode (Tambah atau Edit)
        let url = '/manajemen/ruangan';
        const method = 'POST'; // Selalu gunakan POST

        if (currentEditId) {
            url = `/manajemen/ruangan/${currentEditId}`;
            ruanganData._method = 'PUT'; // Tambahkan _method spoofing untuk edit
        }
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(ruanganData)
            });
            
            const result = await response.json();

            if (!response.ok) {
                if(response.status === 422) {
                    const errors = Object.values(result.errors).map(err => `- ${err[0]}`).join('\n');
                    showNotification('Validasi Gagal', errors, 'error');
                } else { throw new Error(result.message || 'Gagal menyimpan data'); }
            } else {
                showNotification('Berhasil!', result.message, 'success');
                closeModal();
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            console.error("Error saat menyimpan:", error);
            alert("Terjadi kesalahan. Silakan coba lagi.");
        }
    });

    // 5. Tutup modal
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modalRuangan) closeModal();
    });


    // --- AWAL KODE MANAJEMEN DATA MASTER ---

    const btnPengaturanMaster = document.getElementById('btn-pengaturan-master');
    const modalMaster = document.getElementById('modal-master');

    if (btnPengaturanMaster && modalMaster) {
        const closeMasterBtn = modalMaster.querySelector('.close-btn');
        const tabLinks = modalMaster.querySelectorAll('.tab-link-master');
        const tabContents = modalMaster.querySelectorAll('.tab-content-master');
        const formTambahGedung = document.getElementById('form-tambah-gedung');
        const formTambahKelas = document.getElementById('form-tambah-kelas');
        const listGedung = document.getElementById('list-gedung');
        const listKelas = document.getElementById('list-kelas');

        const openMasterModal = () => modalMaster.classList.add('active');
        const closeMasterModal = () => modalMaster.classList.remove('active');

        // Fungsi untuk memuat dan menampilkan daftar
        async function loadAndRenderList(url, listElement, nameKey, deleteUrlPrefix) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                listElement.innerHTML = ''; // Kosongkan list
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <span>${item[nameKey]}</span>
                        <button class="btn-hapus-master" data-id="${item.id}" title="Hapus item ini">&times;</button>
                    `;
                    listElement.appendChild(li);
                });
            } catch (error) {
                console.error(`Gagal memuat data dari ${url}:`, error);
                listElement.innerHTML = '<li>Gagal memuat data.</li>';
            }
        }

        // Event listener untuk membuka modal
        btnPengaturanMaster.addEventListener('click', () => {
            openMasterModal();
            // Muat data untuk tab yang aktif saat modal dibuka
            loadAndRenderList('/api/master/gedungs', listGedung, 'nama_gedung', '/api/master/gedungs/');
            loadAndRenderList('/api/master/kelas', listKelas, 'nama_kelas', '/api/master/kelas/');
        });

        // Event listener untuk menutup modal
        closeMasterBtn.addEventListener('click', closeMasterModal);
        window.addEventListener('click', (e) => {
            if (e.target === modalMaster) closeMasterModal();
        });

        // Event listener untuk navigasi tab
        tabLinks.forEach(link => {
            link.addEventListener('click', () => {
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                link.classList.add('active');
                document.getElementById(link.dataset.tab).classList.add('active');
            });
        });

        // Event listener untuk form Tambah Gedung
        formTambahGedung.addEventListener('submit', async (e) => {
            e.preventDefault();
            const namaGedung = e.target.elements.nama_gedung.value;
            try {
                const response = await fetch('/api/master/gedungs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ nama_gedung: namaGedung })
                });
                if (!response.ok) throw new Error('Gagal menyimpan gedung.');
                e.target.reset(); // Kosongkan form
                loadAndRenderList('/api/master/gedungs', listGedung, 'nama_gedung', '/api/master/gedungs/'); // Refresh list
                loadMasterData(true);
            } catch (error) {
                alert(error.message);
            }
        });

        // Event listerner untuk menghapus list gedung
        listGedung.addEventListener('click', async (e) => {
            // Pastikan yang diklik adalah tombol hapus
            if (e.target.classList.contains('btn-hapus-master')) {
                const id = e.target.dataset.id;
                
                if (confirm('Apakah Anda yakin ingin menghapus gedung ini?')) {
                    try {
                        const response = await fetch(`/api/master/gedungs/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message);

                        alert(result.message);
                        // Hapus item dari tampilan tanpa perlu refresh
                        e.target.closest('li').remove();

                    } catch (error) {
                        console.error('Gagal menghapus gedung:', error);
                        alert(error.message);
                    }
                }
            }
        });

        
        // Event listener untuk form Tambah Kelas
        formTambahKelas.addEventListener('submit', async (e) => {
            e.preventDefault();
            const namaKelas = e.target.elements.nama_kelas.value;
            try {
                const response = await fetch('/api/master/kelas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ nama_kelas: namaKelas })
                });
                if (!response.ok) throw new Error('Gagal menyimpan kelas.');
                e.target.reset(); // Kosongkan form
                loadAndRenderList('/api/master/kelas', listKelas, 'nama_kelas'); // Refresh list
                loadMasterData(true);
            } catch (error) {
                alert(error.message);
            }
        });

        // Event listener untuk menghapus item di list Kelas
        listKelas.addEventListener('click', async (e) => {
            if (e.target.classList.contains('btn-hapus-master')) {
                const id = e.target.dataset.id;
                
                if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
                    try {
                        const response = await fetch(`/api/master/kelas/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const result = await response.json();
                        if (!response.ok) throw new Error(result.message);
                        alert(result.message);
                        e.target.closest('li').remove();
                    } catch (error) {
                        console.error('Gagal menghapus kelas:', error);
                        alert(error.message);
                    }
                }
            }
        });

    }
});