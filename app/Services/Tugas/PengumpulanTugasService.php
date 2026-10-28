<?php

declare(strict_types=1);

namespace App\Services\Tugas;

use App\Jobs\PeriksaJawabanTugasAI;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\UploadedFile;

 
final class PengumpulanTugasService
{
      
    public function submit(Tugas $tugas, User $siswa, ?UploadedFile $file, ?array $jawaban): PengumpulanTugas
    {
        $pakaiFile = $tugas->tipe === 'upload';
        $pakaiJawaban = in_array($tugas->tipe, ['pilihan_ganda', 'essai'], true);
        $jawabanBersih = $pakaiJawaban ? $this->bersihkanJawaban($jawaban ?? []) : [];

        $existing = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $terlambat = false;

        if ($tugas->batas_jam && $existing && $existing->dimulai_pada && $existing->batas_kerjakan) {
            $terlambat = now()->greaterThan($existing->batas_kerjakan);
        } elseif ($tugas->deadline) {
            $terlambat = $tugas->isLewatDeadline();
        }

        $data = [
            'jawaban' => $jawabanBersih,
            'terlambat' => $terlambat,
        ];

        if ($pakaiFile) {
            if ($file instanceof UploadedFile) {
                $data['file'] = $file->store('pengumpulan', 'public');
            } elseif ($existing !== null && $existing->file) {
                $data['file'] = $existing->file;
            } else {
                $data['file'] = null;
            }
        } else {
            $data['file'] = null;
        }

        $pengumpulan = PengumpulanTugas::updateOrCreate(
            ['tugas_id' => $tugas->id, 'siswa_id' => $siswa->id],
            $data
        );


        if ($tugas->tipe === 'essai' && $this->adaIsiJawaban($jawabanBersih)) {
            PeriksaJawabanTugasAI::dispatch($pengumpulan->id);
        }

        return $pengumpulan;
    }

     
    public function simpanNilai(Tugas $tugas, array $nilai): int
    {
        if ($nilai === []) {
            return 0;
        }

        $ids = array_map('intval', array_keys($nilai));

        $pengumpulanList = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $tersimpan = 0;

        foreach ($nilai as $id => $nilaiItem) {
            $pengumpulan = $pengumpulanList->get((int) $id);

            if ($pengumpulan === null) {
                continue;
            }

            $pengumpulan->update(['nilai' => $nilaiItem === null ? null : (int) $nilaiItem]);
            $tersimpan++;
        }

        return $tersimpan;
    }

     
    public function jadwalkanPemeriksaanAi(Tugas $tugas): int
    {
        if ($tugas->tipe !== 'essai') {
            return 0;
        }



        $ids = $tugas->pengumpulan()
            ->whereNotNull('jawaban')
            ->get(['id', 'jawaban'])
            ->filter(fn ($p) => $this->adaIsiJawaban($p->jawaban))
            ->pluck('id');

        foreach ($ids as $id) {
            PeriksaJawabanTugasAI::dispatch((int) $id);
        }

        return $ids->count();
    }

     
    private function bersihkanJawaban(array $jawaban): array
    {
        $bersih = [];

        foreach (array_values($jawaban) as $nilai) {
            $bersih[] = is_scalar($nilai) ? (string) $nilai : '';
        }

        return $bersih;
    }

     
    private function adaIsiJawaban(?array $jawaban): bool
    {
        if (! is_array($jawaban)) {
            return false;
        }

        foreach ($jawaban as $nilai) {
            if (is_string($nilai) && trim($nilai) !== '') {
                return true;
            }
        }

        return false;
    }
}

