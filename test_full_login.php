<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate the full login flow with cookie jar
$cookieJar = new Symfony\Component\HttpClient\CookieJar\CookieJar();

// Step 1: GET login page to get CSRF token and session cookie
$client = Symfony\Component\HttpClient\HttpClient::create(['cookie_jar' => $cookieJar]);
$response = $client->request('GET', 'http://localhost:8000/login');

echo 'GET login page: ' . $response->getStatusCode() . PHP_EOL;

// Extract CSRF token
$content = $response->getContent();
if (preg_match('/name="_token" value="([^"]+)"/', $content, $matches)) {
    $token = $matches[1];
    echo 'CSRF token: ' . $token . PHP_EOL;
} else {
    echo 'CSRF token not found' . PHP_EOL;
    exit;
}

// Step 2: POST login
$response = $client->request('POST', 'http://localhost:8000/login', [
    'body' => [
        '_token' => $token,
        'email' => 'guru@simba.test',
        'password' => 'password',
    ],
    'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
    ],
]);

echo 'POST login: ' . $response->getStatusCode() . PHP_EOL;
echo 'Redirect URL: ' . $response->getHeaders()['location'][0] ?? 'none' . PHP_EOL;

// Check cookies
$cookies = $cookieJar->all();
foreach ($cookies as $cookie) {
    echo 'Cookie: ' . $cookie->getName() . ' = ' . substr($cookie->getValue(), 0, 50) . '...' . PHP_EOL;
}