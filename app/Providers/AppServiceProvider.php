<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Kelas;
use App\Models\Materi;
use App\Observers\KelasObserver;
use App\Observers\MateriObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Materi::observe(MateriObserver::class);
        Kelas::observe(KelasObserver::class);
    }
}
