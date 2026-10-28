<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\User;

class MateriPolicy
{
     
    public function view(User $user, Materi $materi): bool
    {
        return $user->id === $materi->guru_id;
    }

     
    public function create(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function update(User $user, Materi $materi): bool
    {
        return $user->id === $materi->guru_id;
    }

     
    public function delete(User $user, Materi $materi): bool
    {
        return $user->id === $materi->guru_id;
    }
}

