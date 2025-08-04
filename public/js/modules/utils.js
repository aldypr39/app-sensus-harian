// File: public/js/modules/utils.js

/**
 * Menampilkan notifikasi konfirmasi (menggantikan confirm()).
 * Mengembalikan 'true' jika user menekan konfirmasi.
 * @param {string} title - Judul notifikasi.
 * @param {string} text - Teks penjelasan di bawah judul.
 * @returns {Promise<boolean>}
 */
export async function showConfirm(title, text) {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00796b',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal'
    });
    return result.isConfirmed;
}

/**
 * Menampilkan notifikasi info (sukses/error) yang hilang otomatis.
 * @param {string} title - Judul notifikasi.
 * @param {string} text - Teks penjelasan.
 * @param {string} icon - 'success', 'error', 'warning', 'info'
 */
export function showNotification(title, text, icon = 'success') {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        timer: 2000,
        showConfirmButton: false
    });
}