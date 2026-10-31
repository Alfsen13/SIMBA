<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::firstOrCreate(
    ['email' => 'siswa@simba.test'],
    ['name' => 'Siswa Test', 'password' => Hash::make('password'), 'role' => 'pelajar', 'status' => 'verified', 'verified_at' => now()]
);
echo "Created user: " . $user->email . " with role: " . $user->role . PHP_EOL;