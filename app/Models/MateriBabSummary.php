<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriBabSummary extends Model
{
    protected $table = 'materi_bab_summary';

    protected $fillable = ['materi_id', 'ringkasan'];

     
    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class);
    }
}

