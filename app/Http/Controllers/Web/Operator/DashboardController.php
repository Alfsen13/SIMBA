<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\MateriRemedial;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
     
    public function index(): View
    {
        $stats = Cache::remember('operator_dashboard_stats', 60, fn () => [
            'total_pelajar' => User::where('role', 'pelajar')->count(),
            'total_pengajar' => User::where('role', 'pengajar')->count(),
            'total_kelas' => Kelas::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'total_materi' => Materi::count(),
            'total_tugas' => Tugas::count(),
            'total_remedial' => MateriRemedial::count(),
        ]);

        return view('operator.dashboard', compact('stats'));
    }
}

