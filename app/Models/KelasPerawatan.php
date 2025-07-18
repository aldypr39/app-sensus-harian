<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasPerawatan extends Model
{
    use HasFactory;

    // Dengan tidak adanya properti 'protected $table', Laravel akan
    // secara otomatis menggunakan tabel 'kelas_perawatans' (jamak). Ini yang benar.

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ruangan_id',
        'nama_kelas',
        'jumlah_tt',
    ];
}