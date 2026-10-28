<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GeneratePertemuanGagal extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $kelasNama,
        public string $alasan,
        ?string $locale = null,
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

        return [
            'judul' => trans('notifications.generate_pertemuan_gagal.judul', [], $locale),
            'pesan' => trans('notifications.generate_pertemuan_gagal.pesan', [
                'kelas' => $this->kelasNama,
                'alasan' => $this->alasan,
            ], $locale),
            'tipe' => 'peringatan',
            'link' => null,
        ];
    }
}
