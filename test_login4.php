<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate a login request
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => 'guru@simba.test',
    'password' => 'password',
    '_token' => 'HF8zWfKGKSa1VBkIUKDnrZFhRBvMcNiyc1gZO4kb',
]);

$app->instance('request', $request);

// Run the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

echo 'Response status: ' . $response->getStatusCode() . PHP_EOL;
echo 'Response headers: ' . PHP_EOL;
print_r($response->headers->all());

// Check session
$session = $request->session();
echo 'Session ID: ' . $session->getId() . PHP_EOL;
echo 'Session data: ' . json_encode($session->all()) . PHP_EOL;

// Check if user is authenticated
$user = Illuminate\Support\Facades\Auth::user();
if ($user) {
    echo 'Authenticated as: ' . $user->email . PHP_EOL;
} else {
    echo 'Not authenticated' . PHP_EOL;
}