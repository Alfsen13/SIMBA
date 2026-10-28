<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RemedialTersedia extends Notification implements ShouldQueue
{
    use Queueable;

     
    public function __construct(
        public int $remedialId,
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
            'judul' => trans('notifications.remedial_tersedia.judul', [], $locale),
            'pesan' => trans('notifications.remedial_tersedia.pesan', [], $locale),
            'tipe' => 'remedial',
            'link' => "/pelajar/remedial/{$this->remedialId}",
        ];
    }
}

