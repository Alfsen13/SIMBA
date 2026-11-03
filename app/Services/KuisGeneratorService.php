<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class KuisGeneratorService
{
    protected AIProviderService $ai;

    protected SymbolService $symbols;

    protected RAGService $rag;

    protected BloomTaxonomyService $bloom;

    public function __construct()
    {
        $this->ai = app(AIProviderService::class);
        $this->symbols = app(SymbolService::class);
        $this->rag = app(RAGService::class);
        $this->bloom = app(BloomTaxonomyService::class);
    }

     
    public function generate(Pertemuan $pertemuan, int $jumlah, string $kesulitan, ?string $topik, array $sumber = []): array
    {
        $konteks = $this->buildMateriContext($pertemuan, $topik, $sumber);
        if ($konteks === '') {
            throw new RuntimeException('Pertemuan ini belum memiliki materi untuk dijadikan acuan soal.');
        }

        $tingkat = [
            'mudah' => 'mudah (definisi dan konsep dasar)',
            'sedang' => 'sedang (penerapan sederhana)',
            'sulit' => 'sulit (analisis dan perhitungan)',
        ];
        $tingkatTeks = $tingkat[$kesulitan] ?? $tingkat['sedang'];

        $fokusTopik = trim((string) $topik) !== ''
            ? "Fokus topik: {$topik}"
            : 'Fokus topik: seluruh materi yang diberikan';

        $system = 'Anda adalah asisten penyusun soal pilihan ganda untuk guru. Anda menguasai Taksonomi Bloom Revisi. Anda hanya boleh membuat soal berdasarkan materi pembelajaran yang diberikan. Tuliskan semua rumus atau ekspresi matematika/ilmiah di dalam pertanyaan dan pilihan jawaban memakai notasi LaTeX dengan pembatas $...$ untuk rumus inline dan $$...$$ untuk rumus blok.';

        $prompt = <<<PROMPT
Berdasarkan materi pembelajaran di bawah ini, buatkan {$jumlah} soal pilihan ganda untuk asesmen pelajar.

Tingkat kesulitan: {$tingkatTeks}
{$fokusTopik}
{$this->bloomSection($kesulitan, $jumlah)}
Aturan:
1. Setiap soal memiliki tepat 4 pilihan jawaban.
2. Tandai SATU jawaban yang benar memakai indeks: 0 = pilihan A, 1 = B, 2 = C, 3 = D.
3. Soal harus berdasarkan informasi yang benar-benar ada di materi, jangan mengarang konten yang tidak ada di materi.
4. Pilihan jawaban yang salah harus masuk akal (pengecoh berkualitas).
5. Setiap soal unik dan tidak mengulang pertanyaan yang sama.
{$this->bloomRule(6)}{$this->akurasiRule($this->bloom->enabled() ? 7 : 6)}
Keluarkan HANYA pure JSON array tanpa teks lain dan tanpa markdown code fence:
{$this->jsonShape()}

Materi pembelajaran:
{$konteks}
PROMPT;

        $soal = [];
        $terakhirError = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $output = $this->ai->send($prompt, [], $system);
            } catch (\Throwable) {
                throw new RuntimeException('Layanan AI tidak dapat dihubungi saat ini. Silakan coba lagi.');
            }

            try {
                $soal = $this->parseSoal($output, $jumlah, $kesulitan);
            } catch (\RuntimeException $e) {
                $terakhirError = $e;
                Log::warning('KuisGenerator: percobaan '.$percobaan.' gagal parsing JSON.', [
                    'error' => $e->getMessage(),
                    'raw' => mb_substr($output, 0, 300),
                ]);

                continue;
            }

            if (count($soal) > 0) {
                break;
            }

            Log::warning('KuisGenerator: percobaan '.$percobaan.' menghasilkan 0 soal.', [
                'diminta' => $jumlah,
                'raw' => mb_substr($output, 0, 300),
            ]);
        }

        if (count($soal) === 0) {
            throw $terakhirError ?? new RuntimeException('AI tidak menghasilkan soal yang valid. Silakan coba lagi.');
        }

        return $soal;
    }

     
    public function generateEssai(Pertemuan $pertemuan, int $jumlah, ?string $topik, array $sumber = []): array
    {
        $konteks = $this->buildMateriContext($pertemuan, $topik, $sumber);
        if ($konteks === '') {
            throw new RuntimeException('Pertemuan ini belum memiliki materi untuk dijadikan acuan soal.');
        }

        $fokusTopik = trim((string) $topik) !== ''
            ? "Fokus topik: {$topik}"
            : 'Fokus topik: seluruh materi yang diberikan';

        $system = 'Anda adalah asisten penyusun soal esai/uraian untuk guru. Anda menguasai Taksonomi Bloom Revisi. Anda hanya boleh membuat soal berdasarkan materi pembelajaran yang diberikan. Tuliskan semua rumus atau ekspresi matematika/ilmiah di dalam pertanyaan dan referensi jawaban memakai notasi LaTeX dengan pembatas $...$ untuk rumus inline dan $$...$$ untuk rumus blok.';

        $prompt = <<<PROMPT
Berdasarkan materi pembelajaran di bawah ini, buatkan {$jumlah} soal esai/uraian untuk tugas pelajar.

{$fokusTopik}
{$this->bloomSection(null, $jumlah)}
Aturan:
1. Setiap soal harus berdasarkan informasi yang benar-benar ada di materi, jangan mengarang konten yang tidak ada di materi.
2. Setiap soal dilengkapi "referensi" berisi kunci jawaban atau poin-poin penting (1-3 kalimat) untuk acuan penilaian.
3. Soal menuntut jawaban uraian (berpikir, menjelaskan, menghitung, atau membandingkan) — bukan pertanyaan yang bisa dijawab satu kata.
4. Setiap soal unik dan tidak mengulang pertanyaan yang sama.
{$this->bloomRule(5)}{$this->akurasiRule($this->bloom->enabled() ? 6 : 5)}
Keluarkan HANYA pure JSON array tanpa teks lain dan tanpa markdown code fence:
{$this->jsonShape(true)}

Materi pembelajaran:
{$konteks}
PROMPT;

        $soal = [];
        $terakhirError = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $output = $this->ai->send($prompt, [], $system);
            } catch (\Throwable) {
                throw new RuntimeException('Layanan AI tidak dapat dihubungi saat ini. Silakan coba lagi.');
            }

            try {
                $soal = $this->parseEssai($output, $jumlah);
            } catch (\RuntimeException $e) {
                $terakhirError = $e;
                Log::warning('KuisGenerator: percobaan '.$percobaan.' gagal parsing esai.', [
                    'error' => $e->getMessage(),
                    'raw' => mb_substr($output, 0, 300),
                ]);

                continue;
            }

            if (count($soal) > 0) {
                break;
            }

            Log::warning('KuisGenerator: percobaan '.$percobaan.' menghasilkan 0 esai.', [
                'diminta' => $jumlah,
                'raw' => mb_substr($output, 0, 300),
            ]);
        }

        if (count($soal) === 0) {
            throw $terakhirError ?? new RuntimeException('AI tidak menghasilkan soal yang valid. Silakan coba lagi.');
        }

        return $soal;
    }

     
    protected function parseEssai(string $output, int $max): array
    {
        $json = AiJsonParser::arrayFrom($output);

        if ($json === null) {
            Log::warning('KuisGenerator: respons esai bukan JSON yang bisa diparsing.', [
                'raw' => mb_substr($output, 0, 500),
            ]);

            throw new RuntimeException('AI tidak mengembalikan format JSON yang valid. Silakan coba lagi.');
        }

        $normalized = [];
        foreach ($json as $item) {
            if (count($normalized) >= $max) {
                break;
            }

            if (! is_array($item)) {
                continue;
            }

            $pertanyaan = trim((string) ($item['pertanyaan'] ?? ''));
            if ($pertanyaan === '') {
                continue;
            }

            $soal = [
                'pertanyaan' => $pertanyaan,
                'referensi' => trim((string) ($item['referensi'] ?? '')),
            ];

            $soal = $this->sertakanAkurasi($soal, $item);

            if ($this->bloom->enabled()) {
                $soal['bloom'] = $this->bloom->clampToDefaultRange(
                    $this->bloom->decide($pertanyaan, (string) ($item['bloom'] ?? ''), null)
                );
            }

            $normalized[] = $soal;
        }

        return $normalized;
    }

    protected function sertakanAkurasi(array $soal, array $item): array
    {
        if (isset($item['akurasi_materi']) && is_numeric($item['akurasi_materi'])) {
            $soal['akurasi_materi'] = max(0, min(100, (int) $item['akurasi_materi']));
        }

        if (isset($item['catatan_akurasi']) && trim((string) $item['catatan_akurasi']) !== '') {
            $soal['catatan_akurasi'] = trim((string) $item['catatan_akurasi']);
        }

        return $soal;
    }

     
    public function sumberKonteks(int $guruId, ?int $kelasId = null): array
    {
        $kelas = Kelas::query()
            ->where('guru_id', $guruId)
            ->when($kelasId !== null, fn ($q) => $q->where('id', $kelasId))
            ->get(['id', 'nama_kelas'])
            ->keyBy('id');

        if ($kelas->isEmpty()) {
            return [];
        }


        $pertemuans = Pertemuan::query()
            ->whereIn('kelas_id', $kelas->keys()->all())
            ->with(['materi' => fn ($q) => $q->orderBy('id')])
            ->orderBy('kelas_id')
            ->orderBy('urutan')
            ->get(['id', 'kelas_id', 'nama_pertemuan', 'urutan']);

        return $pertemuans->map(function (Pertemuan $pertemuan) use ($kelas) {
            return [
                'id' => $pertemuan->id,
                'kelas_id' => $pertemuan->kelas_id,
                'kelas_nama' => $kelas->get($pertemuan->kelas_id)?->nama_kelas ?? '-',
                'nama' => $pertemuan->nama_pertemuan,
                'urutan' => $pertemuan->urutan,
                'materi' => $pertemuan->materi->map(fn (Materi $m) => [
                    'id' => $m->id,
                    'judul' => $m->judul,
                ])->values()->all(),
            ];
        })->values()->all();
    }

     
    protected function buildMateriContext(Pertemuan $pertemuan, ?string $topik = null, array $sumber = []): string
    {
        [$pertemuans, $materi] = $this->resolveSumber($pertemuan, $sumber);

        $pertemuan->loadMissing('kelas');
        $materi->loadMissing(['babSummary', 'document.chunks']);

        $kategori = $pertemuan->kelas?->kategori;
        $queryRag = $this->queryRag($topik, $materi);



        $maxCharsRag = (int) config('services.kuis_rag.max_chars', 8000);
        $kuotaPerPertemuan = max(1500, intdiv($maxCharsRag, max(1, $pertemuans->count())));

        $bagianRag = [];
        foreach ($pertemuans as $sumberPertemuan) {
            $sumberPertemuan->loadMissing(['materi.babSummary', 'materi.document.chunks']);
            $kutipan = $this->buildRagContext($sumberPertemuan, $topik, $queryRag, $kuotaPerPertemuan);

            if ($kutipan !== '') {
                $bagianRag[] = $kutipan;
            }
        }

        $konteksRag = implode("\n\n", $bagianRag);
        $adaRag = $konteksRag !== '';

        $bagian = [];
        foreach ($materi as $materiSumber) {
            $segmen = "Materi: {$materiSumber->judul}";

            if ($materiSumber->bab) {
                $segmen .= " (Bab: {$materiSumber->bab})";
            }
            if ($materiSumber->deskripsi) {
                $segmen .= "\nDeskripsi: {$materiSumber->deskripsi}";
            }


            if ($materiSumber->babSummary?->ringkasan) {
                $segmen .= "\nRingkasan guru: {$materiSumber->babSummary->ringkasan}";
            } elseif (! $adaRag) {
                $chunks = $materiSumber->document?->chunks?->whereNotNull('content')->take(6);
                if ($chunks && $chunks->isNotEmpty()) {
                    $segmen .= "\nIsi:\n".$chunks->pluck('content')->implode("\n");
                }
            }

            $bagian[] = $segmen;
        }

        $konteks = mb_substr(implode("\n\n---\n\n", $bagian), 0, 12000);

        if ($konteksRag !== '') {
            $konteks = $konteksRag."\n\n---\n\n".$konteks;
        }

        $konteksSimbol = $this->symbols->buildContext(
            $this->symbolQuery($materi, $topik, $konteksRag),
            $this->symbols->resolveDomains($kategori),
        );

        if ($konteksSimbol !== '') {
            $konteks .= "\n\n".$konteksSimbol;
        }

        return trim($konteks);
    }

     
    protected function resolveSumber(Pertemuan $pertemuan, array $sumber): array
    {
        $pertemuanIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($sumber['pertemuan_ids'] ?? []))
        )));

        if ($pertemuanIds === []) {
            $pertemuan->loadMissing(['kelas', 'materi.babSummary', 'materi.document.chunks']);

            return [
                collect([$pertemuan]),
                $pertemuan->materi->sortBy('id')->values(),
            ];
        }

        $pertemuans = Pertemuan::query()
            ->whereIn('id', $pertemuanIds)




            ->when($pertemuan->loadMissing('kelas')->kelas?->guru_id, fn ($q, $guruId) => $q->whereHas(
                'kelas',
                fn ($k) => $k->where('guru_id', $guruId)
            ))
            ->with('kelas')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();


        if ($pertemuans->isEmpty()) {
            $pertemuan->loadMissing(['kelas', 'materi.babSummary', 'materi.document.chunks']);

            return [
                collect([$pertemuan]),
                $pertemuan->materi->sortBy('id')->values(),
            ];
        }

        $materiIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($sumber['materi_ids'] ?? []))
        )));

        $semuaMateri = Materi::query()
            ->whereIn('pertemuan_id', $pertemuans->pluck('id')->all())
            ->orderBy('pertemuan_id')
            ->orderBy('id')
            ->get();

        $dicentang = $semuaMateri->whereIn('id', $materiIds)->groupBy('pertemuan_id');

        $terpakai = $semuaMateri->filter(function (Materi $m) use ($dicentang) {
            $dipilih = $dicentang->get($m->pertemuan_id);

            return $dipilih === null || $dipilih->isEmpty() || $dipilih->contains('id', $m->id);
        })->values();

        return [$pertemuans, $terpakai];
    }

     
    protected function queryRag(?string $topik, Collection $materi): string
    {
        $topik = trim((string) $topik);

        if ($topik !== '') {
            return $topik;
        }

        return mb_substr($materi
            ->map(fn (Materi $m) => trim($m->judul.' '.(string) $m->deskripsi))
            ->filter()
            ->implode(' '), 0, 600);
    }

     
    protected function buildRagContext(Pertemuan $pertemuan, ?string $topik, ?string $queryOverride = null, ?int $maxChars = null): string
    {
        if (! config('services.kuis_rag.enabled', true)) {
            return '';
        }

        $query = trim((string) $topik);

        if ($query === '') {
            $query = trim((string) $queryOverride);
        }

        if ($query === '') {
            $query = mb_substr($pertemuan->materi
                ->map(fn ($m) => trim($m->judul.' '.(string) $m->deskripsi))
                ->filter()
                ->implode(' '), 0, 600);
        }

        if ($query === '') {
            return '';
        }

        $kategori = $pertemuan->kelas?->kategori;
        $topK = (int) config('services.kuis_rag.top_k', 6);
        $minScore = (float) config('services.kuis_rag.min_score', 0.35);

        try {
            $chunks = $this->rag->retrieve($query, $topK, $minScore, $kategori, null, $pertemuan->id);




            if (empty($chunks) && $kategori !== null) {
                $chunks = $this->rag->retrieve($query, $topK, $minScore, null, null, $pertemuan->id);
            }
        } catch (\Throwable $e) {
            Log::warning('KuisGenerator: retrieval RAG gagal, pakai fallback.', [
                'msg' => $e->getMessage(),
            ]);

            return '';
        }

        if (empty($chunks)) {
            return '';
        }

        $maxChars = $maxChars ?? (int) config('services.kuis_rag.max_chars', 8000);
        $isi = "Kutipan dari dokumen pembelajaran (diambil otomatis berdasarkan topik):\n";
        $terpakai = 0;

        foreach ($chunks as $i => $chunk) {
            if ($terpakai >= $maxChars) {
                break;
            }

            $content = trim((string) ($chunk['content'] ?? ''));

            if ($content === '') {
                continue;
            }

            $label = trim((string) ($chunk['materi_judul'] ?? $chunk['source'] ?? 'Dokumen'));
            $halaman = $chunk['page_start'] ?? null;

            if ($halaman !== null) {
                $label .= $chunk['page_end'] !== null && $chunk['page_end'] !== $halaman
                    ? " hlm. {$halaman}-{$chunk['page_end']}"
                    : " hlm. {$halaman}";
            }

            $sisa = $maxChars - $terpakai - mb_strlen($label) - 6;

            if ($sisa <= 20) {
                break;
            }



            if (mb_strlen($content) > $sisa) {
                $content = rtrim(mb_substr($content, 0, $sisa - 1)).'…';
            }

            $isi .= '['.($i + 1)."] {$label}\n{$content}\n\n";
            $terpakai += mb_strlen($content) + mb_strlen($label) + 6;
        }

        return $terpakai > 0 ? $isi : '';
    }

     
    protected function symbolQuery(Collection $materi, ?string $topik, string $konteksRag = ''): string
    {
        $topik = trim((string) $topik);

        if ($topik !== '') {
            return $topik;
        }

        $dariMateri = $materi
            ->map(fn (Materi $m) => trim($m->judul.' '.(string) $m->deskripsi))
            ->filter()
            ->implode(' ');

        $dariRag = trim(preg_replace('/^\[\d+\].*$/m', '', $konteksRag) ?? '');
        $dariRag = trim(preg_replace('/\s+/', ' ', $dariRag) ?? '');

        $gabung = trim($dariMateri.' '.$dariRag);

        return mb_substr($gabung, 0, 800);
    }

     
    protected function parseSoal(string $output, int $max, ?string $kesulitan = null): array
    {
        $json = AiJsonParser::arrayFrom($output);

        if ($json === null) {
            Log::warning('KuisGenerator: respons soal bukan JSON yang bisa diparsing.', [
                'panjang' => mb_strlen($output),



                'awal' => mb_substr($output, 0, 500),
                'ekor' => mb_substr($output, -500),
            ]);

            throw new RuntimeException('AI tidak mengembalikan format JSON yang valid. Silakan coba lagi.');
        }

        return $this->normalizeSoal($json, $max, $kesulitan);
    }

     
    protected function normalizeSoal(array $items, int $max, ?string $kesulitan = null): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (count($normalized) >= $max) {
                break;
            }

            if (! is_array($item)) {
                continue;
            }

            $pertanyaan = trim((string) ($item['pertanyaan'] ?? ''));
            $pilihan = [];

            foreach (($item['pilihan'] ?? []) as $pil) {
                $pil = trim((string) $pil);
                if ($pil !== '') {
                    $pilihan[] = $pil;
                }
            }

            if (count($pilihan) < 2 && isset($item['opsi']) && is_array($item['opsi'])) {
                $pilihan = array_values(array_filter(
                    array_map('trim', array_map('strval', $item['opsi'])),
                    fn ($pil) => $pil !== '',
                ));
            }

            $pilihan = array_slice($pilihan, 0, 4);
            while (count($pilihan) < 4) {
                $pilihan[] = '';
            }

            $jawabanRaw = $item['jawaban'] ?? 0;
            if (is_string($jawabanRaw)) {
                $jawabanStr = strtoupper(trim($jawabanRaw));
                $map = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
                if (array_key_exists($jawabanStr, $map)) {
                    $jawaban = $map[$jawabanStr];
                } elseif (ctype_digit($jawabanStr)) {
                    $jawaban = (int) $jawabanStr;
                } else {
                    $jawaban = 0;
                }
            } elseif (is_numeric($jawabanRaw)) {
                $jawaban = (int) $jawabanRaw;
            } else {
                $jawaban = 0;
            }
            if ($jawaban < 0 || $jawaban > 3) {
                $jawaban = 0;
            }

            if ($pertanyaan === '' || count(array_filter($pilihan, fn ($pil) => $pil !== '')) < 2) {
                continue;
            }

            $soal = [
                'pertanyaan' => $pertanyaan,
                'pilihan' => $pilihan,
                'jawaban' => $jawaban,
            ];

            if ($this->bloom->enabled()) {
                $soal['bloom'] = $this->bloom->clampToRange(
                    $this->bloom->decide($pertanyaan, (string) ($item['bloom'] ?? ''), $kesulitan),
                    $kesulitan
                );
            }

            if (isset($item['akurasi_materi']) || isset($item['catatan_akurasi'])) {
                $soal = $this->sertakanAkurasi($soal, $item);
            }

            $normalized[] = $soal;
        }

        return $normalized;
    }

     
    protected function bloomSection(?string $kesulitan, int $jumlah): string
    {
        if (! $this->bloom->enabled()) {
            return '';
        }

        return $this->bloomDifficulty($kesulitan, $jumlah)
            ."\n\nRubrik Taksonomi Bloom (level C1-C6):\n".$this->bloom->rubric('generate')
            ."\n\nContoh klasifikasi:\n".$this->bloom->classificationExamples(3)
            ."\n";
    }

     
    protected function bloomRule(int $nomor = 6): string
    {
        if (! $this->bloom->enabled()) {
            return '';
        }

        return "{$nomor}. Tentukan level bloom dari tuntutan kognitif utama soal, BUKAN dari tingkat kesulitan dan BUKAN hanya dari kata kerjanya. Jangan menaikkan level hanya agar soal terasa lebih sulit.\n";
    }

    protected function akurasiRule(int $nomor): string
    {
        return "{$nomor}. Setiap soal yang dihasilkan wajib dinilai keakurasiannya terhadap materi: tambahkan field \"akurasi_materi\" (integer 0-100, makin tinggi makin sesuai materi) dan \"catatan_akurasi\" (teks singkat, maks 25 kata) yang menjelaskan dasar nilainya.\n";
    }

     
    protected function jsonShape(bool $essai = false): string
    {
        if (! $this->bloom->enabled()) {
            return $essai
                ? '[{"pertanyaan":"...","referensi":"...","akurasi_materi":95,"catatan_akurasi":"..."}]'
                : '[{"pertanyaan":"...","pilihan":["...","...","...","..."],"jawaban":0,"akurasi_materi":95,"catatan_akurasi":"..."}]';
        }

        return $essai
            ? '[{"pertanyaan":"...","referensi":"...","bloom":"C3","akurasi_materi":95,"catatan_akurasi":"..."}]'
            : '[{"pertanyaan":"...","pilihan":["...","...","...","..."],"jawaban":0,"bloom":"C2","akurasi_materi":95,"catatan_akurasi":"..."}]';
    }

     
    protected function bloomDifficulty(?string $kesulitan, int $jumlah = 4): string
    {
        return $this->bloom->difficultyGuidance($kesulitan, $jumlah);
    }
}

