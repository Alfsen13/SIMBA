<?php

namespace App\Models;

use App\Services\PrediksiRisikoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrediksiAi extends Model
{
    protected $table = 'prediksi_ai';

    protected $fillable = ['siswa_id', 'hasil', 'skor_risiko', 'feature_vector'];

    protected function casts(): array
    {
        return [
            'feature_vector' => 'array',
            'skor_risiko' => 'float',
        ];
    }

     
    public function labelAmbang(): string
    {
        return PrediksiRisikoService::kategoriDariSkor((float) $this->skor_risiko);
    }

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}

