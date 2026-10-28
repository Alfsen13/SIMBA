<?php

use App\Models\ChatLog;
use App\Models\Materi;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->chatService = app(ChatService::class);
});

test('getUserHistory returns array of messages for user', function () {
    $user = User::factory()->create();
    ChatLog::create([
        'user_id' => $user->id,
        'role' => 'user',
        'message' => 'Halo',
    ]);
    ChatLog::create([
        'user_id' => $user->id,
        'role' => 'assistant',
        'message' => 'Hai, ada yang bisa saya bantu?',
    ]);

    $history = $this->chatService->getUserHistory($user->id);

    expect($history)->toBeArray();
    expect($history)->toHaveCount(2);
    expect($history[0])->toHaveKeys(['role', 'content']);
});

test('getUserHistory returns empty array for user with no chat', function () {
    $history = $this->chatService->getUserHistory(999);

    expect($history)->toBeArray()->toBeEmpty();
});

test('getHistory returns array of messages for sesi', function () {
    $user = User::factory()->create();
    $materi = Materi::factory()->create();
    $sesiId = (string) str()->uuid();

    ChatLog::create([
        'user_id' => $user->id,
        'sesi_id' => $sesiId,
        'materi_id' => $materi->id,
        'role' => 'user',
        'message' => 'Apa itu Integral?',
    ]);
    ChatLog::create([
        'user_id' => $user->id,
        'sesi_id' => $sesiId,
        'materi_id' => $materi->id,
        'role' => 'assistant',
        'message' => 'Integral adalah kebalikan dari turunan.',
    ]);

    $history = $this->chatService->getHistory($sesiId);

    expect($history)->toBeArray();
    expect($history)->toHaveCount(2);
    expect($history[0])->toHaveKeys(['role', 'content']);
});

test('buildRagContext returns string for a query', function () {
    $context = $this->chatService->buildRagContext('Apa itu Integral?');

    expect($context)->toBeString();
});
