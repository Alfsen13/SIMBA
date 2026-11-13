<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Penanda region rumus yang ditanam di teks hasil ekstraksi PDF.
 *
 * Bentuknya `⟦rumus:<12 hex pertama region_key>⟧`. Angka heksnya dihitung dari
 * geometri region yang sama dengan yang dipakai pemindai rumus
 * (app/Python/math_regions.py), jadi token inilah alamat yang dicari ketika
 * guru menyetujui hasil OCR — bukan pencarian teks yang bisa ambigu.
 */
class RumusPenanda
{
    public const PANJANG = 20;

    public const POLA = '/⟦rumus:([0-9a-f]{12})⟧/u';

    /** Pengganti untuk manusia dan prompt AI; token mentah tidak layak dibaca. */
    public const LABEL = '[rumus belum diverifikasi]';

    /** Penanda untuk region_key penuh hasil pemindaian; '' bila kuncinya tidak layak. */
    public static function penanda(?string $regionKey): string
    {
        $hex = strtolower(trim((string) $regionKey));

        return preg_match('/^[0-9a-f]{12,}/', $hex) === 1 ? '⟦rumus:'.substr($hex, 0, 12).'⟧' : '';
    }

    public static function ada(string $teks): bool
    {
        return $teks !== '' && preg_match(self::POLA, $teks) === 1;
    }

    public static function utuh(string $potongan): bool
    {
        return preg_match(self::POLA, $potongan, $cocok) === 1 && $cocok[0] === $potongan;
    }

    /** Ganti setiap penanda oleh label yang bisa dibaca siswa, guru, dan model. */
    public static function manusiakan(string $teks): string
    {
        return $teks === '' ? '' : (string) preg_replace(self::POLA, self::LABEL, $teks);
    }

    /**
     * Awal penanda yang terpotong oleh batas pemotongan, atau null bila batas
     * itu aman. Dipakai pengchunking supaya satu region tidak pernah terbelah
     * di dua chunk — kalau terbelah, approve tidak menemukan tokennya lagi.
     */
    public static function batasSebelumPenanda(string $teks, int $batas): ?int
    {
        $kiri = mb_substr($teks, max(0, $batas - self::PANJANG), self::PANJANG);
        $mulai = mb_strrpos($kiri, '⟦');

        if ($mulai === false) {
            return null;
        }

        $posisi = max(0, $batas - self::PANJANG) + (int) $mulai;

        // Hanya batas yang jatuh di tengah token yang perlu digeser: tepat di
        // depan atau tepat di belakang token sama-sama aman.
        if (! self::utuh(mb_substr($teks, $posisi, self::PANJANG))) {
            return null;
        }

        return $posisi < $batas && $posisi + self::PANJANG > $batas ? $posisi : null;
    }
}
