<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

class KelasPolicy
{
     
    public function view(User $user, Kelas $kelas): bool
    {
        // Guru kelas selalu dapat melihat kelasnya.
        if ($user->id === $kelas->guru_id) {
            return true;
        }

        // Operator hanya melihat kelas di kategorinya.
        if ($user->kategori_mapel && $kelas->kategori) {
            return $user->kategori_mapel === $kelas->kategori;
        }

        return false;
    }

     
    public function create(User $user): bool
    {
        return $user->role === 'operator';
    }

     
    public function update(User $user, Kelas $kelas): bool
    {
        // Guru kelas selalu dapat mengedit kelasnya.
        if ($user->id === $kelas->guru_id) {
            return true;
        }

        // Operator hanya mengedit kelas di kategorinya.
        if ($user->kategori_mapel && $kelas->kategori) {
            return $user->kategori_mapel === $kelas->kategori;
        }

        return false;
    }

     
    public function delete(User $user, Kelas $kelas): bool
    {
        // Guru kelas selalu dapat menghapus kelasnya.
        if ($user->id === $kelas->guru_id) {
            return true;
        }

        // Operator hanya menghapus kelas di kategorinya.
        if ($user->kategori_mapel && $kelas->kategori) {
            return $user->kategori_mapel === $kelas->kategori;
        }

        return false;
    }

     
    public function lepasGuru(User $user, Kelas $kelas): bool
    {
        // Hanya guru kelas atau operator dengan kategori yang sama yang
        // boleh mencabut hak ajar dari kelas.
        if ($user->id === $kelas->guru_id) {
            return true;
        }

        if ($user->kategori_mapel && $kelas->kategori) {
            return $user->kategori_mapel === $kelas->kategori;
        }

        return false;
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

