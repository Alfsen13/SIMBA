<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Pertemuan;
use App\Services\AktivitasService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PertemuanController extends Controller
{
    public function show(Pertemuan $pertemuan, AktivitasService $aktivitas): View
    {
        $user = Auth::user();
        if (! $user->kelasDiikuti()->where('kelas_id', $pertemuan->kelas_id)->exists()) {
            abort(403);
        }

        if (! $pertemuan->dibuka) {
            abort(403, 'Pertemuan ini belum dibuka oleh pengajar.');
        }

        $aktivitasLog = $aktivitas->catat($user, 'buka_pertemuan', $pertemuan);

        $pertemuan->load([
            'kelas.guru',
            'materi',
            'tugas',
            'kuis' => fn ($q) => $q->where('is_aktif', true),
        ]);

        return view('siswa.pertemuan.show', compact('pertemuan', 'aktivitasLog'));
    }
}
