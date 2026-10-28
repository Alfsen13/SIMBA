<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kelas;
use App\Models\Tugas;
use App\Models\User;

class TugasPolicy
{
     
    public function view(User $user, Tugas $tugas): bool
    {
        return $user->id === $tugas->guru_id;
    }

     
    public function create(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function update(User $user, Tugas $tugas): bool
    {
        return $user->id === $tugas->guru_id;
    }

     
    public function delete(User $user, Tugas $tugas): bool
    {
        return $user->id === $tugas->guru_id;
    }

     
    public function nilai(User $user, Tugas $tugas): bool
    {
        return $user->id === $tugas->guru_id;
    }

     
    public function penilaian(User $user, Tugas $tugas): bool
    {
        return $user->id === $tugas->guru_id;
    }
}

