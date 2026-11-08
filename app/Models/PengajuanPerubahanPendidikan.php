<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPerubahanPendidikan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_perubahan_pendidikan';

    protected $fillable = [
        'user_id',
        'jalur_id',
        'jenjang_id',
        'tingkat_id',
        'institusi',
        'program',
        'minat',
        'jenis',
        'status',
        'reviewed_by',
        'reviewed_at',
        'alasan',
        'catatan_operator',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendidikan::class);
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class);
    }

    public function tingkat(): BelongsTo
    {
        return $this->belongsTo(TingkatPendidikan::class);
    }
}
