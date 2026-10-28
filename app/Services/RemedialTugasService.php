<?php

namespace App\Services;

use App\Models\MateriRemedial;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemedialTugasService
{
     
    protected const BLOOM_MIN = 'C1';

    protected const BLOOM_MAX = 'C5';

     
    protected const MAKS_PERCOBAAN = 3;

    protected AIProviderService $ai;

    protected BloomTaxonomyService $bloom;

    public function __construct()
    {
        $this->ai = app(AIProviderService::class);
        $this->bloom = app(BloomTaxonomyService::class);
    }

     
    public function generateTugas(MateriRemedial $remedial): bool
    {
        if (! empty($remedial->tugas_remedial)) {
            return false;
        }

        $ringkasan = trim((string) ($remedial->ringkasan ?? ''));
        $topik = trim((string) ($remedial->topik_lemah ?? ''));
        if ($ringkasan === '' || $topik === '') {
            return false;
        }

        $system = 'Anda adalah ahli pedagogi dan penyusun soal remedial sekolah. Anda menguasai dan menerapkan Taksonomi Bloom (revisi Anderson & Krathwohl). Anda hanya membuat soal berdasarkan materi yang diberikan. Tuliskan semua rumus atau ekspresi matematika/ilmiah di dalam pertanyaan dan referensi jawaban memakai notasi LaTeX dengan pembatas $...$ untuk rumus inline dan $$...$$ untuk rumus blok.';

        $prompt = <<<PROMPT
Topik lemah siswa: {$topik}

Ringkasan materi yang sudah dipelajari siswa:
{$ringkasan}

Tugaskanlah oleh Anda:

Rubrik Taksonomi Bloom yang dipakai (level C1-C6):
{$this->bloomRubrik('generate')}

Contoh klasifikasi:
{$this->bloom->classificationExamples(4)}

1. Tentukan 2-4 TUJUAN PEMBELAJARAN untuk topik di atas. Setiap tujuan dipetakan ke SATU level Taksonomi Bloom (C1-C6) dan mencerminkan kompetensi yang harus dikuasai siswa agar keluar dari kondisi remedial.
2. Buatkan {$this->jumlahSoal()} soal uraian (essai) untuk mengukur pencapaian tujuan pembelajaran tersebut. Aturan:
   - Mulai dengan soal level C1/C2 (pemantapan dasar), lalu naik ke C3/C4 (penerapan dan analisis). Maksimal 1 soal level C5.
   - Setiap soal diberi atribut bloom = level kognitif yang DINILAI (bukan yang paling sulit, melainkan kemampuan berpikir yang diukur soal itu).
   - Tentukan level dari tuntutan kognitif utama soal, bukan hanya dari kata kerjanya.
   - referensi = poin-poin kunci jawaban yang benar (dipakai AI saat menilai).
   - Soal harus berdasarkan informasi yang ada di ringkasan; jangan mengarang.
   - Soal saling berbeda dan tidak mengulang.

Keluarkan HANYA pure JSON array-object di bawah ini tanpa teks lain dan tanpa markdown code fence:
{"tujuan":[{"kode":"C2","level":"Memahami","tujuan":"..."}],"soal":[{"pertanyaan":"...","referensi":"...","bloom":"C3"}]}
PROMPT;




        $parsed = null;
        for ($percobaan = 1; $percobaan <= self::MAKS_PERCOBAAN; $percobaan++) {
            try {
                $output = $this->ai->sendLocalPreferred($prompt, [], $system);
            } catch (Throwable $e) {
                Log::warning('RemedialTugas: panggilan AI gagal.', [
                    'remedial_id' => $remedial->id,
                    'percobaan' => $percobaan,
                    'msg' => $e->getMessage(),
                ]);

                continue;
            }

            $parsed = $this->parseGenerate($output);

            if ($parsed !== null && count($parsed['soal']) > 0) {
                break;
            }

            Log::warning('RemedialTugas: output AI tidak bisa di-parse, mencoba lagi.', [
                'remedial_id' => $remedial->id,
                'percobaan' => $percobaan,
                'cuplikan' => mb_substr(trim((string) $output), 0, 160),
            ]);
        }

        if ($parsed === null || count($parsed['soal']) === 0) {
            Log::warning('RemedialTugas: gagal setelah semua percobaan, remedial tanpa soal.', [
                'remedial_id' => $remedial->id,
            ]);

            return false;
        }

        $remedial->update([
            'bloom_tujuan' => $parsed['tujuan'],
            'tugas_remedial' => $parsed['soal'],
        ]);

        return true;
    }

     
    public function nilai(MateriRemedial $remedial): bool
    {
        $soal = array_values($remedial->tugas_remedial ?? []);
        $jawaban = $remedial->jawaban_remedial ?? [];
        if (count($soal) === 0 || count($jawaban) === 0) {
            return false;
        }

        $blokSoal = '';
        foreach ($soal as $i => $item) {
            $jawab = $jawaban[$i] ?? '';
            if (trim((string) $jawab) === '') {
                continue;
            }

            $bloom = $this->validBloom((string) ($item['bloom'] ?? ''));
            $blokSoal .= "\nSoal #".($i + 1)."\n";
            $blokSoal .= 'Pertanyaan: '.($item['pertanyaan'] ?? '')."\n";
            $blokSoal .= "Level Taksonomi Bloom yang diukur: {$bloom} — ".$this->bloom->label($bloom)."\n";
            if (isset($item['referensi']) && trim((string) $item['referensi']) !== '') {
                $blokSoal .= "Referensi jawaban: {$item['referensi']}\n";
            }
            $blokSoal .= "Jawaban siswa: {$jawab}\n";
        }

        if (trim($blokSoal) === '') {
            return false;
        }

        $tujuanText = $this->tujuanText($remedial->bloom_tujuan ?? []);
        $system = 'Anda adalah penilai hasil belajar siswa berbasis Taksonomi Bloom. Anda menilai ketepatan jawaban terhadap soal, referensi, DAN level berpikir yang ditargetkan, lalu menyimpulkan apakah siswa telah mencapai tujuan pembelajaran (mastery). Jawab sesuai format yang diminta. Bila menyebut rumus atau ekspresi matematika/ilmiah, tuliskan memakai notasi LaTeX dengan pembatas $...$ untuk rumus inline dan $$...$$ untuk rumus blok.';

        $prompt = <<<PROMPT
Tujuan pembelajaran yang harus dicapai siswa:
{$tujuanText}

Berikut rubrik Taksonomi Bloom yang dipakai menilai:
{$this->bloomRubrik('penilaian')}

Jawaban siswa terhadap tugas remedial:
{$blokSoal}

Langkah:
1. Untuk setiap soal, nilai status akhir: "benar" (jawaban tepat, sesuai kunci, dan menunjukkan kemampuan berpikir pada level Bloom yang diukur), "mendekati" (sebagian benar atau level berpikirnya masih di bawah target), atau "salah" (keliru/tidak relevan).
2. Berikan skor 0-100 dan alasan singkat (maks 2 kalimat) untuk tiap soal.
3. Tentukan kesimpulan mastery: tercapai=true HANYA jika rata-rata skor >= 70 DAN mayoritas soal dinilai benar pada level yang ditargetkan. Selain itu tercapai=false.

Keluarkan HANYA pure JSON array-object tanpa teks lain dan tanpa markdown code fence:
{"per_soal":[{"indeks":0,"status":"benar","skor":85,"alasan":"...","bloom":"C2"}],"mastery":{"tercapai":false,"alasan":"..."}}
PROMPT;

        $parsed = null;
        for ($percobaan = 1; $percobaan <= self::MAKS_PERCOBAAN; $percobaan++) {
            try {
                $output = $this->ai->sendLocalPreferred($prompt, [], $system);
            } catch (Throwable $e) {
                Log::warning('RemedialAI: penilaian gagal untuk remedial {id}: {msg}', [
                    'id' => $remedial->id,
                    'msg' => $e->getMessage(),
                ]);

                continue;
            }

            $parsed = $this->parseNilai($output);

            if ($parsed !== null && count($parsed['per_soal']) > 0) {
                break;
            }

            Log::warning('RemedialTugas: output penilaian tidak bisa di-parse, mencoba lagi.', [
                'remedial_id' => $remedial->id,
                'percobaan' => $percobaan,
                'cuplikan' => mb_substr(trim((string) $output), 0, 160),
            ]);
        }

        if ($parsed === null || count($parsed['per_soal']) === 0) {
            Log::warning('RemedialTugas: penilaian gagal setelah semua percobaan.', [
                'remedial_id' => $remedial->id,
            ]);

            return false;
        }

        $remedial->update([
            'hasil_remedial' => $parsed['per_soal'],
            'bloom_tercapai' => $parsed['mastery']['tercapai'],
            'mastery_alasan' => $parsed['mastery']['alasan'],
            'dinilai_at' => now(),
            'status' => 'selesai',
        ]);

        return true;
    }

     
    protected function bloomRubrik(string $konteks): string
    {
        return $this->bloom->rubric($konteks);
    }

     
    protected function tujuanText(array $tujuan): string
    {
        if (count($tujuan) === 0) {
            return '- (tidak disebutkan)';
        }

        $lines = [];
        foreach ($tujuan as $i => $t) {
            $bloom = $this->validBloom((string) ($t['kode'] ?? ''));
            $level = trim((string) ($t['level'] ?? '')) !== ''
                ? trim((string) $t['level'])
                : $this->bloom->label($bloom);
            $lines[] = ($i + 1).". ({$bloom} - {$level}) ".($t['tujuan'] ?? '');
        }

        return implode("\n", $lines);
    }

     
    protected function jumlahSoal(): int
    {
        return 4;
    }

     
    protected function validBloom(string $kode): string
    {
        return $this->bloom->valid($kode);
    }

     
    protected function normalizeString(mixed $value): string
    {
        if (is_array($value)) {
            return trim(implode(', ', array_map('strval', $value)));
        }

        return trim((string) $value);
    }

     
    protected function parseGenerate(string $output): ?array
    {
        $json = $this->extractJson($output);
        if ($json === null) {
            return null;
        }

        $json = $this->sejajarkanSkema($json);
        if ($json === null) {
            return null;
        }

        $tujuan = [];
        foreach ($json['tujuan'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $kode = $this->validBloom((string) ($item['kode'] ?? ''));

            if (! $this->bloom->has($kode)) {
                continue;
            }

            $teksTujuan = trim((string) ($item['tujuan'] ?? ''));
            if ($teksTujuan === '') {
                continue;
            }

            $tujuan[] = [
                'kode' => $kode,
                'level' => trim((string) ($item['level'] ?? '')) !== ''
                    ? trim((string) $item['level'])
                    : $this->bloom->label($kode),
                'tujuan' => $teksTujuan,
            ];

            if (count($tujuan) >= 6) {
                break;
            }
        }

        $soal = [];
        foreach ($json['soal'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $pertanyaan = trim((string) ($item['pertanyaan'] ?? ''));
            if ($pertanyaan === '') {
                continue;
            }



            $soal[] = [
                'pertanyaan' => $pertanyaan,
                'referensi' => $this->normalizeString($item['referensi'] ?? ''),
                'bloom' => $this->bloom->clampToSpan(
                    $this->bloom->decide($pertanyaan, (string) ($item['bloom'] ?? ''), null),
                    self::BLOOM_MIN,
                    self::BLOOM_MAX
                ),
            ];

            if (count($soal) >= 6) {
                break;
            }
        }

        if (count($soal) === 0) {
            return null;
        }

        return ['tujuan' => $tujuan, 'soal' => $soal];
    }

     
    protected function parseNilai(string $output): ?array
    {
        $json = $this->extractJson($output);
        if ($json === null) {
            return null;
        }

        $json = $this->sejajarkanSkema($json);
        if ($json === null) {
            return null;
        }

        $perSoal = [];
        foreach ($json['per_soal'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $status = strtolower((string) ($item['status'] ?? 'mendekati'));
            if (! in_array($status, ['benar', 'mendekati', 'salah'], true)) {
                $status = 'mendekati';
            }

            $bloom = trim((string) ($item['bloom'] ?? ''));
            $bloom = $bloom !== '' ? $this->bloom->valid($bloom) : null;

            $perSoal[] = [
                'indeks' => (int) ($item['indeks'] ?? count($perSoal)),
                'status' => $status,
                'skor' => max(0, min(100, (int) ($item['skor'] ?? 50))),
                'alasan' => mb_substr((string) ($item['alasan'] ?? ''), 0, 500),
                'bloom' => $bloom,
            ];
        }

        if (count($perSoal) === 0) {
            return null;
        }

        $mastery = $json['mastery'] ?? [];
        $tercapai = (bool) ($mastery['tercapai'] ?? false);

        return [
            'per_soal' => $perSoal,
            'mastery' => [
                'tercapai' => $tercapai,
                'alasan' => mb_substr((string) ($mastery['alasan'] ?? ''), 0, 800),
            ],
        ];
    }

     
    protected function extractJson(string $output): ?array
    {
        $teksBersih = preg_replace('/^```(?:json)?\s*/i', '', trim($output));
        $teksBersih = preg_replace('/\s*```$/u', '', (string) $teksBersih);

        $decoded = json_decode($teksBersih, true);
        if (is_array($decoded)) {
            return $decoded;
        }


        if (preg_match('/(\{(?:[^{}]|(?R))*\}|\[(?:[^\[\]]|(?R))*\])/', $teksBersih, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

     
    protected function sejajarkanSkema(array $json): ?array
    {
        if (array_is_list($json)) {
            $kunciDikenal = ['soal', 'tujuan', 'per_soal', 'mastery'];
            $json = collect($json)
                ->first(fn ($item) => is_array($item) && array_intersect($kunciDikenal, array_keys($item)) !== []) ?? [];
        }

        foreach (['tujuan', 'soal'] as $kunci) {
            if (isset($json[$kunci]) && is_array($json[$kunci]) && ! array_is_list($json[$kunci])) {
                $json[$kunci] = [$json[$kunci]];
            }
        }

        return $json === [] ? null : $json;
    }
}

