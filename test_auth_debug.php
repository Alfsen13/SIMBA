<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'guru@simba.test',
    'password' => 'password',
    '_token' => csrf_token(),
], [], [], [
    'HTTP_COOKIE' => 'laravel-session=' . urlencode(session()->getId()),
]);

$app->instance('request', $request);

$credentials = ['email' => 'guru@simba.test', 'password' => 'password'];

echo 'Before Auth::attempt:' . PHP_EOL;
echo 'Session ID: ' . session()->getId() . PHP_EOL;
echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;

$result = Illuminate\Support\Facades\Auth::attempt(['email' => 'guru@simba.test', 'password' => 'password']);

echo 'Auth attempt: ' . ($result ? 'success' : 'failed') . PHP_EOL;

echo 'After Auth::attempt:' . PHP_EOL;
echo 'Session ID: ' . session()->getId() . PHP_EOL;
echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;

if ($result) {
    $request->session()->regenerate();
    echo 'After regenerate:' . PHP_EOL;
    echo 'Session ID: ' . session()->getId() . PHP_EOL;
    echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;
    
    session()->save();
    echo 'After save:' . PHP_EOL;
    echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;
}