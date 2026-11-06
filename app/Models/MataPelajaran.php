<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = ['kode', 'nama', 'kategori', 'deskripsi', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function kategoriMataPelajaran(): BelongsTo
    {
        return $this->belongsTo(KategoriMataPelajaran::class, 'kategori', 'nama');
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function pengajarKompetensi(): HasMany
    {
        return $this->hasMany(PengajarKompetensi::class, 'mata_pelajaran_id');
    }
}
