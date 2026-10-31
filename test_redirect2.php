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

$result = Illuminate\Support\Facades\Auth::attempt($credentials, false);
echo 'Auth attempt: ' . ($result ? 'success' : 'failed') . PHP_EOL;

if ($result) {
    $request->session()->regenerate();
    echo 'Session regenerated' . PHP_EOL;
    
    $user = Illuminate\Support\Facades\Auth::user();
    echo 'User: ' . $user->email . ' Role: ' . $user->role . PHP_EOL;
    
    $route = match ($user->role) {
        'pengelola_operator' => 'pengelola-operator.dashboard',
        'operator' => 'operator.dashboard',
        'pengajar' => 'pengajar.dashboard',
        'pelajar' => 'pelajar.dashboard',
        default => 'home',
    };
    
    echo 'Route: ' . $route . PHP_EOL;
    
    echo 'Session ID: ' . session()->getId() . PHP_EOL;
    echo 'Session data: ' . json_encode(session()->all()) . PHP_EOL;
    
    session()->save();
    echo 'Session saved' . PHP_EOL;
}