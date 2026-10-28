<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPengajarKelas extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'pengajuan_pengajar_kelas';

    protected $fillable = [
        'kelas_id',
        'user_id',
        'rencana_ajar',
        'berkas_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'catatan_operator',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /** Pengajar yang mengajukan (kolom user_id — nama relasi tidak boleh 'user'). */
    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
