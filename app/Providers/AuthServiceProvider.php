<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Notifikasi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Policies\KelasPolicy;
use App\Policies\KuisPolicy;
use App\Policies\MateriPolicy;
use App\Policies\NotifikasiPolicy;
use App\Policies\PertemuanPolicy;
use App\Policies\TugasPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kelas::class => KelasPolicy::class,
        Pertemuan::class => PertemuanPolicy::class,
        Materi::class => MateriPolicy::class,
        Tugas::class => TugasPolicy::class,
        Kuis::class => KuisPolicy::class,
        Notifikasi::class => NotifikasiPolicy::class,
    ];

     
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

