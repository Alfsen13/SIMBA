<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use Illuminate\View\View;

class MateriController extends Controller
{
    public function show(Materi $materi): View
    {
        $materi->load(['kelas.mataPelajaran', 'guru', 'pertemuan', 'document']);
        $this->authorize('view', $materi->kelas);

        return view('operator.materi.show', compact('materi'));
    }
}
