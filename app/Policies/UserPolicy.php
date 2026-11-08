<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function manage(User $operator, User $user): Response
    {
        if ($operator->role !== 'operator') {
            return Response::allow();
        }

        if ($user->role === 'operator') {
            return Response::deny('Pengelolaan akun operator hanya dapat dilakukan oleh pengelola operator.');
        }

        if (! $operator->kategori_mapel || $user->role === 'pelajar') {
            return Response::allow();
        }

        if ($user->aplikasiPengajar?->kategori_mapel === $operator->kategori_mapel) {
            return Response::allow();
        }

        return Response::deny('Tidak berwenang mengelola akun di luar kategori ini.');
    }
}
