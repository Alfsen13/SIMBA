<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\KuisJawabanRequest;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Services\RemedialService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KuisController extends Controller
{
    public function __construct(private readonly RemedialService $remedial) {}

    public function index(): View
    {
        $user = Auth::user();
        $kelasIds = $user->kelasDiikuti->pluck('id');
        $now = now();
        $kuisList = Kuis::whereIn('kelas_id', $kelasIds)
            ->where('is_aktif', true)
            ->where(fn (Builder $query) => $query->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $now))
            ->where(fn (Builder $query) => $query->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $now))
            ->where(fn ($q) => $q->whereNull('pertemuan_id')->orWhereHas('pertemuan', fn ($p) => $p->dibuka()))
            ->with('kelas')
            ->withCount(['hasil as sudah_dikerjakan' => function ($q) {
                $q->where('siswa_id', Auth::id());
            }])
            ->latest()
            ->paginate(10);

        return view('siswa.kuis.index', compact('kuisList'));
    }

    public function show(Kuis $kuis): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $kuis->kelas_id)->exists()) {
            abort(403);
        }

        $hasil = KuisHasil::where('kuis_id', $kuis->id)
            ->where('siswa_id', $user->id)->first();

        if ($hasil) {
            return redirect()->route('pelajar.kuis.hasil', $kuis);
        }

        $this->pastikanKuisDapatDikerjakan($kuis);

        return view('siswa.kuis.show', compact('kuis'));
    }

    public function submit(KuisJawabanRequest $request, Kuis $kuis): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $kuis->kelas_id)->exists()) {
            abort(403);
        }

        $already = KuisHasil::where('kuis_id', $kuis->id)
            ->where('siswa_id', $user->id)->exists();
        if ($already) {
            return redirect()->route('pelajar.kuis.hasil', $kuis);
        }

        $this->pastikanKuisDapatDikerjakan($kuis);

        $validated = $request->validated();

        $soal = $kuis->soal;
        $skor = 0;
        $total = count($soal);

        foreach ($soal as $i => $s) {
            $jawabanBenar = (int) ($s['jawaban'] ?? 0);
            $jawabanSiswa = $validated['jawaban'][$i] ?? '';
            if ((int) $jawabanSiswa === $jawabanBenar) {
                $skor++;
            }
        }

        $nilai = $total > 0 ? round(($skor / $total) * 100) : 0;

        $hasil = KuisHasil::firstOrCreate(
            ['kuis_id' => $kuis->id, 'siswa_id' => $user->id],
            ['skor' => $nilai, 'jawaban_siswa' => $validated['jawaban']]
        );

        if (! $hasil->wasRecentlyCreated) {
            return redirect()->route('pelajar.kuis.hasil', $kuis);
        }

        // Nilai kuis baru bisa menurunkan skor pertemuan di bawah ambang.
        $this->remedial->periksaSetelahKuis($kuis, (int) $user->id);

        return redirect()->route('pelajar.kuis.hasil', $kuis)
            ->with('success', "Kuis selesai! Skor: {$nilai}");
    }

    public function hasil(Kuis $kuis): View
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $kuis->kelas_id)->exists()) {
            abort(403);
        }

        $hasil = KuisHasil::where('kuis_id', $kuis->id)
            ->where('siswa_id', $user->id)->firstOrFail();

        return view('siswa.kuis.hasil', compact('kuis', 'hasil'));
    }

    private function pastikanKuisDapatDikerjakan(Kuis $kuis): void
    {
        $now = now();

        if (! $kuis->is_aktif) {
            abort(403, 'Kuis ini tidak sedang aktif.');
        }

        if ($kuis->tanggal_mulai !== null && $kuis->tanggal_mulai->isAfter($now)) {
            abort(403, 'Kuis ini belum dimulai.');
        }

        if ($kuis->tanggal_selesai !== null && $kuis->tanggal_selesai->isBefore($now)) {
            abort(403, 'Waktu pengerjaan kuis ini sudah berakhir.');
        }

        if ($kuis->pertemuan !== null && ! $kuis->pertemuan->dibuka) {
            abort(403, 'Pertemuan ini belum dibuka oleh pengajar.');
        }
    }
}
