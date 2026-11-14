<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class KuisGeneratorService
{
    protected AIProviderService $ai;

    protected SymbolService $symbols;

    protected BloomTaxonomyService $bloom;

    private const SAMPUL_KUTIPAN = "Kutipan dari dokumen pembelajaran (seluruh isi materi terpilih):\n";

    /** Jumlah potongan yang ada versus yang benar-benar masuk ke prompt. */
    protected int $potonganTotal = 0;

    protected int $potonganTermuat = 0;

    public function __construct()
    {
        $this->ai = app(AIProviderService::class);
        $this->symbols = app(SymbolService::class);
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
{$this->bloomRule(6)}
Keluarkan HANYA pure JSON array tanpa teks lain dan tanpa markdown code fence:
{$this->jsonShape()}

Materi pembelajaran:
{$konteks}
PROMPT;

        $soal = [];
        $terakhirError = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $output = $this->ai->sendLocalPreferred($prompt, [], $system);
            } catch (\Throwable) {
                throw new RuntimeException('Layanan AI tidak dapat dihubungi saat ini. Silakan coba lagi.');
            }

            try {
                $soal = $this->parseSoal($output, $jumlah, $kesulitan);
            } catch (RuntimeException $e) {
                $terakhirError = $e;
                Log::warning('KuisGenerator: percobaan '.$percobaan.' gagal parsing JSON.', [
                    'error' => $e->getMessage(),
                    'raw' => mb_substr($output, 0, 300),
                ]);

                continue;
            }

            if (count($soal) === $jumlah) {
                break;
            }

            Log::warning('KuisGenerator: jumlah soal valid tidak sesuai.', [
                'diminta' => $jumlah,
                'valid' => count($soal),
                'raw' => mb_substr($output, 0, 300),
            ]);

            $terakhirError = new RuntimeException('AI hanya menghasilkan '.count($soal).' dari '.$jumlah.' soal yang diminta.');
        }

        if (count($soal) !== $jumlah) {
            throw $terakhirError ?? new RuntimeException('AI tidak menghasilkan soal yang valid. Silakan coba lagi.');
        }

        return $this->hitungKemiripan($soal, $konteks);
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
{$this->bloomRule(5)}
Keluarkan HANYA pure JSON array tanpa teks lain dan tanpa markdown code fence:
{$this->jsonShape(true)}

