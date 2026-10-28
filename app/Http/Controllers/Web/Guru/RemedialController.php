<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MateriRemedial;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RemedialController extends Controller
{
    public function index(): View
    {
        $guruId = Auth::id();
        $kelasIds = Kelas::where('guru_id', $guruId)->pluck('id');

        $remedials = MateriRemedial::whereHas('siswa.kelasDiikuti', function ($q) use ($kelasIds) {
            $q->whereIn('kelas_id', $kelasIds);
        })->with(['siswa', 'prediksiAi'])->latest('generated_at')->get();

        return view('guru.remedial.index', compact('remedials'));
    }

    public function show(MateriRemedial $remedial): View
    {
        $guruId = Auth::id();
        $kelasIds = Kelas::where('guru_id', $guruId)->pluck('id');

        abort_unless(
            $remedial->siswa->kelasDiikuti()->whereIn('kelas_id', $kelasIds)->exists(),
            403
        );

        return view('guru.remedial.show', compact('remedial'));
    }
}
