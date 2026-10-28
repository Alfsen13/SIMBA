<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatLog extends Model
{
    protected $fillable = ['user_id', 'sesi_id', 'materi_id', 'role', 'message', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
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

