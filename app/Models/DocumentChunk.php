<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\RumusPenanda;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array|null $embedding
 * @property-read string $konten_terbaca
 */
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

    /** Isi chunk untuk dibaca manusia: token penanda rumus diganti label. */
    protected function kontenTerbaca(): Attribute
    {
        return Attribute::get(fn (): string => RumusPenanda::manusiakan((string) $this->content));
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
