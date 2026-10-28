<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrediksiMendatang extends Model
{
    protected $table = 'prediksi_mendatang';

    protected $fillable = [
        'siswa_id',
        'pertemuan_sumber_id',
        'pertemuan_target_id',
        'horizon_hari',
        'prediksi_nilai',
        'nilai_ideal',
        'persentase_meleset',
        'nilai_aktual',
        'mae_individual',
        'status_data',
        'model_versi',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'horizon_hari' => 'integer',
            'prediksi_nilai' => 'float',
            'nilai_ideal' => 'float',
            'persentase_meleset' => 'float',
            'nilai_aktual' => 'float',
            'mae_individual' => 'float',
            'calculated_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /** Pertemuan terakhir yang dipakai sebagai input ramalan. */
    public function pertemuanSumber(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_sumber_id');
    }

    /** Pertemuan yang diramalkan (belum tentu sudah berlangsung). */
    public function pertemuanTarget(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_target_id');
    }
}
