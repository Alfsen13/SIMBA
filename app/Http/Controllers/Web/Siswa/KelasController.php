<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(): View
    {
        $kelasList = Auth::user()->kelasDiikuti()->with('guru')->latest()->paginate(10);
        $kelasMenunggu = Auth::user()->kelasMenunggu()->with('guru')->latest()->get();

        return view('siswa.kelas.index', compact('kelasList', 'kelasMenunggu'));
    }

    public function show(Kelas $kelas): View
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $kelas->id)->exists()) {
            abort(403);
        }

        $kelas->load([
            'pertemuan' => fn ($q) => $q->dibuka()->orderBy('urutan'),
            'guru',
        ]);

        return view('siswa.kelas.show', compact('kelas'));
    }

    public function available(Request $request): View
    {
        $user = Auth::user();
        $terpakai = DB::table('kelas_siswa')
            ->where('siswa_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->pluck('kelas_id');

        $availableKelas = Kelas::whereNotIn('id', $terpakai)
            ->where('status', 'dibuka')
            ->when($this->hanyaTerbuka($user), fn ($q) => $q->where('aturan_kelayakan', 'terbuka'))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('nama_kelas', 'like', '%'.$request->search.'%')
                        ->orWhere('kode_kelas', 'like', '%'.$request->search.'%');
                });
            })
            ->with('guru')
            ->latest()
            ->paginate(10);

        return view('siswa.kelas.available', compact('availableKelas'))
            ->with('hanyaTerbuka', $this->hanyaTerbuka($user));
    }

    /**
     * Pelajar jalur belajar mandiri (self_learning) hanya boleh mengakses kelas
     * yang operator tetapkan "terbuka" — label yang hanya operator yang menentukan
     * saat membuka kelas. Pelajar tanpa profil atau jalur lain tidak kena aturan ini.
     */
    private function hanyaTerbuka(User $user): bool
    {
        return $user->profilPendidikan?->jalur?->kode === 'self_learning';
    }

    public function enroll(Kelas $kelas): RedirectResponse
    {
        $user = Auth::user();

        if ($kelas->status !== 'dibuka') {
            return back()->with('error', __('siswa.kelas.tidak_dibuka'));
        }

        $sudahAda = DB::table('kelas_siswa')
            ->where('siswa_id', $user->id)
            ->where('kelas_id', $kelas->id)
            ->whereIn('status', ['active', 'pending'])
            ->exists();

        abort_if($sudahAda, 409, 'Kamu sudah terdaftar atau sedang menunggu validasi di kelas ini.');

        if ($this->hanyaTerbuka($user) && $kelas->aturan_kelayakan !== 'terbuka') {
            return back()->with('error', __('siswa.kelas.ditolak_belajar_mandiri'));
        }

        $existing = DB::table('kelas_siswa')
            ->where('siswa_id', $user->id)
            ->where('kelas_id', $kelas->id)
            ->first();

        if ($existing) {

            DB::table('kelas_siswa')->where('id', $existing->id)->update([
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'updated_at' => now(),
            ]);
        } else {
            $user->semuaKelasDiikuti()->firstOrCreate(
                ['kelas.id' => $kelas->id],
                [],
                ['status' => 'pending'],
            );
        }

        return redirect()->route('pelajar.kelas.index')
            ->with('success', 'Pendaftaran terkirim. Menunggu validasi operator.');
    }

    public function unenroll(Kelas $kelas): RedirectResponse
    {
        $this->authorize('unenroll', $kelas);

        Auth::user()->semuaKelasDiikuti()->detach($kelas->id);

        return redirect()->route('pelajar.kelas.index')->with('success', 'Berhasil keluar dari kelas.');
    }
}
