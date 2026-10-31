<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'guru@simba.test',
    'password' => 'password',
    '_token' => csrf_token(),
]);

$app->instance('request', $request);

$credentials = ['email' => 'guru@simba.test', 'password' => 'password'];

echo 'Before Auth::attempt:' . PHP_EOL;
echo 'Session ID: ' . session()->getId() . PHP_EOL;
echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;

$result = Illuminate\Support\Facades\Auth::attempt(['email' => 'guru@simba.test', 'password' => 'password']);

echo 'Auth attempt: ' . ($result ? 'success' : 'failed') . PHP_EOL;

if ($result) {
    $request->session()->regenerate();
    echo 'Session regenerated' . PHP_EOL;
    
    $user = Illuminate\Support\Facades\Auth::user();
    echo 'User: ' . $user->email . ' Role: ' . $user->role . PHP_EOL;
    
    echo 'Session ID after regenerate: ' . session()->getId() . PHP_EOL;
    echo 'Session data after regenerate: ' . json_encode(session()->all()) . PHP_EOL;
    
    session()->save();
    echo 'Session saved' . PHP_EOL;
    
    // Check Redis
    $redis = app('redis')->connection();
    $sessionId = session()->getId();
    $sessionKey = 'laravel_database_laravel_cache_session_' . $sessionId;
    $data = $redis->get('laravel_database_laravel_cache_session_' . $sessionId);
    echo 'Redis session data: ' . $data . PHP_EOL;
}