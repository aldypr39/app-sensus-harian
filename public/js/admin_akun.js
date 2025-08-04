import { showConfirm, showNotification } from './modules/utils.js';

document.addEventListener('DOMContentLoaded', () => {
    // Referensi Elemen
    const modalAkun = document.getElementById('modal-akun');
    const formAkun = document.getElementById('form-akun');
    const btnTambahAkun = document.getElementById('btn-tambah-akun');
    const modalTitle = document.getElementById('modal-akun-title');
    const tableBody = document.querySelector('.table-container tbody');
    const closeBtn = modalAkun.querySelector('.close-btn');

    let currentEditId = null;

    const openModal = () => modalAkun.classList.add('active');
    const closeModal = () => modalAkun.classList.remove('active');

    // Fungsi untuk mengisi dropdown ruangan dari server
    async function populateRuanganDropdown(selectedValue = null) {
        const select = document.getElementById('ruangan_id');
        try {
            const response = await fetch('/api/ruangans');
            const ruangans = await response.json();
            select.innerHTML = '<option value="">Pilih Ruangan</option>';
            ruangans.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.nama_ruangan;
                if(r.id == selectedValue) opt.selected = true;
                select.appendChild(opt);
            });
        } catch (error) {
            select.innerHTML = '<option value="">Gagal memuat ruangan</option>';
        }
    }

    // Event saat tombol "Buat Akun Baru" diklik
    btnTambahAkun.addEventListener('click', () => {
        currentEditId = null;
        formAkun.reset();
        modalTitle.textContent = 'Buat Akun Baru';
        formAkun.querySelector('#password').required = true;
        populateRuanganDropdown();
        openModal();
    });

    // Event untuk tombol di tabel (Edit & Hapus)
    tableBody.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = btn.dataset.id;

        // Logika Tombol Edit
        if (btn.classList.contains('edit')) {
            currentEditId = id;
            formAkun.reset();
            modalTitle.textContent = 'Edit Akun';
            formAkun.querySelector('#password').required = false; // Password tidak wajib saat edit
            
            try {
                const response = await fetch(`/manajemen/akun/${id}/edit`);
                const user = await response.json();
                populateRuanganDropdown(user.ruangan_id);
                formAkun.querySelector('#name').value = user.name;
                formAkun.querySelector('#username').value = user.username;
                openModal();
            } catch (error) {
                showNotification('Gagal', 'Gagal memuat data akun.', 'error');
            }
        }

        // Logika Tombol Hapus
        if (btn.classList.contains('delete')) {
            if (await showConfirm('Anda Yakin?', 'Akun ini akan dihapus permanen.')) {
                try {
                    const response = await fetch(`/manajemen/akun/${id}`, {
                        method: 'POST', // Selalu POST
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ '_method': 'DELETE' }) // Method Spoofing
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

    // Event saat form disubmit
    formAkun.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formAkun);
        const data = Object.fromEntries(formData.entries());

        const url = currentEditId ? `/manajemen/akun/${currentEditId}` : '/manajemen/akun';
        const method = currentEditId ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: 'POST', // Selalu POST
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ...data, '_method': method }) // Method Spoofing
            });
            const result = await response.json();

            if (!response.ok) {
                if(response.status === 422) {
                    const errors = Object.values(result.errors).map(err => `- ${err[0]}`).join('\n');
                    showNotification('Validasi Gagal', errors, 'error');
                } else { throw new Error(result.message); }
            } else {
                showNotification('Berhasil!', result.message, 'success');
                closeModal();
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error) {
            showNotification('Gagal!', error.message, 'error');
        }
    });

    // Event untuk menutup modal
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (e) => {
        if (e.target === modalAkun) closeModal();
    });
});