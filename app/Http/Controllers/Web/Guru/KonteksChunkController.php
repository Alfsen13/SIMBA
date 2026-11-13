<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Services\RAGService;
use App\Support\KonteksBatas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Kandidat potongan dokumen yang boleh dipakai guru sebagai acuan generate soal.
 *
 * Isi chunk selalu diambil lewat `konten_terbaca`: token penanda rumus tidak
 * pernah boleh keluar ke browser, dan field `embedding` tidak ikut dikirim.
 */
class KonteksChunkController extends Controller
{
    private const PER_HALAMAN = 40;

    private const REKOMENDASI = 25;

    /** Batas kandidat yang diperingkat sebelum dibuang yang di luar scope. */
    private const KANDIDAT_MAKS = 200;

    public function index(Request $request, RAGService $rag): JsonResponse
    {
        $data = $request->validate([
            'pertemuan_ids' => ['required', 'array', 'min:1', 'max:'.KonteksBatas::PERTEMUAN],
            'pertemuan_ids.*' => [
                'integer',
                Rule::exists('pertemuan', 'id')->whereIn('kelas_id', $this->kelasMilikGuru()),
            ],
            'mode' => ['nullable', Rule::in(['lengkap', 'rekomendasi'])],
            'q' => ['nullable', 'string', 'max:150'],
            'hal' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $ids = array_values(array_unique(array_map('intval', (array) $data['pertemuan_ids'])));
        $dasar = $this->dasar($ids);

        if (($data['mode'] ?? 'lengkap') === 'rekomendasi') {
            $kelas = Pertemuan::query()->with('kelas')->whereIn('id', $ids)->first()?->kelas;

            return response()->json($this->rekomendasi(
                $dasar,
                $rag,
                trim((string) ($data['q'] ?? '')),
                $kelas instanceof Kelas ? $kelas->kategori : null
            ));
        }

        $halaman = $dasar
            ->orderBy('document_chunks.document_id')
            ->orderBy('document_chunks.chunk_index')
            ->paginate(self::PER_HALAMAN, ['document_chunks.*'], 'hal', (int) ($data['hal'] ?? 1));

        return response()->json([
            'data' => $halaman->getCollection()->map(fn (DocumentChunk $c): array => $this->item($c))->all(),
            'total' => $halaman->total(),
            'halaman' => $halaman->currentPage(),
            'total_halaman' => $halaman->lastPage(),
        ]);
    }

    /**
     * Similaritas hanya dipakai untuk menyarankan, bukan untuk memaksa: hasilnya
     * dibatasi ke pertemuan yang sedang dipilih guru dan tetap bisa diubah.
     *
     * @param  Builder<DocumentChunk>  $dasar
     * @return array{data: list<array<string, mixed>>, total: int, halaman: int, total_halaman: int}
     */
    private function rekomendasi(Builder $dasar, RAGService $rag, string $query, ?string $kategori): array
    {
        if ($query === '') {
            abort(422, 'Tulis topik terlebih dahulu.');
        }

        $boleh = (clone $dasar)->pluck('document_chunks.id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();

        $urut = collect($rag->retrieve(
            $query,
            max(self::REKOMENDASI, min(count($boleh), self::KANDIDAT_MAKS)),
            (float) config('services.kuis_rag.min_score', 0.35),
            $kategori
        ))
            ->filter(fn (array $c): bool => isset($boleh[(int) $c['id']]))
            ->values();

        $urut = $urut->take(self::REKOMENDASI);

        if ($urut->isEmpty()) {
            return ['data' => [], 'total' => 0, 'halaman' => 1, 'total_halaman' => 1];
        }

        $skor = $urut->mapWithKeys(fn (array $c) => [(int) $c['id'] => (float) $c['score']])->all();
        $isi = (clone $dasar)->whereIn('document_chunks.id', array_keys($skor))->get()->keyBy('id');

        $data = [];

        foreach ($urut as $calon) {
            $chunk = $isi->get((int) $calon['id']);

            if ($chunk instanceof DocumentChunk) {
                $data[] = $this->item($chunk, (float) $calon['score']);
            }
        }

        return ['data' => $data, 'total' => count($data), 'halaman' => 1, 'total_halaman' => 1];
    }

    /** @return Builder<DocumentChunk> */
    private function dasar(array $pertemuanIds): Builder
    {
        return DocumentChunk::query()
            ->with(['document:id,original_name,materi_id', 'document.materi:id,judul,pertemuan_id'])
            ->whereHas('document', function (Builder $document) use ($pertemuanIds): void {
                $document->where('status', 'completed')
                    ->whereHas('materi', fn (Builder $materi) => $materi->whereIn('pertemuan_id', $pertemuanIds));
            });
    }

    /** @return array<string, mixed> */
    private function item(DocumentChunk $chunk, ?float $skor = null): array
    {
        $dokumen = $chunk->document instanceof Document ? $chunk->document : null;
        $materi = $dokumen?->materi instanceof Materi ? $dokumen->materi : null;
        $teks = trim((string) preg_replace('/\s+/u', ' ', $chunk->konten_terbaca));
        $awal = $chunk->page_start;
        $akhir = $chunk->page_end;

        return [
            'id' => (int) $chunk->id,
            'materi_judul' => $materi?->judul,
            'pertemuan_id' => $materi?->pertemuan_id,
            'dokumen' => $dokumen?->original_name,
            'halaman' => $awal === null ? null : ($akhir !== null && $akhir !== $awal ? $awal.'-'.$akhir : (string) $awal),
            'chunk_index' => (int) $chunk->chunk_index,
            'cuplikan' => mb_substr($teks, 0, 220),
            'panjang' => mb_strlen($teks),
            'skor' => $skor === null ? null : round($skor, 4),
        ];
    }

    /** @return list<int> */
    private function kelasMilikGuru(): array
    {
        return Kelas::query()->where('guru_id', Auth::id())->pluck('id')->all();
    }
}
