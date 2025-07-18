<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    
}
