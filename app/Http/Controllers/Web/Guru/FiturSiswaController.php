<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\FiturSiswaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FiturSiswaController extends Controller
{
     
    public function index(): View
    {
        $siswaList = User::where('role', 'pelajar')->where('status', 'verified')
            ->whereHas('kelasDiikuti', fn ($q) => $q->where('guru_id', Auth::id()))
            ->with(['fiturHybridSiswa', 'kelasDiikuti' => fn ($q) => $q->where('guru_id', Auth::id())])
            ->paginate(15);

        return view('guru.fitur-siswa.index', compact('siswaList'));
    }

     
    public function update(FiturSiswaRequest $request, User $siswa): RedirectResponse
    {
        abort_if($siswa->role !== 'pelajar', 404);

        return back()->with(
            'error',
            'Parameter siswa dihitung otomatis dari data akademik dan tidak bisa diisi manual. '
            .'Jalankan Prediksi untuk memperbaruinya.'
        );
    }
}

