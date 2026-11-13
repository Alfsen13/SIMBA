<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MateriController extends Controller
{
    public function show(Materi $materi, Request $request, AktivitasService $aktivitas): View
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $materi->kelas_id)->exists()) {
            abort(403);
        }

        $materi->load('pertemuan', 'guru', 'babSummary');
        $this->pastikanPertemuanDibuka($materi);

        $aktivitasLog = $aktivitas->catat($user, 'buka_materi', $materi->pertemuan, $materi->kelas, ['materi_id' => $materi->id]);

        $highlight = $request->query('highlight');
        $chunks = collect();
        if ($materi->document) {
            $chunks = $materi->document->chunks()->orderBy('chunk_index')->get(['id', 'content', 'chunk_index']);
        }

        return view('siswa.materi.show', compact('materi', 'highlight', 'chunks', 'aktivitasLog'));
    }

    public function download(Materi $materi): StreamedResponse
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $materi->kelas_id)->exists()) {
            abort(403);
        }

        $this->pastikanPertemuanDibuka($materi);

        if (! $materi->file_path || ! Storage::disk('public')->exists($materi->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($materi->file_path);
    }

    public function preview(Materi $materi)
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $materi->kelas_id)->exists()) {
            abort(403);
        }

        $this->pastikanPertemuanDibuka($materi);

        if (! $materi->file_path || ! Storage::disk('public')->exists($materi->file_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($materi->file_path);
        $mime = mime_content_type($path) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($materi->file_path).'"',
        ]);
    }

    private function pastikanPertemuanDibuka(Materi $materi): void
    {
        if ($materi->pertemuan !== null && ! $materi->pertemuan->dibuka) {
            abort(403, 'Pertemuan ini belum dibuka oleh pengajar.');
        }
    }
}
