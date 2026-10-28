<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiturHybridSiswa extends Model
{
    protected $table = 'fitur_hybrid_siswa';

    protected $fillable = [
        'siswa_id',
        'periode',
        'rata_nilai',
        'persen_terlambat',
        'persen_terlewat',
        'nilai_keseluruhan',
    ];

    protected function casts(): array
    {
        return [
            'rata_nilai' => 'float',
            'persen_terlambat' => 'float',
            'persen_terlewat' => 'float',
            'nilai_keseluruhan' => 'float',
        ];
    }

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}

