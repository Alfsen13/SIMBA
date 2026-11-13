<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\ApproveFormulasBatchRequest;
use App\Http\Requests\Guru\RejectFormulasBatchRequest;
use App\Jobs\DetectDocumentFormulas;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\DocumentFormulaReview;
use App\Models\Materi;
use App\Services\EmbeddingService;
use App\Services\MateriRingkasanService;
use App\Services\RumusPerbaikanChunk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class FormulaReviewController extends Controller
{
    /**
     * Status yang berarti pemindaian sedang berjalan, sehingga pemicu ganda
     * harus diabaikan.
     */
    private const RUNNING = ['pending', 'processing'];

    public function scan(Materi $materi): RedirectResponse
    {
        $this->authorize('update', $materi);
        abort_unless(config('services.pdf.formula_ocr_enabled'), 403);
        $document = $materi->document;
        abort_unless($document && $document->status === 'completed' && strtolower($document->type ?? '') === 'pdf', 404);

        // Pemindaian ulang aman: job melompati region yang sudah punya review,
        // jadi hasil tambahan saja yang baru. Pemindaian yang macet (worker mati
        // atau antrean tidak jalan) boleh dipicu ulang setelah anggaran habis.
        $updated = Document::whereKey($document->id)
            ->where(function ($query) {
                $query->whereNull('formula_ocr_status')
                    ->orWhereNotIn('formula_ocr_status', self::RUNNING)
                    ->orWhere('updated_at', '<', now()->subSeconds(DetectDocumentFormulas::budget()));
            })->update(['formula_ocr_status' => 'pending', 'formula_ocr_error' => null]);

        if (! $updated) {
            return back()->with('info', __('guru_kelas.materi.rumus_sedang_diproses'));
        }

        try {
            DetectDocumentFormulas::dispatch($document->id)->afterCommit();
        } catch (Throwable $e) {
            $document->update(['formula_ocr_status' => 'failed', 'formula_ocr_error' => 'schedule_failed']);

            throw $e;
        }

        return back()->with('success', __('guru_kelas.materi.rumus_dijadwalkan'));
    }

    /**
     * Kondisi pemindaian untuk polling halaman materi: berapa lama lagi guru
     * sebaiknya menunggu, dan kapan pemindaian dianggap macet.
     */
    public function status(Materi $materi): JsonResponse
    {
        $this->authorize('update', $materi);
        $document = $materi->document;
        abort_unless($document && $document->status === 'completed' && strtolower($document->type ?? '') === 'pdf', 404);

        $status = $document->formula_ocr_status;
        $berjalan = in_array($status, self::RUNNING, true);
        $terlambat = $berjalan && $document->updated_at
            && $document->updated_at->lt(now()->subSeconds(DetectDocumentFormulas::budget()));

        return response()->json([
            'status' => $status,
            'berjalan' => $berjalan,
            'terlambat' => $terlambat,
            'batas' => DetectDocumentFormulas::budget(),
            'jumlah' => $document->formulaReviews()->count(),
            'menunggu' => $document->formulaReviews()->where('status', DocumentFormulaReview::STATUS_PENDING)->count(),
        ]);
    }

    public function approveBatch(
        ApproveFormulasBatchRequest $request,
        Materi $materi,
        EmbeddingService $embedding,
        MateriRingkasanService $ringkasan,
        RumusPerbaikanChunk $perbaikan,
    ): RedirectResponse {
        $this->authorize('update', $materi);
        $document = $materi->document;
        abort_unless($document && $document->status === 'completed', 404);

        $ulasan = $document->formulaReviews()->whereIn('id', $request->selectedIds())->orderBy('id')->get();
        $pending = $ulasan->where('status', DocumentFormulaReview::STATUS_PENDING)->values();

        if ($pending->isEmpty()) {
            return back()->with('info', __('guru_kelas.materi.rumus_sudah_diproses'));
        }

        $isi = [];
        foreach ($pending as $review) {
            $isi[$review->id] = trim((string) data_get($request->validated(), "reviews.{$review->id}.latex", ''));
        }

        if (in_array('', $isi, true)) {
            return back()->with('info', __('guru_kelas.materi.rumus_latex_belum_lengkap'));
        }

        $hasil = $this->coba(fn (): array => $this->tulisApprove($document, $pending, $isi, $embedding, $perbaikan));

        if (! $hasil['selesai']) {
            return back()->with('info', __('guru_kelas.materi.rumus_konflik'));
        }

        return back()->with('success', __(
            'guru_kelas.materi.rumus_batch_disetujui',
            ['count' => count($isi), 'tambal' => $hasil['tambal'], 'tambah' => $hasil['tambah']],
        ).$this->catatanRingkasan($ringkasan->sinkronkanRumus($materi)));
    }

    /**
     * Setujui hasil pindai tanpa panggil AI di dalam lock: konten dihitung dari
     * snapshot, embedding dihitung sekali, lalu transaksi memverifikasi ulang.
     *
     * @param  Collection<int, DocumentFormulaReview>  $pending
     * @param  array<int, string>  $isi  review_id => latex
     * @return array{selesai: bool, tambal: int, tambah: int}
     */
    private function tulisApprove(
        Document $document,
        Collection $pending,
        array $isi,
        EmbeddingService $embedding,
        RumusPerbaikanChunk $perbaikan,
    ): array {
        $reviews = $pending->each(function (DocumentFormulaReview $review) use ($isi): void {
            $review->latex = $isi[$review->id];
        })->all();

        $chunk = $document->chunks()->orderBy('id')->get();
        $rencana = $perbaikan->rencanakan($reviews, $chunk);
        $tambal = $rencana['konten'];
        $tambah = [];

        foreach ($rencana['mandiri'] as $id) {
            $ulasan = $pending->firstWhere('id', $id);

            if ($ulasan !== null) {
                $tambah[$id] = RumusPerbaikanChunk::isiMandiri($ulasan);
            }
        }

        $vektor = $embedding->embedBatch([...array_values($tambal), ...array_values($tambah)]);

        if (count($vektor) !== count($tambal) + count($tambah)
            || collect($vektor)->contains(fn ($v) => ! is_array($v) || $v === [])) {
            throw new RuntimeException('Embedding rumus tidak tersedia.');
        }

        $nomor = 0;
        $vektorTambal = [];
        foreach (array_keys($tambal) as $id) {
            $vektorTambal[$id] = $vektor[$nomor++];
        }
        $vektorTambah = [];
        foreach (array_keys($tambah) as $id) {
            $vektorTambah[$id] = $vektor[$nomor++];
        }

        $awal = $chunk->pluck('content', 'id')->all();

        $ok = $this->transaksi(function () use ($document, $isi, $rencana, $tambal, $vektorTambal, $tambah, $vektorTambah, $awal): bool {
            $terkunci = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $ulasan = $terkunci->formulaReviews()->whereIn('id', array_keys($isi))->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($ulasan->count() !== count($isi)
                || $ulasan->contains(fn (DocumentFormulaReview $review): bool => $review->status !== DocumentFormulaReview::STATUS_PENDING)) {
                return false;
            }

            $index = (int) $terkunci->chunks()->max('chunk_index');
            $baru = [];

            foreach ($tambah as $id => $content) {
                $baru[$id] = (int) $terkunci->chunks()->create([
                    'content' => $content,
                    'embedding' => $vektorTambah[$id],
                    'chunk_index' => ++$index,
                    'kategori' => $terkunci->kategori,
                    'page_start' => $ulasan[$id]->page,
                    'page_end' => $ulasan[$id]->page,
                ])->id;
            }

            if ($tambal !== []) {
                $terkunciChunk = DocumentChunk::whereIn('id', array_keys($tambal))->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($tambal as $id => $content) {
                    $baris = $terkunciChunk->get($id);

                    if (! $baris || $baris->content !== $awal[$id]) {
                        return false;
                    }

                    $baris->update(['content' => $content, 'embedding' => $vektorTambal[$id]]);
                }
            }

            foreach ($ulasan as $id => $review) {
                $tersentuh = $rencana['tambalan'][$id] ?? null;

                $review->update([
                    'latex' => $isi[$id],
                    'status' => DocumentFormulaReview::STATUS_APPROVED,
                    'document_chunk_id' => $baru[$id] ?? null,
                    'repaired_chunk_ids' => $tersentuh,
                    'repaired_via_marker' => $tersentuh === null ? null : in_array((int) $id, $rencana['penanda'], true),
                    'approved_at' => now(),
                    'rejected_at' => null,
                    'ocr_error' => null,
                ]);
            }

            return true;
        });

        return [
            'selesai' => $ok,
            'tambal' => $ok ? count($rencana['tambalan']) : 0,
            'tambah' => $ok ? count($tambah) : 0,
        ];
    }

    /**
     * Menolak hasil deteksi. Chunk RAG buatan yang tidak lagi diverifikasi
     * pengajar dihapus, dan teks rumus di chunk asli dikembalikan.
     */
    public function rejectBatch(
        RejectFormulasBatchRequest $request,
        Materi $materi,
        EmbeddingService $embedding,
        MateriRingkasanService $ringkasan,
        RumusPerbaikanChunk $perbaikan,
    ): RedirectResponse {
        $this->authorize('update', $materi);
        $document = $materi->document;
        abort_unless($document && $document->status === 'completed', 404);

        $ulasan = $document->formulaReviews()->whereIn('id', $request->selectedIds())->orderBy('id')->get();
        $dipilih = $ulasan->reject(fn (DocumentFormulaReview $review): bool => $review->status === DocumentFormulaReview::STATUS_REJECTED)->values();

        if ($dipilih->isEmpty()) {
            return back()->with('info', __('guru_kelas.materi.rumus_sudah_diproses'));
        }

        $hasil = $this->coba(fn (): array => $this->tulisReject($document, $dipilih, $embedding, $perbaikan));

        if (! $hasil['selesai']) {
            return back()->with('info', __('guru_kelas.materi.rumus_konflik'));
        }

        $pesan = __('guru_kelas.materi.rumus_batch_ditolak', ['count' => $dipilih->count()]);

        if ($hasil['pulih'] > 0) {
            $pesan .= ' '.__('guru_kelas.materi.rumus_batch_dipulihkan', ['count' => $hasil['pulih']]);
        }

        return back()->with('success', $pesan.$this->catatanRingkasan($ringkasan->sinkronkanRumus($materi)));
    }

    /**
     * @param  Collection<int, DocumentFormulaReview>  $dipilih
     * @return array{selesai: bool, pulih: int}
     */
    private function tulisReject(
        Document $document,
        Collection $dipilih,
        EmbeddingService $embedding,
        RumusPerbaikanChunk $perbaikan,
    ): array {
        $pernahDitambal = $dipilih
            ->flatMap(fn (DocumentFormulaReview $review): array => (array) $review->repaired_chunk_ids)
            ->unique()
            ->values();
        $chunk = DocumentChunk::whereIn('id', $pernahDitambal)->orderBy('id')->get();

        $pulih = $perbaikan->pulihkan($dipilih->all(), $chunk);
        $vektor = $embedding->embedBatch(array_values($pulih['konten']));

        if (count($vektor) !== count($pulih['konten'])
            || collect($vektor)->contains(fn ($v) => ! is_array($v) || $v === [])) {
            throw new RuntimeException('Embedding rumus tidak tersedia.');
        }

        $vektorPulih = array_combine(array_keys($pulih['konten']), $vektor);
        $awal = $chunk->pluck('content', 'id')->all();
        $hapus = $dipilih->pluck('document_chunk_id')->filter()->values()->all();

        $ok = $this->transaksi(function () use ($document, $dipilih, $pulih, $vektorPulih, $awal, $hapus): bool {
            $terkunci = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $ulasan = $terkunci->formulaReviews()->whereIn('id', $dipilih->pluck('id'))->orderBy('id')->lockForUpdate()->get();

            foreach ($ulasan as $review) {
                $lama = $dipilih->firstWhere('id', $review->id);

                if (! $lama || $review->status !== $lama->status || $review->latex !== $lama->latex
                    || $review->repaired_chunk_ids !== $lama->repaired_chunk_ids) {
                    return false;
                }
            }

            if ($pulih['konten'] !== []) {
                $terkunciChunk = DocumentChunk::whereIn('id', array_keys($pulih['konten']))->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($pulih['konten'] as $id => $content) {
                    $baris = $terkunciChunk->get($id);

                    if (! $baris || $baris->content !== $awal[$id]) {
                        return false;
                    }

                    $baris->update(['content' => $content, 'embedding' => $vektorPulih[$id]]);
                }
            }

            if ($hapus !== []) {
                DocumentChunk::whereIn('id', $hapus)->delete();
            }

            foreach ($ulasan as $review) {
                $sisa = array_values(array_diff((array) $review->repaired_chunk_ids, $pulih['review_ids'][$review->id] ?? []));

                $review->update([
                    'status' => DocumentFormulaReview::STATUS_REJECTED,
                    'document_chunk_id' => null,
                    'repaired_chunk_ids' => $sisa === [] ? null : $sisa,
                    'repaired_via_marker' => $sisa === [] ? null : $review->repaired_via_marker,
                    'approved_at' => null,
                    'rejected_at' => now(),
                ]);
            }

            return true;
        });

        return ['selesai' => $ok, 'pulih' => $ok ? count($pulih['review_ids']) : 0];
    }

    /**
     * Jalankan rencana tulis; bila konten berubah di antara snapshot dan lock,
     * hitung ulang sekali sebelum menyerah.
     *
     * @param  callable(): array{selesai: bool, ...}  $jalan
     * @return array{selesai: bool, ...}
     */
    private function coba(callable $jalan): array
    {
        $hasil = ['selesai' => false];

        for ($percobaan = 0; $percobaan < 2; $percobaan++) {
            $hasil = $jalan();

            if ($hasil['selesai']) {
                return $hasil;
            }
        }

        return $hasil;
    }

    /** Transaksi yang boleh membatalkan dirinya sendiri: false berarti ada yang berubah. */
    private function transaksi(callable $jalan): bool
    {
        DB::beginTransaction();

        try {
            if (! $jalan()) {
                DB::rollBack();

                return false;
            }

            DB::commit();

            return true;
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /** Kalimat tambahan saja bila ringkasan materi benar-benar berubah. */
    private function catatanRingkasan(bool $berubah): string
    {
        return $berubah ? ' '.__('guru_kelas.materi.rumus_ringkasan_diperbarui') : '';
    }
}
