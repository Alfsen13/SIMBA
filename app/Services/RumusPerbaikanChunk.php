<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentChunk;
use App\Models\DocumentFormulaReview;
use Illuminate\Support\Collection;

/**
 * Menambal teks rumus yang salah diekstrak, langsung di dalam chunk hasil
 * pemrosesan pertama, supaya konteks sekitarnya tetap utuh.
 */
class RumusPerbaikanChunk
{
    /** Baris hasil anotasi simbol; teks di dalamnya bukan bagian materi aslinya. */
    private const PENANDA_ANOTASI = ['FORMULA:', '[Simbol]'];

    /** Isi chunk tambahan untuk review yang lokasi aslinya tidak bisa ditemukan. */
    public static function isiMandiri(DocumentFormulaReview $review): string
    {
        return 'Rumus yang diperiksa pengajar pada halaman '.$review->page.': $'.trim((string) $review->latex).'$';
    }

    /**
     * Hitung konten chunk hasil penambalan tanpa menulis ke basis data.
     *
     * @param  list<DocumentFormulaReview>  $reviews  latex-nya sudah berisi nilai yang akan disimpan
     * @param  Collection<int, DocumentChunk>  $chunks
     * @return array{konten: array<int, string>, tambalan: array<int, list<int>>, penanda: list<int>, mandiri: list<int>}
     */
    public function rencanakan(array $reviews, Collection $chunks): array
    {
        $kerja = $chunks->pluck('content', 'id')->all();
        $rentang = $chunks
            ->mapWithKeys(fn (DocumentChunk $chunk): array => [
                $chunk->id => [(int) $chunk->page_start, (int) $chunk->page_end],
            ])
            ->all();

        $tambalan = [];
        $lewatPenanda = [];
        $mandiri = [];

        foreach ($reviews as $review) {
            $ganti = '$'.trim((string) $review->latex).'$';

            if ($ganti === '$$') {
                $mandiri[] = $review->id;

                continue;
            }

            $tersentuh = $this->tambalPenanda($review, $ganti, $kerja, $rentang);

            if ($tersentuh !== []) {
                $tambalan[$review->id] = $tersentuh;
                $lewatPenanda[] = $review->id;

                continue;
            }

            // Tanpa teks sumber tidak ada yang bisa dicari: biarkan jadi chunk
            // tambahan seperti sebelum fitur ini ada.
            $sumber = trim((string) $review->source_text);
            if ($sumber === '') {
                $mandiri[] = $review->id;

                continue;
            }

            $pola = $this->pola($sumber);
            $tersentuh = [];

            foreach ($kerja as $id => $konten) {
                if (! $this->cakupHalaman($rentang[$id] ?? [0, 0], (int) $review->page)) {
                    continue;
                }

                // Sudah ditambal pada approve sebelumnya: tidak boleh dobel.
                if (str_contains((string) $konten, $ganti)) {
                    $tersentuh[] = (int) $id;

                    continue;
                }

                $hasil = $this->tambal((string) $konten, $pola, $ganti);
                if ($hasil !== null) {
                    $kerja[$id] = $hasil;
                    $tersentuh[] = (int) $id;
                }
            }

            if ($tersentuh === []) {
                $mandiri[] = $review->id;

                continue;
            }

            $tambalan[$review->id] = $tersentuh;
        }

        return [
            'konten' => array_diff_assoc($kerja, $chunks->pluck('content', 'id')->all()),
            'tambalan' => $tambalan,
            'penanda' => $lewatPenanda,
            'mandiri' => $mandiri,
        ];
    }

    /**
     * Ganti penanda region pada tempat teks rumus dulu salah diektrak.
     *
     * Semua kemunculan token yang sama ikut diganti: satu region hanya punya
     * satu kunci, jadi kemunculan ganda bukan tebak-tebakan seperti pada
     * pencocokan teks sumber.
     *
     * @param  array<int, string>  $kerja
     * @param  array<int, array{0: int, 1: int}>  $rentang
     * @return list<int> chunk yang tersentuh
     */
    private function tambalPenanda(DocumentFormulaReview $review, string $ganti, array &$kerja, array $rentang): array
    {
        $penanda = RumusPenanda::penanda($review->region_key);

        if ($penanda === '') {
            return [];
        }

        $tersentuh = [];

        foreach ($kerja as $id => $konten) {
            if (! $this->cakupHalaman($rentang[$id] ?? [0, 0], (int) $review->page)) {
                continue;
            }

            if (str_contains((string) $konten, $penanda)) {
                $kerja[$id] = str_replace($penanda, $ganti, (string) $konten);
                $tersentuh[] = (int) $id;

                continue;
            }

            // Penandanya sudah pernah terganti oleh persetujuan sebelumnya.
            if (str_contains((string) $konten, $ganti)) {
                $tersentuh[] = (int) $id;
            }
        }

        return $tersentuh;
    }

