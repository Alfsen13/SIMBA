<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebSearchService
{
    protected int $timeout;

    protected int $maxResults;

    public function __construct()
    {
        $this->timeout = (int) config('services.web_search.timeout', 5);
        $this->maxResults = (int) config('services.web_search.max_results', 3);
    }

    public function search(string $query): array
    {
        if (config('services.web_search.enabled', true) === false) {
            return [];
        }

        if ($key = config('services.web_search.google_api_key')) {
            $results = $this->searchGoogle($query, $key);
            if (! empty($results)) {
                return $results;
            }
        }

        $results = $this->searchDuckDuckGo($query);
        if (! empty($results)) {
            return $results;
        }

        Log::info('WebSearchService: semua provider gagal, fallback ke RAG saja');

        return [];
    }

    protected function searchGoogle(string $query, string $apiKey): array
    {
        $cx = config('services.web_search.google_cx');
        if (! $cx) {
            return [];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get('https://www.googleapis.com/customsearch/v1', [
                    'key' => $apiKey,
                    'cx' => $cx,
                    'q' => $query,
                    'num' => $this->maxResults,
                ]);

            if (! $response->successful()) {
                return [];
            }

            $items = $response->json('items') ?? [];
            $results = [];
            foreach ($items as $item) {
                $results[] = [
                    'title' => $item['title'] ?? '',
                    'snippet' => $item['snippet'] ?? '',
                    'url' => $item['link'] ?? '',
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            Log::warning('Google CSE search gagal: {msg}', ['msg' => $e->getMessage()]);

            return [];
        }
    }

    protected function searchDuckDuckGo(string $query): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withOptions(['verify' => false])
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->get('https://html.duckduckgo.com/html/', ['q' => $query]);

            if (! $response->successful()) {
                return [];
            }

            libxml_use_internal_errors(true);
            $dom = new \DOMDocument;
            $dom->loadHTML('<meta charset="utf-8">'.$response->body());
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $results = [];

            foreach ($xpath->query("//a[contains(@class, 'result__a')]") as $node) {
                if (count($results) >= $this->maxResults) {
                    break;
                }

                $href = $node->getAttribute('href');
                $title = trim($node->textContent);

                $snippet = '';
                $parent = $xpath->query("ancestor::div[contains(@class, 'result__body')]", $node)->item(0);
                if ($parent) {
                    $snippetNodes = $xpath->query(".//a[contains(@class, 'result__snippet')]", $parent);
                    if ($snippetNodes->length > 0) {
                        $snippet = trim($snippetNodes->item(0)->textContent);
                    }
                }

                if ($href && $title) {
                    $results[] = [
                        'title' => strip_tags($title),
                        'snippet' => strip_tags($snippet),
                        'url' => $href,
                    ];
                }
            }

            return $results;
        } catch (\Throwable $e) {
            Log::warning('DuckDuckGo search gagal: {msg}', ['msg' => $e->getMessage()]);

            return [];
        }
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->head('https://html.duckduckgo.com');

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
