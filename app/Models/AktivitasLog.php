<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasLog extends Model
{
    protected $table = 'aktivitas_log';

    protected $fillable = ['siswa_id', 'pertemuan_id', 'kelas_id', 'tipe', 'deskripsi', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

     
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }

     
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}

