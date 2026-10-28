<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Services\AktivitasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AktivitasController extends Controller
{
    /**
     * Terima heartbeat durasi belajar dari halaman pelajar. Dipanggil berkala
     * lewat `navigator.sendBeacon` (mengirim `_token` di body) sehingga juga
     * tetap terkirim saat tab ditutup/dipindah.
     */
    public function durasi(Request $request, AktivitasLog $aktivitas, AktivitasService $service): JsonResponse
    {
        abort_unless($aktivitas->siswa_id === Auth::id(), 403);

        $validated = $request->validate([
            'detik' => ['required', 'integer', 'min:1', 'max:3600'],
        ]);

        $service->catatDurasi($aktivitas, (int) $validated['detik']);

        return response()->json(['ok' => true]);
    }
}
