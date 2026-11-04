<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role','pelajar')->first();
echo 'User: ' . $user->name . PHP_EOL;

$kelas = App\Models\Kelas::find(10);
$pertemuan = $kelas->pertemuan()->find(1);

echo 'Kelas: ' . $kelas->nama_kelas . PHP_EOL;
echo 'Pertemuan: ' . $pertemuan->nama_pertemuan . PHP_EOL;

// Set authenticated user
Illuminate\Support\Facades\Auth::login($user);

// Share errors bag
$app->make('view')->share('errors', new Illuminate\Support\MessageBag());

$controller = new App\Http\Controllers\Web\Siswa\AITutorController();
$request = Illuminate\Http\Request::create('/pelajar/ai-tutor/kelas/10/pertemuan/1', 'GET');

try {
    $response = $controller->chat($kelas, $pertemuan);
    echo 'Response type: ' . get_class($response) . PHP_EOL;
    if (method_exists($response, 'getStatusCode')) {
        echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
    }
    if (method_exists($response, 'render')) {
        $content = $response->render();
        echo 'Content length: ' . strlen($content) . PHP_EOL;
        echo 'SUCCESS! View rendered correctly.' . PHP_EOL;
    }
} catch (\Throwable $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
}