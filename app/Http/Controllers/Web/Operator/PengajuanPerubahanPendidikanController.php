<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\JenjangPendidikan;
use App\Models\PengajuanPerubahanPendidikan;
use App\Models\ProfilPendidikan;
use App\Models\TingkatPendidikan;
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

        $hasil = DB::transaction(function () use ($pengajuan, $validated): string {
            $pengajuanTerkunci = PengajuanPerubahanPendidikan::query()
                ->lockForUpdate()
                ->findOrFail($pengajuan->id);

            if (! in_array($pengajuanTerkunci->status, ['pending', 'under_review', 'revision_required'], true)) {
                return 'sudah_diproses';
            }

            $tujuanValid = $pengajuanTerkunci->jenjang_id !== null
                && $pengajuanTerkunci->tingkat_id !== null
                && JenjangPendidikan::query()
                    ->whereKey($pengajuanTerkunci->jenjang_id)
                    ->where('jalur_id', $pengajuanTerkunci->jalur_id)
                    ->where('aktif', true)
                    ->exists()
                && TingkatPendidikan::query()
                    ->whereKey($pengajuanTerkunci->tingkat_id)
                    ->where('jenjang_id', $pengajuanTerkunci->jenjang_id)
                    ->where('aktif', true)
                    ->exists();

            if (! $tujuanValid) {
                return 'tujuan_tidak_valid';
            }

            $profil = ProfilPendidikan::firstOrCreate(
                ['user_id' => $pengajuanTerkunci->user_id],
                ['jalur_id' => $pengajuanTerkunci->jalur_id]
            );

            $profil->update([
                'jalur_id' => $pengajuanTerkunci->jalur_id,
                'jenjang_id' => $pengajuanTerkunci->jenjang_id,
                'tingkat_id' => $pengajuanTerkunci->tingkat_id,
            ]);

            $pengajuanTerkunci->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'catatan_operator' => $validated['catatan_operator'] ?: $pengajuanTerkunci->catatan_operator,
            ]);

            return 'disetujui';
        });

        if ($hasil === 'sudah_diproses') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        if ($hasil === 'tujuan_tidak_valid') {
            return back()->with('error', 'Tujuan pendidikan pada pengajuan sudah tidak aktif atau tidak valid. Profil siswa tidak diubah.');
        }

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

        $dimintaRevisi = DB::transaction(function () use ($pengajuan, $validated): bool {
            $pengajuanTerkunci = PengajuanPerubahanPendidikan::query()
                ->lockForUpdate()
                ->findOrFail($pengajuan->id);

            if (! in_array($pengajuanTerkunci->status, ['pending', 'under_review'], true)) {
                return false;
            }

            $pengajuanTerkunci->update([
                'status' => 'revision_required',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'catatan_operator' => $validated['catatan_operator'],
            ]);

            return true;
        });

        if (! $dimintaRevisi) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

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

        $ditolak = DB::transaction(function () use ($pengajuan, $validated): bool {
            $pengajuanTerkunci = PengajuanPerubahanPendidikan::query()
                ->lockForUpdate()
                ->findOrFail($pengajuan->id);

            if (! in_array($pengajuanTerkunci->status, ['pending', 'under_review', 'revision_required'], true)) {
                return false;
            }

            $pengajuanTerkunci->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'catatan_operator' => $validated['catatan_operator'],
            ]);

            return true;
        });

        if (! $ditolak) {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        return redirect()->route('operator.pengajuan-perubahan-pendidikan.index')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }
}
