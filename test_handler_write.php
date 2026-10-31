<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$handler = app('session')->getHandler();
echo 'Handler: ' . get_class($handler) . PHP_EOL;

if ($handler instanceof \Illuminate\Session\CacheBasedSessionHandler) {
    echo 'Using CacheBasedSessionHandler' . PHP_EOL;
    $cache = $handler->getCache();
    echo 'Handler cache: ' . get_class($cache) . PHP_EOL;
    
    // Test the cache directly
    $result = $cache->put('handler_test', 'handler_value', 60);
    echo 'Handler cache put: ' . ($result ? 'true' : 'false') . PHP_EOL;
    
    $value = $cache->get('handler_test');
    echo 'Handler cache get: ' . $value . PHP_EOL;
}

// Test session save with the handler
$session = app('session');
$session->start();
$session->put('test', 'value');
echo 'Session ID: ' . $session->getId() . PHP_EOL;

// Manually call the handler's write method
$handler->write($session->getId(), json_encode($session->getAttributes()));
echo 'Handler write called' . PHP_EOL;

// Check Redis
$redis = app('redis')->connection('cache');
$key = 'simba-database-simba-cache-session_' . $session->getId();
$data = $redis->get($key);
echo 'Redis data after write: ' . $data . PHP_EOL;