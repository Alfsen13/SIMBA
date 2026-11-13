<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\MateriRequest;
use App\Http\Requests\Guru\UpdateMateriRequest;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class MateriController extends Controller
{
    public function create(): View
    {
        $pertemuan = Pertemuan::with('kelas')->findOrFail(request('pertemuan_id'));
        $this->authorize('create', [Materi::class, $pertemuan->kelas]);

        return view('guru.materi.create', compact('pertemuan'));
    }

    public function store(MateriRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $kelas = Kelas::findOrFail($validated['kelas_id']);
        $this->authorize('create', [Materi::class, $kelas]);

        $validated['guru_id'] = Auth::id();

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('materi', 'public');

            if ($filePath === false) {
                throw new RuntimeException('Gagal menyimpan berkas materi.');
            }

            $validated['file_path'] = $filePath;
            $validated['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        try {
            $materi = Materi::create($validated);
        } catch (Throwable $e) {
            if ($filePath !== null) {
                Storage::disk('public')->delete($filePath);
            }

            throw $e;
        }

        $response = redirect()->route('pengajar.pertemuan.show', $materi->pertemuan_id)
            ->with('success', 'Materi berhasil ditambahkan.');

        return $this->withRagProcessingWarning($response, $materi);
    }

    public function show(Materi $materi): View
    {
        $this->authorize('view', $materi);
        $materi->load([
            'kelas', 'pertemuan', 'guru', 'document', 'babSummary',
            'document.formulaReviews' => fn ($query) => $query->orderBy('page')->orderBy('id'),
            'document.chunks' => fn ($query) => $query
                ->select('id', 'document_id', 'chunk_index', 'content', 'page_start', 'page_end')
                ->orderBy('chunk_index'),
        ]);

        return view('guru.materi.show', compact('materi'));
    }

    public function edit(Materi $materi): View
    {
        $this->authorize('update', $materi);
        $kelasList = Kelas::where('guru_id', Auth::id())->get();
        $pertemuanList = Pertemuan::whereIn('kelas_id', Kelas::where('guru_id', Auth::id())->select('id'))
            ->get()
            ->groupBy('kelas_id');

        return view('guru.materi.edit', compact('materi', 'kelasList', 'pertemuanList'));
    }

    public function update(UpdateMateriRequest $request, Materi $materi): RedirectResponse
    {
        $this->authorize('update', $materi);
        $validated = $request->validated();

        $fileLama = $materi->file_path;
        $fileBaru = null;

        if ($request->hasFile('file')) {
            $fileBaru = $request->file('file')->store('materi', 'public');

            if ($fileBaru === false) {
                throw new RuntimeException('Gagal menyimpan berkas materi.');
            }

            $validated['file_path'] = $fileBaru;
            $validated['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        try {
            $materi->update($validated);
        } catch (Throwable $e) {
            if ($fileBaru !== null) {
                Storage::disk('public')->delete($fileBaru);
            }

            throw $e;
        }

        if ($fileBaru !== null && $fileLama !== null && $fileLama !== $fileBaru) {
            Storage::disk('public')->delete($fileLama);
        }

        $target = $materi->pertemuan_id
            ? route('pengajar.pertemuan.show', $materi->pertemuan_id)
            : route('pengajar.kelas.show', $materi->kelas_id);

        $response = redirect($target)->with('success', 'Materi berhasil diupdate.');

        return $this->withRagProcessingWarning($response, $materi);
    }

    public function destroy(Materi $materi): RedirectResponse
    {
        $this->authorize('delete', $materi);
        if ($materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }
        $materi->delete();

        $target = $materi->pertemuan_id
            ? route('pengajar.pertemuan.show', $materi->pertemuan_id)
            : route('pengajar.kelas.show', $materi->kelas_id);

        return redirect($target)->with('success', 'Materi berhasil dihapus.');
    }

    private function withRagProcessingWarning(RedirectResponse $response, Materi $materi): RedirectResponse
    {
        $document = $materi->loadMissing('document')->document;

        if ($document?->status !== 'failed') {
            return $response;
        }

        return $response->with('warning', __('guru_kelas.materi.rag_upload_failed', [
            'error' => $document->error_message ?: __('guru_kelas.materi.rag_unknown_error'),
        ]));
    }
}
