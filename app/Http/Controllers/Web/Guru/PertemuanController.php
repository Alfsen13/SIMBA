<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\PertemuanRequest;
use App\Models\Kelas;
use App\Models\Pertemuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PertemuanController extends Controller
{
     
    public function show(Pertemuan $pertemuan): View
    {
        $this->authorize('view', $pertemuan);
        $pertemuan->load(['kelas', 'materi', 'tugas', 'kuis', 'kehadiran']);

        return view('guru.kelas.pertemuan.show', compact('pertemuan'));
    }

     
    public function create(Kelas $kelas): View
    {
        $this->authorize('create', [Pertemuan::class, $kelas]);

        return view('guru.kelas.pertemuan.create', compact('kelas'));
    }

     
    public function store(PertemuanRequest $request, Kelas $kelas): RedirectResponse
    {
        $this->authorize('create', [Pertemuan::class, $kelas]);
        $validated = $request->validated();
        $validated['kelas_id'] = $kelas->id;

        $validated['dibuka'] = $request->boolean('dibuka');
        Pertemuan::create($validated);

        return redirect()->route('pengajar.kelas.show', $kelas)->with('success', 'Pertemuan berhasil ditambahkan.');
    }

     
    public function edit(Pertemuan $pertemuan): View
    {
        $this->authorize('update', $pertemuan);

        return view('guru.kelas.pertemuan.edit', compact('pertemuan'));
    }

     
    public function update(PertemuanRequest $request, Pertemuan $pertemuan): RedirectResponse
    {
        $this->authorize('update', $pertemuan);
        $validated = $request->validated();
        if ($request->has('dibuka')) {
            $validated['dibuka'] = $request->boolean('dibuka');
        }
        $pertemuan->update($validated);

        return redirect()->route('pengajar.kelas.show', $pertemuan->kelas_id)->with('success', 'Pertemuan berhasil diupdate.');
    }

     
    public function buka(Pertemuan $pertemuan): RedirectResponse
    {
        $this->authorize('update', $pertemuan);
        $pertemuan->update(['dibuka' => true]);

        return back()->with('success', 'Pertemuan dibuka. Pelajar sekarang dapat mengakses seluruh kontennya.');
    }

     
    public function kunci(Pertemuan $pertemuan): RedirectResponse
    {
        $this->authorize('update', $pertemuan);
        $pertemuan->update(['dibuka' => false]);

        return back()->with('success', 'Pertemuan dikunci. Pelajar tidak dapat mengakses kontennya.');
    }

     
    public function destroy(Pertemuan $pertemuan): RedirectResponse
    {
        $this->authorize('delete', $pertemuan);
        $pertemuan->delete();

        return back()->with('success', 'Pertemuan berhasil dihapus.');
    }
}

