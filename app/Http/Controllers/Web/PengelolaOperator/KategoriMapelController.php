<?php

namespace App\Http\Controllers\Web\PengelolaOperator;

use App\Http\Controllers\Controller;
use App\Models\AplikasiPengajar;
use App\Models\KategoriMataPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriMapelController extends Controller
{
    public function index(): View
    {
        $kategoriList = KategoriMataPelajaran::query()
            ->withCount(['mataPelajaran as mapel_count'])
            ->orderBy('nama')
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
            'kategori' => ['required', 'string', 'max:100', Rule::unique('kategori_mata_pelajaran', 'nama')],
        ]);

        KategoriMataPelajaran::create(['nama' => $validated['kategori']]);

        return redirect()->route('pengelola-operator.kategori-mata-pelajaran.index')->with('success', 'Kategori mata pelajaran berhasil ditambahkan.');
    }

    public function edit(string $kategori): View
    {
        $kategoriData = $kategori;

        return view('pengelola-operator.kategori-mata-pelajaran.edit', compact('kategoriData'));
    }

    public function update(Request $request, string $kategori): RedirectResponse
    {
        abort_unless(KategoriMataPelajaran::where('nama', $kategori)->exists(), 404);

        $validated = $request->validate([
            'kategori' => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($kategori): void {
                    if ($value !== $kategori && KategoriMataPelajaran::where('nama', $value)->exists()) {
                        $fail('Nama kategori sudah digunakan.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($kategori, $validated): void {
            KategoriMataPelajaran::where('nama', $kategori)->update(['nama' => $validated['kategori']]);
            MataPelajaran::where('kategori', $kategori)->update(['kategori' => $validated['kategori']]);
            Kelas::where('kategori', $kategori)->update(['kategori' => $validated['kategori']]);
            User::where('kategori_mapel', $kategori)->update(['kategori_mapel' => $validated['kategori']]);
            AplikasiPengajar::where('kategori_mapel', $kategori)->update(['kategori_mapel' => $validated['kategori']]);
        });

        return redirect()->route('pengelola-operator.kategori-mata-pelajaran.index')->with('success', 'Kategori mata pelajaran berhasil diperbarui.');
    }

    public function destroy(string $kategori): RedirectResponse
    {
        $kategoriModel = KategoriMataPelajaran::where('nama', $kategori)->firstOrFail();
        $kategoriDipakai = Kelas::where('kategori', $kategori)->exists()
            || MataPelajaran::where('kategori', $kategori)->whereHas('kelas')->exists()
            || User::where('kategori_mapel', $kategori)->exists()
            || AplikasiPengajar::where('kategori_mapel', $kategori)->exists();

        if ($kategoriDipakai) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan.');
        }

        DB::transaction(function () use ($kategori, $kategoriModel): void {
            MataPelajaran::where('kategori', $kategori)->update(['kategori' => null]);
            $kategoriModel->delete();
        });

        return redirect()->route('pengelola-operator.kategori-mata-pelajaran.index')->with('success', 'Kategori mata pelajaran berhasil dihapus.');
    }
}
