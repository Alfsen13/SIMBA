<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$view = $app->make('view');
$engine = $view->getEngineFromPath('resources/views/layouts/app.blade.php');
echo 'Engine: ' . get_class($engine) . PHP_EOL;
$compiler = $engine->getCompiler();
echo 'Compiler: ' . get_class($compiler) . PHP_EOL;
$compiledPath = $compiler->compile('resources/views/layouts/app.blade.php');
echo 'Compiled path: [' . $compiledPath . ']' . PHP_EOL;
if ($compiledPath) {
    echo file_get_contents($compiledPath);
} else {
    echo 'Compiled path is empty!' . PHP_EOL;
    // Try alternative
    $paths = $compiler->getCompiledPath('resources/views/layouts/app.blade.php');
    echo 'getCompiledPath: ' . $paths . PHP_EOL;
}