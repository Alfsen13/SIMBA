<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ProcessDocument;
use App\Models\Document;
use App\Models\Materi;
use Illuminate\Support\Facades\Storage;

class MateriObserver
{
    private const array SUPPORTED_TYPES = ['pdf', 'txt', 'md', 'csv', 'docx', 'pptx', 'xlsx'];

    public function created(Materi $materi): void
    {
        $this->syncDocument($materi);
    }

    public function updated(Materi $materi): void
    {
        if ($materi->wasChanged('file_path')) {
            $materi->babSummary()->delete();
            $this->removeDocumentArtifacts($materi);
            if (! $materi->document_id) {
                $this->syncDocument($materi);
            }
        }
    }

    public function deleted(Materi $materi): void
    {
        $this->removeDocumentArtifacts($materi);
    }

    private function removeDocumentArtifacts(Materi $materi): void
    {
        $document = $materi->document;
        $storage = Storage::disk('public');
        $paths = ['summaries/materi_'.$materi->id.'.txt'];

        foreach ($storage->allDirectories('summaries') as $directory) {
            $paths[] = $directory.'/materi_'.$materi->id.'.txt';
        }

        $storage->delete($paths);
        $document?->delete();
    }

    private function syncDocument(Materi $materi): void
    {
        if ($materi->document_id) {
            return;
        }

        if (! $materi->file_path || ! in_array($materi->file_type, self::SUPPORTED_TYPES, true)) {
            return;
        }

        if (! Storage::disk('public')->exists($materi->file_path)) {
            return;
        }

        $kelas = $materi->kelas;

        $document = Document::create([
            'name' => $materi->judul,
            'original_name' => basename($materi->file_path),
            'path' => $materi->file_path,
            'type' => $materi->file_type,
            'size' => Storage::disk('public')->size($materi->file_path),
            'kategori' => $kelas?->kategori,
            'status' => 'pending',
            'user_id' => $materi->guru_id,
            'materi_id' => $materi->id,
            'kelas_id' => $materi->kelas_id,
        ]);

        ProcessDocument::dispatchSync($document);
    }
}
