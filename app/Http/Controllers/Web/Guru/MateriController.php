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

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('materi', 'public');
            $validated['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        $materi = Materi::create($validated);

        return redirect()->route('pengajar.pertemuan.show', $materi->pertemuan_id)->with('success', 'Materi berhasil ditambahkan.');
    }

     
    public function show(Materi $materi): View
    {
        $this->authorize('view', $materi);
        $materi->load(['kelas', 'pertemuan', 'guru']);

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

        if ($request->hasFile('file')) {
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('materi', 'public');
            $validated['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        $materi->update($validated);

        $target = $materi->pertemuan_id
            ? route('pengajar.pertemuan.show', $materi->pertemuan_id)
            : route('pengajar.kelas.show', $materi->kelas_id);

        return redirect($target)->with('success', 'Materi berhasil diupdate.');
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
}

