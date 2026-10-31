<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate a login request with correct CSRF token
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'guru@simba.test',
    'password' => 'password',
    '_token' => csrf_token(),
], [], [], [
    'HTTP_COOKIE' => 'laravel-session=' . urlencode(session()->getId()),
]);

$app->instance('request', $request);

$result = Illuminate\Support\Facades\Auth::attempt(['email' => 'guru@simba.test', 'password' => 'password']);
echo 'Auth attempt: ' . ($result ? 'success' : 'failed') . PHP_EOL;

if ($result) {
    echo 'User: ' . Illuminate\Support\Facades\Auth::user()->email . PHP_EOL;
}

$request->session()->save();
echo 'Session ID: ' . session()->getId() . PHP_EOL;
echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;