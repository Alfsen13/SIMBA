<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateRemedialAI;
use App\Jobs\GenerateRemedialPertemuanAI;
use App\Models\Kelas;
use App\Models\MateriRemedial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RemedialController extends Controller
{
    public function index(): View
    {
        $guruId = Auth::id();
        $kelasIds = Kelas::where('guru_id', $guruId)->pluck('id');

        $remedials = MateriRemedial::query()
            ->select(['id', 'siswa_id', 'topik_lemah', 'status', 'provider_llm', 'generated_at', 'generasi_gagal_at'])
            ->whereHas('siswa.kelasDiikuti', function ($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->with(['siswa:id,name'])
            ->latest('generated_at')
            ->paginate(20);

        return view('guru.remedial.index', compact('remedials'));
    }

    public function show(MateriRemedial $remedial): View
    {
        $this->authorizeRemedial($remedial);

        return view('guru.remedial.show', compact('remedial'));
    }

    public function retryGeneration(MateriRemedial $remedial): RedirectResponse
    {
        $this->authorizeRemedial($remedial);

        abort_unless(
            $remedial->generasi_gagal_at !== null
                && empty($remedial->tugas_remedial)
                && $remedial->jawaban_remedial === null,
            409
        );

        if ($remedial->prediksi_ai_id !== null) {
            GenerateRemedialAI::dispatch($remedial->siswa_id, $remedial->prediksi_ai_id);
        } elseif ($remedial->pertemuan_id !== null) {
            GenerateRemedialPertemuanAI::dispatch(
                $remedial->siswa_id,
                $remedial->pertemuan_id,
                $remedial->skor_pertemuan
            );
        } else {
            return back()->with('error', __('guru_analitik.remedial.retry_unavailable'));
        }

        return back()->with('success', __('guru_analitik.remedial.retry_scheduled'));
    }

    private function authorizeRemedial(MateriRemedial $remedial): void
    {
        $kelasIds = Kelas::where('guru_id', Auth::id())->select('id');

        abort_unless(
            $remedial->siswa->kelasDiikuti()->whereIn('kelas_id', $kelasIds)->exists(),
            403
        );
    }
}
