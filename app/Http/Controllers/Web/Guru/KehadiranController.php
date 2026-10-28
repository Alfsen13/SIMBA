<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\KehadiranRequest;
use App\Models\Kehadiran;
use App\Models\Pertemuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KehadiranController extends Controller
{
     
    public function edit(Pertemuan $pertemuan): View
    {
        $this->authorize('update', $pertemuan);

        $pertemuan->load('kelas');

        $siswa = $pertemuan->kelas->siswa()->orderBy('name')->get();
        $kehadiran = $pertemuan->kehadiran()->get()->keyBy('siswa_id');

        return view('guru.kehadiran.edit', compact('pertemuan', 'siswa', 'kehadiran'));
    }

     
    public function update(KehadiranRequest $request, Pertemuan $pertemuan): RedirectResponse
    {
        $this->authorize('update', $pertemuan);

        $validated = $request->validated();
        $siswaValid = $pertemuan->kelas->siswa()->pluck('users.id')->all();

        DB::transaction(function () use ($validated, $pertemuan, $siswaValid) {
            foreach ($validated['status'] as $siswaId => $status) {
                $siswaId = (int) $siswaId;

                if (! in_array($siswaId, $siswaValid, true)) {
                    continue;
                }

                Kehadiran::updateOrCreate(
                    ['siswa_id' => $siswaId, 'pertemuan_id' => $pertemuan->id],
                    [
                        'status' => $status,
                        'catatan' => $validated['catatan'][$siswaId] ?? null,
                        'dicatat_oleh' => Auth::id(),
                    ]
                );
            }
        });

        return redirect()
            ->route('pengajar.pertemuan.show', $pertemuan)
            ->with('success', 'Kehadiran berhasil disimpan.');
    }
}

