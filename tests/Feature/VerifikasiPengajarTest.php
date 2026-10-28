<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(RefreshDatabase::class);

test('calon pengajar tidak dapat mengakses fitur pengajar penuh sebelum disetujui', function () {
    $guru = User::factory()->pengajar()->create(['status' => 'pending']);
    $this->actingAs($guru)->get(route('pengajar.dashboard'))->assertRedirect(route('pending'));
});

test('calon pengajar terverifikasi dapat mengakses dashboard pengajar', function () {
    $guru = User::factory()->pengajar()->verified()->create();
    $this->actingAs($guru)->get(route('pengajar.dashboard'))->assertOk();
});
