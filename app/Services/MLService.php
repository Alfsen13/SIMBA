<?php

namespace App\Services;

use App\Models\FiturHybridSiswa;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;

/**
 * Perhitungan 4 parameter akademik agregat per siswa (seluruh kelas).
 *
 * Sejak deteksi risiko memakai skor penyimpangan per-pertemuan
 * (`PrediksiRisikoService`), class ini hanya menyimpan agregat parameter yang
 * dipakai dashboard & halaman "Parameter Akademik Siswa" (read-only).
 *
 * Keempat parameter ini juga menjadi fitur input Random Forest
 * (`KlasifikasiRisikoService`), yang dilatih dari label acuan baseline dan
 * hasilnya disimpan terpisah sebagai pembanding - bukan pengganti baseline.
 */
class MLService
{
    public const FEATURES = [
        'rata_nilai',
        'persen_terlambat',
        'persen_terlewat',
        'nilai_keseluruhan',
    ];

    public const FEATURE_LABELS = [
        'rata_nilai' => 'Rata Nilai Kuis & Tugas',
        'persen_terlambat' => 'Keterlambatan Pengumpulan',
        'persen_terlewat' => 'Tugas Terlewat',
        'nilai_keseluruhan' => 'Nilai Keseluruhan',
    ];

    public function computeAcademicFeatures(int $siswaId, ?User $siswa = null): array
    {
        $siswa ??= User::with('kelasDiikuti')->find($siswaId);
        $kelasIds = $siswa?->kelasDiikuti->pluck('id')->all() ?? [];

        if ($kelasIds === []) {
            return array_fill_keys(self::FEATURES, null);
        }

        $now = now();

        $idKuisMacet = Kuis::whereIn('kelas_id', $kelasIds)
            ->where(fn ($q) => $q->whereNull('pertemuan_id')->orWhereHas('pertemuan', fn ($p) => $p->dibuka()))
            ->where(function ($q) use ($now) {
                $q->where('is_aktif', false)
                    ->orWhere(fn ($q2) => $q2->whereNotNull('tanggal_selesai')->where('tanggal_selesai', '<=', $now));
            })
            ->pluck('id');

        $idTugasMacet = Tugas::whereIn('kelas_id', $kelasIds)
            ->where(fn ($q) => $q->whereNull('pertemuan_id')->orWhereHas('pertemuan', fn ($p) => $p->dibuka()))
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $now)
            ->pluck('id');

        $skorKuis = $idKuisMacet->isEmpty()
            ? collect()
            : KuisHasil::where('siswa_id', $siswaId)->whereIn('kuis_id', $idKuisMacet)->pluck('skor');

        $semuaPengumpulan = $idTugasMacet->isEmpty()
            ? collect()
            : PengumpulanTugas::where('siswa_id', $siswaId)
                ->whereIn('tugas_id', $idTugasMacet)
                ->get(['nilai', 'terlambat']);

        $nilaiTugas = $semuaPengumpulan->whereNotNull('nilai')->values();

        $jumlahDikumpulkan = $semuaPengumpulan->count();
        $jumlahDinilai = $nilaiTugas->count();

        $semuaNilai = $skorKuis->concat($nilaiTugas->pluck('nilai'));
        $rataNilai = $semuaNilai->isNotEmpty()
            ? round((float) $semuaNilai->avg(), 1)
            : null;

        $persenTerlambat = $jumlahDikumpulkan > 0
            ? round($semuaPengumpulan->where('terlambat', true)->count() / $jumlahDikumpulkan * 100, 1)
            : null;

        $jumlahTugasMacet = $idTugasMacet->count();
        $terlewat = max(0, $jumlahTugasMacet - $jumlahDikumpulkan);
        $persenTerlewat = $jumlahTugasMacet > 0
            ? round($terlewat / $jumlahTugasMacet * 100, 1)
            : null;

        $perolehan = (int) $skorKuis->sum() + (int) $nilaiTugas->sum('nilai');
        $nilaiSempurna = ($idKuisMacet->count() * 100) + ($jumlahDinilai * 100);
        $nilaiKeseluruhan = $nilaiSempurna > 0
            ? round($perolehan / $nilaiSempurna * 100, 1)
            : null;

        return [
            'rata_nilai' => $rataNilai,
            'persen_terlambat' => $persenTerlambat,
            'persen_terlewat' => $persenTerlewat,
            'nilai_keseluruhan' => $nilaiKeseluruhan,
        ];
    }

    public function computeAllAcademicFeatures(): array
    {
        $users = User::with('kelasDiikuti')
            ->where('role', 'pelajar')
            ->get();

        $hasil = [];
        foreach ($users as $user) {
            $fitur = $this->computeAcademicFeatures($user->id, $user);

            FiturHybridSiswa::updateOrCreate(
                ['siswa_id' => $user->id],
                [...$fitur, 'periode' => now()->format('Y-m')],
            );

            $hasil[$user->id] = $fitur;
        }

        return $hasil;
    }

    public function backfillFiturPertemuan(): int
    {
        return app(FiturPertemuanService::class)->backfill();
    }
}
