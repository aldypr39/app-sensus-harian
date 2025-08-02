<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory;

    protected $fillable = [
        'gedung_id',
        'nama_ruangan',
        'lantai',
    ];

    /**
     * Mendefinisikan relasi ke model Gedung.
     */
    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    /**
     * Mendefinisikan relasi ke model KelasPerawatan.
     * INI FUNGSI YANG HILANG.
     */
    public function kelasPerawatans(): HasMany
    {
        return $this->hasMany(KelasPerawatan::class);
    }
}