<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$router = app('router');
$routes = $router->getRoutes();

foreach ($routes as $route) {
    if ($route->uri() === 'login' && in_array('POST', $route->methods())) {
        echo 'Route: ' . $route->uri() . PHP_EOL;
        echo 'Methods: ' . implode(', ', $route->methods()) . PHP_EOL;
        echo 'Middleware: ' . implode(', ', $route->gatherMiddleware()) . PHP_EOL;
        
        // Check the middleware stack
        $middleware = $route->gatherMiddleware();
        foreach ($middleware as $m) {
            echo '  Middleware: ' . $m . PHP_EOL;
        }
        break;
    }
}