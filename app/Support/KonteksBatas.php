<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Batas pilihan konteks untuk generate soal. Aturan validasi request, endpoint
 * kandidat potongan, dan teks bantuan di picker membacanya dari sini supaya
 * angkanya tidak bercabang jadi tiga tempat.
 */
final class KonteksBatas
{
    public const PERTEMUAN = 12;

    public const MATERI = 60;

    public const CHUNK = 40;
}
