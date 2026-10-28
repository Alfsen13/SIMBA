<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Berita;
use App\Models\Kelas;
use Illuminate\Support\Str;

class KelasObserver
{
    public function created(Kelas $kelas): void
    {
        $userId = $kelas->guru_id ?? auth()->id();
        if (! $userId) {
            return;
        }

        $kategori = $kelas->kategori ?: 'Umum';
        $pengajar = $kelas->guru ? "sudah diisi oleh {$kelas->guru->name}" : 'belum ada pengajar';

        Berita::create([
            'judul' => "Pembukaan Kelas Baru: {$kelas->nama_kelas}",
            'slug' => Str::slug("pembukaan-kelas-{$kelas->nama_kelas}-{$kelas->id}"),
            'konten' => "Pembukaan kelas baru {$kategori}, {$kelas->nama_kelas}, {$pengajar}.",
            'excerpt' => "Pembukaan kelas baru {$kategori}, {$kelas->nama_kelas}",
            'published_at' => now(),
            'user_id' => $userId,
        ]);
    }
}
