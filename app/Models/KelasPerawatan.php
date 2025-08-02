<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasPerawatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruangan_id',
        'kelas_id',
        'jumlah_tt',
    ];

    /**
     * Mendefinisikan relasi ke model Ruangan.
     */
    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * Mendefinisikan relasi ke model Kelas.
     * INI FUNGSI YANG HILANG.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}