<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PengajuanMengajarDiverifikasi extends Notification implements ShouldQueue
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
                'judul' => trans('notifications.pengajuan_mengajar.disetujui_judul', [], $locale),
                'pesan' => trans('notifications.pengajuan_mengajar.disetujui_pesan', ['kelas' => $this->namaKelas], $locale),
                'tipe' => 'info',
                'link' => '/pengajar/kelas',
            ];
        }

        return [
            'judul' => trans('notifications.pengajuan_mengajar.ditolak_judul', [], $locale),
            'pesan' => trans('notifications.pengajuan_mengajar.ditolak_pesan', ['kelas' => $this->namaKelas], $locale),
            'tipe' => 'peringatan',
            'link' => '/pengajar/kelas',
        ];
    }
}
