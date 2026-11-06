<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AplikasiPengajar;
use App\Models\JalurPendidikan;
use App\Models\JenjangPendidikan;
use App\Models\KategoriMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class RegisterController extends Controller
{
    /** Field aplikasi calon pengajar (dipisah dari kolom users). */
    private const FIELD_APLIKASI = [
        'pendidikan_tertinggi', 'pengalaman_mengajar', 'cv', 'ijazah',
        'kategori_mapel', 'mata_pelajaran', 'jenjang_dan_tingkat',
    ];

    public function showForm(): View
    {
        $jalur = JalurPendidikan::where('aktif', true)
            ->whereIn('kode', ['formal', 'nonformal', 'self_learning'])
            ->orderBy('urutan')
            ->get(['id', 'kode', 'nama']);

        $jenjangRows = JenjangPendidikan::where('aktif', true)
            ->whereHas('jalur', function ($q) {
                $q->whereIn('kode', ['formal', 'nonformal']);
            })
            ->orderBy('urutan')
            ->get(['id', 'jalur_id', 'nama']);
        $tingkatRows = TingkatPendidikan::where('aktif', true)
            ->whereIn('jenjang_id', $jenjangRows->pluck('id'))
            ->orderBy('urutan')
            ->get(['id', 'jenjang_id', 'nama']);

        $jenjangMap = $jenjangRows->groupBy('jalur_id')->map(fn ($g) => $g->map(fn ($r) => ['id' => $r->id, 'nama' => $r->nama])->values());
        $tingkatMap = $tingkatRows->groupBy('jenjang_id')->map(fn ($g) => $g->map(fn ($r) => ['id' => $r->id, 'nama' => $r->nama])->values());

        $kategoriMapelList = KategoriMataPelajaran::query()
            ->whereHas('mataPelajaran', fn ($query) => $query->where('aktif', true))
            ->orderBy('nama')
            ->pluck('nama');

        $mapelByKategori = MataPelajaran::query()
            ->where('aktif', true)
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get()
            ->groupBy('kategori')
            ->map(fn ($items) => $items->pluck('nama')->values());

        return view('auth.register', [
            'jalurPayload' => $jalur->map(fn ($r) => ['id' => $r->id, 'kode' => $r->kode, 'nama' => $r->nama])->values(),
            'kategoriMapelList' => $kategoriMapelList,
            'mapelByKategori' => $mapelByKategori,
            'regMaster' => [
                'jalur' => $jalur->map(fn ($r) => ['id' => $r->id, 'kode' => $r->kode, 'nama' => $r->nama])->values(),
                'jenjang' => $jenjangMap->all(),
                'tingkat' => $tingkatMap->all(),
                'mapel' => old('mata_pelajaran', []),
            ],
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'pending';

        $storedFiles = [];
        try {
            $user = DB::transaction(function () use ($request, $validated, &$storedFiles): User {
                $userData = collect($validated)
                    ->except(['jalur_id', 'jenjang_id', 'tingkat_id', 'institusi', 'program', 'minat'])
                    ->except(self::FIELD_APLIKASI)
                    ->all();

                $user = User::create($userData);

                if (! empty($validated['jalur_id'])) {
                    $user->profilPendidikan()->create(collect([
                        'jalur_id' => $validated['jalur_id'],
                        'jenjang_id' => $validated['jenjang_id'] ?? null,
                        'tingkat_id' => $validated['tingkat_id'] ?? null,
                        'institusi' => $validated['institusi'] ?? null,
                        'program' => $validated['program'] ?? null,
                        'minat' => $validated['minat'] ?? null,
                    ])->filter(fn ($value) => $value !== null && $value !== '')->all());
                }

                $this->simpanAplikasiPengajar($request, $validated, $user, $storedFiles);

                return $user;
            });
        } catch (Throwable $exception) {
            if ($storedFiles !== [] && ! Storage::disk('public')->delete($storedFiles)) {
                Log::error('Gagal membersihkan berkas pendaftaran setelah transaksi dibatalkan.', [
                    'files' => $storedFiles,
                ]);
            }

            throw $exception;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('pending');
    }

    /**
     * Simpan berkas + data aplikasi calon pengajar. Hanya dibuat saat minimal
     * satu field terisi, supaya pendaftaran pengajar polos (NIP saja) tidak
     * menghasilkan baris aplikasi kosong.
     */
    private function simpanAplikasiPengajar(RegisterRequest $request, array $validated, User $user, array &$storedFiles): void
    {
        $adaIsi = collect(self::FIELD_APLIKASI)->contains(
            fn ($f) => $request->filled($f) || $request->hasFile($f)
        );

        if (! $adaIsi) {
            return;
        }

        $data = [
            'user_id' => $user->id,
            'pendidikan_tertinggi' => $validated['pendidikan_tertinggi'] ?? null,
            'pengalaman_mengajar' => $validated['pengalaman_mengajar'] ?? null,
            'kategori_mapel' => $validated['kategori_mapel'] ?? null,
            'mata_pelajaran' => $validated['mata_pelajaran'] ?? [],
            'jenjang_dan_tingkat' => $validated['jenjang_dan_tingkat'] ?? null,
            'status_aplikasi' => 'pending',
        ];

        foreach (['cv', 'ijazah'] as $berkas) {
            if ($request->hasFile($berkas)) {
                $path = $request->file($berkas)->store('aplikasi_pengajar/' . $berkas, 'public');

                if ($path === false) {
                    throw new \RuntimeException('Gagal menyimpan berkas pendaftaran.');
                }

                $storedFiles[] = $path;
                $data[$berkas . '_path'] = $path;
            }
        }

        AplikasiPengajar::create($data);
    }
}
