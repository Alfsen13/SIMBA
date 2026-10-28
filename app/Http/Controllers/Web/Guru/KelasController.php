<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\KelasRequest;
use App\Jobs\GeneratePertemuanJob;
use App\Jobs\SaranMateriJob;
use App\Models\Kelas;
use App\Models\PengajuanPengajarKelas;
use App\Notifications\GeneratePertemuanGagal;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $kelasSaya = Kelas::where('guru_id', Auth::id())
            ->withCount('siswa', 'pertemuan')
            ->latest()
            ->paginate(15, ['*'], 'saya');

        $kelasTersedia = Kelas::whereNull('guru_id')
            ->withCount('siswa', 'pertemuan')
            ->latest()
            ->paginate(15, ['*'], 'tersedia');

        // Status pengajuan mengajar saya per kelas tersedia (badge menunggu /
        // ditolak + tombol ajukan ulang di halaman).
        $pengajuanSaya = PengajuanPengajarKelas::where('user_id', Auth::id())
            ->whereIn('kelas_id', $kelasTersedia->getCollection()->pluck('id'))
            ->get()
            ->keyBy('kelas_id');

        return view('guru.kelas.index', compact('kelasSaya', 'kelasTersedia', 'pengajuanSaya'));
    }

    public function available(): RedirectResponse
    {
        return redirect()->route('pengajar.kelas.index');
    }

    public function show(Kelas $kelas): View
    {
        $this->authorize('view', $kelas);
        $kelas->load(['siswa', 'pertemuan' => fn ($q) => $q->orderBy('urutan')->withCount(['materi', 'tugas', 'kuis'])]);

        return view('guru.kelas.show', compact('kelas'));
    }

    public function edit(Kelas $kelas): View
    {
        $this->authorize('update', $kelas);

        return view('guru.kelas.edit', compact('kelas'));
    }

    public function update(KelasRequest $request, Kelas $kelas): RedirectResponse
    {
        $this->authorize('update', $kelas);
        $validated = $request->validated();
        // Kolom aturan_kelayakan NOT NULL (MySQL): opsi kosong berarti
        // pertahankan aturan yang sudah dipilih operator.
        $validated['aturan_kelayakan'] ??= $kelas->aturan_kelayakan;
        $kelas->update($validated);

        return redirect()->route('pengajar.kelas.index')->with('success', 'Kelas berhasil diupdate.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $this->authorize('delete', $kelas);
        $kelas->delete();

        return redirect()->route('pengajar.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * Form pengajuan mengajar: pengajar melampirkan bahan rencana ajar sebagai
     * bahan pertimbangan operator sebelum kelas ditetapkan kepadanya.
     */
    public function formAjukan(Kelas $kelas): View|RedirectResponse
    {
        if ($kelas->guru_id !== null) {
            return redirect()->route('pengajar.kelas.index')
                ->with('error', __('guru_kelas.ajukan.kelas_terisi'));
        }

        $pengajuan = PengajuanPengajarKelas::where('kelas_id', $kelas->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($pengajuan?->status === PengajuanPengajarKelas::STATUS_PENDING) {
            return redirect()->route('pengajar.kelas.index')
                ->with('error', __('guru_kelas.ajukan.sudah_pending'));
        }

        return view('guru.kelas.ajukan', compact('kelas', 'pengajuan'));
    }

    public function ajukan(Request $request, Kelas $kelas): RedirectResponse
    {
        if ($kelas->guru_id !== null) {
            return back()->with('error', __('guru_kelas.ajukan.kelas_terisi'));
        }

        $pengajuan = PengajuanPengajarKelas::where('kelas_id', $kelas->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($pengajuan?->status === PengajuanPengajarKelas::STATUS_PENDING) {
            return back()->with('error', __('guru_kelas.ajukan.sudah_pending'));
        }

        $validated = $request->validate([
            'rencana_ajar' => ['required', 'string', 'min:20', 'max:4000'],
            'berkas' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $berkasPath = $pengajuan?->berkas_path;
        if ($request->hasFile('berkas')) {
            // Berkas lama (bila pengajuan ulang) dibuang supaya tidak menumpuk.
            if ($berkasPath) {
                Storage::disk('local')->delete($berkasPath);
            }
            $berkasPath = $request->file('berkas')->store('pengajuan', 'local');
        }

        // Pengajuan yang ditolak boleh diajukan ulang: baris yang sama di-reset
        // menjadi pending (UNIQUE kelas_id+user_id — tidak menggandakan baris).
        PengajuanPengajarKelas::updateOrCreate(
            ['kelas_id' => $kelas->id, 'user_id' => Auth::id()],
            [
                'rencana_ajar' => $validated['rencana_ajar'],
                'berkas_path' => $berkasPath,
                'status' => PengajuanPengajarKelas::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'catatan_operator' => null,
            ],
        );

        return redirect()->route('pengajar.kelas.index')
            ->with('success', __('guru_kelas.ajukan.terkirim'));
    }

    public function generatePertemuan(Request $request, Kelas $kelas): RedirectResponse
    {
        $this->authorize('update', $kelas);

        $data = $request->validate([
            'materi_topics' => 'required|string|max:2000',
            'jumlah_pertemuan' => 'required|integer|min:4|max:20',
            'catatan' => 'nullable|string|max:2000',
        ]);

        if (! app(OllamaService::class)->isAvailable()) {
            return back()->with('error', 'Server AI (Ollama) tidak aktif. Nyalakan dulu, lalu coba lagi.');
        }

        try {
            set_time_limit(0);
            $jumlah = GeneratePertemuanJob::dispatchSync(
                $kelas->id,
                $data['materi_topics'],
                $data['jumlah_pertemuan'],
                $data['catatan'] ?? null,
            );
        } catch (\Throwable $e) {
            Log::error('generatePertemuan gagal sinkron: kelas={kelas} msg={msg}', [
                'kelas' => $kelas->id,
                'msg' => $e->getMessage(),
            ]);

            try {
                if ($kelas->guru_id) {
                    $kelas->guru?->notify(new GeneratePertemuanGagal($kelas->nama_kelas, $e->getMessage(), app()->getLocale()));
                }
            } catch (\Throwable $notifE) {
                Log::warning('generatePertemuan: gagal kirim notifikasi: {msg}', ['msg' => $notifE->getMessage()]);
            }

            return back()->with('error', 'Generate pertemuan gagal: '.$e->getMessage());
        }

        return redirect()
            ->route('pengajar.kelas.show', $kelas)
            ->with('success', $jumlah.' pertemuan berhasil digenerate oleh AI.');
    }

    public function saranMateri(Request $request, Kelas $kelas): JsonResponse
    {
        $this->authorize('update', $kelas);

        $cached = Cache::get("saran_materi_{$kelas->id}");
        if ($cached) {
            return response()->json(['success' => true, 'data' => $cached]);
        }

        if (! app(OllamaService::class)->isAvailable()) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Server AI (Ollama) tidak aktif. Nyalakan dulu, lalu coba lagi.',
            ]);
        }

        SaranMateriJob::dispatch($kelas->id);

        return response()->json([
            'success' => false,
            'status' => 'pending',
            'message' => 'Saran materi sedang digenerate oleh AI. Silakan coba lagi dalam 1-2 menit.',
        ]);
    }
}
