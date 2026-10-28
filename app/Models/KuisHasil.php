<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KuisHasil extends Model
{
    protected $table = 'kuis_hasil';

    protected $fillable = ['kuis_id', 'siswa_id', 'skor', 'jawaban_siswa'];

    protected function casts(): array
    {
        return [
            'jawaban_siswa' => 'array',
        ];
    }

     
    public function kuis(): BelongsTo
    {
        return $this->belongsTo(Kuis::class);
    }

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}

