<?php

namespace App\Services;

class AiJsonParser
{
    public static function arrayFrom(string $output, bool $preferLatex = true): ?array
    {
        // Ekstrak JSON (array/objek) dari output AI yang bisa mengandung markdown fence/prosa
        $decoded = self::decode($output, $preferLatex);

        if (! is_array($decoded)) {
            return null;
        }

        if (array_is_list($decoded)) {
            return $decoded;
        }

        foreach ($decoded as $value) {
            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        return null;
    }

    public static function objectFrom(string $output, bool $preferLatex = true): ?array
    {
        $decoded = self::decode($output, $preferLatex);

        if (! is_array($decoded) || array_is_list($decoded)) {
            return null;
        }

        return $decoded;
    }

    public static function decode(string $output, bool $preferLatex = true): ?array
    {
        foreach (self::candidates($output, $preferLatex) as $candidate) {
            $decoded = json_decode($candidate, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private static function candidates(string $output, bool $preferLatex): array
    {
        $base = self::stripFences($output);
        $balanced = self::balancedSlice($base);

        $variants = [];

        foreach (array_unique(array_filter([$balanced, $base], fn ($v) => $v !== null && $v !== '')) as $variant) {
            $escaped = self::repairEscapes($variant, $preferLatex);

            $variants[] = $escaped;
            $variants[] = self::repairRawControlChars($escaped);
            $variants[] = self::normalizeQuotes($escaped);
            $variants[] = $variant;
            $variants[] = self::repairTrailingCommas($escaped);
            $variants[] = self::repairUnterminatedKeys($escaped);
            $variants[] = self::repairStrayQuotes($escaped);
            $variants[] = self::repairAll($variant, $preferLatex);
            $variants[] = self::repairAll($escaped, $preferLatex);
        }

        return array_values(array_unique($variants));
    }

    private static function stripFences(string $output): string
    {
        $text = preg_replace('/```[a-zA-Z0-9_-]*\s*/', '', $output) ?? $output;

        return trim($text);
    }

    private static function balancedSlice(string $text): ?string
    {
        $length = strlen($text);
        $inString = false;
        $escaped = false;
        $start = null;
        $depth = 0;

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;

                continue;
            }

            if ($char === '[' || $char === '{') {
                $start = $i;
                $depth = 1;

                for ($j = $i + 1; $j < $length; $j++) {
                    $inner = $text[$j];

                    if ($inString) {
                        if ($escaped) {
                            $escaped = false;
                        } elseif ($inner === '\\') {
                            $escaped = true;
                        } elseif ($inner === '"') {
                            $inString = false;
                        }

                        continue;
                    }

                    if ($inner === '"') {
                        $inString = true;

                        continue;
                    }

                    if ($inner === '[' || $inner === '{') {
                        $depth++;
                    } elseif ($inner === ']' || $inner === '}') {
                        $depth--;

                        if ($depth === 0) {
                            return substr($text, $start, $j - $start + 1);
                        }
                    }
                }

                return substr($text, $start);
            }
        }

        return null;
    }

    private static function repairEscapes(string $text, bool $preferLatex): string
    {
        $out = '';
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            if ($text[$i] !== '\\') {
                $out .= $text[$i];

                continue;
            }

            $next = $text[$i + 1] ?? '';

            if ($next === '') {
                $out .= '\\\\';

                continue;
            }

            if ($next === 'u') {
                if (preg_match('/^[0-9a-fA-F]{4}/', substr($text, $i + 2, 4))) {
                    $out .= substr($text, $i, 6);
                    $i += 5;

                    continue;
                }

                $out .= '\\\\';

                continue;
            }

            if ($next === '"' || $next === '\\' || $next === '/') {
                $out .= '\\'.$next;
                $i++;

                continue;
            }

            if (! $preferLatex && str_contains('bfnrt', $next)) {
                $out .= '\\'.$next;
                $i++;

                continue;
            }

            if ($preferLatex && str_contains('bfnrt', $next) && ! preg_match('/^[A-Za-z{\\\\]/', $text[$i + 2] ?? '')) {
                $out .= '\\'.$next;
                $i++;

                continue;
            }

            $out .= '\\\\';
        }

        return $out;
    }

    private static function repairRawControlChars(string $text): string
    {
        $out = '';
        $length = strlen($text);
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    $out .= $char;

                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    $out .= $char;

                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                    $out .= $char;

                    continue;
                }

                $out .= match ($char) {
                    "\n" => '\\n',
                    "\r" => '\\r',
                    "\t" => '\\t',
                    default => $char,
                };

                continue;
            }

            if ($char === '"') {
                $inString = true;
            }

            $out .= $char;
        }

        return $out;
    }

    private static function normalizeQuotes(string $text): string
    {
        return strtr($text, [
            "\u{201C}" => '"',
            "\u{201D}" => '"',
            "\u{201E}" => '"',
            "\u{201F}" => '"',
            "\u{2033}" => '"',
        ]);
    }

    private static function repairTrailingCommas(string $text): string
    {
        return preg_replace('/,\s*([}\]])/', '$1', $text) ?? $text;
    }

    private static function repairUnterminatedKeys(string $text): string
    {
        return preg_replace(
            '/"([A-Za-z_][A-Za-z0-9_]{0,40})(\s*):(?=\s*["\[\{\-0-9])/',
            '"$1$2":',
            $text
        ) ?? $text;
    }

    private static function repairStrayQuotes(string $text): string
    {
        return preg_replace(
            '/(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?|true|false|null)"(?=\s*[,}\]])/i',
            '$1',
            $text
        ) ?? $text;
    }

    private static function repairAll(string $text, bool $preferLatex): string
    {
        $text = self::normalizeQuotes($text);
        $text = self::repairUnterminatedKeys($text);
        $text = self::repairStrayQuotes($text);
        $text = self::repairTrailingCommas($text);
        $text = self::repairEscapes($text, $preferLatex);

        return self::repairRawControlChars($text);
    }
}