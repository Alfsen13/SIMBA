<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EndChatRequest;
use App\Http\Requests\Api\HistoryRequest;
use App\Http\Requests\Api\SendChatRequest;
use App\Http\Requests\Api\StartChatRequest;
use App\Services\ChatService;
use App\Services\ChatSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService,
        protected ChatSessionService $sessionService,
    ) {}

      
    public function start(StartChatRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::guard('sanctum')->user();
        $session = $this->sessionService->startSession(
            $user->id,
            $validated['pertemuan_id'],
            $validated['materi_id'] ?? null,
        );

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

      
    public function send(SendChatRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $user = Auth::guard('sanctum')->user();
        $this->sessionService->assertOwned($validated['sesi_id'], (int) $user->id);

        return $this->chatService->sendMessage(
            $validated['pesan'],
            $validated['sesi_id'],
            $validated['pertemuan_id'],
            $user->id,
            $validated['materi_id'] ?? null,
        );
    }

      
    public function end(EndChatRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::guard('sanctum')->user();
        $this->sessionService->endSession($validated['sesi_id'], (int) $user->id);

        return response()->json(['success' => true]);
    }

      
    public function history(HistoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::guard('sanctum')->user();
        $chatLogs = $this->sessionService->getSessionHistory($validated['sesi_id'], (int) $user->id);

        return response()->json([
            'success' => true,
            'data' => $chatLogs,
        ]);
    }
}

