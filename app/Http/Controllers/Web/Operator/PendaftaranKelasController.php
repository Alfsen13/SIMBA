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
        $permintaan = DB::table('kelas_siswa')
            ->join('users', 'users.id', '=', 'kelas_siswa.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'kelas_siswa.kelas_id')
            ->where('kelas_siswa.status', 'pending')
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
        if ($kelas->status !== 'dibuka') {
            return back()->with('error', __('siswa.kelas.tidak_dibuka'));
        }

        if ($this->ubahStatus($kelas, $siswa, 'active') === 0) {
            return back()->with('error', 'Pendaftaran tidak ditemukan atau sudah diproses.');
        }

        $siswa->notify(new PendaftaranKelasDiverifikasi(true, $kelas->nama_kelas, $kelas->id, app()->getLocale()));

        return back()->with('success', "Pendaftaran {$siswa->name} ke kelas {$kelas->nama_kelas} disetujui.");
    }

    public function tolak(Kelas $kelas, User $siswa): RedirectResponse
    {
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
