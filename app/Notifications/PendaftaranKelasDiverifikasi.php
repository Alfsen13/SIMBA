<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PendaftaranKelasDiverifikasi extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public bool $disetujui,
        public string $namaKelas,
        public int $kelasId,
        ?string $locale = null
    ) {
        $this->locale = $locale ?? app()->getLocale();
    }

    public function via(object $notifiable): array
    {
        return [NotifikasiChannel::class];
    }

    public function toNotifikasi(object $notifiable): array
    {
        $locale = $this->locale ?? app()->getLocale();

        if ($this->disetujui) {
            return [
                'judul' => trans('notifications.pendaftaran_kelas.disetujui_judul', [], $locale),
                'pesan' => trans('notifications.pendaftaran_kelas.disetujui_pesan', ['kelas' => $this->namaKelas], $locale),
                'tipe' => 'info',
                'link' => '/pelajar/kelas',
            ];
        }

        return [
            'judul' => trans('notifications.pendaftaran_kelas.ditolak_judul', [], $locale),
            'pesan' => trans('notifications.pendaftaran_kelas.ditolak_pesan', ['kelas' => $this->namaKelas], $locale),
            'tipe' => 'peringatan',
            'link' => '/pelajar/kelas/available',
        ];
    }
}
