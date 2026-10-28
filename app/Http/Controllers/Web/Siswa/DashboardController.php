<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\FiturHybridSiswa;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Materi;
use App\Models\MateriRemedial;
use App\Models\NilaiHarian;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $kelasIds = $user->kelasDiikuti()->pluck('kelas.id');

        $cacheKey = "siswa_dashboard_stats_{$user->id}";
        $stats = Cache::remember($cacheKey, 60, fn () => [
            'total_kelas' => $kelasIds->count(),
            'total_tugas' => Tugas::whereIn('kelas_id', $kelasIds)->count(),
            'total_kuis' => Kuis::whereIn('kelas_id', $kelasIds)->count(),
            'rata_nilai' => NilaiHarian::where('siswa_id', $user->id)->avg('nilai') ?? 0,
            'rata_nilai_akademik' => FiturHybridSiswa::where('siswa_id', $user->id)->avg('rata_nilai') ?? 0,
            'persen_terlambat' => FiturHybridSiswa::where('siswa_id', $user->id)->avg('persen_terlambat') ?? 0,
            'remedial_belum_dibaca' => MateriRemedial::where('siswa_id', $user->id)
                ->where('status', 'belum_dibaca')
                ->count(),
        ]);

        $kelasDiikuti = $user->kelasDiikuti()
            ->withCount('pertemuan')
            ->withCount('materi')
            ->latest()
            ->get();

        $materiTerbaru = Materi::whereIn('kelas_id', $kelasIds)
            ->with('kelas', 'pertemuan')
            ->latest()
            ->take(5)
            ->get();

        $tugasTerbaru = Tugas::whereIn('kelas_id', $kelasIds)
            ->with('kelas')
            ->latest()
            ->take(10)
            ->get();
        $pengumpulanIndex = PengumpulanTugas::whereIn('tugas_id', $tugasTerbaru->pluck('id'))
            ->where('siswa_id', $user->id)
            ->get()
            ->keyBy('tugas_id');
        $tugasTerbaru->each(fn ($t) => $t->pengumpulan = $pengumpulanIndex->get($t->id));

        $kuisTersedia = Kuis::whereIn('kelas_id', $kelasIds)
            ->where('is_aktif', true)
            ->with('kelas')
            ->latest()
            ->take(6)
            ->get();
        $hasilIndex = KuisHasil::whereIn('kuis_id', $kuisTersedia->pluck('id'))
            ->where('siswa_id', $user->id)
            ->get()
            ->keyBy('kuis_id');
        $kuisTersedia->each(fn ($k) => $k->hasil = $hasilIndex->get($k->id));

        $remedialList = MateriRemedial::where('siswa_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('siswa.dashboard', compact('stats', 'kelasDiikuti', 'materiTerbaru', 'tugasTerbaru', 'kuisTersedia', 'remedialList'));
    }
}
