<?php

use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->verified()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;

    $this->withHeader('Accept', 'application/json');
});

function setAuthToken($test)
{
    $test->withHeader('Authorization', 'Bearer '.$test->token);
}

test('unauthenticated user cannot start a session', function () {
    $this->postJson(route('api.chat.start'), [
        'pertemuan_id' => 1,
    ])->assertStatus(401);
});

test('unauthenticated user cannot send a message', function () {
    $this->postJson(route('api.chat.send'), [
        'sesi_id' => 'fake-uuid',
        'pertemuan_id' => 1,
        'pesan' => 'Hello',
    ])->assertStatus(401);
});

test('authenticated user can start a session', function () {
    setAuthToken($this);
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    $response = $this->postJson(route('api.chat.start'), [
        'pertemuan_id' => $pertemuan->id,
    ]);

    $response->assertOk()
        ->assertJsonStructure(['success', 'data' => ['sesi_id']]);
});

test('send endpoint returns sse content type for authenticated user', function () {
    setAuthToken($this);
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    $startResponse = $this->postJson(route('api.chat.start'), [
        'pertemuan_id' => $pertemuan->id,
    ]);
    $sesiId = $startResponse->json('data.sesi_id');

    $response = $this->post(route('api.chat.send'), [
        'sesi_id' => $sesiId,
        'pertemuan_id' => $pertemuan->id,
        'pesan' => 'Apa itu PHP?',
    ]);

    $response->assertOk();
    $response->assertHeader('Content-Type');
    expect($response->headers->get('Content-Type'))->toContain('text/event-stream');
});

test('end session dispatches job', function () {
    setAuthToken($this);
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    $startResponse = $this->postJson(route('api.chat.start'), [
        'pertemuan_id' => $pertemuan->id,
    ]);
    $sesiId = $startResponse->json('data.sesi_id');

    $this->postJson(route('api.chat.end'), [
        'sesi_id' => $sesiId,
    ])->assertOk();
});

test('history endpoint returns chat logs', function () {
    setAuthToken($this);
    $guru = User::factory()->pengajar()->verified()->create();
    $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
    $pertemuan = Pertemuan::create([
        'kelas_id' => $kelas->id,
        'nama_pertemuan' => 'Pertemuan 1',
        'urutan' => 1,
    ]);

    $startResponse = $this->postJson(route('api.chat.start'), [
        'pertemuan_id' => $pertemuan->id,
    ]);
    $sesiId = $startResponse->json('data.sesi_id');

    $response = $this->getJson(route('api.chat.history', ['sesi_id' => $sesiId]));

    $response->assertOk()
        ->assertJsonStructure(['success', 'data']);
});
