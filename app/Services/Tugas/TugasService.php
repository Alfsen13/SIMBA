<?php

declare(strict_types=1);

namespace App\Services\Tugas;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\BloomTaxonomyService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

 
final class TugasService
{
    public function __construct(
        private readonly SoalTugasService $soal,
        private readonly BloomTaxonomyService $bloom,
    ) {}

     
    public function daftarUntukGuru(int $guruId): LengthAwarePaginator
    {
        return Tugas::where('guru_id', $guruId)
            ->with(['kelas', 'pertemuan'])
            ->withCount('pengumpulan')
            ->withCount(['pengumpulan as belum_dinilai' => fn ($q) => $q->whereNull('nilai')])
            ->latest()
            ->paginate(15);
    }

     
    public function tugasBelumDinilai(int $guruId): Collection
    {
        return Tugas::where('guru_id', $guruId)
            ->whereNotNull('deadline')
            ->where('deadline', '<=', now())
            ->whereHas('pengumpulan', fn ($q) => $q->whereNull('nilai'))
            ->withCount(['pengumpulan as belum_dinilai' => fn ($q) => $q->whereNull('nilai')])
            ->get(['id', 'judul', 'deadline']);
    }

     
    public function daftarUntukSiswa(User $siswa): LengthAwarePaginator
    {
        $kelasIds = $siswa->kelasDiikuti()->pluck('kelas.id');

        return Tugas::whereIn('kelas_id', $kelasIds)


            ->where(fn ($q) => $q->whereNull('pertemuan_id')->orWhereHas('pertemuan', fn ($p) => $p->dibuka()))
            ->with('kelas')
            ->withCount(['pengumpulan as sudah_dikumpulkan' => fn ($q) => $q->where('siswa_id', $siswa->id)])
            ->latest()
            ->paginate(10);
    }

     
    public function buat(array $data, int $guruId, array $materiPendukungIds = []): Tugas
    {
        $data['guru_id'] = $guruId;
        $data['soal'] = $this->normalisasiSoal($data['soal'] ?? null, (string) ($data['tipe'] ?? 'upload'));

        return DB::transaction(function () use ($data, $materiPendukungIds): Tugas {
            $tugas = Tugas::create($data);
            $tugas->materiPendukung()->sync($materiPendukungIds);

            return $tugas;
        });
    }

     
    public function perbarui(Tugas $tugas, array $data, array $materiPendukungIds = []): Tugas
    {
        if (array_key_exists('soal', $data) || array_key_exists('tipe', $data)) {
            $data['soal'] = $this->normalisasiSoal(
                $data['soal'] ?? $tugas->soal,
                (string) ($data['tipe'] ?? $tugas->tipe)
            );
        }

        DB::transaction(function () use ($tugas, $data, $materiPendukungIds): void {
            $tugas->update($data);
            $tugas->materiPendukung()->sync($materiPendukungIds);
        });

        return $tugas;
    }

    public function hapus(Tugas $tugas): void
    {
        $tugas->delete();
    }

     
    public function pertemuanUntukGuru(int $guruId): Collection
    {
        $kelasIds = Kelas::where('guru_id', $guruId)->pluck('id');

        return Pertemuan::whereIn('kelas_id', $kelasIds)
            ->get()
            ->groupBy('kelas_id');
    }

     
    public function semuaMateriUntukGuru(int $guruId): Collection
    {
        $kelasIds = Kelas::where('guru_id', $guruId)->pluck('id');

        return Materi::whereIn('kelas_id', $kelasIds)
            ->orderBy('judul')
            ->get();
    }

     
    private function normalisasiSoal(mixed $soal, string $tipe): ?array
    {
        if (is_string($soal)) {
            $soal = $this->soal->decode($soal);
        }

        if (! is_array($soal) || $soal === []) {
            return null;
        }

        $tipe = in_array($tipe, ['pilihan_ganda', 'essai'], true) ? $tipe : 'upload';

        if ($tipe === 'upload') {
            return null;
        }




        return $this->bloom->lengkapiOtomatis(
            $this->soal->normalisasiDaftar($soal, $tipe)
        );
    }
}
