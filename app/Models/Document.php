<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'name', 'original_name', 'path', 'type', 'size', 'kategori',
        'status', 'error_message', 'user_id', 'materi_id', 'kelas_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }
}