Materi pembelajaran:
{$konteks}
PROMPT;

        $soal = [];
        $terakhirError = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            try {
                $output = $this->ai->sendLocalPreferred($prompt, [], $system);
            } catch (\Throwable) {
                throw new RuntimeException('Layanan AI tidak dapat dihubungi saat ini. Silakan coba lagi.');
            }

            try {
                $soal = $this->parseEssai($output, $jumlah);
            } catch (RuntimeException $e) {
                $terakhirError = $e;
                Log::warning('KuisGenerator: percobaan '.$percobaan.' gagal parsing esai.', [
                    'error' => $e->getMessage(),
                    'raw' => mb_substr($output, 0, 300),
                ]);

                continue;
            }

            if (count($soal) === $jumlah) {
                break;
            }

            Log::warning('KuisGenerator: jumlah soal esai valid tidak sesuai.', [
                'diminta' => $jumlah,
                'valid' => count($soal),
                'raw' => mb_substr($output, 0, 300),
            ]);

            $terakhirError = new RuntimeException('AI hanya menghasilkan '.count($soal).' dari '.$jumlah.' soal yang diminta.');
        }

        if (count($soal) !== $jumlah) {
            throw $terakhirError ?? new RuntimeException('AI tidak menghasilkan soal yang valid. Silakan coba lagi.');
        }

        return $this->hitungKemiripan($soal, $konteks);
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

            $pertanyaan = is_string($item['pertanyaan'] ?? null) ? trim($item['pertanyaan']) : '';
            if ($pertanyaan === '') {
                continue;
            }

            $soal = [
                'pertanyaan' => $pertanyaan,
                'referensi' => is_string($item['referensi'] ?? null) ? trim($item['referensi']) : '',
            ];

            if ($this->bloom->enabled()) {
                $soal['bloom'] = $this->bloom->clampToDefaultRange(
                    $this->bloom->decide($pertanyaan, (string) ($item['bloom'] ?? ''), null)
                );
            }

            $normalized[] = $soal;
        }

        return $normalized;
    }

    protected function hitungKemiripan(array $soal, string $konteks): array
    {
        foreach ($soal as &$item) {
            $item['kemiripan_materi'] = null;
            $item['kemiripan_pertanyaan'] = $item['pertanyaan'];
        }
        unset($item);

        try {
            $bagian = mb_str_split($konteks, 1200);
            $embedding = app(EmbeddingService::class);
            $vectors = $embedding->embedBatch([...$bagian, ...array_column($soal, 'pertanyaan')]);
            if (count($vectors) !== count($bagian) + count($soal)) {
                throw new RuntimeException('Respons embedding tidak lengkap.');
            }

            foreach ($vectors as $vector) {
                if (! is_array($vector) || $vector === [] || ! array_filter($vector, fn ($v) => is_numeric($v) && is_finite((float) $v) && (float) $v !== 0.0)) {
                    throw new RuntimeException('Vektor embedding kosong atau bernilai nol.');
                }
                foreach ($vector as $value) {
                    if (! is_numeric($value) || ! is_finite((float) $value)) {
                        throw new RuntimeException('Vektor embedding tidak valid.');
                    }
                }
            }

            $hasil = [];
            foreach ($soal as $i => $item) {
                $skor = 0.0;
                foreach (array_slice($vectors, 0, count($bagian)) as $vector) {
                    $skor += $embedding->cosineSimilarity($vector, $vectors[count($bagian) + $i]);
                }
                $nilai = $skor / count($bagian);
                if (! is_finite($nilai) || $nilai < -1.000001 || $nilai > 1.000001) {
                    throw new RuntimeException('Hasil kemiripan embedding tidak valid.');
                }
                $hasil[] = max(-1.0, min(1.0, $nilai));
            }
            foreach ($hasil as $i => $nilai) {
                $soal[$i]['kemiripan_materi'] = $nilai;
            }
        } catch (\Throwable $e) {
            Log::warning('KuisGenerator: kemiripan materi tidak tersedia.', ['error' => $e->getMessage()]);
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

        // Guru perlu tahu materi mana yang benar-benar punya isi untuk dicentang:
        // tanpa dokumen terindeks, tidak ada yang bisa dikutip dari materi itu.
        $berchunk = Materi::query()
            ->whereIn('id', $pertemuans->flatMap(fn (Pertemuan $p) => $p->materi->pluck('id'))->unique()->all())
            ->whereHas('document', fn ($q) => $q->where('status', 'completed')->has('chunks'))
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();

        return $pertemuans->map(function (Pertemuan $pertemuan) use ($kelas, $berchunk) {
            return [
                'id' => $pertemuan->id,
                'kelas_id' => $pertemuan->kelas_id,
                'kelas_nama' => $kelas->get($pertemuan->kelas_id)?->nama_kelas ?? '-',
                'nama' => $pertemuan->nama_pertemuan,
                'urutan' => $pertemuan->urutan,
                'materi' => $pertemuan->materi->map(fn (Materi $m) => [
                    'id' => $m->id,
                    'judul' => $m->judul,
                    'punya_chunk' => isset($berchunk[(int) $m->id]),
                ])->values()->all(),
            ];
        })->values()->all();
    }

    protected function buildMateriContext(Pertemuan $pertemuan, ?string $topik = null, array $sumber = []): string
    {
        $materi = $this->resolveSumber($pertemuan, $sumber);

        $pertemuan->loadMissing('kelas');
        $materi->loadMissing('babSummary');

        $kategori = $pertemuan->kelas?->kategori;
        $kutipan = $this->kutipanMateri($materi);

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
            }

            $bagian[] = $segmen;
        }

        $konteks = mb_substr(implode("\n\n---\n\n", $bagian), 0, 12000);

        if ($kutipan !== '') {
            $konteks = $kutipan."\n\n---\n\n".$konteks;
        }

        $konteksSimbol = $this->symbols->buildContext(
            $this->symbolQuery($materi, $topik, $kutipan),
            $this->symbols->resolveDomains($kategori),
        );

        if ($konteksSimbol !== '') {
            $konteks .= "\n\n".$konteksSimbol;
        }

        return trim($konteks);
    }

    /**
     * Seluruh potongan dokumen milik materi terpilih, dikutip berurutan
     * document + chunk_index tanpa similaritas, supaya generate ulang menyusun
     * acuan yang sama persis.
     *
     * @param  Collection<int, Materi>  $materi
     */
    protected function kutipanMateri(Collection $materi): string
    {
        $this->potonganTotal = 0;
        $this->potonganTermuat = 0;

        if (! config('services.soal_konteks.enabled', true)) {
            return '';
        }

        $perPertemuan = $this->potonganPerPertemuan($materi);
        $this->potonganTotal = array_sum(array_map('count', $perPertemuan));

        if ($perPertemuan === []) {
            return '';
        }

        $anggaran = (int) config('services.soal_konteks.max_chars', 24000);
        $ukuran = [];

        foreach ($perPertemuan as $pertemuanId => $potongan) {
            $ukuran[$pertemuanId] = array_sum(array_map(
                fn (array $p): int => mb_strlen($p['content']) + mb_strlen($p['materi_judul']) + 6,
                $potongan
            ));
        }

        $muatSemua = array_sum($ukuran) <= $anggaran;
        $totalUkuran = max(1, array_sum($ukuran));
        $bagian = [];

        foreach ($perPertemuan as $pertemuanId => $potongan) {
            // Kuota dibagi proporsional supaya satu pertemuan besar tidak
            // menghabiskan jatah pertemuan lain yang ikut dicentang.
            $kuota = $muatSemua ? $anggaran : (int) floor($anggaran * $ukuran[$pertemuanId] / $totalUkuran);
            $kutipan = $this->formatKutipan($potongan, $kuota, self::SAMPUL_KUTIPAN);

            if ($kutipan !== '') {
                $bagian[] = $kutipan;
            }
        }

        return implode("\n\n", $bagian);
    }

    /**
     * Potongan yang ada isinya, dikelompokkan per pertemuan dan dinomori
     * global supaya nomor kutipan tidak bergantung pada kelompoknya.
     *
     * @param  Collection<int, Materi>  $materi
     * @return array<int, array<int, array<string, mixed>>>
     */
    protected function potonganPerPertemuan(Collection $materi): array
    {
        $baris = DB::table('document_chunks')
            ->join('documents', 'documents.id', '=', 'document_chunks.document_id')
            ->join('materi', 'materi.id', '=', 'documents.materi_id')
            ->whereIn('documents.materi_id', $materi->pluck('id')->all())
            ->where('documents.status', 'completed')
            ->whereNotNull('document_chunks.content')
            ->orderBy('document_chunks.document_id')
            ->orderBy('document_chunks.chunk_index')
            ->get([
                'document_chunks.content',
                'document_chunks.page_start',
                'document_chunks.page_end',
                'documents.original_name',
                'materi.judul',
                'materi.pertemuan_id',
            ]);

        $hasil = [];
        $nomor = 0;

        foreach ($baris as $chunk) {
            $isi = RumusPenanda::manusiakan((string) $chunk->content);

            if (trim($isi) === '') {
                continue;
            }

            $hasil[(int) $chunk->pertemuan_id][$nomor++] = [
                'content' => $isi,
                'materi_judul' => (string) $chunk->judul,
                'source' => (string) $chunk->original_name,
                'page_start' => $chunk->page_start,
                'page_end' => $chunk->page_end,
            ];
        }

        return $hasil;
    }

    /**
     * Materi yang jadi acuan soal: pilihan guru kalau ada, kalau tidak seluruh
     * materi pertemuan yang sedang dibuka.
     *
     * @param  array<string, mixed>  $sumber
     * @return EloquentCollection<int, Materi>
     */
    protected function resolveSumber(Pertemuan $pertemuan, array $sumber): EloquentCollection
    {
        $pertemuanIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($sumber['pertemuan_ids'] ?? []))
        )));

        if ($pertemuanIds === []) {
            return $this->materiPertemuanSaatIni($pertemuan);
        }

        $pertemuans = Pertemuan::query()
            ->whereIn('id', $pertemuanIds)
            ->when($pertemuan->loadMissing('kelas')->kelas?->guru_id, fn ($q, $guruId) => $q->whereHas(
                'kelas',
                fn ($k) => $k->where('guru_id', $guruId)
            ))
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        if ($pertemuans->isEmpty()) {
            return $this->materiPertemuanSaatIni($pertemuan);
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

        return $terpakai;
    }

    /** Acuan cadangan: seluruh materi pada pertemuan yang sedang dibuka. */
    protected function materiPertemuanSaatIni(Pertemuan $pertemuan): EloquentCollection
    {
        return Materi::query()
            ->where('pertemuan_id', $pertemuan->id)
            ->orderBy('id')
            ->with('babSummary')
            ->get();
    }

    /**
     * Susun kutipan bernomor sampai kuota karakter habis, dan catat berapa
     * potongan yang sempat masuk supaya pemotongan bisa dilaporkan.
     *
     * @param  array<int, array<string, mixed>>  $chunks
     */
    protected function formatKutipan(array $chunks, int $maxChars, string $sampul): string
    {
        $isi = $sampul;
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

            $isi .= '['.((int) $i + 1)."] {$label}\n{$content}\n\n";
            $terpakai += mb_strlen($content) + mb_strlen($label) + 6;
            $this->potonganTermuat++;
        }

        return $terpakai > 0 ? $isi : '';
    }

    /**
     * Isi materi hampir selalu lebih panjang dari anggaran karakter, jadi guru
     * perlu tahu bahwa tidak seluruhnya sempat masuk ke prompt.
     */
    public function peringatanKonteks(): ?string
    {
        if ($this->potonganTermuat === 0 || $this->potonganTermuat >= $this->potonganTotal) {
            return null;
        }

        $anggaran = (int) config('services.soal_konteks.max_chars', 24000);

        return number_format($this->potonganTermuat, 0, ',', '.').' dari '
            .number_format($this->potonganTotal, 0, ',', '.').' potongan dokumen ikut dipakai; sisanya tidak muat '
            .'dalam anggaran konteks '.number_format($anggaran, 0, ',', '.').' karakter.';
    }

    protected function symbolQuery(Collection $materi, ?string $topik, string $kutipan = ''): string
    {
        $topik = trim((string) $topik);

        if ($topik !== '') {
            return $topik;
        }

        $dariMateri = $materi
            ->map(fn (Materi $m) => trim($m->judul.' '.(string) $m->deskripsi))
            ->filter()
            ->implode(' ');

        $dariKutipan = trim(preg_replace('/^\[\d+\].*$/m', '', $kutipan) ?? '');
        $dariKutipan = trim(preg_replace('/\s+/', ' ', $dariKutipan) ?? '');

        $gabung = trim($dariMateri.' '.$dariKutipan);

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

            $pertanyaan = is_string($item['pertanyaan'] ?? null) ? trim($item['pertanyaan']) : '';
            $pilihanRaw = $item['pilihan'] ?? $item['opsi'] ?? null;
            if (! is_array($pilihanRaw) || count($pilihanRaw) !== 4) {
                continue;
            }

            $pilihan = [];
            foreach ($pilihanRaw as $pil) {
                if (! is_string($pil) || trim($pil) === '') {
                    continue 2;
                }
                $pilihan[] = trim($pil);
            }

            $jawabanRaw = $item['jawaban'] ?? null;
            if (is_string($jawabanRaw)) {
                $jawabanStr = strtoupper(trim($jawabanRaw));
                $map = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
                if (array_key_exists($jawabanStr, $map)) {
                    $jawaban = $map[$jawabanStr];
                } elseif (ctype_digit($jawabanStr)) {
                    $jawaban = (int) $jawabanStr;
                } else {
                    continue;
                }
            } elseif (is_int($jawabanRaw)) {
                $jawaban = (int) $jawabanRaw;
            } else {
                continue;
            }
            if ($jawaban < 0 || $jawaban > 3) {
                continue;
            }

            if ($pertanyaan === '') {
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

    protected function jsonShape(bool $essai = false): string
    {
        if (! $this->bloom->enabled()) {
            return $essai
                ? '[{"pertanyaan":"...","referensi":"..."}]'
                : '[{"pertanyaan":"...","pilihan":["...","...","...","..."],"jawaban":0}]';
        }

        return $essai
            ? '[{"pertanyaan":"...","referensi":"...","bloom":"C3"}]'
            : '[{"pertanyaan":"...","pilihan":["...","...","...","..."],"jawaban":0,"bloom":"C2"}]';
    }

    protected function bloomDifficulty(?string $kesulitan, int $jumlah = 4): string
    {
        return $this->bloom->difficultyGuidance($kesulitan, $jumlah);
    }
}
