<?php

namespace App\Notifications;

use App\Models\Notifikasi;
use Illuminate\Notifications\Notification;

class NotifikasiChannel
{
     
    public function send(object $notifiable, Notification $notification): void
    {
        $notifPayload = $notification->toNotifikasi($notifiable);

        Notifikasi::create([
            'user_id' => $notifiable->id,
            'judul' => $notifPayload['judul'],
            'pesan' => $notifPayload['pesan'],
            'tipe' => $notifPayload['tipe'] ?? 'info',
            'link' => $notifPayload['link'] ?? null,
            'is_read' => false,
        ]);
    }
}

