<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Notifikasi;
use App\Models\User;

class NotifikasiPolicy
{
     
    public function read(User $user, Notifikasi $notifikasi): bool
    {
        return $notifikasi->user_id === $user->id;
    }
}

