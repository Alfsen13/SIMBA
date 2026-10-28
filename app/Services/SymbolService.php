<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Symbol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

 
class SymbolService
{
    protected int $topK;

    protected float $minScore;

    protected int $maxChars;

     
    protected ?Collection $cache = null;

    public function __construct()
    {
        $this->topK = (int) config('services.symbols.top_k', 6);
        $this->minScore = (float) config('services.symbols.min_score', 1.0);
        $this->maxChars = (int) config('services.symbols.max_chars', 2400);
    }

    public function enabled(): bool
    {
        return (bool) config('services.symbols.enabled', true);
    }

     
    public function retrieve(string $query, string|array|null $domains = null, ?int $limit = null): Collection
    {
        if (! $this->enabled()) {
            return new Collection;
        }

        $terms = $this->tokenize($query);

        if ($terms === []) {
            return new Collection;
        }

        $limit ??= $this->topK;

        $wanted = $domains === null
            ? null
            : array_values(array_filter(array_map('mb_strtolower', (array) $domains)));

        $candidates = $wanted === null
            ? $this->all()
            : $this->all()->filter(fn (Symbol $s) => in_array(mb_strtolower((string) $s->domain), $wanted, true));

        if ($candidates->isEmpty()) {
            return new Collection;
        }

        $idf = $this->inverseDocumentFrequency($terms, $candidates);
        $scored = [];

        foreach ($candidates as $symbol) {
            $score = $this->score($symbol, $terms, $idf);

            if ($score >= $this->minScore) {
                $scored[] = ['symbol' => $symbol, 'score' => $score];
            }
        }

        if ($scored === []) {
            return new Collection;
        }

        usort($scored, fn ($a, $b) => [$b['score'], $a['symbol']->id] <=> [$a['score'], $b['symbol']->id]);

        return (new Collection($scored))
            ->take($limit)
            ->pluck('symbol')
            ->values();
    }

     
    protected function inverseDocumentFrequency(array $terms, Collection $candidates): array
    {
        $total = max($candidates->count(), 1);
        $idf = [];

        foreach ($terms as $term) {
            $documents = 0;

            foreach ($candidates as $symbol) {
                if (str_contains($this->searchableText($symbol), $term)) {
                    $documents++;
                }
            }

            $idf[$term] = log(1 + $total / (1 + $documents)) / log(1 + $total);
        }

        return $idf;
    }

     
    public function resolveDomains(?string $kategori): ?array
    {
        $kategori = mb_strtolower(trim((string) $kategori));

        if ($kategori === '') {
            return null;
        }

        $map = [
            'matemat' => ['matematika', 'kalkulus'],
            'kalkul' => ['kalkulus', 'matematika'],
            'fisika' => ['fisika'],
            'kimia' => ['kimia'],
            'biolog' => ['biologi'],
        ];

        foreach ($map as $needle => $domains) {
            if (str_contains($kategori, $needle)) {
                return $domains;
            }
        }

        return null;
    }

     
    public function buildContext(string $query, string|array|null $domains = null, ?int $limit = null, ?int $maxChars = null): string
    {
        $symbols = $this->retrieve($query, $domains, $limit);

        if ($symbols->isEmpty()) {
            return '';
        }

        $maxChars ??= $this->maxChars;
        $lines = [];
        $used = 0;

        foreach ($symbols as $symbol) {
            $line = $this->format($symbol);

            if ($used + mb_strlen($line) + 1 > $maxChars) {
                break;
            }

            $lines[] = $line;
            $used += mb_strlen($line) + 1;
        }

        if ($lines === []) {
            return '';
        }

        return "Referensi simbol dan rumus (pakai bila relevan, jangan mengarang di luar daftar ini):\n"
            .implode("\n", $lines);
    }

     
    public function annotateFormulaPages(array $pages, ?string $kategori = null): array
    {
        foreach ($pages as &$page) {
            if (isset($page['text']) && is_string($page['text'])) {
                $page['text'] = $this->annotateFormulaText($page['text'], $kategori);
            }
        }

        return $pages;
    }

     
    public function annotateFormulaText(string $text, ?string $kategori = null): string
    {
        if (! $this->enabled()) {
            return $text;
        }

        $lines = preg_split('/\r?\n/', $text) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $out[] = $line;

            if (str_starts_with(trim($line), 'FORMULA:')) {
                $annotations = $this->annotateFormulaLine($line, $kategori);

                foreach ($annotations as $annotation) {
                    $out[] = $annotation;
                }
            }
        }

