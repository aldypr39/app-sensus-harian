document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-login');
    const usernameInput = document.getElementById('login-username');
    const passwordInput = document.getElementById('login-password');

    // Jadikan event listener-nya async untuk menangani fetch
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = usernameInput.value.trim();
        const password = passwordInput.value;

        // Tampilkan loading atau nonaktifkan tombol untuk mencegah klik ganda
        const loginButton = form.querySelector('.login-btn');
        loginButton.disabled = true;
        loginButton.textContent = 'Memproses...';

        try {
            // Kirim data ke API Laravel kita
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    username: username,
                    password: password,
                }),
            });

            // Ambil data JSON dari respon
            const data = await response.json();

            // Periksa apakah responnya sukses (status 200-299)
            if (response.ok) {
                // Jika login berhasil
                alert('Login berhasil!'); // Notifikasi sementara

                // Simpan data user yang login ke localStorage
                // agar bisa diakses oleh halaman lain (index.html, dll)
                localStorage.setItem('sensus_harian_current_user', JSON.stringify(data.user));

                // Arahkan ke halaman dashboard
                window.location.href = '/';

            } else {
                // Jika login gagal (misal: status 401 dari Laravel)
                // Tampilkan pesan error dari server
                alert(data.message || 'Terjadi kesalahan');
                loginButton.disabled = false;
                loginButton.textContent = 'Login';
            }

        } catch (error) {
            // Jika ada error jaringan atau server tidak aktif
            console.error('Error saat mencoba login:', error);
            alert('Tidak dapat terhubung ke server. Pastikan server Laravel berjalan.');
            loginButton.disabled = false;
            loginButton.textContent = 'Login';
        }
    });
});