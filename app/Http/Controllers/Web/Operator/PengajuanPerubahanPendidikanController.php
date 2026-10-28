<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPerubahanPendidikan;
use App\Models\ProfilPendidikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengajuanPerubahanPendidikanController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $pengajuanList = PengajuanPerubahanPendidikan::with(['user.profilPendidikan.jalur', 'user.profilPendidikan.jenjang', 'user.profilPendidikan.tingkat', 'jalur', 'jenjang', 'tingkat', 'reviewedBy'])
            ->when($status && in_array($status, ['pending', 'under_review', 'revision_required', 'approved', 'rejected'], true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByRaw("FIELD(status, 'pending', 'under_review', 'revision_required', 'approved', 'rejected')")
            ->latest()
            ->paginate(15);

        return view('operator.pengajuan-perubahan-pendidikan.index', compact('pengajuanList', 'status'));
    }

    public function show(PengajuanPerubahanPendidikan $pengajuan): View
    {
        $pengajuan->load(['user.profilPendidikan.jalur', 'user.profilPendidikan.jenjang', 'user.profilPendidikan.tingkat', 'jalur', 'jenjang', 'tingkat', 'reviewedBy']);

        return view('operator.pengajuan-perubahan-pendidikan.show', compact('pengajuan'));
    }

    public function setujui(Request $request, PengajuanPerubahanPendidikan $pengajuan): RedirectResponse
    {
        if (! in_array($pengajuan->status, ['pending', 'under_review', 'revision_required'], true)) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_operator' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($pengajuan, $validated) {
            $profil = ProfilPendidikan::firstOrCreate(
                ['user_id' => $pengajuan->user_id],
                ['jalur_id' => $pengajuan->jalur_id]
            );

            $profil->update([
                'jalur_id' => $pengajuan->jalur_id,
                'jenjang_id' => $pengajuan->jenjang_id,
                'tingkat_id' => $pengajuan->tingkat_id,
            ]);

            $pengajuan->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'catatan_operator' => $validated['catatan_operator'] ?: $pengajuan->catatan_operator,
            ]);
        });

        return redirect()->route('operator.pengajuan-perubahan-pendidikan.index')
            ->with('success', 'Pengajuan berhasil disetujui. Profil pendidikan siswa telah diperbarui.');
    }

    public function mintaRevisi(Request $request, PengajuanPerubahanPendidikan $pengajuan): RedirectResponse
    {
        if (! in_array($pengajuan->status, ['pending', 'under_review'], true)) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_operator' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan_operator.required' => 'Catatan revisi wajib diisi.',
            'catatan_operator.min' => 'Catatan revisi minimal 5 karakter.',
        ]);

        $pengajuan->update([
            'status' => 'revision_required',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'catatan_operator' => $validated['catatan_operator'],
        ]);

        return redirect()->route('operator.pengajuan-perubahan-pendidikan.index')
            ->with('success', 'Permintaan revisi berhasil dikirim.');
    }

    public function tolak(Request $request, PengajuanPerubahanPendidikan $pengajuan): RedirectResponse
    {
        if (! in_array($pengajuan->status, ['pending', 'under_review', 'revision_required'], true)) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'catatan_operator' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'catatan_operator.required' => 'Alasan penolakan wajib diisi.',
            'catatan_operator.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $pengajuan->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'catatan_operator' => $validated['catatan_operator'],
        ]);

        return redirect()->route('operator.pengajuan-perubahan-pendidikan.index')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }
}
