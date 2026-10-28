<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSummary extends Model
{
    protected $fillable = ['user_id', 'sesi_summary_id', 'ringkasan'];

     
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     
    public function sesiSummary(): BelongsTo
    {
        return $this->belongsTo(SesiSummary::class);
    }
}

