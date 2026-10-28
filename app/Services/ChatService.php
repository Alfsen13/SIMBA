<?php

namespace App\Services;

use App\Models\ChatLog;
use App\Models\Pertemuan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatService
{
    protected AIProviderService $ai;

    protected RAGService $rag;

    protected SymbolService $symbols;

    public function __construct()
    {
        $this->ai = app(AIProviderService::class);
        $this->rag = app(RAGService::class);
        $this->symbols = app(SymbolService::class);
    }

    public function saveMessage(int $userId, string $sesiId, ?int $materiId, string $message, string $role = 'user'): ChatLog
    {
        return ChatLog::create([
            'user_id' => $userId,
            'sesi_id' => $sesiId,
            'materi_id' => $materiId,
            'role' => $role,
            'message' => $message,
        ]);
    }

    public function getHistory(string $sesiId, int $limit = 10): array
    {
        return ChatLog::where('sesi_id', $sesiId)
            ->latest()
            ->take($limit)
            ->get()
            ->reverse()
            ->map(fn ($log) => [
                'role' => $log->role === 'user' ? 'user' : 'model',
                'content' => $log->message,
            ])
            ->toArray();
    }

    public function getUserHistory(int $userId, int $limit = 10): array
    {
        return ChatLog::where('user_id', $userId)
            ->latest()
            ->take($limit)
            ->get()
            ->reverse()
            ->map(fn ($log) => [
                'role' => $log->role === 'user' ? 'user' : 'model',
                'content' => $log->message,
            ])
            ->toArray();
    }

     
    public function buildRagContext(string $query, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): string
    {
        $konteks = $this->rag->buildContext($query, $kategori, $materiId, $pertemuanId);

        $konteksSimbol = $this->symbols->buildContext(
            $query,
            $this->symbols->resolveDomains($kategori),
        );

        if ($konteksSimbol !== '') {
            $konteks = $konteks === '' ? $konteksSimbol : $konteks."\n\n".$konteksSimbol;
        }

        return $konteks;
    }

     
    public function sendMessage(string $message, string $sesiId, int $pertemuanId, int $userId, ?int $materiId = null): StreamedResponse
    {
        $this->saveMessage($userId, $sesiId, $materiId, $message, 'user');

        $kategori = Pertemuan::with('kelas')->find($pertemuanId)?->kelas?->kategori;

        $historyMsgs = $this->getHistory($sesiId);
        $ragContext = $this->buildRagContext($message, $kategori, $materiId, $pertemuanId);

        $references = $this->rag->getLastReferences();

        foreach ($references as &$ref) {
            if (isset($ref['materi_id']) && $ref['materi_id'] && ! isset($ref['url'])) {
                $ref['url'] = route('pelajar.materi.show', $ref['materi_id']);
            }
        }
        unset($ref);

        $webResults = $this->searchWeb($message);
        $webContext = '';
        $webRefIndex = count($references);
        if (! empty($webResults)) {
            $webContext = "\n\nInformasi tambahan dari internet:\n\n";
            foreach ($webResults as $i => $result) {
                $num = $webRefIndex + $i + 1;
                $webContext .= "[sumber internet*{$result['title']}] {$result['snippet']}\nURL: {$result['url']}\n\n";
                $references[] = [
                    'id' => $num,
                    'judul' => $result['title'],
                    'url' => $result['url'],
                    'type' => 'web',
                ];
            }
        }

        $referencesJson = ! empty($references) ? json_encode(['references' => $references]) : null;

        $fullPrompt = '';
        if ($ragContext) {
            $fullPrompt = "Konteks dari dokumen pembelajaran:\n{$ragContext}{$webContext}\n\nPertanyaan:\n{$message}";
        } elseif ($webContext) {
            $fullPrompt = "Informasi dari internet:\n{$webContext}\n\nPertanyaan:\n{$message}";
        } else {
            $fullPrompt = "TIDAK ADA INFORMASI TERSEDIA. Jawab dengan jujur bahwa kamu tidak memiliki informasi yang cukup untuk menjawab pertanyaan ini.\n\nPertanyaan:\n{$message}";
        }

        return response()->stream(function () use ($fullPrompt, $historyMsgs, $userId, $sesiId, $materiId, $referencesJson) {
            set_time_limit(0);
            @ini_set('output_buffering', '0');
            @ini_set('zlib.output_compression', 0);

            $fullResponse = '';

            echo 'data: '.json_encode(['event' => 'start'])."\n\n";
            @ob_flush();
            flush();

            if ($referencesJson) {
                echo 'data: '.$referencesJson."\n\n";
                @ob_flush();
                flush();
            }

            try {
                $stream = $this->ai->sendStreamLocalPreferred($fullPrompt, $historyMsgs);
                foreach ($stream as $token) {
                    $fullResponse .= $token;
                    echo 'data: '.json_encode(['token' => $token])."\n\n";
                    @ob_flush();
                    flush();
                }
            } catch (\Exception $e) {
                Log::error('ChatService sendMessage: {class} {msg}', [
                    'class' => get_class($e),
                    'msg' => $e->getMessage(),
                ]);
                $fullResponse = 'Maaf, layanan AI tidak tersedia saat ini. Silakan coba lagi.';
                echo 'data: '.json_encode(['token' => $fullResponse])."\n\n";
                @ob_flush();
                flush();
            }

            echo 'data: '.json_encode(['done' => true])."\n\n";
            @ob_flush();
            flush();

            if ($fullResponse) {
                $this->saveMessage($userId, $sesiId, $materiId, $fullResponse, 'assistant');
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    protected function searchWeb(string $query): array
    {
        try {
            return app(WebSearchService::class)->search($query);
        } catch (\Throwable $e) {
            Log::warning('ChatService searchWeb gagal: {msg}', ['msg' => $e->getMessage()]);

            return [];
        }
    }

     
    public function chat(int $userId, string $message, array $context = [], bool $useFallback = true, bool $preferLocal = false, ?string $kategori = null, ?int $pertemuanId = null): string
    {
        set_time_limit(0);

        $ragContext = $this->buildRagContext($message, $kategori, null, $pertemuanId);

        $contextLines = '';
        if (! empty($context)) {
            foreach ($context as $key => $value) {
                $contextLines .= "- {$key}: {$value}\n";
            }
        }

        if ($ragContext) {
            $fullMessage = "Dokumen pembelajaran:\n{$ragContext}\n\n";
            $fullMessage .= $contextLines ? "Konteks sistem saat ini:\n{$contextLines}\n\n" : '';
            $fullMessage .= "Pertanyaan:\n{$message}";
        } else {
            $fullMessage = $contextLines ? "Konteks sistem saat ini:\n{$contextLines}\n\n" : '';
            $fullMessage .= "TIDAK ADA DOKUMEN PEMBELAJARAN YANG RELEVAN. Jawab: Materi ini belum tersedia di sistem. Sarankan pengguna menghubungi operator atau guru untuk mengunggah materi terkait.\n\n";
            $fullMessage .= "Pertanyaan:\n{$message}";
        }

        ChatLog::create([
            'user_id' => $userId,
            'role' => 'user',
            'message' => $fullMessage,
        ]);

        $history = $this->getUserHistory($userId);

        try {
            if ($preferLocal) {
                $reply = $this->ai->sendLocalPreferred($fullMessage, $history);
            } elseif ($useFallback) {
                $reply = $this->ai->send($fullMessage, $history);
            } else {
                $reply = $this->ai->sendOllama($fullMessage, $history);
            }
        } catch (\Exception $e) {
            Log::error('ChatService chat: {class} {msg}', [
                'class' => get_class($e),
                'msg' => $e->getMessage(),
            ]);
            $reply = 'Maaf, layanan AI tidak tersedia.';
        }

        ChatLog::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'message' => $reply,
        ]);

        return $reply;
    }
}

