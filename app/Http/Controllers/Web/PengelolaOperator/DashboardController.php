<?php

namespace App\Http\Controllers\Web\PengelolaOperator;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $jumlahOperator = User::where('role', 'operator')->count();
        $jumlahKategori = MataPelajaran::query()->distinct()->count('kategori');
        $jumlahPengajar = User::where('role', 'pengajar')->count();
        $jumlahPelajar = User::where('role', 'pelajar')->count();

        return view('pengelola-operator.dashboard.index', compact('jumlahOperator', 'jumlahKategori', 'jumlahPengajar', 'jumlahPelajar'));
    }
}
