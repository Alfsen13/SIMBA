<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\GenerateSoalKuisRequest;
use App\Http\Requests\Guru\KuisRequest;
use App\Http\Requests\Guru\UpdateKuisRequest;
use App\Jobs\GenerateSoalKuisAI;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Pertemuan;
use App\Services\BloomTaxonomyService;
use App\Services\KuisGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KuisController extends Controller
{
    public function index(): View
    {
        $kuisList = Kuis::whereHas('kelas', fn ($q) => $q->where('guru_id', Auth::id()))
            ->with(['kelas', 'pertemuan'])
            ->withCount('hasil')
            ->latest()
            ->paginate(15);

        return view('guru.kuis.index', compact('kuisList'));
    }

    public function create(): View
    {
        $pertemuan = Pertemuan::with('kelas')->findOrFail(request('pertemuan_id'));
        $this->authorize('create', [Kuis::class, $pertemuan->kelas]);

        $bloomLevels = app(BloomTaxonomyService::class)->levels();
        $sumberKonteks = app(KuisGeneratorService::class)->sumberKonteks(Auth::id(), $pertemuan->kelas_id);

        return view('guru.kuis.create', compact('pertemuan', 'bloomLevels', 'sumberKonteks'));
    }

    public function store(KuisRequest $request): RedirectResponse
    {
        $validated = $request->terapkanKonteksSoal($this->soalDenganBloom($request->validated()));

        $kelas = Kelas::findOrFail($validated['kelas_id']);
        $this->authorize('create', [Kuis::class, $kelas]);

        $validated['is_aktif'] = true;
        $kuis = Kuis::create($validated);

        $kuis->materiPendukung()->sync($request->materiPendukungIds($validated));

        return redirect()->route('pengajar.pertemuan.show', $kuis->pertemuan_id)->with('success', 'Kuis berhasil dibuat.');
    }

    private function soalDenganBloom(array $validated): array
    {
        $raw = $validated['soal'] ?? null;

        if (is_array($raw)) {

            $validated['soal'] = app(BloomTaxonomyService::class)->lengkapiOtomatis($raw);

            return $validated;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return $validated;
        }

        $soal = json_decode($raw, true);

        if (! is_array($soal) || $soal === []) {
            return $validated;
        }

        $validated['soal'] = app(BloomTaxonomyService::class)->lengkapiOtomatis($soal);

        return $validated;
    }

    public function edit(Kuis $kuis): View
    {
        $this->authorize('update', $kuis);
        $kelasList = Kelas::where('guru_id', Auth::id())->get();
        $pertemuanList = Pertemuan::whereIn('kelas_id', $kelasList->pluck('id'))
            ->get()
            ->groupBy('kelas_id');

        $bloomLevels = app(BloomTaxonomyService::class)->levels();
        $sumberKonteks = app(KuisGeneratorService::class)->sumberKonteks((int) Auth::id(), $kuis->kelas_id);

        return view('guru.kuis.edit', compact('kuis', 'kelasList', 'pertemuanList', 'bloomLevels', 'sumberKonteks'));
    }

    public function generate(GenerateSoalKuisRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $pertemuan = Pertemuan::with('kelas')->findOrFail($validated['pertemuan_id']);
        $this->authorize('create', [Kuis::class, $pertemuan->kelas]);

        $sumber = $request->sumberKonteks();
        $key = 'ai-soal:kuis:'.Auth::id().':'.Str::uuid();
        Cache::put($key, ['status' => 'processing'], now()->addMinutes(30));
        try {
            GenerateSoalKuisAI::dispatch(
                (int) Auth::id(),
                (int) $pertemuan->id,
                (int) $validated['jumlah'],
                $validated['kesulitan'],
                $validated['topik'] ?? null,
                $sumber,
                $key
            );
        } catch (\Throwable $e) {
            Cache::forget($key);
            Log::error('GenerateSoalKuisAI gagal dijadwalkan', [
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
            return response()->json([
                'success' => true,
                'soal' => $status['soal'],
                'peringatan' => $status['peringatan'] ?? null,
            ]);
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

        if (! is_string($key) || ! str_starts_with($key, 'ai-soal:kuis:'.$userId.':')) {
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

    public function update(UpdateKuisRequest $request, Kuis $kuis): RedirectResponse
    {
        $this->authorize('update', $kuis);
        $validated = $request->terapkanKonteksSoal($this->soalDenganBloom($request->validated()));
        $kuis->update($validated);

        $kuis->materiPendukung()->sync($request->materiPendukungIds($validated));

        return redirect()->route('pengajar.kuis.index')->with('success', 'Kuis berhasil diupdate.');
    }

    public function destroy(Kuis $kuis): RedirectResponse
    {
        $this->authorize('delete', $kuis);
        $kuis->delete();

        $target = $kuis->pertemuan_id
            ? route('pengajar.pertemuan.show', $kuis->pertemuan_id)
            : route('pengajar.kuis.index');

        return redirect($target)->with('success', 'Kuis berhasil dihapus.');
    }
}
