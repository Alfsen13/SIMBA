<?php

namespace App\Jobs;

use App\Models\ChatLog;
use App\Models\ChatSummary;
use App\Models\SesiSummary;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class GenerateChatSummary implements ShouldQueue
{
    use Dispatchable, Queueable;

     
    public function __construct(public int $userId) {}

     
    public function handle(GeminiService $gemini): void
    {
        $logs = ChatLog::where('user_id', $this->userId)
            ->where('created_at', '>=', now()->subDay())
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

        $conversation = $logs->map(fn ($l) => "[{$l->role}]: {$l->message}")->implode("\n");
        $prompt = "Ringkas percakapan berikut dalam bahasa Indonesia:\n\n{$conversation}";
        $ringkasan = $gemini->send($prompt);

        $sesi = SesiSummary::create([
            'user_id' => $this->userId,
            'ringkasan' => $ringkasan,
            'periode_mulai' => $logs->first()->created_at,
            'periode_selesai' => $logs->last()->created_at,
        ]);

        ChatSummary::create([
            'user_id' => $this->userId,
            'sesi_summary_id' => $sesi->id,
            'ringkasan' => $ringkasan,
        ]);
    }
}

