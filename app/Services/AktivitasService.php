<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\User;

 
class AktivitasService
{
    public function __construct(protected FiturPertemuanService $fitur) {}

     
    public function catat(
        User $siswa,
        string $tipe,
        ?Pertemuan $pertemuan = null,
        ?Kelas $kelas = null,
        array $metadata = []
    ): AktivitasLog {
        $log = AktivitasLog::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $pertemuan?->id,
            'kelas_id' => $kelas?->id ?? $pertemuan?->kelas_id,
            'tipe' => $tipe,
            'deskripsi' => null,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);

        if ($pertemuan !== null) {
            $this->fitur->simpan($siswa->id, $pertemuan);
        }

        return $log;
    }

    /**
     * Tambahkan durasi belajar (detik) ke satu log aktivitas lalu hitung ulang
     * snapshot fitur pertemuan tersebut. Dipakai heartbeat timer di sisi klien:
     * `rata_durasi_belajar` dihitung dari `metadata.durasi_menit` (menit, boleh
     * pecahan) yang diakumulasi lintas ping.
     */
    public function catatDurasi(AktivitasLog $log, int $detik): void
    {
        if ($detik <= 0) {
            return;
        }

        $metadata = $log->metadata ?? [];
        $metadata['durasi_menit'] = round((float) ($metadata['durasi_menit'] ?? 0) + $detik / 60, 3);

        $log->update(['metadata' => $metadata]);

        if ($log->pertemuan_id !== null) {
            $pertemuan = $log->pertemuan;
            if ($pertemuan !== null) {
                $this->fitur->simpan($log->siswa_id, $pertemuan);
            }
        }
    }
}

