<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\GenerateChatSummary;
use App\Models\ChatLog;
use App\Models\SesiSummary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

class ChatSessionService
{
      
    public function startSession(int $userId, int $pertemuanId, ?int $materiId = null): array
    {
        $sesiId = (string) Str::uuid();

        SesiSummary::create([
            'user_id' => $userId,
            'sesi_id' => $sesiId,
            'materi_id' => $materiId,
            'ringkasan' => '',
            'periode_mulai' => now(),
            'periode_selesai' => now(),
        ]);

        return ['sesi_id' => $sesiId];
    }

      
    public function endSession(string $sesiId, int $userId): void
    {
        $sesi = SesiSummary::where('sesi_id', $sesiId)->firstOrFail();
        if ((int) $sesi->user_id !== $userId) {
            throw new UnauthorizedException('Tidak diizinkan mengakhiri sesi ini.');
        }
        $sesi->update(['periode_selesai' => now()]);

        GenerateChatSummary::dispatch($sesi->user_id);
    }

      
    public function getSessionHistory(string $sesiId, int $userId): Collection
    {
        $sesi = SesiSummary::where('sesi_id', $sesiId)->first();
        if ($sesi && (int) $sesi->user_id !== $userId) {
            throw new UnauthorizedException('Tidak diizinkan mengakses riwayat sesi ini.');
        }

        return ChatLog::where('sesi_id', $sesiId)
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->get(['role', 'message', 'created_at']);
    }

    public function assertOwned(string $sesiId, int $userId): SesiSummary
    {
        $sesi = SesiSummary::where('sesi_id', $sesiId)->firstOrFail();
        if ((int) $sesi->user_id !== $userId) {
            throw new UnauthorizedException('Tidak diizinkan mengakses sesi ini.');
        }

        return $sesi;
    }
}

