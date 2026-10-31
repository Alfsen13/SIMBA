<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate the exact login request
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'guru@simba.test',
    'password' => 'password',
    '_token' => csrf_token(),
], [], [], [
    'HTTP_COOKIE' => 'laravel-session=' . urlencode(session()->getId()),
]);

$app->instance('request', $request);

$credentials = ['email' => 'guru@simba.test', 'password' => 'password'];

if (!Illuminate\Support\Facades\Auth::attempt($credentials, false)) {
    echo 'Auth failed' . PHP_EOL;
    exit;
}

echo 'Auth success' . PHP_EOL;

$request->session()->regenerate();
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

$redirect = redirect()->intended(route($route));
echo 'Redirect URL: ' . $redirect->getTargetUrl() . PHP_EOL;
echo 'Redirect status: ' . $redirect->getStatusCode() . PHP_EOL;