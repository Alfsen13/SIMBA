<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materi';

    protected $fillable = [
        'judul', 'deskripsi', 'bab', 'pertemuan_id', 'kelas_id', 'guru_id',
        'file_path', 'file_type', 'document_id',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function babSummary(): HasOne
    {
        return $this->hasOne(MateriBabSummary::class);
    }

    /** @return HasOne<Document, $this> */
    public function document(): HasOne
    {
        return $this->hasOne(Document::class, 'materi_id');
    }

    /** @return BelongsTo<Document, $this> */
    public function referensiDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
