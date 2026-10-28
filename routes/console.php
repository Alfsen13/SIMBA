<?php

use App\Console\Commands\FlushChatLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:flush', function () {
    $this->call(FlushChatLog::class);
})->purpose('Truncate chat tables');

