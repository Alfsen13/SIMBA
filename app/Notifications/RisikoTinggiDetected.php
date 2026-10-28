<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RisikoTinggiDetected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $siswaNama,
        public float $skorRisiko,
        public int $prediksiId,
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

        return [
            'judul' => trans('notifications.risiko_tinggi.judul', [], $locale),
            'pesan' => trans('notifications.risiko_tinggi.pesan', [
                'nama' => $this->siswaNama,
                'skor' => $this->skorRisiko,
            ], $locale),
            'tipe' => 'peringatan',
            'link' => '/pengajar/remedial',
        ];
    }
}
