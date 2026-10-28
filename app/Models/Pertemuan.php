<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pertemuan extends Model
{
    protected $table = 'pertemuan';

    protected $fillable = ['kelas_id', 'nama_pertemuan', 'urutan', 'deskripsi', 'tanggal_mulai', 'tanggal_selesai', 'dibuka'];

     
    protected $attributes = [
        'dibuka' => true,
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'dibuka' => 'boolean',
        ];
    }

     
    public function scopeDibuka(Builder $query): Builder
    {
        return $query->where('dibuka', true);
    }

     
    public function scopeTerkunci(Builder $query): Builder
    {
        return $query->where('dibuka', false);
    }

     
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

     
    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class);
    }

     
    public function tugas(): HasMany
    {
        return $this->hasMany(Tugas::class);
    }

     
    public function kuis(): HasMany
    {
        return $this->hasMany(Kuis::class);
    }

     
    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }

     
    public function aktivitasLog(): HasMany
    {
        return $this->hasMany(AktivitasLog::class, 'pertemuan_id');
    }
}

