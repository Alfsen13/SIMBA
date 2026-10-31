<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$router = app('router');
$routes = $router->getRoutes();

foreach ($routes as $route) {
    if ($route->uri() === 'login' && $route->methods()[0] === 'POST') {
        echo 'Route: ' . $route->uri() . PHP_EOL;
        echo 'Methods: ' . implode(', ', $route->methods()) . PHP_EOL;
        echo 'Middleware: ' . implode(', ', $route->gatherMiddleware()) . PHP_EOL;
        break;
    }
}