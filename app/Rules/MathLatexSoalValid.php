<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Memeriksa LaTeX di payload soal kuis yang dikirim sebagai JSON. Diperiksa per
 * string supaya kurung struktural JSON tidak dihitung sebagai rumus.
 */
class MathLatexSoalValid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = json_decode(is_scalar($value) ? (string) $value : '', true);
        if (! is_array($decoded)) {
            return;
        }

        foreach ($this->teks((array) $decoded) as $teks) {
            foreach (MathLatexValid::periksa($teks) as $pesan) {
                $fail($pesan);

                return;
            }
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<string>
     */
    private function teks(array $data): array
    {
        $teks = [];

        foreach ($data as $nilai) {
            if (is_array($nilai)) {
                array_push($teks, ...$this->teks($nilai));
            } elseif (is_string($nilai)) {
                $teks[] = $nilai;
            }
        }

        return $teks;
    }
}
