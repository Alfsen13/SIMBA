<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->password = 'password123';
});

test('guest can see login page', function () {
    $this->get(route('login'))->assertOk();
});

test('guest can see register page', function () {
    $this->get(route('register'))->assertOk();
});

test('guest can register with valid data', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $this->password,
        'password_confirmation' => $this->password,
        'role' => 'pelajar',
        'nisn' => '1234567890',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'pelajar',
        'status' => 'pending',
    ]);
});

test('guest cannot register with invalid data', function () {
    $response = $this->post(route('register'), [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'not-matching',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@test.com',
        'password' => Hash::make($this->password),
        'role' => 'pelajar',
        'status' => 'verified',
    ]);

    $response = $this->post(route('login'), [
        'email' => 'login@test.com',
        'password' => $this->password,
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();
});

test('user cannot login with wrong password', function () {
    $user = User::factory()->create([
        'email' => 'wrong@test.com',
        'password' => Hash::make($this->password),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'wrong@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('authenticated user can logout', function () {
    $user = User::factory()->verified()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect();

    $this->assertGuest();
});

test('guru with pending status redirected to verifikasi-pending', function () {
    $user = User::factory()->pengajar()->create(['status' => 'pending']);

    $this->actingAs($user)
        ->get(route('pengajar.dashboard'))
        ->assertRedirect(route('pending'));
});

test('siswa with pending status redirected to verifikasi-pending', function () {
    $user = User::factory()->create(['role' => 'pelajar', 'status' => 'pending']);

    $this->actingAs($user)
        ->get(route('pelajar.dashboard'))
        ->assertRedirect(route('pending'));
});

test('verified guru can access guru dashboard', function () {
    $user = User::factory()->pengajar()->verified()->create();

    $this->actingAs($user)
        ->get(route('pengajar.dashboard'))
        ->assertOk();
});

test('verified siswa can access siswa dashboard', function () {
    $user = User::factory()->verified()->create();

    $this->actingAs($user)
        ->get(route('pelajar.dashboard'))
        ->assertOk();
});

test('operator accessing guru route is redirected to operator dashboard', function () {
    $user = User::factory()->operator()->create();

    $this->actingAs($user)
        ->get(route('pengajar.dashboard'))
        ->assertRedirect(route('operator.dashboard'));
});

test('siswa accessing operator route is redirected to siswa dashboard', function () {
    $user = User::factory()->verified()->create();

    $this->actingAs($user)
        ->get(route('operator.dashboard'))
        ->assertRedirect(route('pelajar.dashboard'));
});
