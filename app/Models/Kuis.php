<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuis extends Model
{
    protected $table = 'kuis';

    protected $fillable = ['judul', 'kelas_id', 'pertemuan_id', 'materi_id', 'soal', 'konteks_soal', 'durasi_menit', 'tanggal_mulai', 'tanggal_selesai', 'is_aktif'];

    protected function casts(): array
    {
        return [
            'soal' => 'array',
            'konteks_soal' => 'array',
            'tanggal_mulai' => 'datetime',
            'tanggal_selesai' => 'datetime',
            'is_aktif' => 'boolean',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class);
    }

    public function materiPendukung(): BelongsToMany
    {
        return $this->belongsToMany(Materi::class, 'kuis_materi');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(KuisHasil::class);
    }
}
