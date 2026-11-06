<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\GenerateSoalTugasRequest;
use App\Http\Requests\Guru\NilaiTugasRequest;
use App\Http\Requests\Guru\TugasRequest;
use App\Http\Requests\Guru\UpdateTugasRequest;
use App\Jobs\GenerateSoalTugasAI;
use App\Jobs\PeriksaRemedialSetelahNilaiTugas;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Services\BloomTaxonomyService;
use App\Services\KuisGeneratorService;
use App\Services\Tugas\PengumpulanTugasService;
use App\Services\Tugas\TugasPresenter;
use App\Services\Tugas\TugasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $tugasList = $this->tugas->daftarUntukGuru((int) Auth::id());
        $tugasBelumDinilai = $this->tugas->tugasBelumDinilai((int) Auth::id());

        return view('guru.tugas.index', compact('tugasList', 'tugasBelumDinilai'));
    }

    public function create(): View
    {
        $pertemuan = Pertemuan::with('kelas')->findOrFail(request('pertemuan_id'));
        $this->authorize('create', [Tugas::class, $pertemuan->kelas]);

        return view('guru.tugas.create', $this->dataForm($pertemuan));
    }

    public function store(TugasRequest $request)
    {
        // Validasi input tugas
        $validated = $request->validated();

        $kelas = Kelas::findOrFail($validated['kelas_id']);
        $this->authorize('create', [Tugas::class, $kelas]);

        $tugas = $this->tugas->buat(
            $validated,
            (int) Auth::id(),
            $request->materiPendukungIds($validated)
        );

        return redirect()
            ->route('pengajar.pertemuan.show', $tugas->pertemuan_id)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    public function show(Tugas $tugas): View
    {
        $this->authorize('view', $tugas);
        $tugas->load(['kelas', 'pengumpulan.siswa']);

        return view('guru.tugas.show', compact('tugas'));
    }

    public function edit(Tugas $tugas): View
    {
        $this->authorize('update', $tugas);

        $kelasList = Kelas::where('guru_id', Auth::id())->get();
        $pertemuanList = $this->tugas->pertemuanUntukGuru((int) Auth::id());

        return view('guru.tugas.edit', $this->dataForm(null, $tugas, [
            'kelasList' => $kelasList,
            'pertemuanList' => $pertemuanList,
        ]));
    }

    public function update(UpdateTugasRequest $request, Tugas $tugas): RedirectResponse
    {
        $this->authorize('update', $tugas);

        $validated = $request->validated();

        $this->tugas->perbarui($tugas, $validated, $request->materiPendukungIds($validated));

        return redirect()->route('pengajar.tugas.index')->with('success', 'Tugas berhasil diupdate.');
    }

    public function destroy(Tugas $tugas): RedirectResponse
    {
        $this->authorize('delete', $tugas);

        $this->tugas->hapus($tugas);

        $target = $tugas->pertemuan_id
            ? route('pengajar.pertemuan.show', $tugas->pertemuan_id)
            : route('pengajar.tugas.index');

        return redirect($target)->with('success', 'Tugas berhasil dihapus.');
    }

    public function generate(GenerateSoalTugasRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pertemuan = Pertemuan::with('kelas')->findOrFail($validated['pertemuan_id']);
        $this->authorize('create', [Tugas::class, $pertemuan->kelas]);

        $sumber = $request->sumberKonteks();
        $key = 'ai-soal:tugas:'.Auth::id().':'.Str::uuid();
        Cache::put($key, ['status' => 'processing'], now()->addMinutes(30));
        try {
            GenerateSoalTugasAI::dispatch(
                (int) Auth::id(),
                (int) $pertemuan->id,
                $validated['tipe'],
                (int) $validated['jumlah'],
                $validated['kesulitan'] ?? null,
                $validated['topik'] ?? null,
                $sumber,
                $key
            );
        } catch (\Throwable $e) {
            Cache::forget($key);
            Log::error('GenerateSoalTugasAI gagal dijadwalkan', [
                'user_id' => (int) Auth::id(),
                'pertemuan_id' => (int) $pertemuan->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menjadwalkan proses generate soal. Silakan coba lagi.',
            ], 503);
        }

        $status = Cache::get($key);
        if (($status['status'] ?? null) === 'done') {
            return response()->json(['success' => true, 'soal' => $status['soal']]);
        }
        if (($status['status'] ?? null) === 'failed') {
            return response()->json(['success' => false, 'message' => $status['message']], 422);
        }

        return response()->json([
            'status' => 'queued',
            'key' => $key,
        ], 202);
    }

    public function generateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'key' => ['required', 'string'],
        ]);

        $key = $request->input('key');
        $userId = (int) Auth::id();

        if (! is_string($key) || ! str_starts_with($key, 'ai-soal:tugas:'.$userId.':')) {
            abort(403);
        }

        $data = Cache::get($key);

        if (! $data) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Proses generate sudah kedaluwarsa atau tidak ditemukan.',
            ]);
        }

        return response()->json($data);
    }

    public function penilaian(Tugas $tugas): View
    {
        $this->authorize('penilaian', $tugas);
        $tugas->load(['pengumpulan.siswa', 'kelas.siswa']);




        return view('guru.tugas.penilaian', [
            'tugas' => $tugas,
            'presenter' => $this->presenter,
        ]);
    }

    public function nilaiSubmit(NilaiTugasRequest $request, Tugas $tugas): RedirectResponse
    {
        $this->authorize('nilai', $tugas);

        $this->pengumpulan->simpanNilai($tugas, $request->validated()['nilai']);

        PeriksaRemedialSetelahNilaiTugas::dispatch($tugas->id);

        return back()->with('success', 'Nilai berhasil disimpan. Proses remedial akan dijalankan di latar belakang.');
    }

    public function periksaAI(Tugas $tugas): RedirectResponse
    {
        $this->authorize('nilai', $tugas);

        $jumlah = $this->pengumpulan->jadwalkanPemeriksaanAi($tugas);

        [$kunci, $pesan] = $this->presenter->pesanPeriksaAI((string) $tugas->tipe, $jumlah);

        return back()->with($kunci, $pesan);
    }

     
    private function dataForm(?Pertemuan $pertemuan = null, ?Tugas $tugas = null, array $tambahan = []): array
    {
        $guruId = (int) Auth::id();

        $data = [
            'bloomLevels' => app(BloomTaxonomyService::class)->levels(),
            'sumberKonteks' => app(KuisGeneratorService::class)->sumberKonteks(
                $guruId,
                $pertemuan?->kelas_id
            ),
        ];

        return array_merge($data, $tambahan, array_filter([
            'pertemuan' => $pertemuan,
            'tugas' => $tugas,
        ], fn ($v) => $v !== null));
    }
}
