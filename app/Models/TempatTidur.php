<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempatTidur extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruangan_id',
        'kelas_id',
        'nomor_tt',
        'status',
    ];

    /**
     * Relasi untuk mendapatkan data ruangan dari tempat tidur.
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * Relasi untuk mendapatkan data kelas dari tempat tidur.
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}