<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    protected $table = 'kehadiran';

     
    public const STATUS = ['hadir', 'izin', 'sakit', 'alpha'];

     
    public const STATUS_HADIR = ['hadir', 'izin', 'sakit'];

    protected $fillable = ['siswa_id', 'pertemuan_id', 'status', 'catatan', 'dicatat_oleh'];

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

     
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id');
    }

     
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}

