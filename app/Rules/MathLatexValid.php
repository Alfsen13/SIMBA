<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Menolak LaTeX yang merusak render: pembatas atau kurung yang tidak seimbang.
 * Kasus ringan seperti `\frac{1}` sengaja dibiarkan agar guru tidak terkunci.
 */
class MathLatexValid implements ValidationRule
{
    private const PEMBATAS = [['$$', '$$'], ['\\[', '\\]'], ['\\(', '\\)']];

    /**
     * @param  bool  $rumus  true bila nilai sudah berupa isi rumus tanpa pembatas
     */
    public function __construct(private bool $rumus = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (self::periksa(is_scalar($value) ? (string) $value : '', $this->rumus) as $pesan) {
            $fail($pesan);
        }
    }

    /**
     * Daftar pesan untuk satu teks; kosong berarti aman.
     *
     * @return list<string>
     */
    public static function periksa(string $text, bool $rumus = false): array
    {
        if (trim($text) === '') {
            return [];
        }

        $pesan = [];

        foreach (self::pembatasButuhPenutup($text) as $pembatas) {
            $pesan[] = __('validation.math_latex_unfinished', ['delimiter' => $pembatas]);
        }

        $potongan = self::potonganRumus($text);

        if ($rumus && $potongan === []) {
            $potongan = [$text];
        }

        foreach ($potongan as $bagian) {
            if (! self::seimbang($bagian)) {
                $pesan[] = __('validation.math_latex_unbalanced');

                return $pesan;
            }
        }

        return $pesan;
    }

    /**
     * Pembatas berpasangan yang muncul tetapi tidak menemukan penutupnya.
     *
     * @return list<string>
     */
    public static function pembatasButuhPenutup(string $text): array
    {
        $rusak = [];

        foreach (self::PEMBATAS as [$buka, $tutup]) {
            $mulai = 0;
            while (($posisi = strpos($text, $buka, $mulai)) !== false) {
                $penutup = $buka === $tutup
                    ? strpos($text, $tutup, $posisi + strlen($buka))
                    : strpos($text, $tutup, $posisi);
                if ($penutup === false) {
                    $rusak[] = $buka;

                    break;
                }
                $mulai = $penutup + strlen($tutup);
            }
        }

        return $rusak;
    }

    /**
     * Isi setiap bagian rumus, termasuk `$...$` satu baris.
     *
     * @return list<string>
     */
    public static function potonganRumus(string $text): array
    {
        $potongan = [];

        foreach (self::PEMBATAS as [$buka, $tutup]) {
            preg_replace_callback(
                '/'.preg_quote($buka, '/').'(.*?)'.preg_quote($tutup, '/').'/s',
                function (array $m) use (&$potongan): void {
                    $potongan[] = $m[1];
                },
                $text
            );
        }

        preg_match_all('/(?<!\\\\)\$([^$\n]+?)\$(?<!\\\\)/', $text, $single);
        foreach ($single[1] as $isi) {
            $potongan[] = $isi;
        }

        return $potongan;
    }

    public static function seimbang(string $rumus): bool
    {
        $kedalaman = 0;

        for ($i = 0; $i < strlen($rumus); $i++) {
            $karakter = $rumus[$i];

            if ($karakter === '\\') {
                $berikutnya = $rumus[$i + 1] ?? '';
                if ($berikutnya === '{') {
                    $kedalaman++;
                } elseif ($berikutnya === '}') {
                    $kedalaman--;
                }
                $i++;

                continue;
            }

            if ($karakter === '{') {
                $kedalaman++;
            } elseif ($karakter === '}') {
                $kedalaman--;
            }

            if ($kedalaman < 0) {
                return false;
            }
        }

        return $kedalaman === 0;
    }
}
