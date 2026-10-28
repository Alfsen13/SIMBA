<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Models\User;

class PertemuanPolicy
{
     
    public function view(User $user, Pertemuan $pertemuan): bool
    {
        return $user->id === $pertemuan->kelas->guru_id;
    }

     
    public function create(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function update(User $user, Pertemuan $pertemuan): bool
    {
        return $user->id === $pertemuan->kelas->guru_id;
    }

     
    public function delete(User $user, Pertemuan $pertemuan): bool
    {
        return $user->id === $pertemuan->kelas->guru_id;
    }
}

