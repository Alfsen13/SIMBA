<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'guru@simba.test',
    'password' => 'password',
    '_token' => 'HF8zWfKGKSa1VBkIUKDnrZFhRBvMcNiyc1gZO4kb',
]);

$app->instance('request', $request);

$result = Illuminate\Support\Facades\Auth::attempt(['email' => 'guru@simba.test', 'password' => 'password']);
echo 'Auth attempt: ' . ($result ? 'success' : 'failed') . PHP_EOL;

if ($result) {
    echo 'User: ' . Illuminate\Support\Facades\Auth::user()->email . PHP_EOL;
}