<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Batas pilihan konteks untuk generate soal. Aturan validasi request dan teks
 * bantuan di picker membacanya dari sini supaya angkanya tidak bercabang.
 */
final class KonteksBatas
{
    public const PERTEMUAN = 12;

    public const MATERI = 60;
}
