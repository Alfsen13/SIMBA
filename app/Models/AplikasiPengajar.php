<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AplikasiPengajar extends Model
{
    use HasFactory;

    protected $table = 'aplikasi_pengajar';

    protected $fillable = [
        'user_id',
        'pendidikan_tertinggi',
        'pengalaman_mengajar',
        'cv_path',
        'ijazah_path',
        'kategori_mapel',
        'mata_pelajaran',
        'jenjang_dan_tingkat',
        'status_aplikasi',
        'reviewed_by',
        'reviewed_at',
        'catatan_operator',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'mata_pelajaran' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function kategoriMataPelajaran(): BelongsTo
    {
        return $this->belongsTo(KategoriMataPelajaran::class, 'kategori_mapel', 'nama');
    }
}
