<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    protected $fillable = [
        'document_id', 'content', 'embedding', 'chunk_index', 'kategori', 'page_start', 'page_end',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
