<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\MateriRemedial;
use App\Models\Notifikasi;
use App\Models\PrediksiAi;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = Cache::remember('admin_dashboard_stats', 60, fn () => [
            'total_users' => User::count(),
            'total_pengajar' => User::where('role', 'pengajar')->count(),
            'total_pelajar' => User::where('role', 'pelajar')->count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'total_kelas' => Kelas::count(),
            'rejected_users' => User::where('status', 'rejected')->count(),
            'operator_users' => User::where('role', 'operator')->count(),
            'total_materi' => Materi::count(),
            'total_tugas' => Tugas::count(),
            'total_kuis' => Kuis::count(),
            'remedial_count' => MateriRemedial::count(),
            'berisiko_count' => PrediksiAi::where('hasil', 'berisiko_tinggi')->count(),
        ]);

        $recentUsers = User::latest()->take(5)->get();
        $recentNotifications = Notifikasi::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentNotifications'));
    }
}
