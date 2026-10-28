<?php

declare(strict_types=1);

namespace App\Services\Tugas;

 
final class SoalTugasService
{
     
    public const JUMLAH_PILIHAN = 4;

     
    public const INDEKS_JAWABAN_VALID = [0, 1, 2, 3];

     
    public const BLOOM_VALID = ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'];

     
    public function decode(?string $soalJson): ?array
    {
        if ($soalJson === null || trim($soalJson) === '') {
            return null;
        }

        $decoded = json_decode($soalJson, true);

        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        return $decoded;
    }

     
    public function encode(?array $soal): ?string
    {
        if ($soal === null || $soal === []) {
            return null;
        }

        return json_encode($soal, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

     
    public function normalisasiSoalPilihanGanda(mixed $soal): array
    {
        $soal = is_array($soal) ? $soal : [];

        $pilihan = $this->opsiUntukNormalisasi($soal);


        $pilihan = array_slice(array_values($pilihan), 0, self::JUMLAH_PILIHAN);
        while (count($pilihan) < self::JUMLAH_PILIHAN) {
            $pilihan[] = '';
        }
        $pilihan = array_map(fn ($p) => $this->teks($p), $pilihan);

        return $this->sertakanAkurasi([
            'pertanyaan' => $this->teks($soal['pertanyaan'] ?? null),
            'pilihan' => $pilihan,
            'jawaban' => $this->indeksJawaban($soal['jawaban'] ?? null),
            'bloom' => $this->bloom($soal['bloom'] ?? null),
        ], $soal);
    }

     
    public function normalisasiSoalEssai(mixed $soal): array
    {
        $soal = is_array($soal) ? $soal : [];

        return $this->sertakanAkurasi([
            'pertanyaan' => $this->teks($soal['pertanyaan'] ?? null),
            'referensi' => $this->teks($soal['referensi'] ?? null),
            'bloom' => $this->bloom($soal['bloom'] ?? null),
        ], $soal);
    }

    /**
     * Field akurasi soal terhadap materi bersifat opsional (hanya ada pada soal
     * hasil generate AI), jadi hanya ikut disimpan ketika nilainya valid.
     */
    private function sertakanAkurasi(array $baris, array $sumber): array
    {
        if (isset($sumber['akurasi_materi']) && is_numeric($sumber['akurasi_materi'])) {
            $baris['akurasi_materi'] = max(0, min(100, (int) $sumber['akurasi_materi']));
        }

        if (isset($sumber['catatan_akurasi'])) {
            $catatan = trim((string) $sumber['catatan_akurasi']);

            if ($catatan !== '') {
                $baris['catatan_akurasi'] = $catatan;
            }
        }

        return $baris;
    }

     
    public function normalisasiDaftar(mixed $soal, string $tipe): array
    {
        if (! is_array($soal)) {
            return [];
        }

        $normalisasi = match ($tipe) {
            'pilihan_ganda' => $this->normalisasiSoalPilihanGanda(...),
            'essai' => $this->normalisasiSoalEssai(...),
            default => null,
        };

        if ($normalisasi === null) {
            return [];
        }




        $baris = array_values(array_filter($soal, 'is_array'));

        return array_map($normalisasi, $baris);
    }

     
    public function opsiUntukTampil(mixed $soal): array
    {
        $soal = is_array($soal) ? $soal : [];

        $opsi = $soal['opsi'] ?? null;

        if (! is_array($opsi) || count($opsi) < 2) {
            $opsi = is_array($soal['pilihan'] ?? null) ? $soal['pilihan'] : [];
        }

        return array_values($opsi);
    }

     
    public function kunciJawaban(mixed $jawaban): int
    {
        return $this->indeksJawaban($jawaban);
    }

     
    public function hasilAiUntukIndeks(mixed $hasilAi, int $indeks): ?array
    {
        if (! is_array($hasilAi)) {
            return null;
        }

        foreach ($hasilAi as $baris) {
            if (is_array($baris) && (int) ($baris['indeks'] ?? -1) === $indeks) {
                return $baris;
            }
        }

        return null;
    }

     
    public function bloom(mixed $kode): ?string
    {
        $kode = strtoupper(trim((string) $kode));

        return in_array($kode, self::BLOOM_VALID, true) ? $kode : null;
    }

     
    private function opsiUntukNormalisasi(array $soal): array
    {
        $pilihan = $soal['pilihan'] ?? null;

        if (! is_array($pilihan) || $pilihan === []) {
            $pilihan = $soal['opsi'] ?? null;
        }

        return is_array($pilihan) ? $pilihan : [];
    }

     
    private function indeksJawaban(mixed $jawaban): int
    {
        if (is_int($jawaban)) {
            $nilai = $jawaban;
        } elseif (is_float($jawaban) && $jawaban === floor($jawaban)) {


            $nilai = (int) $jawaban;
        } elseif (is_string($jawaban) && preg_match('/^\d+$/', trim($jawaban)) === 1) {
            $nilai = (int) trim($jawaban);
        } else {
            return 0;
        }

        return in_array($nilai, self::INDEKS_JAWABAN_VALID, true) ? $nilai : 0;
    }

     
    private function teks(mixed $nilai): string
    {
        if ($nilai === null || is_bool($nilai)) {
            return $nilai === true ? '1' : '';
        }

        return is_scalar($nilai) ? (string) $nilai : '';
    }
}

