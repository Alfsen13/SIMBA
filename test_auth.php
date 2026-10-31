<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'guru@simba.test')->first();
echo "User: " . $user->email . " Role: " . $user->role . " Status: " . $user->status . PHP_EOL;

$result = Illuminate\Support\Facades\Auth::attempt(['email' => 'guru@simba.test', 'password' => 'password']);
echo 'Auth attempt: ' . ($result ? 'success' : 'failed') . PHP_EOL;

if ($result) {
    echo 'Logged in as: ' . Illuminate\Support\Facades\Auth::user()->email . PHP_EOL;
}