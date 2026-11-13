<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentFormulaReview;
use App\Models\Materi;
use App\Models\MateriBabSummary;

class MateriRingkasanService
{
    /**
     * Penanda bagian rumus di dalam ringkasan. Segala teks dari penanda ini
     * sampai akhir ditulis ulang, jadi ringkasan dasar AI tidak ikut menumpuk.
     */
    public const MARKER = 'Rumus yang telah diperiksa pengajar:';

    /** Benar bila ringkasan materi benar-benar ditulis ulang. */
    public function sinkronkanRumus(Materi $materi): bool
    {
        $summary = MateriBabSummary::where('materi_id', $materi->id)->first();

        // Tanpa ringkasan dasar tidak ada yang bisa digabung; chunk RAG sudah
        // membawa rumusnya, jadi tidak perlu membuat baris berisi daftar saja.
        if (! $summary) {
            return false;
        }

        $dasar = $this->pangkas(trim(RumusPenanda::manusiakan((string) $summary->ringkasan)));
        if ($dasar === '') {
            return false;
        }

        $ringkasan = $dasar.$this->bagianRumus($materi);

        if ($ringkasan === (string) $summary->ringkasan) {
            return false;
        }

        $summary->update(['ringkasan' => $ringkasan]);

        return true;
    }

    private function bagianRumus(Materi $materi): string
    {
        $documentId = $materi->document?->id;

        $rumus = $documentId === null ? collect() : DocumentFormulaReview::query()
            ->where('document_id', $documentId)
            ->where('status', DocumentFormulaReview::STATUS_APPROVED)
            ->whereNotNull('latex')
            ->orderBy('page')
            ->orderBy('id')
            ->pluck('latex')
            ->map(fn ($latex): string => trim((string) $latex))
            ->filter(fn (string $latex): bool => $latex !== '')
            ->map(fn (string $latex): string => '$'.$latex.'$')
            ->unique()
            ->values();

        if ($rumus->isEmpty()) {
            return '';
        }

        return "\n\n".self::MARKER.' '.$rumus->implode(', ');
    }

    private function pangkas(string $ringkasan): string
    {
        $mulai = mb_strpos($ringkasan, self::MARKER);

        return $mulai === false ? $ringkasan : rtrim(mb_substr($ringkasan, 0, $mulai));
    }
}
