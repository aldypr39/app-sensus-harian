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
        'no_rm', 'nama_pasien', 'jenis_kelamin', 'tgl_masuk',
        'asal_pasien', 'tempat_tidur_id', 'status', 'tgl_keluar',
        'keadaan_keluar', 'lama_dirawat',
    ];

    // Relasi untuk mendapatkan data tempat tidur
    public function tempatTidur()
    {
        return $this->belongsTo(TempatTidur::class);
    }

    // Relasi 'shortcut' untuk mendapatkan data ruangan melalui tempat tidur
    public function ruangan()
    {
        return $this->hasOneThrough(Ruangan::class, TempatTidur::class, 'id', 'id', 'tempat_tidur_id', 'ruangan_id');
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