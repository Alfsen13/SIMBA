<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel, 403, 'Operator belum ditugaskan kategori mata pelajaran.');

        $mataPelajaran = MataPelajaran::where('kategori', $user->kategori_mapel)->latest()->paginate(15);

        return view('operator.mata-pelajaran.index', compact('mataPelajaran'));
    }

    public function show(MataPelajaran $mataPelajaran): View
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel && $mataPelajaran->kategori === $user->kategori_mapel, 403);

        $kelasList = $mataPelajaran->kelas()
            ->where('kategori', $user->kategori_mapel)
            ->with('guru:id,name')
            ->withCount('materi')
            ->latest()
            ->paginate(15);

        return view('operator.mata-pelajaran.show', compact('mataPelajaran', 'kelasList'));
    }

    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel, 403);

        return view('operator.mata-pelajaran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel, 403);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:mata_pelajaran,kode'],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        MataPelajaran::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'kategori' => $user->kategori_mapel,
        ]);

        return redirect()->route('operator.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran): View
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel && $mataPelajaran->kategori === $user->kategori_mapel, 403);

        return view('operator.mata-pelajaran.edit', compact('mataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel && $mataPelajaran->kategori === $user->kategori_mapel, 403);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:mata_pelajaran,kode,'.$mataPelajaran->id],
            'nama' => ['required', 'string', 'max:255'],
        ]);

        $mataPelajaran->update($validated);

        return redirect()->route('operator.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->kategori_mapel && $mataPelajaran->kategori === $user->kategori_mapel, 403);

        if ($mataPelajaran->kelas()->exists() || $mataPelajaran->pengajarKompetensi()->exists()) {
            return back()->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan.');
        }

        $mataPelajaran->delete();

        return redirect()->route('operator.mata-pelajaran.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
