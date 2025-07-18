// js/admin.js
import { DataManager, dataStore } from './modules/dataManager.js';
import { UIController }           from './modules/uiController.js';
import { Utils }                  from './modules/utils.js';

document.addEventListener('DOMContentLoaded', () => {
  // Pastikan ini halaman Admin Ruangan
  if (!window.location.pathname.includes('admin_ruangan')) return;
  DataManager.load();

  // Elemen modal & tombol tambah
  const modalRuangan     = document.getElementById('modal-ruangan');
  const btnTambahRuangan = document.getElementById('btn-tambah-ruangan');
  const closeBtn         = modalRuangan.querySelector('.close-btn');
  let currentEditRuanganId = null;

  // Helper buka/tutup
  function openModal()  { modalRuangan.classList.add('active'); }
  function closeModal() { modalRuangan.classList.remove('active'); }

  // Inisialisasi event buka modal “Tambah Ruangan”
  btnTambahRuangan.addEventListener('click', () => {
    currentEditRuanganId = null;
    modalRuangan.querySelector('#modal-ruangan-title').textContent = 'Tambah Ruangan Baru';
    formRuangan.reset();
    // kosongkan kelas-container, mulai dengan satu row default
    kelasContainer.innerHTML = '';
    kelasContainer.appendChild(buatKelasRow());
    openModal();
  });
  closeBtn.addEventListener('click', closeModal);
  window.addEventListener('click', e => {
    if (e.target === modalRuangan) closeModal();
  });

  // References untuk form & dynamic kelas
  const formRuangan      = document.getElementById('form-ruangan');
  const kelasContainer   = document.getElementById('kelas-container');
  const btnTambahKelas   = document.getElementById('btn-tambah-kelas');

  // 1) Helper: buat satu baris input kelas
  function buatKelasRow(nama = '', jumlah = '') {
    const row = document.createElement('div');
    row.className = 'kelas-row';
    row.innerHTML = `
      <input type="text" name="nama_kelas" placeholder="Nama Kelas" required value="${nama}">
      <input type="number" name="jumlah_tt" placeholder="Jumlah Tempat Tidur" min="1" required value="${jumlah}">
      <button type="button" class="btn-hapus-kelas">−</button>
    `;
    // event hapus baris
    row.querySelector('.btn-hapus-kelas').addEventListener('click', () => row.remove());
    return row;
  }

  // 2) Mulai dengan satu row
  kelasContainer.appendChild(buatKelasRow());

  // 3) Tambah row baru
  btnTambahKelas.addEventListener('click', () => {
    kelasContainer.appendChild(buatKelasRow());
  });

  // 4) Submit form: kumpulkan nama_ruangan + classes[]
  formRuangan.addEventListener('submit', e => {
    e.preventDefault();

    // Nama ruangan
    const nama_ruangan = formRuangan.querySelector('[name="nama_ruangan"]').value.trim();

    // Array kelas: [{ nama_kelas, jumlah_tt }, ...]
    const classes = Array.from(kelasContainer.querySelectorAll('.kelas-row')).map(row => ({
      nama_kelas: row.querySelector('[name="nama_kelas"]').value.trim(),
      jumlah_tt:  parseInt(row.querySelector('[name="jumlah_tt"]').value, 10)
    }));

    // Panggil create or update
    if (currentEditRuanganId) {
      DataManager.ruangan.update(currentEditRuanganId, { nama_ruangan, classes });
    } else {
      DataManager.ruangan.create({ nama_ruangan, classes });
    }

    // Re‐render tabel dan tutup modal
    UIController.renderRuanganAdmin();
    closeModal();
  });

  // 5) Table actions: Edit + Delete
  const tableBody = document.querySelector('.admin-content table tbody');
  tableBody.addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;

    // Edit
    if (btn.classList.contains('btn-edit')) {
      const id = parseInt(btn.dataset.id, 10);
      const ru = dataStore.ruangan.find(r => r.id === id);
      if (!ru) return;

      currentEditRuanganId = id;
      modalRuangan.querySelector('#modal-ruangan-title').textContent = 'Edit Ruangan';
      formRuangan.querySelector('[name="nama_ruangan"]').value = ru.nama;

      // Render ulang kelas-container sesuai ru.classes (jika ada)
      kelasContainer.innerHTML = '';
      (ru.classes || []).forEach(c => {
        kelasContainer.appendChild(buatKelasRow(c.nama_kelas, c.jumlah_tt));
      });
      openModal();
    }

    // Delete
    if (btn.classList.contains('btn-delete')) {
      const id = parseInt(btn.dataset.id, 10);
      if (confirm('Yakin ingin menghapus ruangan ini?')) {
        DataManager.ruangan.delete(id);
        UIController.renderRuanganAdmin();
        Utils.showNotification('Ruangan berhasil dihapus!', 'success');
      }
    }
  });

  // 6) Initial render
  UIController.renderRuanganAdmin();
});


function showToast(message, type = 'success') {
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.textContent = message;
  document.body.appendChild(t);
  // otomatis terhapus setelah animasi
  t.addEventListener('animationend', () => t.remove());
}