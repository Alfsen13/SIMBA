<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['kode', 'nama', 'kategori', 'deskripsi', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];
}
