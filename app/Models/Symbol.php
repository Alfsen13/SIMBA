<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Symbol extends Model
{
    protected $table = 'symbols';

     
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'domain',
        'category',
        'subcategory',
        'symbol',
        'latex',
        'name',
        'definition',
        'meaning',
        'formula',
        'variables',
        'unit',
        'example',
        'related_symbols',
        'related_concepts',
        'common_misunderstanding',
        'keywords',
        'retrieval_text',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'related_symbols' => 'array',
            'related_concepts' => 'array',
            'keywords' => 'array',
        ];
    }
}

