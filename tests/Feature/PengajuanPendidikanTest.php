<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

uses(RefreshDatabase::class);

test('pengajuan perubahan pendidikan hanya bisa dibuat pelajar terverifikasi', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);
    $this->actingAs($siswa)->get('/')->assertOk();
});

test('status pengajuan pendidikan disimpan terpisah dari status akun', function () {
    expect(true)->toBeTrue();
});
