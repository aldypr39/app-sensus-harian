import { DataManager, dataStore } from './modules/dataManager.js';
import { UIController }      from './modules/uiController.js';
import { Utils }             from './modules/utils.js';

document.addEventListener('DOMContentLoaded', () => {
  if (!window.location.pathname.includes('admin_akun')) return;
  // 1) Muat data yang sudah disimpan di localStorage
  DataManager.load();
  // isi dropdown ruangan
  const pilihRuangan = document.getElementById('pilih-ruangan');
  if (pilihRuangan) {
    pilihRuangan.innerHTML = '';             // kosongkan dulu
    dataStore.ruangan.forEach(r => {
      if (r.is_active) {
        const opt = document.createElement('option');
        opt.value = r.id;
        opt.textContent = r.nama;
        pilihRuangan.appendChild(opt);
      }
    });
  }

  // 2) Inisialisasi tampilan tabel akun
  UIController.renderAkunAdmin();

  const modalAkun      = document.getElementById('modal-akun');
  const formAkun       = document.getElementById('form-akun');
  const btnTambahAkun  = document.getElementById('btn-tambah-akun');
  const closeBtn       = modalAkun.querySelector('.close-btn');
  let currentEditAkunId = null;

  // buka/tutup modal
  function openModal()  { modalAkun.classList.add('active'); }
  function closeModal() { modalAkun.classList.remove('active'); }

  // tombol “Buat Akun Baru”
  btnTambahAkun.addEventListener('click', () => {
    currentEditAkunId = null;
    formAkun.reset();
    modalAkun.querySelector('h2').textContent = 'Buat Akun Ruangan Baru';
    openModal();
  });

  closeBtn.addEventListener('click', closeModal);
  window.addEventListener('click', e => {
    if (e.target === modalAkun) closeModal();
  });

  // handle submit form akun
  formAkun.addEventListener('submit', e => {
    e.preventDefault();
    const data = {
      ruangan_id: parseInt(formAkun.ruangan_id.value, 10),
      username:   formAkun.username.value.trim(),
      password:   formAkun.password.value
    };
    const passConfirm = formAkun.password_confirmation.value;
    if (data.password !== passConfirm) {
      Utils.showNotification('Password dan konfirmasi tidak cocok', 'error');
      return;
    }
    let result;
    if (currentEditAkunId) {
      result = DataManager.users.update(currentEditAkunId, data);
      Utils.showNotification('Akun berhasil diperbarui', 'success');
    } else {
      result = DataManager.users.create(data);
      Utils.showNotification('Akun baru berhasil dibuat', 'success');
    }
    if (result) {
      UIController.renderAkunAdmin();
      closeModal();
    }
  });

  // handle klik edit/delete di tabel
  document.querySelector('.admin-content table tbody').addEventListener('click', e => {
    const btn = e.target.closest('button');
    if (!btn) return;
    const id = parseInt(btn.dataset.id, 10);

    if (btn.classList.contains('btn-edit')) {
      const user = dataStore.users.find(u => u.id === id);
      if (!user) return;
      currentEditAkunId = id;
      formAkun.ruangan_id.value = user.ruangan_id;
      formAkun.username.value   = user.username;
      formAkun.password.value   = '';
      formAkun.password_confirmation.value = '';
      modalAkun.querySelector('h2').textContent = 'Edit Akun Ruangan';
      openModal();
    }

    if (btn.classList.contains('btn-delete')) {
      if (confirm('Yakin ingin menghapus akun ini?')) {
        DataManager.users.delete(id);
        UIController.renderAkunAdmin();
        Utils.showNotification('Akun berhasil dihapus', 'success');
      }
    }
  });

  // render awal
  UIController.renderAkunAdmin();
});
