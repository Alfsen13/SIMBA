<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilPendidikan extends Model
{
    use HasFactory;

    protected $table = 'profil_pendidikan';

    protected $fillable = [
        'user_id',
        'jalur_id',
        'jenjang_id',
        'tingkat_id',
        'institusi',
        'program',
        'minat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jalur(): BelongsTo
    {
        return $this->belongsTo(JalurPendidikan::class);
    }

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(JenjangPendidikan::class);
    }

    public function tingkat(): BelongsTo
    {
        return $this->belongsTo(TingkatPendidikan::class);
    }
}
