<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixDocumentKategori extends Command
{
    protected $signature = 'kategori:fix';

    protected $description = 'Update kategori dokumen & chunks dari kelas terkait, pindahkan file storage';

    public function handle(): int
    {
        $this->info('Memperbaiki kategori dokumen dan chunks...');

        $documents = Document::whereNull('kategori')->orWhere('kategori', '')->get();

        if ($documents->isEmpty()) {
            $this->warn('Semua dokumen sudah memiliki kategori.');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();

        $moved = 0;

        foreach ($documents as $doc) {
            $kategori = $doc->kelas?->kategori;

            if (! $kategori) {
                $bar->advance();

                continue;
            }

            $oldKategori = $doc->kategori;
            $doc->update(['kategori' => $kategori]);

            DocumentChunk::where('document_id', $doc->id)
                ->update(['kategori' => $kategori]);

            if ($doc->materi_id) {
                $oldPath = 'summaries/materi_'.$doc->materi_id.'.txt';
                $newPath = 'summaries/'.$kategori.'/materi_'.$doc->materi_id.'.txt';

                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);
                    $moved++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai. File dipindahkan: '.$moved);

        return Command::SUCCESS;
    }
}
