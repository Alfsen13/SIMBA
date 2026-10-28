<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\TugasJawabanRequest;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Services\Tugas\PengumpulanTugasService;
use App\Services\Tugas\TugasPresenter;
use App\Services\Tugas\TugasService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

 
class TugasController extends Controller
{
    public function __construct(
        private readonly TugasService $tugas,
        private readonly PengumpulanTugasService $pengumpulan,
        private readonly TugasPresenter $presenter,
    ) {}

    public function index(): View
    {
        $tugasList = $this->tugas->daftarUntukSiswa(Auth::user());

        return view('siswa.tugas.index', compact('tugasList'));
    }

    public function show(Tugas $tugas): View
    {
        $user = Auth::user();
        $this->pastikanIkutKelas($user, $tugas);
        $this->pastikanPertemuanDibuka($tugas);

        $tugas->load('kelas');

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $user->id)->first();

        $soalTampil = $this->presenter->soalUntukSiswa(
            $tugas->soal,
            (string) $tugas->tipe,
            $pengumpulan
        );

        return view('siswa.tugas.show', compact('tugas', 'pengumpulan', 'soalTampil'));
    }

    public function mulai(Tugas $tugas): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanIkutKelas($user, $tugas);
        $this->pastikanPertemuanDibuka($tugas);

        if (! $tugas->batas_jam) {
            return redirect()
                ->route('pelajar.tugas.show', $tugas)
                ->with('error', 'Tugas ini belum diatur batas waktu pengerjaan oleh pengajar.');
        }

        $pengumpulan = PengumpulanTugas::firstOrCreate(
            ['tugas_id' => $tugas->id, 'siswa_id' => $user->id],
            ['jawaban' => [], 'terlambat' => false]
        );

        if ($pengumpulan->dimulai_pada) {
            return redirect()
                ->route('pelajar.tugas.show', $tugas)
                ->with('info', 'Kamu sudah memulai tugas ini.');
        }

        $pengumpulan->update([
            'dimulai_pada' => now(),
            'batas_kerjakan' => now()->addHours((int) $tugas->batas_jam),
        ]);

        return redirect()
            ->route('pelajar.tugas.show', $tugas)
            ->with('success', 'Waktu pengerjaan dimulai. Perhatikan sisa waktu ya.');
    }

    public function submit(TugasJawabanRequest $request, Tugas $tugas): RedirectResponse
    {
        $user = Auth::user();
        $this->pastikanIkutKelas($user, $tugas);
        $this->pastikanPertemuanDibuka($tugas);

        if ($tugas->batas_jam) {
            $pengumpulanSaatIni = PengumpulanTugas::where('tugas_id', $tugas->id)
                ->where('siswa_id', $user->id)
                ->first();

            if (! $pengumpulanSaatIni || ! $pengumpulanSaatIni->dimulai_pada) {
                return redirect()
                    ->route('pelajar.tugas.show', $tugas)
                    ->with('error', 'Silakan klik Mulai Kerjakan terlebih dahulu untuk memulai hitungan waktu.');
            }
        }

        $validated = $request->validated();

        $pengumpulan = $this->pengumpulan->submit(
            $tugas,
            $user,
            $request->file('file'),
            $validated['jawaban'] ?? null
        );

        return redirect()
            ->route('pelajar.tugas.show', $tugas)
            ->with('success', $this->presenter->pesanSubmit((string) $tugas->tipe, (bool) $pengumpulan->terlambat));
    }

     
    private function pastikanIkutKelas(Authenticatable $user, Tugas $tugas): void
    {
        abort_unless(
            $user->kelasDiikuti()->where('kelas_id', $tugas->kelas_id)->exists(),
            403
        );
    }

     
    private function pastikanPertemuanDibuka(Tugas $tugas): void
    {
        if ($tugas->pertemuan !== null && ! $tugas->pertemuan->dibuka) {
            abort(403, 'Pertemuan ini belum dibuka oleh pengajar.');
        }
    }
}

