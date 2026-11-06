<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\RemedialJawabanRequest;
use App\Jobs\NilaiRemedialTugasAI;
use App\Models\MateriRemedial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RemedialController extends Controller
{
     
    public function index(): View
    {
        $remedials = MateriRemedial::where('siswa_id', Auth::id())
            ->with('prediksiAi')
            ->latest('generated_at')
            ->get();

        return view('siswa.remedial.index', compact('remedials'));
    }

     
    public function show(MateriRemedial $remedial): View
    {
        if ($remedial->siswa_id !== Auth::id()) {
            abort(403);
        }

        if ($remedial->status === 'belum_dibaca') {
            $remedial->update(['status' => 'dibaca']);
        }

        return view('siswa.remedial.show', compact('remedial'));
    }

     
    public function submit(RemedialJawabanRequest $request, MateriRemedial $remedial): RedirectResponse
    {
        if ($remedial->siswa_id !== Auth::id()) {
            abort(403);
        }

        if (empty($remedial->tugas_remedial)) {
            return back()->with('error', 'Tugas remedial belum tersedia.');
        }

        if ($remedial->hasil_remedial !== null) {
            return back()->with('error', 'Penilaian sudah selesai. Pilih kerjakan ulang sebelum mengirim jawaban baru.');
        }

        $validated = $request->validated();
        $jawabanLama = array_map('strval', array_values($remedial->jawaban_remedial ?? []));
        $jawabanBaru = array_map('strval', array_values($validated['jawaban']));

        if ($jawabanLama !== [] && $jawabanLama !== $jawabanBaru) {
            return back()->with('error', 'Jawaban yang sudah dikirim tidak dapat diubah saat menunggu penilaian.');
        }

        $remedial->update([
            'jawaban_remedial' => $jawabanLama === [] ? $jawabanBaru : $remedial->jawaban_remedial,
            'hasil_remedial' => null,
            'bloom_tercapai' => null,
            'mastery_alasan' => null,
            'dinilai_at' => null,
            'status' => 'dibaca',
        ]);

        NilaiRemedialTugasAI::dispatch($remedial->id);

        return redirect()->route('pelajar.remedial.show', $remedial)
            ->with('success', 'Jawaban remedialmu sedang diperiksa AI untuk menentukan pencapaian tujuan pembelajaran.');
    }

     
    public function reset(MateriRemedial $remedial): RedirectResponse
    {
        if ($remedial->siswa_id !== Auth::id()) {
            abort(403);
        }

        if ($remedial->jawaban_remedial !== null && $remedial->hasil_remedial === null) {
            return back()->with('error', 'Jawabanmu masih menunggu penilaian AI.');
        }

        $remedial->update([
            'jawaban_remedial' => null,
            'hasil_remedial' => null,
            'bloom_tercapai' => null,
            'mastery_alasan' => null,
            'dinilai_at' => null,
            'status' => 'dibaca',
        ]);

        return back()->with('success', 'Kamu boleh mengerjakan ulang tugas remedial.');
    }
}
