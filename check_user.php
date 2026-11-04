<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role','pelajar')->first();
echo 'User: ' . $user->name . PHP_EOL;
echo 'Email: ' . $user->email . PHP_EOL;
echo 'Password check: ' . (password_verify('password', $user->password) ? 'OK' : 'FAIL') . PHP_EOL;