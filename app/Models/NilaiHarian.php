<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiHarian extends Model
{
    protected $table = 'nilai_harian';

    protected $fillable = ['siswa_id', 'kelas_id', 'nilai'];

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

     
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}

