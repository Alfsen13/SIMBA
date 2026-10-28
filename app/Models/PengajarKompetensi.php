<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajarKompetensi extends Model
{
    use HasFactory;

    protected $table = 'pengajar_kompetensi';

    protected $fillable = [
        'user_id',
        'mata_pelajaran_id',
        'mata_pelajaran_lain',
        'jenjang_id',
        'tingkat_id',
        'status',
        'disetujui_oleh',
        'disetujui_pada',
        'catatan',
    ];

    protected $casts = [
        'disetujui_pada' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class);
    }

    public function tingkat(): BelongsTo
    {
        return $this->belongsTo(TingkatPendidikan::class);
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
