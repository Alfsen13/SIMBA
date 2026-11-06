<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Services\AIProviderService;
use App\Services\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AITutorController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $kelasList = $user->kelasDiikuti()
            ->whereHas('pertemuan.materi')
            ->with(['pertemuan' => function ($q) {
                $q->dibuka()->with('materi.babSummary')->orderBy('urutan');
            }])
            ->get();

        return view('siswa.ai-tutor.index', compact('kelasList'));
    }

    public function pertemuan(Kelas $kelas): View
    {
        $user = Auth::user();

        if (! $user->kelasDiikuti()->where('kelas_id', $kelas->id)->exists()) {
            abort(403);
        }

        $pertemuans = $kelas->pertemuan()
            ->dibuka()
            ->with('materi.babSummary')
            ->orderBy('urutan')
            ->get()
            ->filter(fn ($p) => $p->materi->isNotEmpty());

        return view('siswa.ai-tutor.pertemuan', compact('kelas', 'pertemuans'));
    }

    public function chat(Kelas $kelas, Pertemuan $pertemuan): View
    {
        $user = Auth::user();

        if (! $user->kelasDiikuti()->where('kelas_id', $kelas->id)->exists()) {
            abort(403);
        }

        if ($pertemuan->kelas_id !== $kelas->id) {
            abort(404);
        }

        if (! $pertemuan->dibuka) {
            abort(403, 'Pertemuan ini belum dibuka oleh pengajar.');
        }

        $materis = $pertemuan->materi()
            ->with(['babSummary', 'document'])
            ->get();

        if ($materis->isEmpty()) {
            return redirect()->route('pelajar.ai-tutor.pertemuan', $kelas)
                ->with('error', 'Tidak ada materi dalam pertemuan ini.');
        }

        $materiData = $materis->map(fn ($m) => [
            'id' => $m->id,
            'judul' => $m->judul,
            'ringkasan' => $m->babSummary?->ringkasan,
        ])->values();

        $materiUrlMap = $materis->mapWithKeys(
            fn ($m) => [$this->normalizeJudul($m->judul) => route('pelajar.materi.show', $m->id)]
        );

        return view('siswa.ai-tutor.chat', compact('kelas', 'pertemuan', 'materis', 'materiData'))
            ->with('allMateriInPertemuan', $materis)
            ->with('materiUrlMap', $materiUrlMap);
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'materi_id' => [
                'nullable',
                'integer',
                Rule::exists('materi', 'id')->where('pertemuan_id', $request->input('pertemuan_id')),
            ],
            'question' => 'required|string|max:2000',
            'history' => 'nullable|array|max:10',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:2000',
        ]);

        $user = Auth::user();
        $pertemuan = Pertemuan::findOrFail($validated['pertemuan_id']);

        if (! $user->kelasDiikuti()->where('kelas_id', $pertemuan->kelas_id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $pertemuan->dibuka) {
            return response()->json(['error' => 'Pertemuan belum dibuka'], 403);
        }

        $materiId = $validated['materi_id'] ?? null;

        $kategori = $pertemuan->kelas?->kategori;
        $rag = app(RAGService::class);
        $context = $rag->buildContext($validated['question'], $kategori, $materiId, $pertemuan->id);

        $prompt = '';
        if ($context) {
            $prompt = "Konteks dari materi pembelajaran:\n{$context}\n\nPertanyaan:\n{$validated['question']}";
        } else {
            $prompt = "TIDAK ADA INFORMASI TERSEDIA. Jawab dengan jujur bahwa kamu tidak memiliki informasi yang cukup untuk menjawab pertanyaan ini.\n\nPertanyaan:\n{$validated['question']}";
        }

        try {
            $ai = app(AIProviderService::class);
            $answer = $ai->sendForUser($user, $prompt, $validated['history'] ?? []);
        } catch (\Throwable $e) {
            \Log::error('AI Tutor ask error', ['msg' => $e->getMessage()]);

            return response()->json(['error' => 'Layanan AI tidak tersedia'], 503);
        }

        $references = $rag->getLastReferences();
        foreach ($references as &$ref) {
            if (isset($ref['materi_id']) && $ref['materi_id'] && ! isset($ref['url'])) {
                $ref['url'] = route('pelajar.materi.show', $ref['materi_id']);
            }
        }
        unset($ref);

        return response()->json([
            'answer' => $answer,
            'references' => $references,
        ]);
    }

    private function normalizeJudul(?string $judul): string
    {
        $j = mb_strtolower((string) $judul);

        $j = preg_replace('/\bhlm\.?\s*\d+\s*(?:[-–]\s*\d+)?/u', ' ', $j);

        $j = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $j);
        $j = preg_replace('/\s+/u', ' ', $j);

        return trim($j);
    }
}
