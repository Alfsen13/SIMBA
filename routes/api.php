<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

RateLimiter::for('chat', function ($request) {
    $userId = optional($request->user('sanctum'))->id ?? $request->ip();

    return [
        \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($userId),
        \Illuminate\Cache\RateLimiting\Limit::perHour(300)->by($userId),
    ];
});

Route::middleware([ForceJsonResponse::class])->group(function () {
    Route::get('materi/{id}/file', function ($id) {
        return response()->json(['message' => 'File serve endpoint (placeholder)']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:chat'])->group(function () {
    Route::post('chat/start', [ChatController::class, 'start'])->name('api.chat.start');
    Route::post('chat/send', [ChatController::class, 'send'])->name('api.chat.send');
    Route::post('chat/end', [ChatController::class, 'end'])->name('api.chat.end');
    Route::get('chat/history', [ChatController::class, 'history'])->name('api.chat.history');
});
