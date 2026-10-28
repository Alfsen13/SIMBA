<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
     
    public function index(): View
    {
        $notifikasi = Auth::user()->notifikasi()->latest()->paginate(20);

        return view('notifikasi.index', compact('notifikasi'));
    }

     
    public function read(Notifikasi $notifikasi): RedirectResponse
    {
        $this->authorize('read', $notifikasi);

        $notifikasi->update(['is_read' => true]);

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

     
    public function readAll(): RedirectResponse
    {
        Auth::user()->notifikasi()->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'Semua notifikasi telah dibaca.');
    }
}

