<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

 
class FiturSiswaHistory extends Model
{
    protected $table = 'fitur_siswa_history';

    protected $fillable = [
        'siswa_id',
        'pertemuan_id',
        'tanggal',
        'frekuensi_akses_pertemuan',
        'rata_durasi_belajar',
        'persen_tugas_terkumpul',
        'tren_nilai',
        'persen_kehadiran',
        'rata_nilai',
        'jumlah_pertemuan_tidak_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'frekuensi_akses_pertemuan' => 'integer',
            'rata_durasi_belajar' => 'float',
            'persen_tugas_terkumpul' => 'float',
            'tren_nilai' => 'float',
            'persen_kehadiran' => 'float',
            'rata_nilai' => 'float',
            'jumlah_pertemuan_tidak_aktif' => 'integer',
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
}

