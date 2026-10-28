<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenjangPendidikan extends Model
{
    use HasFactory;

    protected $table = 'jenjang_pendidikan';

    protected $fillable = ['jalur_id', 'kode', 'nama', 'deskripsi', 'urutan', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendidikan::class, 'jalur_id');
    }

    public function tingkat(): HasMany
    {
        return $this->hasMany(TingkatPendidikan::class, 'jenjang_id');
    }
}
