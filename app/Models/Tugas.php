<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = ['judul', 'deskripsi', 'kelas_id', 'guru_id', 'pertemuan_id', 'materi_id', 'tipe', 'soal', 'konteks_soal', 'deadline', 'batas_jam'];

    protected function casts(): array
    {
        return [
            'soal' => 'array',
            'konteks_soal' => 'array',
            'deadline' => 'datetime',
            'batas_jam' => 'integer',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
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
        return $this->belongsToMany(Materi::class, 'tugas_materi');
    }

    public function pengumpulan(): HasMany
    {
        return $this->hasMany(PengumpulanTugas::class);
    }

    public function isLewatDeadline(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }
}
