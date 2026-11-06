<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Notifications\PendaftaranKelasDiverifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendaftaranKelasController extends Controller
{
    public function index(): View
    {
        $kategoriOperator = Auth::user()->kategori_mapel;
        $permintaan = DB::table('kelas_siswa')
            ->join('users', 'users.id', '=', 'kelas_siswa.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'kelas_siswa.kelas_id')
            ->where('kelas_siswa.status', 'pending')
            ->whereNotNull('kelas.kategori')
            ->where('kelas.kategori', $kategoriOperator)
            ->orderBy('kelas_siswa.created_at')
            ->select([
                'kelas_siswa.id',
                'kelas_siswa.created_at',
                'users.id as siswa_id',
                'users.name as siswa_nama',
                'users.email as siswa_email',
                'kelas.id as kelas_id',
                'kelas.nama_kelas',
                'kelas.kode_kelas',
                'kelas.kategori',
            ])
            ->paginate(20);

        return view('operator.pendaftaran-kelas.index', compact('permintaan'));
    }

    public function setujui(Kelas $kelas, User $siswa): RedirectResponse
    {
        $this->authorize('view', $kelas);

        $hasil = DB::transaction(function () use ($kelas, $siswa): string {
            $kelas = Kelas::query()->lockForUpdate()->findOrFail($kelas->id);

            if ($kelas->status !== 'dibuka') {
                return 'ditutup';
            }

            $pending = DB::table('kelas_siswa')
                ->where('kelas_id', $kelas->id)
                ->where('siswa_id', $siswa->id)
                ->where('status', 'pending')
                ->exists();

            if (! $pending) {
                return 'sudah_diproses';
            }

            if ($kelas->kapasitas !== null && $kelas->jumlahAnggotaAktifDanMenunggu() > $kelas->kapasitas) {
                return 'penuh';
            }

            $updated = DB::table('kelas_siswa')
                ->where('kelas_id', $kelas->id)
                ->where('siswa_id', $siswa->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'active',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);

            return $updated === 1 ? 'disetujui' : 'sudah_diproses';
        });

        if ($hasil === 'ditutup') {
            return back()->with('error', __('siswa.kelas.tidak_dibuka'));
        }

        if ($hasil === 'sudah_diproses') {
            return back()->with('error', 'Pendaftaran tidak ditemukan atau sudah diproses.');
        }

        if ($hasil === 'penuh') {
            return back()->with('error', __('siswa.kelas.kapasitas_terlampaui'));
        }

        $siswa->notify(new PendaftaranKelasDiverifikasi(true, $kelas->nama_kelas, $kelas->id, app()->getLocale()));

        return back()->with('success', "Pendaftaran {$siswa->name} ke kelas {$kelas->nama_kelas} disetujui.");
    }

    public function tolak(Kelas $kelas, User $siswa): RedirectResponse
    {
        $this->authorize('view', $kelas);

        if ($this->ubahStatus($kelas, $siswa, 'rejected') === 0) {
            return back()->with('error', 'Pendaftaran tidak ditemukan atau sudah diproses.');
        }

        $siswa->notify(new PendaftaranKelasDiverifikasi(false, $kelas->nama_kelas, $kelas->id, app()->getLocale()));

        return back()->with('success', "Pendaftaran {$siswa->name} ke kelas {$kelas->nama_kelas} ditolak.");
    }

    private function ubahStatus(Kelas $kelas, User $siswa, string $status): int
    {
        return DB::table('kelas_siswa')
            ->where('kelas_id', $kelas->id)
            ->where('siswa_id', $siswa->id)
            ->where('status', 'pending')
            ->update([
                'status' => $status,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
