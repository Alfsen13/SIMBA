<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\RunPrediksiJob;
use App\Models\FiturSiswaHistory;
use App\Models\PrediksiMendatang;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\User;
use App\Services\FiturPertemuanService;
use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiMendatangService;
use App\Services\RemedialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Throwable;

class PrediksiController extends Controller
{
    public function index(): View
    {
        $siswaList = User::where('role', 'pelajar')->where('status', 'verified')
            ->with(['kelasDiikuti' => fn ($q) => $q->where('guru_id', Auth::id()), 'prediksiAi'])
            ->whereHas('kelasDiikuti', fn ($q) => $q->where('guru_id', Auth::id()))
            ->paginate(15);

        // Daftar siswa yang punya pemicu remedial (risiko global atau nilai
        // pertemuan di bawah ambang), untuk menandai baris yang relevan.
        $butuhRemedial = app(RemedialService::class)->siswaButuhRemedial();
        $prediksiStatus = Cache::get(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'idle']);

        return view('guru.prediksi.index', compact('siswaList', 'butuhRemedial', 'prediksiStatus'));
    }

    public function show(Request $request): View
    {
        $siswaOptions = User::where('role', 'pelajar')->where('status', 'verified')
            ->with(['kelasDiikuti' => fn ($q) => $q->where('guru_id', Auth::id())])
            ->whereHas('kelasDiikuti', fn ($q) => $q->where('guru_id', Auth::id()))
            ->orderBy('name')
            ->get(['id', 'name']);

        $siswa = null;
        if ($request->query('siswa')) {
            $siswa = $siswaOptions->first(fn ($s) => (int) $s->id === (int) $request->query('siswa'));
            abort_if(! $siswa, 404);
        } else {
            $siswa = $siswaOptions->first();
        }

        $history = $siswa
            ? FiturSiswaHistory::with('pertemuan.kelas')
                ->where('siswa_id', $siswa->id)
                ->whereNotNull('pertemuan_id')
                ->whereHas('pertemuan', fn ($q) => $q->dibuka())
                ->get()
                ->sortBy(fn (FiturSiswaHistory $r) => [
                    $r->pertemuan?->kelas_id ?? 0,
                    $r->pertemuan?->urutan ?? 0,
                ])
                ->values()
            : collect();

        $fitur = $history->last();
        $pred = $siswa?->prediksiAi->first();

        // Hasil klasifikasi Random Forest untuk pertemuan terakhir. Ditampilkan
        // berdampingan dengan kategori baseline, bukan menggantikannya.
        $rf = ($siswa && $fitur?->pertemuan_id)
            ? PrediksiRisikoPertemuan::where('siswa_id', $siswa->id)
                ->where('pertemuan_id', $fitur->pertemuan_id)
                ->first()
            : null;

        $benchmarkRf = app(KlasifikasiRisikoService::class)->loadBenchmark();

        $chartData = $this->buildChartData($history);
        $mendatang = $this->buildForecast($siswa, $fitur);
        $prediksiStatus = Cache::get(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'idle']);

        return view('guru.prediksi.show', compact(
            'siswaOptions', 'siswa', 'fitur', 'pred', 'rf', 'benchmarkRf', 'history', 'chartData', 'mendatang', 'prediksiStatus'
        ));
    }

    public function status(): JsonResponse
    {
        return response()->json(
            Cache::get(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'idle'])
        );
    }

    protected function buildChartData(Collection $history): array
    {
        $labels = $history
            ->map(fn (FiturSiswaHistory $h) => $h->pertemuan?->nama_pertemuan ?? 'Pertemuan')
            ->values()
            ->all();

        $features = [];
        // Grafik "Perkembangan Indikator" memakai fitur yang sama dengan vektor
        // model; frekuensi akses tidak ikut (skalanya beda jauh).
        foreach (FiturPertemuanService::fiturModel() as $key) {
            $features[] = [
                'key' => $key,
                'label' => FiturPertemuanService::FEATURE_LABELS[$key],
                'suffix' => in_array($key, ['persen_tugas_terkumpul', 'persen_kehadiran'], true) ? '%' : '',
                'data' => $history
                    ->map(fn (FiturSiswaHistory $h) => $h->{$key} === null ? null : (float) $h->{$key})
                    ->values()
                    ->all(),
            ];
        }

        $aktivitas = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Frekuensi Akses',
                    'data' => $history->map(fn (FiturSiswaHistory $h) => $h->frekuensi_akses_pertemuan)->values()->all(),
                ],
                [
                    'label' => 'Durasi Belajar (menit)',
                    'data' => $history->map(fn (FiturSiswaHistory $h) => $h->rata_durasi_belajar)->values()->all(),
                ],
                [
                    'label' => 'Persen Kehadiran (%)',
                    'data' => $history->map(fn (FiturSiswaHistory $h) => $h->persen_kehadiran)->values()->all(),
                ],
            ],
        ];

        return [
            'riwayat' => [
                'labels' => $labels,
                'features' => $features,
            ],
            'aktivitas' => $aktivitas,
        ];
    }

    protected function buildForecast(?User $siswa, ?FiturSiswaHistory $fitur): array
    {
        if (! $siswa) {
            return ['siap' => false];
        }

        $svc = app(PrediksiMendatangService::class);
        $hasil = $svc->predict($siswa->id);

        if (isset($hasil['error'])) {
            return ['siap' => false, 'alasan' => $hasil['error']];
        }

        $prediksi = $hasil[PrediksiMendatangService::TARGET] ?? null;

        if ($prediksi === null) {
            return ['siap' => false];
        }

        // Nama pertemuan target & sumber diambil dari baris tersimpan supaya
        // halaman tidak menampilkan ID mentah.
        $tersimpan = PrediksiMendatang::with(['pertemuanSumber', 'pertemuanTarget'])
            ->where('siswa_id', $siswa->id)
            ->first();

        return [
            'siap' => true,
            'nilai' => $prediksi,
            'sekarang_nilai' => $fitur?->rata_nilai,
            'input' => $this->fiturInput($fitur),
            'pertemuan' => $fitur?->pertemuan?->nama_pertemuan,
            'pertemuan_target' => $tersimpan?->pertemuanTarget?->nama_pertemuan,
            'nilai_ideal' => $hasil['nilai_ideal'] ?? null,
            'persentase_meleset' => $hasil['persentase_meleset'] ?? null,
            'status_data' => $hasil['status_data'] ?? null,
            'model_versi' => $hasil['model_versi'] ?? null,
            'nilai_aktual' => $hasil['nilai_aktual'] ?? null,
            'mae_individual' => $hasil['mae_individual'] ?? null,
            'benchmark' => $svc->loadBenchmark(),
        ];
    }

    protected function fiturInput(?FiturSiswaHistory $fitur): array
    {
        if ($fitur === null) {
            return [];
        }

        $persen = ['persen_tugas_terkumpul', 'persen_kehadiran'];
        $out = [];

        // "Dasar ramalan" harus sama persis dengan input vektor SVR.
        foreach (FiturPertemuanService::fiturModel() as $key) {
            $nilai = $fitur->{$key};

            $out[] = [
                'label' => FiturPertemuanService::FEATURE_LABELS[$key],
                'suffix' => in_array($key, $persen, true) ? '%' : '',
                'nilai' => $nilai === null ? null : (float) $nilai,
            ];
        }

        return $out;
    }

    public function run(): RedirectResponse
    {
        return $this->jalankanPrediksi();
    }

    public function retrain(): RedirectResponse
    {
        return $this->jalankanPrediksi();
    }

    /**
     * Buat materi remedial untuk SATU pelajar, dijalankan langsung (sinkron)
     * di request ini. Dulu pembuatan remedial dijalankan sekaligus untuk semua
     * pelajar berisiko dari tombol "Jalankan Prediksi" - dengan AI yang kini
     * sinkron, itu berarti guru menunggu puluhan panggilan AI dalam satu
     * request. Sekarang dipisah per pelajar: satu klik = satu pelajar.
     */
    public function remedial(User $siswa): RedirectResponse
    {
        abort_unless($siswa->role === 'pelajar', 404);
        abort_unless(
            $siswa->kelasDiikuti()->where('guru_id', Auth::id())->exists(),
            403
        );

        set_time_limit(0);

        $hasil = app(RemedialService::class)->prosesSiswaBerisiko($siswa->id);

        if ($hasil['dijadwalkan'] > 0) {
            return back()->with('success', sprintf(
                '%d materi remedial dibuat untuk %s.',
                $hasil['dijadwalkan'],
                $siswa->name
            ));
        }

        return back()->with('warning', sprintf(
            'Tidak ada remedial baru untuk %s: %d kandidat dilewati (sudah ada yang belum diselesaikan atau masih dalam masa cooldown).',
            $siswa->name,
            $hasil['dilewati']
        ));
    }

    protected function jalankanPrediksi(): RedirectResponse
    {
        $status = Cache::get(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'idle']);
        if (in_array($status['status'] ?? null, ['queued', 'processing'], true)) {
            return back()->with('info', __('guru_analitik.prediksi.already_running'));
        }

        Cache::put(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'queued'], now()->addHours(2));

        try {
            RunPrediksiJob::dispatch();
        } catch (Throwable $exception) {
            Cache::put(RunPrediksiJob::STATUS_CACHE_KEY, ['status' => 'failed'], now()->addHours(2));
            report($exception);

            return back()->with('error', __('guru_analitik.prediksi.schedule_failed'));
        }

        if ((Cache::get(RunPrediksiJob::STATUS_CACHE_KEY)['status'] ?? null) === 'completed') {
            return back()->with('success', __('guru_analitik.prediksi.completed'));
        }

        return back()->with('info', __('guru_analitik.prediksi.queued'));
    }
}
