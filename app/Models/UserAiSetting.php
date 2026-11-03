<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserAiSetting extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'endpoint',
        'api_key',
        'model',
        'options',
        'enabled',
    ];

    protected $casts = [
        'options' => 'array',
        'enabled' => 'boolean',
        'tested_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $setting) {
            // Pastikan hanya satu setting aktif per user (unique constraint sudah ada di DB).
            // Jika ingin "default global", set user_id = null dan hapus unique constraint.
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Encrypt API key before storing.
     */
    protected function apiKey(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
        );
    }

    /**
     * Apakah setting ini siap pakai (ada provider, model, dan API key kalau diperlukan).
     */
    public function isReady(): bool
    {
        return $this->enabled
            && $this->provider
            && $this->model
            && (! in_array($this->provider, ['ollama', 'gemini', 'nvidia_nim', 'openai', 'anthropic']) || $this->api_key);
    }

    /**
     * Scope untuk user yang punya setting enabled.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
