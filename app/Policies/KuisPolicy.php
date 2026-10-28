<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\User;

class KuisPolicy
{
     
    public function view(User $user, Kuis $kuis): bool
    {
        return $user->id === $kuis->kelas->guru_id;
    }

     
    public function create(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function update(User $user, Kuis $kuis): bool
    {
        return $user->id === $kuis->kelas->guru_id;
    }

     
    public function delete(User $user, Kuis $kuis): bool
    {
        return $user->id === $kuis->kelas->guru_id;
    }
}

