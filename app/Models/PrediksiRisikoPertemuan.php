<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrediksiRisikoPertemuan extends Model
{
    protected $table = 'prediksi_risiko_pertemuan';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'pertemuan_id',
        'metode',
        'versi_konfigurasi',
        'rata_nilai',
        'persen_terlambat',
        'persen_terlewat',
        'nilai_keseluruhan',
        'skor_penyimpangan',
        'kategori_risiko',
        'kategori_risiko_rf',
        'prediksi_nilai_mendatang',
        'status_data',
    ];

    protected function casts(): array
    {
        return [
            'rata_nilai' => 'float',
            'persen_terlambat' => 'float',
            'persen_terlewat' => 'float',
            'nilai_keseluruhan' => 'float',
            'skor_penyimpangan' => 'float',
            'prediksi_nilai_mendatang' => 'float',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }
}
