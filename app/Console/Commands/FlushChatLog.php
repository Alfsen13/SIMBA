<?php

namespace App\Console\Commands;

use App\Models\ChatLog;
use App\Models\ChatSummary;
use App\Models\SesiSummary;
use Illuminate\Console\Command;

class FlushChatLog extends Command
{
    protected $signature = 'chat:flush';

    protected $description = 'Truncate chat tables and archive summaries';

     
    public function handle(): int
    {
        $this->info('Flushing chat logs...');

        ChatSummary::truncate();
        SesiSummary::truncate();
        ChatLog::truncate();

        $this->info('Chat tables flushed successfully.');

        return Command::SUCCESS;
    }
}

