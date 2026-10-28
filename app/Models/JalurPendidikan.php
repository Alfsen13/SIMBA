<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JalurPendidikan extends Model
{
    use HasFactory;

    protected $table = 'jalur_pendidikan';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'urutan', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function jenjang(): HasMany
    {
        return $this->hasMany(JenjangPendidikan::class, 'jalur_id');
    }
}