        return implode("\n", $out);
    }

     
    protected function annotateFormulaLine(string $line, ?string $kategori = null): array
    {
        $formulas = preg_split('/\s*\|\s*FORMULA:\s*/', $line) ?: [];
        $annotations = [];

        foreach ($formulas as $formula) {
            $formula = trim((string) preg_replace('/^FORMULA:\s*/', '', $formula));

            if ($formula === '') {
                continue;
            }

            $symbol = $this->matchFormulaSymbol($formula, $kategori);

            if ($symbol === null) {
                continue;
            }

            $annotation = $this->annotateSymbol($symbol);

            if ($annotation !== '') {
                $annotations[] = $annotation;
            }
        }

        return array_values(array_unique($annotations));
    }

     
    protected function matchFormulaSymbol(string $formula, ?string $kategori = null): ?Symbol
    {
        $normalized = $this->normalizeLatex($formula);

        if (mb_strlen($normalized) < (int) config('services.symbols.formula_min_chars', 6)) {
            return null;
        }

        $threshold = (float) config('services.symbols.formula_match_threshold', 0.9);
        $domains = $this->resolveDomains($kategori);
        $symbols = $this->all();

        if ($domains !== null) {
            $symbols = $symbols->filter(fn (Symbol $s) => in_array(mb_strtolower((string) $s->domain), $domains, true));
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($symbols as $symbol) {
            $score = $this->formulaScore($formula, $symbol);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $symbol;
            }
        }

        return $bestScore >= $threshold ? $best : null;
    }

     
    protected function formulaScore(string $formula, Symbol $symbol): float
    {
        $normalized = $this->normalizeLatex($formula);
        $candidates = array_values(array_filter([
            $symbol->latex,
            $symbol->formula,
            $symbol->symbol,
        ]));

        $best = 0.0;

        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $candidateNormalized = $this->normalizeLatex($candidate);

            if ($candidateNormalized === '') {
                continue;
            }

            if ($candidateNormalized === $normalized) {
                return 1.0;
            }

            $lenFormula = mb_strlen($normalized);
            $lenCandidate = mb_strlen($candidateNormalized);
            $min = min($lenFormula, $lenCandidate);

            if ($min >= (int) config('services.symbols.formula_min_chars', 6)) {
                if (str_contains($candidateNormalized, $normalized) && $lenFormula >= 0.4 * $lenCandidate) {
                    $best = max($best, 0.9);
                } elseif (str_contains($normalized, $candidateNormalized) && $lenCandidate >= 0.4 * $lenFormula) {
                    $best = max($best, 0.9);
                }
            }
        }

        return $best;
    }

     
    protected function normalizeLatex(string $latex): string
    {
        $latex = mb_strtolower($latex);
        $latex = (string) preg_replace(
            '/\\\\[,;:! ]|\\\\quad|\\\\qquad|\\\\displaystyle|\\\\left|\\\\right/',
            '',
            $latex
        );

        return (string) preg_replace('/\s+/', '', $latex);
    }

     
    protected function annotateSymbol(Symbol $symbol): string
    {
        $parts = [];

        if ($symbol->name) {
            $parts[] = $symbol->name;
        }

        if ($symbol->formula) {
            $parts[] = $symbol->formula;
        } elseif ($symbol->latex) {
            $parts[] = $symbol->latex;
        }

        if ($symbol->meaning) {
            $parts[] = mb_substr($symbol->meaning, 0, 120);
        } elseif ($symbol->definition) {
            $parts[] = mb_substr($symbol->definition, 0, 120);
        }

        if ($parts === []) {
            return '';
        }

        $line = '[Simbol] '.implode(' | ', $parts);

        if ($symbol->unit) {
            $line .= ' (satuan: '.$symbol->unit.')';
        }

        return $line;
    }

     
    protected function format(Symbol $symbol): string
    {
        $parts = [];

        if ($symbol->name) {
            $parts[] = $symbol->name;
        }

        if ($symbol->formula) {
            $parts[] = $symbol->formula;
        } elseif ($symbol->latex) {
            $parts[] = $symbol->latex;
        }

        if ($symbol->meaning) {
            $parts[] = mb_substr($symbol->meaning, 0, 160);
        }

        if ($symbol->common_misunderstanding) {
            $parts[] = 'jebak: '.mb_substr($symbol->common_misunderstanding, 0, 160);
        }

        $line = '- '.implode(' | ', array_filter($parts));

        if ($symbol->unit) {
            $line .= ' (satuan: '.$symbol->unit.')';
        }

        return $line;
    }

     
    protected function score(Symbol $symbol, array $terms, array $idf): float
    {
        $fields = $this->fieldHaystacks($symbol);

        if (array_filter($fields) === []) {
            return 0.0;
        }

        $weights = ['strong' => 2.0, 'medium' => 0.6, 'weak' => 0.25];
        $score = 0.0;

        foreach ($terms as $term) {
            foreach ($weights as $field => $weight) {
                if (str_contains($fields[$field], $term)) {

                    $lengthFactor = mb_strlen($term) >= 4 ? 1.25 : 1.0;

                    $score += $weight * $lengthFactor * ($idf[$term] ?? 1.0);

                    break;
                }
            }
        }

        return $score;
    }

     
    protected function fieldHaystacks(Symbol $symbol): array
    {
        return [
            'strong' => mb_strtolower(implode(' ', array_filter([
                $symbol->name,
                $symbol->symbol,
                implode(' ', (array) $symbol->keywords),
                implode(' ', (array) $symbol->related_concepts),
            ]))),
            'medium' => mb_strtolower(implode(' ', array_filter([
                $symbol->retrieval_text,
                $symbol->category,
                $symbol->subcategory,
                $symbol->formula,
                $symbol->latex,
            ]))),
            'weak' => mb_strtolower(implode(' ', array_filter([
                $symbol->definition,
                $symbol->meaning,
                $symbol->example,
                $symbol->common_misunderstanding,
            ]))),
        ];
    }

     
    protected array $searchableCache = [];

    protected function searchableText(Symbol $symbol): string
    {
        return $this->searchableCache[$symbol->id] ??= implode(' ', array_filter([
            $symbol->name,
            $symbol->symbol,
            $symbol->retrieval_text,
            $symbol->category,
            $symbol->subcategory,
            $symbol->definition,
            $symbol->meaning,
            $symbol->formula,
            $symbol->latex,
            $symbol->example,
            $symbol->common_misunderstanding,
            implode(' ', (array) $symbol->keywords),
            implode(' ', (array) $symbol->related_concepts),
        ]));
    }

     
    protected function tokenize(string $query): array
    {
        $stopwords = [
            'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan',
            'itu', 'ini', 'apa', 'bagaimana', 'mengapa', 'adalah', 'sebuah',
            'the', 'of', 'and', 'is', 'are', 'a', 'an', 'to', 'in', 'for', 'how',
            'what', 'why',
        ];

        $cleaned = mb_strtolower($query);
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $cleaned, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $terms = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) < 3 || in_array($part, $stopwords, true)) {
                continue;
            }

            $terms[$part] = true;
        }

        return array_keys($terms);
    }

     
    protected function all(): Collection
    {
        if ($this->cache === null) {
            try {
                $this->cache = Symbol::query()->get();
            } catch (\Throwable $e) {
                Log::warning('SymbolService: gagal memuat tabel symbols: {msg}', ['msg' => $e->getMessage()]);
                $this->cache = new Collection;
            }
        }

        return $this->cache;
    }
}

