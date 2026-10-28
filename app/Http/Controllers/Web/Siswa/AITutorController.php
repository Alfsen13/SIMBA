<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pertemuan;
use Illuminate\Support\Facades\Auth;
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

     
    private function normalizeJudul(?string $judul): string
    {
        $j = mb_strtolower((string) $judul);

        $j = preg_replace('/\bhlm\.?\s*\d+\s*(?:[-–]\s*\d+)?/u', ' ', $j);

        $j = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $j);
        $j = preg_replace('/\s+/u', ' ', $j);

        return trim($j);
    }
}

