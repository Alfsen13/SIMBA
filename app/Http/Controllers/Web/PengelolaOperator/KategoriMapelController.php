<?php

namespace App\Http\Controllers\Web\PengelolaOperator;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KategoriMapelController extends Controller
{
    public function index(): View
    {
        $kategoriList = MataPelajaran::query()
            ->select('kategori', \DB::raw('count(*) as mapel_count'))
            ->whereNotNull('kategori')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        return view('pengelola-operator.kategori-mata-pelajaran.index', compact('kategoriList'));
    }

    public function create(): View
    {
        return view('pengelola-operator.kategori-mata-pelajaran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100', 'unique:mata_pelajaran,kategori'],
        ]);

        MataPelajaran::create([
            'kode' => strtoupper(str_replace(' ', '', $validated['kategori'])).'_'.time(),
            'nama' => $validated['kategori'],
            'kategori' => $validated['kategori'],
        ]);

        return redirect()->route('pengelola-operator.kategori-mata-pelajaran.index')->with('success', 'Kategori mata pelajaran berhasil ditambahkan.');
    }

    public function edit(string $kategori): View
    {
        $kategoriData = $kategori;

        return view('pengelola-operator.kategori-mata-pelajaran.edit', compact('kategoriData'));
    }

    public function update(Request $request, string $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100'],
        ]);

        MataPelajaran::where('kategori', $kategori)->update(['kategori' => $validated['kategori'], 'nama' => $validated['kategori']]);

        User::where('kategori_mapel', $kategori)->update(['kategori_mapel' => $validated['kategori']]);

        return redirect()->route('pengelola-operator.kategori-mata-pelajaran.index')->with('success', 'Kategori mata pelajaran berhasil diperbarui.');
    }

    public function destroy(string $kategori): RedirectResponse
    {
        $adaMapelLain = MataPelajaran::where('kategori', $kategori)->whereDoesntHave('kelas')->count();

        MataPelajaran::where('kategori', $kategori)->delete();
        User::where('kategori_mapel', $kategori)->update(['kategori_mapel' => null]);

        return redirect()->route('pengelola-operator.kategori-mata-pelajaran.index')->with('success', 'Kategori mata pelajaran berhasil dihapus.');
    }
}
