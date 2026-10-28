<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TingkatPendidikan extends Model
{
    use HasFactory;

    protected $table = 'tingkat_pendidikan';

    protected $fillable = ['jenjang_id', 'kode', 'nama', 'deskripsi', 'urutan', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class, 'jenjang_id');
    }
}
