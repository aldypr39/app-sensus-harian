<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pasien extends Model
{
    use HasFactory;

    // Dengan tidak mendefinisikan '$table', Laravel akan otomatis
    // menggunakan nama tabel 'pasiens' (jamak), dan ini yang kita inginkan.

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_rm',
        'nama_pasien',
        'jenis_kelamin',
        'tgl_masuk',
        'asal_pasien',
        'ruangan_id',
        'kelas',
        'no_tt',
        'status',
        'tgl_keluar',
        'keadaan_keluar',
        'lama_dirawat',
    ];

    /**
     * Mendefinisikan relasi ke model Ruangan.
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public static function hitungLamaDirawat($tglMasuk, $tglKeluar) // <-- INI FUNGSI YANG HILANG
    {
        $masuk = Carbon::parse($tglMasuk)->startOfDay();
        $keluar = Carbon::parse($tglKeluar)->startOfDay();

        $selisihHari = $masuk->diffInDays($keluar);

        // Jika selisih 0 (keluar di hari yang sama), hitung sebagai 1 hari.
        return $selisihHari == 0 ? 1 : $selisihHari;
    }
}