    /**
     * Kembalikan teks sumber pada chunk yang pernah ditambal review tersebut.
     *
     * @param  list<DocumentFormulaReview>  $reviews
     * @param  Collection<int, DocumentChunk>  $chunks
     * @return array{konten: array<int, string>, review_ids: array<int, list<int>>}
     */
    public function pulihkan(array $reviews, Collection $chunks): array
    {
        $kerja = $chunks->pluck('content', 'id')->all();
        $terpulih = [];

        foreach ($reviews as $review) {
            $cari = '$'.trim((string) $review->latex).'$';
            $kembali = $review->repaired_via_marker
                ? RumusPenanda::penanda($review->region_key)
                : $this->rapikan((string) $review->source_text);

            if ($cari === '$$' || $kembali === '') {
                continue;
            }

            foreach ((array) $review->repaired_chunk_ids as $id) {
                $teks = (string) ($kerja[$id] ?? '');

                // Hanya kemunculan tunggal yang boleh dipulihkan: kalau lebih
                // dari satu, tidak jelas mana yang berasal dari review ini.
                if ($teks === '' || substr_count($teks, $cari) !== 1) {
                    continue;
                }

                $kerja[$id] = substr_replace($teks, $kembali, strpos($teks, $cari), strlen($cari));
                $terpulih[(int) $review->id][] = (int) $id;
            }
        }

        return [
            'konten' => array_diff_assoc($kerja, $chunks->pluck('content', 'id')->all()),
            'review_ids' => $terpulih,
        ];
    }

    /** Teks sumber dipulihkan apa adanya, hanya spasi berlebih hasil ekstraksi yang dirapikan. */
    private function rapikan(string $sumber): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $sumber));
    }

    /** Pola pencarian: token teks sumber yang boleh dipisah spasi/baris apa pun. */
    private function pola(string $sumber): string
    {
        $token = preg_split('/\s+/u', $sumber, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return '/'.implode('\\s+', array_map(fn (string $t): string => preg_quote($t, '/'), $token)).'/u';
    }

    /** @return string|null  konten hasil penambalan, null bila lokasi teks sumber tidak aman */
    private function tambal(string $konten, string $pola, string $ganti): ?string
    {
        if (! preg_match_all($pola, $konten, $cocok, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        // Muncul dua kali (mis. di chunk kembar dalam halaman yang sama, atau
        // rumus yang sama dipakai dua tempat) menebak = merusak.
        if (count($cocok[0]) !== 1) {
            return null;
        }

        [$teks, $mulai] = $cocok[0][0];

        if ($this->dariAnotasi($konten, (int) $mulai)) {
            return null;
        }

        return substr_replace($konten, $ganti, (int) $mulai, strlen((string) $teks));
    }

    /** Baris yang memuat offset ini hanyalah anotasi simbol, bukan teks materi. */
    private function dariAnotasi(string $konten, int $mulai): bool
    {
        $awal = strrpos(substr($konten, 0, $mulai), "\n");
        $awalBaris = $awal === false ? 0 : $awal + 1;
        $akhirBaris = strpos($konten, "\n", $mulai);
        $baris = substr($konten, $awalBaris, ($akhirBaris === false ? strlen($konten) : $akhirBaris) - $awalBaris);

        foreach (self::PENANDA_ANOTASI as $penanda) {
            if (str_starts_with(ltrim($baris), $penanda)) {
                return true;
            }
        }

        return false;
    }

    /** @param  array{0: int, 1: int}  $rentang */
    private function cakupHalaman(array $rentang, int $halaman): bool
    {
        [$awal, $akhir] = $rentang;

        return $awal > 0 && $halaman >= $awal && $halaman <= max($akhir, $awal);
    }
}
