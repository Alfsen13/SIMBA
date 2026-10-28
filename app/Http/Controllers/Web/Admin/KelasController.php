<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KelasRequest;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TingkatPendidikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $kelasList = Kelas::with('guru')
            // Jumlah pengajuan mengajar yang masih menunggu tinjauan operator.
            ->withCount(['pengajuan as pengajuan_pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->latest()
            ->paginate(15);

        return view('admin.kelas.index', compact('kelasList'));
    }

    public function create(): View
    {
        return view('admin.kelas.create', $this->dataForm());
    }

    public function store(KelasRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['kode_kelas'] = strtoupper(Str::random(8));
        $validated['status'] ??= 'dibuka';
        $validated['aturan_kelayakan'] ??= 'sesuai_profil';

        Kelas::create($validated);

        return redirect()->route('operator.kelas.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function edit(Kelas $kelas): View
    {
        return view('admin.kelas.edit', ['kelas' => $kelas] + $this->dataForm());
    }

    public function update(KelasRequest $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validated();
        // Kolom status & aturan_kelayakan NOT NULL (MySQL): kosong berarti
        // pertahankan nilai sekarang, jangan pernah menulis NULL.
        $validated['status'] ??= $kelas->status;
        $validated['aturan_kelayakan'] ??= $kelas->aturan_kelayakan;

        $kelas->update($validated);

        return redirect()->route('operator.kelas.index')->with('success', 'Kelas berhasil diupdate.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $kelas->delete();

        return redirect()->route('operator.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * Master untuk form buka/ubah kelas: mapel dikelompokkan per kategori
     * (opt-group) supaya mata pelajaran cepat ditemukan saat membuka kelas.
     */
    private function dataForm(): array
    {
        $user = auth()->user();
        $mapelQuery = MataPelajaran::where('aktif', true)
            ->orderBy('kategori')
            ->orderBy('nama');

        if ($user && $user->role === 'operator' && $user->kategori_mapel) {
            $mapelQuery->where('kategori', $user->kategori_mapel);
        }

        $mapel = $mapelQuery->get();

        $jalur = JalurPendidikan::where('aktif', true)->orderBy('urutan')->get(['id', 'nama']);
        $jenjang = JenjangPendidikan::where('aktif', true)
            ->whereHas('jalur', function ($q) {
                $q->where('kode', 'formal');
            })
            ->where(function ($q) {
                $q->where('nama', 'like', '%SD%')
                    ->orWhere('nama', 'like', '%MI%')
                    ->orWhere('nama', 'like', '%SMP%')
                    ->orWhere('nama', 'like', '%MTs%')
                    ->orWhere('nama', 'like', '%SMA%')
                    ->orWhere('nama', 'like', '%SMK%')
                    ->orWhere('nama', 'like', '%MA%')
                    ->orWhere('nama', 'like', '%Perguruan Tinggi%');
            })
            ->orderBy('urutan')
            ->get(['id', 'nama']);
        $tingkat = TingkatPendidikan::where('aktif', true)
            ->whereIn('jenjang_id', $jenjang->pluck('id'))
            ->orderBy('urutan')
            ->get(['id', 'jenjang_id', 'nama']);

        return [
            'mapelGrup' => $mapel
                ->groupBy(fn ($m) => (string) $m->kategori)
                ->map(fn ($g, $kategori) => [
                    'kategori' => $kategori,
                    'items' => $g->values(),
                ])
                ->values(),
            'jalurList' => $jalur,
            'jenjangList' => $jenjang,
            'tingkatGrup' => $tingkat
                ->groupBy('jenjang_id')
                ->map(fn ($g, $jenjangId) => [
                    'nama' => $jenjang->firstWhere('id', $jenjangId)?->nama ?? '',
                    'items' => $g->values(),
                ])
                ->values(),
        ];
    }
}
