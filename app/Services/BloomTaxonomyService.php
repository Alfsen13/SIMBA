<?php

namespace App\Services;

use RuntimeException;

class BloomTaxonomyService
{
    protected ?array $cache = null;

    protected ?array $signalMap = null;

    protected array $levelsValid = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];

    public const DEFAULT_RANGE = ['C2', 'C3', 'C4'];

    public function data(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $path = database_path('data/bloom.json');

        if (! is_file($path)) {
            throw new RuntimeException('Dataset Taksonomi Bloom tidak ditemukan di database/data/bloom.json.');
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Dataset Taksonomi Bloom di database/data/bloom.json bukan JSON yang valid.');
        }

        return $this->cache = $decoded;
    }

    public function levels(): array
    {
        $levels = $this->data()['levels'] ?? [];

        return array_values(array_map(function (array $level): array {
            $level['label'] = $level['name_id'] ?? ($level['name'] ?? ($level['code'] ?? ''));
            $level['short'] = $level['name_id'] ?? ($level['name'] ?? ($level['code'] ?? ''));

            return $level;
        }, $levels));
    }

    public function metadata(): array
    {
        $data = $this->data();
        $meta = $data['metadata'] ?? [];

        return [
            'name' => $meta['dataset_name'] ?? 'Bloom Taxonomy Knowledge Base',
            'version' => $meta['version'] ?? '',
            'language' => $meta['language'] ?? '',
            'framework' => $meta['framework'] ?? '',
            'levels' => count($data['levels'] ?? []),
            'examples' => count($data['question_classification_examples'] ?? []),
        ];
    }

    public function enabled(): bool
    {
        return (bool) config('services.bloom.enabled', true);
    }

    protected function level(string $kode): ?array
    {
        $kode = strtoupper(trim($kode));

        foreach ($this->levels() as $level) {
            if (($level['code'] ?? null) === $kode) {
                return $level;
            }
        }

        return null;
    }

    public function has(string $kode): bool
    {
        return in_array(strtoupper(trim($kode)), $this->levelsValid, true);
    }

    public function valid(string $kode, string $fallback = 'C2'): string
    {
        $kode = strtoupper(trim($kode));

        return $this->has($kode) ? $kode : strtoupper(trim($fallback));
    }

    public function label(string $kode): string
    {
        $level = $this->level($kode);

        return $level['name_id'] ?? ($level['name'] ?? $this->valid($kode));
    }

    public function shortLabel(string $kode): string
    {
        $level = $this->level($kode);

        return $level['name_id'] ?? ($level['name'] ?? $this->valid($kode));
    }

    public function order(string $kode): int
    {
        return (int) ($this->level($kode)['order'] ?? (int) substr($this->valid($kode), 1));
    }

    public function allowedRange(?string $kesulitan): array
    {
        return match (strtolower(trim((string) $kesulitan))) {
            'mudah' => ['C1', 'C2'],
            'sedang' => ['C2', 'C3', 'C4'],
            'sulit' => ['C3', 'C4', 'C5'],
            default => self::DEFAULT_RANGE,
        };
    }

    public function distribute(int $jumlah, ?string $kesulitan = null): array
    {
        $jumlah = max(0, $jumlah);
        $range = $this->allowedRange($kesulitan);

        if ($jumlah === 0 || $range === []) {
            return [];
        }

        $count = count($range);
        $base = intdiv($jumlah, $count);
        $rem = $jumlah % $count;

        $hasil = [];

        foreach ($range as $i => $kode) {
            $n = $base + ($i < $rem ? 1 : 0);

            if ($n > 0) {
                $hasil[$kode] = $n;
            }
        }

        return $hasil;
    }

    public function difficultyProfile(?string $kesulitan, int $jumlah = 4): array
    {
        return $this->distribute($jumlah, $kesulitan);
    }

    public function difficultyGuidance(?string $kesulitan, int $jumlah = 4): string
    {
        $jumlah = max(1, $jumlah);
        $range = $this->allowedRange($kesulitan);
        $label = strtolower(trim((string) $kesulitan));
        $label = $label !== '' ? $label : 'default (esai)';
        $rentang = $range[0].'-'.end($range);

        $distribusi = $this->distribute($jumlah, $kesulitan);
        $bagian = [];
        foreach ($distribusi as $kode => $n) {
            $bagian[] = "{$kode}={$n}";
        }

        $teks = "Target level Bloom untuk tingkat kesulitan {$label} (rentang {$rentang}).";
        $teks .= "\nContoh sebaran untuk {$jumlah} soal: ".implode(', ', $bagian).'.';
        $teks .= "\nJaga agar seluruh soal tetap berada di dalam rentang {$rentang}.";

        return $teks;
    }

    public function clampToRange(string $kode, ?string $kesulitan): string
    {
        $kode = $this->valid($kode);

        $kesulitan = strtolower(trim((string) $kesulitan));
        if (! in_array($kesulitan, ['mudah', 'sedang', 'sulit'], true)) {
            return $kode;
        }

        return $this->clampToSpan($kode, ...$this->spanOf($this->allowedRange($kesulitan)));
    }

    public function clampToDefaultRange(string $kode): string
    {
        return $this->clampToSpan($kode, self::DEFAULT_RANGE[0], self::DEFAULT_RANGE[count(self::DEFAULT_RANGE) - 1]);
    }

    public function clampToSpan(string $kode, string $min, string $max): string
    {
        $kode = $this->valid($kode);
        $n = $this->order($kode);
        $bawah = $this->order($min);
        $atas = $this->order($max);

        if ($bawah > $atas) {
            [$bawah, $atas] = [$atas, $bawah];
        }

        if ($n < $bawah) {
            return $this->valid($min);
        }

        if ($n > $atas) {
            return $this->valid($max);
        }

        return $kode;
    }

    protected function spanOf(array $range): array
    {
        return [$range[0], $range[count($range) - 1]];
    }

    public function rubric(string $konteks = 'generate'): string
    {
        $konteks = strtolower(trim($konteks)) === 'penilaian' ? 'penilaian' : 'generate';
        $judul = $konteks === 'penilaian'
            ? 'Rubrik penilaian Taksonomi Bloom (C1-C6):'
            : 'Rubrik penyusunan soal Taksonomi Bloom (C1-C6):';

        $baris = [];
        foreach ($this->levels() as $level) {
            $verbs = implode(', ', array_slice($level['operational_verbs'] ?? [], 0, 8));
            $pola = implode('; ', array_slice($level['question_patterns'] ?? [], 0, 4));
            $fokus = implode(', ', $level['assessment_focus'] ?? []);

            $baris[] = "{$level['code']} - {$level['name_id']}: ".($level['definition'] ?? '');
            $baris[] = "  Kata kerja: {$verbs}";
            $baris[] = "  Pola pertanyaan: {$pola}";
            $baris[] = "  Fokus penilaian: {$fokus}";
            $baris[] = '  Catatan: '.($level['common_misunderstanding'] ?? '');
        }

        $teks = $judul."\n".implode("\n", $baris);
        $teks .= "\n\nPANDUAN KLASIFIKASI:\n".$this->classificationRulesText();

        return $teks;
    }

    protected function classificationRulesText(): string
    {
        $guidelines = $this->data()['classification_guidelines'] ?? [];
        $baris = [];

        if (! empty($guidelines['primary_rule'])) {
            $baris[] = '- '.$guidelines['primary_rule'];
        }

        foreach ($guidelines['rules'] ?? [] as $rule) {
            $baris[] = '- '.$rule;
        }

        foreach ($this->data()['verb_disambiguation'] ?? [] as $verb) {
            $baris[] = '- Kata kerja ambigu "'.($verb['verb'] ?? '').'" ('.implode(
                '/',
                $verb['possible_levels'] ?? []
            ).'): '.($verb['guidance'] ?? '');
        }

        return implode("\n", $baris);
    }

    public function classificationExamples(int $jumlah = 3): string
    {
        $contoh = array_slice($this->data()['question_classification_examples'] ?? [], 0, max(0, $jumlah));

        $lines = [];
        foreach ($contoh as $baris) {
            $lines[] = '['.($baris['bloom_code'] ?? '').'] '.($baris['question'] ?? '').' - '.($baris['reason'] ?? '');
        }

        return implode("\n", $lines);
    }

    public function verbDisambiguationText(): string
    {
        $lines = [];
        foreach ($this->data()['verb_disambiguation'] ?? [] as $verb) {
            $lines[] = ($verb['verb'] ?? '').' ('.implode('/', $verb['possible_levels'] ?? []).'): '.($verb['guidance'] ?? '');
        }

        return implode("\n", $lines);
    }

    protected function signalMap(): array
    {
        if ($this->signalMap !== null) {
            return $this->signalMap;
        }

        $map = [];

        foreach ($this->data()['levels'] ?? [] as $level) {
            $code = $level['code'] ?? null;
            if ($code === null) {
                continue;
            }

            foreach ($level['operational_verbs'] ?? [] as $verb) {
                foreach ($this->stemForms(mb_strtolower((string) $verb)) as $form) {
                    $map[$form] = ['code' => $code, 'score' => 1.0];
                }
            }

            foreach ($level['keywords'] ?? [] as $keyword) {
                foreach ($this->stemForms(mb_strtolower((string) $keyword)) as $form) {
                    if (! isset($map[$form])) {
                        $map[$form] = ['code' => $code, 'score' => 0.5];
                    }
                }
            }
        }

        return $this->signalMap = $map;
    }

    protected function stemForms(string $word): array
    {
        $word = mb_strtolower(trim($word));

        if ($word === '') {
            return [];
        }

        $found = [];
        $suffixes = ['annya', 'kan', 'lah', 'kah', 'nya', 'an', 'i'];
        $prefixes = ['meng', 'meny', 'menge', 'mem', 'men', 'me', 'di', 'ter', 'ber', 'pe', 'per'];

        $queue = [$word];
        $seen = [];

        while ($queue !== []) {
            $current = array_shift($queue);

            if (isset($seen[$current])) {
                continue;
            }
            $seen[$current] = true;

            if (mb_strlen($current) >= 3) {
                $found[$current] = true;
            }

            foreach ($suffixes as $suffix) {
                if (mb_strlen($current) > mb_strlen($suffix) + 2 && str_ends_with($current, $suffix)) {
                    $queue[] = mb_substr($current, 0, -mb_strlen($suffix));
                }
            }
        }

        foreach (array_keys($found) as $current) {
            foreach ($prefixes as $prefix) {
                if (mb_strlen($current) > mb_strlen($prefix) + 3 && str_starts_with($current, $prefix)) {
                    $root = mb_substr($current, mb_strlen($prefix));
                    if (mb_strlen($root) >= 4) {
                        $found[$root] = true;
                    }
                }
            }
        }

        return array_keys($found);
    }

    protected function tokenize(string $teks): array
    {
        return preg_split('/[^\p{L}]+/u', $teks, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    protected function detectPhrase(string $teks): ?string
    {
        foreach ($this->levels() as $level) {
            foreach ($level['question_patterns'] ?? [] as $pattern) {
                $pattern = mb_strtolower(trim((string) $pattern));
                if (! str_contains($pattern, '...')) {
                    continue;
                }

                $frasa = str_replace('...', ' ', $pattern);
                $frasa = preg_replace('/[^\p{L}\s]+/u', ' ', $frasa);
                $frasa = trim((string) preg_replace('/\s+/u', ' ', (string) $frasa));

                if ($frasa === '' || ! str_contains($frasa, ' ')) {
                    continue;
                }

                if (str_contains($teks, $frasa)) {
                    return $level['code'] ?? null;
                }
            }
        }

        return null;
    }

    protected function analyze(string $pertanyaan): ?array
    {
        $teks = mb_strtolower(trim($pertanyaan));

        if ($teks === '') {
            return null;
        }

        $frasa = $this->detectPhrase($teks);
        if ($frasa !== null) {
            return ['code' => $frasa, 'score' => 1.0];
        }

        $map = $this->signalMap();

        foreach ($this->tokenize($teks) as $token) {
            foreach ($this->stemForms($token) as $form) {
                if (isset($map[$form])) {
                    return $map[$form];
                }
            }
        }

        return null;
    }

    public function detect(string $pertanyaan): ?string
    {
        return $this->analyze($pertanyaan)['code'] ?? null;
    }

    public function confidence(string $pertanyaan): float
    {
        return (float) ($this->analyze($pertanyaan)['score'] ?? 0.0);
    }

    public function decide(string $pertanyaan, string $ai = '', ?string $kesulitan = null): string
    {
        $analisis = $this->analyze($pertanyaan);
        $minConfidence = (float) config('services.bloom.min_confidence', 0.34);

        if ($analisis !== null && $analisis['score'] >= $minConfidence) {
            $kode = $analisis['code'];
        } else {
            $kode = $this->valid($ai);
        }

        return $this->clampToRange($kode, $kesulitan);
    }

    public function lengkapiOtomatis(array $soal, ?string $kesulitan = null): array
    {
        if (! $this->enabled()) {
            return $soal;
        }

        foreach ($soal as $i => $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $mentah = strtoupper(trim((string) ($baris['bloom'] ?? '')));

            if ($this->has($mentah)) {
                $baris['bloom'] = $mentah;
            } else {
                $pertanyaan = (string) ($baris['pertanyaan'] ?? '');
                $kode = $this->decide($pertanyaan, '', null);

                $baris['bloom'] = $kesulitan !== null
                    ? $this->clampToRange($kode, $kesulitan)
                    : $this->clampToDefaultRange($kode);
            }

            $soal[$i] = $baris;
        }

        return $soal;
    }
}
