<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct()
    {
        $this->documentService = app(DocumentService::class);
    }

    public function index(Request $request): View
    {
        $documents = Document::with('user')
            ->orderBy('kategori')
            ->latest()
            ->paginate(50);

        $grouped = $documents->groupBy(function ($doc) {
            return $doc->kategori ?: 'Tanpa Kategori';
        });

        return view('admin.documents.index', compact('documents', 'grouped'));
    }

    public function create(): View
    {
        return view('admin.documents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,txt,md,csv|max:20480',
            'kategori' => 'nullable|string|max:100',
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $name = pathinfo($originalName, PATHINFO_FILENAME);
            $size = $file->getSize();
            $kategori = $validated['kategori'] ?? 'umum';

            $kategoriDir = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($kategori));
            $path = $file->store("documents/{$kategoriDir}", 'public');

            $document = Document::create([
                'name' => $name,
                'original_name' => $originalName,
                'path' => $path,
                'type' => $extension,
                'size' => $size,
                'kategori' => $kategori,
                'status' => 'pending',
                'user_id' => Auth::id(),
            ]);

            ProcessDocument::dispatchSync($document);
            $document->refresh();

            if ($document->status === 'completed') {
                return redirect()->route('operator.documents.index')
                    ->with('success', 'Dokumen berhasil diunggah dan langsung diproses.');
            }

            return redirect()->route('operator.documents.index')
                ->with('error', 'Dokumen terunggah tetapi gagal diproses: '.($document->error_message ?: 'kesalahan tidak diketahui'));
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengunggah dokumen: '.$e->getMessage());
        }
    }

    public function show(Document $document): View
    {
        $document->load('user');
        $chunks = $document->chunks()->orderBy('chunk_index')->paginate(50);

        return view('admin.documents.show', compact('document', 'chunks'));
    }

    public function destroy(Document $document): RedirectResponse
    {
        try {
            if (Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
            $document->chunks()->delete();
            $document->delete();

            return redirect()->route('operator.documents.index')
                ->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus dokumen: '.$e->getMessage());
        }
    }

    public function reprocess(Document $document): RedirectResponse
    {
        $document->chunks()->delete();
        $document->update(['status' => 'pending', 'error_message' => null]);
        ProcessDocument::dispatchSync($document);
        $document->refresh();

        if ($document->status === 'completed') {
            return redirect()->route('operator.documents.index')
                ->with('success', 'Dokumen berhasil diproses ulang.');
        }

        return redirect()->route('operator.documents.index')
            ->with('error', 'Gagal memproses ulang dokumen: '.($document->error_message ?: 'kesalahan tidak diketahui'));
    }
}
