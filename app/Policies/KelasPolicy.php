<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

class KelasPolicy
{
     
    public function view(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function create(User $user): bool
    {
        return $user->role === 'operator';
    }

     
    public function update(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->id === $kelas->guru_id;
    }

     
    public function enroll(User $user, Kelas $kelas): bool
    {
        return $user->role === 'pelajar'
            && ! $user->semuaKelasDiikuti()
                ->wherePivot('kelas_id', $kelas->id)
                ->wherePivotIn('status', ['active', 'pending'])
                ->exists();
    }

     
    public function unenroll(User $user, Kelas $kelas): bool
    {
        return $user->role === 'pelajar'
            && $user->semuaKelasDiikuti()->wherePivot('kelas_id', $kelas->id)->exists();
    }
}

