<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\MateriRemedial;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $guruId = Auth::id();
        $kelasIds = Kelas::query()
            ->where('guru_id', $guruId)
            ->select('id');
        $siswaIds = User::query()
            ->where('role', 'pelajar')
            ->whereHas('kelasDiikuti', fn ($q) => $q->whereIn('kelas_id', $kelasIds))
            ->select('users.id');

        $cacheKey = "guru_dashboard_stats_{$guruId}";
        $stats = Cache::remember($cacheKey, 60, fn () => [
            'total_kelas' => (clone $kelasIds)->count(),
            'total_materi' => Materi::where('guru_id', $guruId)->count(),
            'total_tugas' => Tugas::where('guru_id', $guruId)->count(),
            'total_pertemuan' => Pertemuan::whereIn('kelas_id', clone $kelasIds)->count(),
            'total_kuis' => Kuis::whereIn('kelas_id', clone $kelasIds)->count(),
            'total_siswa' => (clone $siswaIds)->count(),
            'total_remedial' => MateriRemedial::whereIn('siswa_id', clone $siswaIds)->count(),
            'siswa_berisiko' => PrediksiAi::where('hasil', 'berisiko_tinggi')
                ->whereIn('siswa_id', clone $siswaIds)
                ->count(),
        ]);

        $kelasList = Kelas::where('guru_id', $guruId)
            ->withCount('siswa')
            ->withCount('pertemuan')
            ->latest()
            ->take(6)
            ->get();

        $remedialList = MateriRemedial::whereIn('siswa_id', clone $siswaIds)
            ->with('siswa')
            ->latest()
            ->take(5)
            ->get();

        $siswaBerisikoList = PrediksiAi::where('hasil', 'berisiko_tinggi')
            ->whereIn('siswa_id', clone $siswaIds)
            ->with('siswa')
            ->orderByDesc('skor_risiko')
            ->take(5)
            ->get();

        return view('guru.dashboard', compact('stats', 'kelasList', 'remedialList', 'siswaBerisikoList'));
    }
}
