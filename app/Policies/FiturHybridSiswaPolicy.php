<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class FiturHybridSiswaPolicy
{
     
    public function update(User $user, User $siswa): bool
    {
        return $siswa->role === 'pelajar';
    }
}

