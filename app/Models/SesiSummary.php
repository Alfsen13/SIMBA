<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiSummary extends Model
{
    protected $fillable = ['user_id', 'sesi_id', 'materi_id', 'ringkasan', 'periode_mulai', 'periode_selesai'];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'datetime',
            'periode_selesai' => 'datetime',
        ];
    }

     
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     
    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class);
    }
}

