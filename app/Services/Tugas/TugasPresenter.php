<?php

declare(strict_types=1);

namespace App\Services\Tugas;

use App\Models\PengumpulanTugas;

 
final class TugasPresenter
{
    public function __construct(
        private readonly SoalTugasService $soal,
    ) {}

     
    public function soalUntukSiswa(mixed $soal, string $tipe, ?PengumpulanTugas $pengumpulan = null): array
    {
        if ($tipe === 'essai') {
            return $this->barisPenilaian(is_array($soal) ? $soal : [], $pengumpulan);
        }

        return $this->soalUntukTampil($soal, $tipe);
    }

     
    public function soalUntukTampil(mixed $soal, string $tipe): array
    {
        if (! is_array($soal)) {
            return [];
        }

        if ($tipe === 'pilihan_ganda') {
            return array_map(function ($s, $nomor) {
                $s = is_array($s) ? $s : [];

                return [
                    'nomor' => $nomor + 1,
                    'pertanyaan' => $this->teksSoal($s['pertanyaan'] ?? null),
                    'opsi' => $this->soal->opsiUntukTampil($s),
                    'kunci' => $this->soal->kunciJawaban($s['jawaban'] ?? null),
                    'bloom' => $this->soal->bloom($s['bloom'] ?? null),
                ];
            }, array_values($soal), array_keys(array_values($soal)));
        }

        return [];
    }

     
    public function barisPilihanGanda(array $soal, ?PengumpulanTugas $pengumpulan): array
    {
        $jawabanSiswa = is_array($pengumpulan?->jawaban) ? $pengumpulan->jawaban : [];

        $baris = [];

        foreach ($this->soalUntukTampil($soal, 'pilihan_ganda') as $i => $s) {
            $opsi = $s['opsi'];
            $kunci = $s['kunci'];
            $pilih = $this->pilihPilihan($jawabanSiswa[$i] ?? null);

            $baris[] = $s + [
                'jawaban' => $pilih,
                'jawaban_teks' => $pilih !== null ? ($opsi[$pilih] ?? null) : null,
                'kunci_teks' => $opsi[$kunci] ?? null,
                'benar' => $pilih !== null && $pilih === $kunci,
            ];
        }

        return $baris;
    }

     
    private function pilihPilihan(mixed $nilai): ?int
    {
        if (is_int($nilai)) {
            return $nilai;
        }

        if (is_string($nilai) && preg_match('/^\d+$/', trim($nilai)) === 1) {
            return (int) trim($nilai);
        }

        return null;
    }

     
    public function barisPenilaian(array $soal, ?PengumpulanTugas $pengumpulan): array
    {
        $baris = [];

        foreach (array_values($soal) as $i => $s) {
            $s = is_array($s) ? $s : [];

            $hasil = $this->soal->hasilAiUntukIndeks($pengumpulan?->hasil_ai, $i);

            $baris[] = [
                'nomor' => $i + 1,
                'pertanyaan' => $this->teksSoal($s['pertanyaan'] ?? null),
                'bloom' => $this->soal->bloom($s['bloom'] ?? null),
                'referensi' => $this->teksSoal($s['referensi'] ?? null),
                'jawaban' => $pengumpulan?->jawaban[$i] ?? null,
                'hasil' => $hasil,
                'varian' => $this->varianHasil($hasil['status'] ?? null),
                'label' => $this->labelHasil($hasil['status'] ?? null),
                'skor' => isset($hasil['skor']) ? $hasil['skor'] : null,
                'alasan' => $hasil['alasan'] ?? null,
            ];
        }

        return $baris;
    }

     
    public function varianHasil(mixed $status): string
    {
        return match ($status) {
            'benar' => 'verified',
            'mendekati' => 'warning',
            'salah' => 'danger',
            default => 'warning',
        };
    }

     
    public function labelHasil(mixed $status): string
    {
        return match ($status) {
            'benar' => 'Benar',
            'mendekati' => 'Mendekati',
            'salah' => 'Salah',
            default => 'Menunggu',
        };
    }

     
    public function pesanSubmit(string $tipe, bool $terlambat): string
    {
        $pesan = $terlambat
            ? 'Tugas berhasil dikumpulkan (terlambat).'
            : 'Tugas berhasil dikumpulkan.';

        if ($tipe === 'essai') {
            $pesan .= ' Jawabanmu sedang diperiksa oleh AI.';
        }

        return $pesan;
    }

     
    public function pesanPeriksaAI(string $tipe, int $jumlah): array
    {
        if ($tipe !== 'essai') {
            return ['error', 'Fitur pemeriksaan AI hanya tersedia untuk tipe tugas Esai.'];
        }

        if ($jumlah === 0) {
            return ['error', 'Tidak ada pengumpulan yang perlu diperiksa ulang.'];
        }

        return ['success', 'Pemeriksaan AI dijalankan di antrean. Segarkan halaman ini sebentar untuk melihat hasil terbaru.'];
    }

     
    private function teksSoal(mixed $nilai): string
    {
        if ($nilai === null || is_array($nilai) || is_object($nilai)) {
            return 'Soal';
        }

        $teks = trim((string) $nilai);

        return $teks === '' ? 'Soal' : $teks;
    }
}

