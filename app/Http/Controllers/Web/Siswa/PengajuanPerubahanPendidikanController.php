<?php

namespace App\Http\Controllers\Web\Siswa;

use App\Http\Controllers\Controller;
use App\Models\JenjangPendidikan;
use App\Models\PengajuanPerubahanPendidikan;
use App\Models\TingkatPendidikan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengajuanPerubahanPendidikanController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $profil = $user->profilPendidikan?->load(['jalur', 'jenjang', 'tingkat']);
        $pengajuanList = $user->pengajuanPerubahanPendidikan()
            ->with(['jalur', 'jenjang', 'tingkat', 'reviewedBy'])
            ->latest()
            ->get();

        return view('siswa.pengajuan-pendidikan.index', compact('profil', 'pengajuanList'));
    }

    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        $profil = $user->profilPendidikan?->load(['jalur', 'jenjang', 'tingkat']);

        if (! $profil || ! $profil->jalur) {
            return redirect()->route('pelajar.pengajuan-pendidikan.index')
                ->with('error', 'Profil pendidikan belum lengkap. Silakan lengkapi terlebih dahulu.');
        }

        $adaAktif = $user->pengajuanPerubahanPendidikan()
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($adaAktif) {
            return redirect()->route('pelajar.pengajuan-pendidikan.index')
                ->with('error', 'Masih ada pengajuan perubahan pendidikan yang sedang diproses.');
        }

        $jalur = $profil->jalur;
        $jenjangList = JenjangPendidikan::where('aktif', true)
            ->where('jalur_id', $jalur->id)
            ->orderBy('urutan')
            ->get();

        if ($jalur->kode === 'nonformal') {
            $jenjangList = $jenjangList->filter(function ($j) {
                return stripos($j->nama, 'kesetaraan') !== false || stripos($j->kode, 'kesetaraan') !== false;
            })->values();
        }

        $tingkatList = TingkatPendidikan::where('aktif', true)
            ->whereIn('jenjang_id', $jenjangList->pluck('id'))
            ->orderBy('urutan')
            ->get();

        return view('siswa.pengajuan-pendidikan.create', compact('profil', 'jalur', 'jenjangList', 'tingkatList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profil = $user->profilPendidikan?->load('jalur');

        if (! $profil || ! $profil->jalur) {
            return redirect()->route('pelajar.pengajuan-pendidikan.index')
                ->with('error', 'Profil pendidikan belum lengkap.');
        }

        $jalur = $profil->jalur;
        $jenjangList = JenjangPendidikan::where('aktif', true)
            ->where('jalur_id', $jalur->id)
            ->pluck('id');

        if ($jalur->kode === 'nonformal') {
            $kesetaraan = JenjangPendidikan::where('jalur_id', $jalur->id)
                ->where(function ($q) {
                    $q->where('kode', 'like', '%kesetaraan%')->orWhere('nama', 'like', '%Kesetaraan%');
                })
                ->first();

            if ($kesetaraan) {
                $jenjangList = collect([$kesetaraan->id]);
            }
        }

        $validated = $request->validate([
            'jenis' => ['required', 'in:kenaikan_kelas,perpindahan_jenjang'],
            'jenjang_id' => ['required', 'integer', 'in:'.$jenjangList->implode(',')],
            'tingkat_id' => [
                'required',
                'integer',
                Rule::exists('tingkat_pendidikan', 'id')
                    ->where('aktif', true)
                    ->where('jenjang_id', $request->input('jenjang_id')),
            ],
            'alasan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'jenis.required' => 'Jenis pengajuan wajib dipilih.',
            'jenis.in' => 'Jenis pengajuan tidak valid.',
            'jenjang_id.required' => 'Jenjang tujuan wajib dipilih.',
            'jenjang_id.in' => 'Jenjang tujuan tidak sesuai dengan jalur pendidikan kamu.',
            'tingkat_id.required' => 'Tingkat tujuan wajib dipilih.',
            'tingkat_id.exists' => 'Tingkat tujuan tidak sesuai dengan jenjang yang dipilih.',
            'alasan.required' => 'Alasan pengajuan wajib diisi.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
            'alasan.max' => 'Alasan maksimal 1000 karakter.',
        ]);

        $dibuat = DB::transaction(function () use ($user, $jalur, $validated): bool {
            User::query()->lockForUpdate()->findOrFail($user->id);

            if ($user->pengajuanPerubahanPendidikan()
                ->whereIn('status', ['pending', 'under_review'])
                ->exists()) {
                return false;
            }

            PengajuanPerubahanPendidikan::create([
                'user_id' => $user->id,
                'jalur_id' => $jalur->id,
                'jenjang_id' => $validated['jenjang_id'],
                'tingkat_id' => $validated['tingkat_id'],
                'jenis' => $validated['jenis'],
                'status' => 'pending',
                'catatan_operator' => $validated['alasan'],
            ]);

            return true;
        });

        if (! $dibuat) {
            return redirect()->route('pelajar.pengajuan-pendidikan.index')
                ->with('error', 'Masih ada pengajuan perubahan pendidikan yang sedang diproses.');
        }

        return redirect()->route('pelajar.pengajuan-pendidikan.index')
            ->with('success', 'Pengajuan perubahan pendidikan berhasil dikirim. Menunggu review operator.');
    }

    public function show(PengajuanPerubahanPendidikan $pengajuan): View
    {
        abort_unless($pengajuan->user_id === Auth::id(), 403);

        $pengajuan->load(['jalur', 'jenjang', 'tingkat', 'reviewedBy']);

        return view('siswa.pengajuan-pendidikan.show', compact('pengajuan'));
    }
}
