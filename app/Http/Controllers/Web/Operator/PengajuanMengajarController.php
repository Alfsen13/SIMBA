<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PengajuanPengajarKelas;
use App\Notifications\PengajuanMengajarDiverifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Tinjauan pengajar mengisi kelas: pengajar mengajukan bahan rencana ajar,
 * operator memilih satu (kelas.guru_id hanya terisi lewat sini).
 */
class PengajuanMengajarController extends Controller
{
    public function show(Kelas $kelas): View
    {
        $this->authorize('view', $kelas);

        $pengajuanList = PengajuanPengajarKelas::with(['pengajar.aplikasiPengajar'])
            ->where('kelas_id', $kelas->id)
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'disetujui' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->get();

        return view('operator.pengajuan-mengajar.show', compact('kelas', 'pengajuanList'));
    }

    public function setujui(PengajuanPengajarKelas $pengajuan): RedirectResponse
    {
        $this->authorize('view', $pengajuan->kelas);

        if ($pengajuan->status !== PengajuanPengajarKelas::STATUS_PENDING) {
            return back()->with('error', __('operator.pengajuan_mengajar.sudah_diproses'));
        }

        if ($pengajuan->kelas->guru_id !== null) {
            return back()->with('error', __('operator.pengajuan_mengajar.kelas_sudah_terisi'));
        }

        DB::transaction(function () use ($pengajuan) {
            $pengajuan->kelas->update(['guru_id' => $pengajuan->user_id]);

            $pengajuan->update([
                'status' => PengajuanPengajarKelas::STATUS_DISETUJUI,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Pengajuan pending lain untuk kelas yang sama otomatis ditolak —
            // kelas hanya boleh punya satu guru.
            PengajuanPengajarKelas::with('pengajar')
                ->where('kelas_id', $pengajuan->kelas_id)
                ->where('id', '!=', $pengajuan->id)
                ->where('status', PengajuanPengajarKelas::STATUS_PENDING)
                ->get()
                ->each(function (PengajuanPengajarKelas $lain) {
                    $lain->update([
                        'status' => PengajuanPengajarKelas::STATUS_DITOLAK,
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'catatan_operator' => __('operator.pengajuan_mengajar.catatan_otomatis_terisi'),
                    ]);

                    $lain->pengajar->notify((new PengajuanMengajarDiverifikasi(
                        false, $lain->kelas->nama_kelas, $lain->kelas_id, app()->getLocale(),
                    ))->afterCommit());
                });
        });

        $pengajuan->pengajar->notify(new PengajuanMengajarDiverifikasi(
            true, $pengajuan->kelas->nama_kelas, $pengajuan->kelas_id, app()->getLocale(),
        ));

        return back()->with('success', __('operator.pengajuan_mengajar.terkirim_setujui', [
            'nama' => $pengajuan->pengajar->name,
        ]));
    }

    public function tolak(Request $request, PengajuanPengajarKelas $pengajuan): RedirectResponse
    {
        $this->authorize('view', $pengajuan->kelas);

        if ($pengajuan->status !== PengajuanPengajarKelas::STATUS_PENDING) {
            return back()->with('error', __('operator.pengajuan_mengajar.sudah_diproses'));
        }

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $pengajuan->update([
            'status' => PengajuanPengajarKelas::STATUS_DITOLAK,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'catatan_operator' => $validated['catatan'] ?: null,
        ]);

        $pengajuan->pengajar->notify(new PengajuanMengajarDiverifikasi(
            false, $pengajuan->kelas->nama_kelas, $pengajuan->kelas_id, app()->getLocale(),
        ));

        return back()->with('success', __('operator.pengajuan_mengajar.terkirim_tolak', [
            'nama' => $pengajuan->pengajar->name,
        ]));
    }

    public function berkas(PengajuanPengajarKelas $pengajuan): StreamedResponse
    {
        $this->authorize('view', $pengajuan->kelas);

        abort_unless(
            $pengajuan->berkas_path && Storage::disk('local')->exists($pengajuan->berkas_path),
            404,
        );

        return Storage::disk('local')->download(
            $pengajuan->berkas_path,
            'bahan-rencana-ajar-'.basename($pengajuan->berkas_path),
        );
    }

    /**
     * Unduh berkas profil pelamar (CV/ijazah dari pendaftaran) — khusus
     * operator, meski file tersimpan di disk public.
     */
    public function profilBerkas(PengajuanPengajarKelas $pengajuan, string $jenis): StreamedResponse
    {
        $this->authorize('view', $pengajuan->kelas);

        $path = match ($jenis) {
            'cv' => $pengajuan->pengajar->aplikasiPengajar?->cv_path,
            'ijazah' => $pengajuan->pengajar->aplikasiPengajar?->ijazah_path,
            default => null,
        };

        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, basename($path));
    }
}
