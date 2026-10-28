<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NilaiController extends Controller
{
     
    public function index(Request $request): View
    {
        $kelasList = Kelas::where('guru_id', Auth::id())
            ->orderBy('nama_kelas')
            ->get();

        $kelasId = $request->integer('kelas') ?: $kelasList->first()?->id;
        $kelas = $kelasList->firstWhere('id', $kelasId);

        if (! $kelas) {
            return view('guru.nilai.index', [
                'kelasList' => $kelasList,
                'kelas' => null,
                'siswaList' => collect(),
                'kolom' => [],
                'nilai' => [],
                'rata' => [],
                'jumlahKolom' => 0,
            ]);
        }

        $siswaList = $kelas->siswa()
            ->where('users.role', 'pelajar')
            ->where('users.status', 'verified')
            ->orderBy('users.name')
            ->get();

        $siswaIds = $siswaList->pluck('id');

        $kuis = Kuis::where('kelas_id', $kelas->id)
            ->with('pertemuan:id,urutan,nama_pertemuan')
            ->get();

        $tugas = Tugas::where('kelas_id', $kelas->id)
            ->with('pertemuan:id,urutan,nama_pertemuan')
            ->get();

        $items = collect();
        foreach ($kuis as $k) {
            $items->push([
                'tipe' => 'kuis',
                'id' => $k->id,
                'judul' => $k->judul,
                'pertemuan' => $k->pertemuan?->nama_pertemuan,
                'urutan' => $k->pertemuan?->urutan ?? 9999,
            ]);
        }
        foreach ($tugas as $t) {
            $items->push([
                'tipe' => 'tugas',
                'id' => $t->id,
                'judul' => $t->judul,
                'pertemuan' => $t->pertemuan?->nama_pertemuan,
                'urutan' => $t->pertemuan?->urutan ?? 9999,
            ]);
        }

        $items = $items->sortBy([['urutan', 'asc'], ['id', 'asc']])->values();

        $counter = ['kuis' => 0, 'tugas' => 0];
        $kolom = [];
        foreach ($items as $it) {
            $counter[$it['tipe']]++;
            $kolom[] = [
                'key' => $it['tipe'].'-'.$it['id'],
                'tipe' => $it['tipe'],
                'kode' => ($it['tipe'] === 'kuis' ? 'K' : 'T').$counter[$it['tipe']],
                'judul' => $it['judul'],
                'pendek' => Str::limit($it['judul'], 16),
                'pertemuan' => $it['pertemuan'],
            ];
        }

        $nilai = [];
        if ($siswaIds->isNotEmpty()) {
            KuisHasil::whereIn('siswa_id', $siswaIds)
                ->whereIn('kuis_id', $kuis->pluck('id'))
                ->get(['siswa_id', 'kuis_id', 'skor'])
                ->each(function ($r) use (&$nilai) {
                    $nilai[$r->siswa_id]['kuis-'.$r->kuis_id] = (float) $r->skor;
                });

            PengumpulanTugas::whereIn('siswa_id', $siswaIds)
                ->whereIn('tugas_id', $tugas->pluck('id'))
                ->whereNotNull('nilai')
                ->get(['siswa_id', 'tugas_id', 'nilai'])
                ->each(function ($r) use (&$nilai) {
                    $nilai[$r->siswa_id]['tugas-'.$r->tugas_id] = (float) $r->nilai;
                });
        }

        $rata = [];
        foreach ($siswaIds as $sid) {
            $vals = array_values($nilai[$sid] ?? []);
            $rata[$sid] = $vals === [] ? null : round(array_sum($vals) / count($vals), 1);
        }

        return view('guru.nilai.index', [
            'kelasList' => $kelasList,
            'kelas' => $kelas,
            'siswaList' => $siswaList,
            'kolom' => $kolom,
            'nilai' => $nilai,
            'rata' => $rata,
            'jumlahKolom' => count($kolom),
        ]);
    }

     
    public function show(Request $request, User $siswa): View
    {
        $payload = $this->detailPayload($request, $siswa);

        return view('guru.nilai.show', $payload);
    }

     
    public function pdf(Request $request, User $siswa): Response
    {
        $payload = $this->detailPayload($request, $siswa);

        $namaFile = 'nilai-'.Str::slug($siswa->name).'.pdf';

        return Pdf::loadView('guru.nilai.pdf', $payload)
            ->setPaper('a4', 'landscape')
            ->download($namaFile);
    }

     
    private function detailPayload(Request $request, User $siswa): array
    {
        $guru = Auth::user();

        $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id')->all();

        $kelasId = $request->integer('kelas') ?: null;
        if ($kelasId !== null && ! in_array($kelasId, $kelasIds, true)) {
            abort(404);
        }

        $efektif = $kelasId !== null ? [$kelasId] : $kelasIds;

        $punyaAkses = $siswa->kelasDiikuti()
            ->wherePivotIn('kelas_id', $efektif)
            ->exists();

        abort_unless($punyaAkses, 404);

        $rows = collect();

        $hasilKuis = KuisHasil::where('siswa_id', $siswa->id)
            ->whereHas('kuis', fn ($q) => $q->whereIn('kelas_id', $efektif))
            ->with(['kuis.kelas', 'kuis.pertemuan'])
            ->get();

        foreach ($hasilKuis as $h) {
            if (! $h->kuis) {
                continue;
            }
            $rows->push([
                'jenis' => 'Kuis',
                'judul' => $h->kuis->judul,
                'kelas' => $h->kuis->kelas?->nama_kelas,
                'pertemuan' => $h->kuis->pertemuan?->nama_pertemuan,
                'nilai' => (float) $h->skor,
                'tanggal' => $h->created_at,
                'catatan' => null,
            ]);
        }

        $pengumpulan = PengumpulanTugas::where('siswa_id', $siswa->id)
            ->whereHas('tugas', fn ($q) => $q->whereIn('kelas_id', $efektif))
            ->with(['tugas.kelas', 'tugas.pertemuan'])
            ->get();

        foreach ($pengumpulan as $p) {
            if (! $p->tugas) {
                continue;
            }
            $rows->push([
                'jenis' => 'Tugas',
                'judul' => $p->tugas->judul,
                'kelas' => $p->tugas->kelas?->nama_kelas,
                'pertemuan' => $p->tugas->pertemuan?->nama_pertemuan,
                'nilai' => $p->nilai === null ? null : (float) $p->nilai,
                'tanggal' => $p->created_at,
                'catatan' => $p->terlambat ? 'Terlambat' : null,
            ]);
        }

        $rows = $rows
            ->sortBy(fn ($r) => $r['tanggal']?->timestamp ?? 0)
            ->values();

        $nilaiKuis = $rows->where('jenis', 'Kuis')->pluck('nilai')->filter(fn ($v) => $v !== null);
        $nilaiTugas = $rows->where('jenis', 'Tugas')->pluck('nilai')->filter(fn ($v) => $v !== null);
        $semua = $nilaiKuis->concat($nilaiTugas);

        return [
            'siswa' => $siswa,
            'kelasDipilih' => $kelasId !== null ? Kelas::find($kelasId) : null,
            'rows' => $rows,
            'rataKuis' => $nilaiKuis->isNotEmpty() ? round($nilaiKuis->avg(), 1) : null,
            'rataTugas' => $nilaiTugas->isNotEmpty() ? round($nilaiTugas->avg(), 1) : null,
            'rataTotal' => $semua->isNotEmpty() ? round($semua->avg(), 1) : null,
            'jumlahDinilai' => $semua->count(),
            'guru' => $guru,
        ];
    }
}

