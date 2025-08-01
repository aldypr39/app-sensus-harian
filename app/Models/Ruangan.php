<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang benar di database.
     *
     * @var string
     */
    protected $fillable = [
        'nama_ruangan',
        'gedung',
        'lantai',
    ];

    
    public function kelasPerawatans(): HasMany
    {
        return $this->hasMany(KelasPerawatan::class);
    }
    
}
