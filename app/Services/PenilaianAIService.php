<?php

namespace App\Services;

use App\Models\PengumpulanTugas;
use Illuminate\Support\Facades\Log;

class PenilaianAIService
{
    protected AIProviderService $ai;

    public function __construct()
    {
        $this->ai = app(AIProviderService::class);
    }

     
    public function nilai(PengumpulanTugas $pengumpulan): void
    {
        $tugas = $pengumpulan->tugas;
        if (! $tugas || $tugas->tipe !== 'essai') {
            return;
        }

        $soal = array_values($tugas->soal ?? []);
        if (count($soal) === 0) {
            return;
        }

        $jawabanSiswa = $pengumpulan->jawaban ?? [];

        $indeksSah = [];
        $blokSoal = '';
        foreach ($soal as $i => $pertanyaan) {
            $jawab = $jawabanSiswa[$i] ?? '';
            if (trim((string) $jawab) === '') {
                continue;
            }

            $indeksSah[] = $i;
            $blokSoal .= "\n[Soal indeks {$i}]\n";
            $blokSoal .= 'Pertanyaan: '.($pertanyaan['pertanyaan'] ?? '')."\n";
            if (isset($pertanyaan['referensi']) && trim((string) $pertanyaan['referensi']) !== '') {
                $blokSoal .= "Referensi jawaban: {$pertanyaan['referensi']}\n";
            }
            $blokSoal .= "Jawaban siswa: {$jawab}\n";
        }

        if (trim($blokSoal) === '') {
            return;
        }

        $system = 'Anda adalah penilai tugas esai pelajar. Anda menilai ketepatan jawaban siswa terhadap soal dan referensi jawaban. Balas sesuai format yang diminta.';

        $prompt = <<<PROMPT
Tugas: {$tugas->judul}

Nilai setiap jawaban siswa di bawah ini. Untuk tiap soal, tentukan status akhir jawaban:
- "benar"     : jawaban tepat dan mencakup poin-poin kunci jawaban
- "mendekati" : jawaban sebagian benar, tetapi kurang lengkap atau kurang tepat
- "salah"     : jawaban keliru, tidak relevan, atau tidak menjawab soal

Berikan skor 0-100 dan alasan singkat (maksimal 2 kalimat) untuk tiap soal.

Setiap blok diberi label "[Soal indeks N]". Isi field "indeks" dengan nilai N PERSIS seperti label blok (berbasis nol: 0, 1, 2, ...), bukan nomor urut 1, 2, 3.

{$blokSoal}

Keluarkan HANYA pure JSON array tanpa teks lain dan tanpa markdown code fence:
[{"indeks":0,"status":"benar","skor":90,"alasan":"..."}]
PROMPT;

        try {
            $output = $this->ai->sendLocalPreferred($prompt, [], $system);
        } catch (\Throwable $e) {
            Log::warning('PenilaianAI: AI gagal untuk pengumpulan {id}: {msg}', [
                'id' => $pengumpulan->id,
                'msg' => $e->getMessage(),
            ]);

            return;
        }

        $hasil = $this->parseHasil($output, $indeksSah);
        if (count($hasil) === 0) {
            return;
        }

        $total = count($hasil);
        $nilaiAi = (int) round(array_sum(array_column($hasil, 'skor')) / $total);

        $pengumpulan->update([
            'hasil_ai' => $hasil,
            'nilai_ai' => $nilaiAi,
            'koreksi_ai_at' => now(),
        ]);
    }

     
    protected function parseHasil(string $output, array $indeksSah = []): array
    {
        $teksBersih = preg_replace('/^```(?:json)?\s*/i', '', trim($output));
        $teksBersih = preg_replace('/\s*```$/u', '', (string) $teksBersih);

        $json = null;

        $decoded = json_decode($teksBersih, true);
        if (is_array($decoded)) {
            $json = $decoded;
        } elseif (preg_match('/\[[\s\S]*\]/', $teksBersih, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                $json = $decoded;
            }
        }

        if (! is_array($json)) {
            return [];
        }

        $hasil = [];

        foreach ($json as $item) {
            if (! is_array($item)) {
                continue;
            }

            $status = strtolower((string) ($item['status'] ?? 'mendekati'));
            if (! in_array($status, ['benar', 'mendekati', 'salah'], true)) {
                $status = 'mendekati';
            }

            $hasil[] = [
                'indeks' => (isset($item['indeks']) && is_numeric($item['indeks'])) ? (int) $item['indeks'] : null,
                'status' => $status,
                'skor' => max(0, min(100, (int) ($item['skor'] ?? 50))),
                'alasan' => mb_substr((string) ($item['alasan'] ?? ''), 0, 500),
            ];
        }

        return $this->sejajarkanIndeks($hasil, $indeksSah);
    }

     
    private function sejajarkanIndeks(array $hasil, array $indeksSah): array
    {
        if ($hasil === []) {
            return $hasil;
        }

        if ($indeksSah === []) {
            foreach ($hasil as $k => $h) {
                if ($h['indeks'] === null) {
                    $hasil[$k]['indeks'] = $k;
                }
            }

            return $hasil;
        }

        $eksplisit = array_map(static fn ($h) => $h['indeks'], $hasil);

        $cocok = count($eksplisit) === count($indeksSah)
            && count(array_unique($eksplisit)) === count($eksplisit)
            && array_diff($eksplisit, $indeksSah) === []
            && array_diff($indeksSah, $eksplisit) === [];

        if (! $cocok) {
            foreach ($hasil as $k => $h) {
                $hasil[$k]['indeks'] = $indeksSah[$k] ?? ($h['indeks'] ?? $k);
            }
        }

        return $hasil;
    }
}

