<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $berita = Berita::where('published_at', '<=', now())
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('home', compact('berita'));
    }

    public function show(Berita $beritum): View
    {
        if ($beritum->published_at > now()) {
            abort(404);
        }

        $beritaLainnya = Berita::where('id', '!=', $beritum->id)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('berita-show', compact('beritum', 'beritaLainnya'));
    }
}
