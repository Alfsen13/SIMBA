<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\KuisHasil;
use App\Models\NilaiHarian;
use App\Models\PengumpulanTugas;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NilaiController extends Controller
{
     
    public function index(): View
    {
        $user = Auth::user();
        $nilaiHarian = NilaiHarian::where('siswa_id', $user->id)
            ->with('kelas')->latest()->paginate(20);
        $nilaiKuis = KuisHasil::where('siswa_id', $user->id)
            ->with('kuis.kelas')->latest()->paginate(20);
        $nilaiTugas = PengumpulanTugas::where('siswa_id', $user->id)
            ->whereNotNull('nilai')->with('tugas.kelas')->latest()->paginate(20);

        return view('siswa.nilai.index', compact('nilaiHarian', 'nilaiKuis', 'nilaiTugas'));
    }
}

