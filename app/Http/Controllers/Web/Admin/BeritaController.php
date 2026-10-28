<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BeritaController extends Controller
{
    public function index(Request $request): View
    {
        $berita = Berita::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.berita.index', compact('berita'));
    }

    public function create(): View
    {
        return view('admin.berita.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        try {
            Berita::create([
                'judul' => $validated['judul'],
                'slug' => Str::slug($validated['judul']).'-'.Str::random(5),
                'konten' => $validated['konten'],
                'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['konten']), 200),
                'published_at' => $validated['published_at'] ?? now(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('operator.berita.index')
                ->with('success', 'Berita berhasil dibuat.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat berita: '.$e->getMessage());
        }
    }

    public function edit(Berita $berita): View
    {
        return view('admin.berita.edit', compact('berita'));
    }

    public function update(Request $request, Berita $berita): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
        ]);

        try {
            $berita->update([
                'judul' => $validated['judul'],
                'konten' => $validated['konten'],
                'excerpt' => $validated['excerpt'],
                'published_at' => $validated['published_at'],
            ]);

            return redirect()->route('operator.berita.index')
                ->with('success', 'Berita berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui berita: '.$e->getMessage());
        }
    }

    public function destroy(Berita $berita): RedirectResponse
    {
        try {
            $berita->delete();

            return redirect()->route('operator.berita.index')
                ->with('success', 'Berita berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus berita: '.$e->getMessage());
        }
    }
}
