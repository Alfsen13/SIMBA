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
            ->with('kelas')->latest()->orderByDesc('id')->paginate(20, ['*'], 'harian_page')->withQueryString();
        $nilaiKuis = KuisHasil::where('siswa_id', $user->id)
            ->with('kuis.kelas')->latest()->orderByDesc('id')->paginate(20, ['*'], 'kuis_page')->withQueryString();
        $nilaiTugas = PengumpulanTugas::where('siswa_id', $user->id)
            ->whereNotNull('nilai')->with('tugas.kelas')->latest()->orderByDesc('id')->paginate(20, ['*'], 'tugas_page')->withQueryString();

        return view('siswa.nilai.index', compact('nilaiHarian', 'nilaiKuis', 'nilaiTugas'));
    }
}
