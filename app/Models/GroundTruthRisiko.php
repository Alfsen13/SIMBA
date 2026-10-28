<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

 
class GroundTruthRisiko extends Model
{
    protected $table = 'ground_truth_risiko';

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'label',
        'sumber',
        'catatan',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function pengonfirmasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

     
    public function scopeDikonfirmasi($query)
    {
        return $query->where('sumber', 'guru');
    }
}

