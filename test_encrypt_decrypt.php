<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Create a fresh session and test encryption/decryption
$session = app('session');
$session->put('test', 'value');
$session->save();

$sessionId = $session->getId();
echo 'Session ID: ' . $sessionId . PHP_EOL;

// Get the encrypted cookie value
$encrypted = app('encrypter')->encrypt($sessionId);
echo 'Encrypted: ' . $encrypted . PHP_EOL;

// Decrypt it back
$decrypted = app('encrypter')->decrypt($encrypted);
echo 'Decrypted: ' . $decrypted . PHP_EOL;

// Check if it matches
echo 'Match: ' . ($decrypted === $sessionId ? 'yes' : 'no') . PHP_EOL;