<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumpulanTugas extends Model
{
    protected $table = 'pengumpulan_tugas';

    protected $fillable = [
        'tugas_id', 'siswa_id', 'file', 'jawaban', 'nilai', 'terlambat',
        'dimulai_pada', 'batas_kerjakan',
        'nilai_ai', 'hasil_ai', 'koreksi_ai_at', 'koreksi_ai_gagal',
    ];

    protected function casts(): array
    {
        return [
            'jawaban' => 'array',
            'terlambat' => 'boolean',
            'dimulai_pada' => 'datetime',
            'batas_kerjakan' => 'datetime',
            'hasil_ai' => 'array',
            'koreksi_ai_at' => 'datetime',
            'koreksi_ai_gagal' => 'boolean',
        ];
    }

    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
