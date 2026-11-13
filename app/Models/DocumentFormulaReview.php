<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentFormulaReview extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'document_id', 'document_chunk_id', 'repaired_chunk_ids', 'repaired_via_marker', 'page', 'region_key', 'image_base64',
        'source_text', 'latex', 'status', 'ocr_error', 'approved_at', 'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'repaired_chunk_ids' => 'array',
            'repaired_via_marker' => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', self::STATUS_APPROVED);
    }
}
