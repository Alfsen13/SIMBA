<?php

namespace App\Services;

use App\Jobs\GenerateRemedialAI;
use App\Jobs\GenerateRemedialPertemuanAI;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\MateriRemedial;
use App\Models\NilaiHarian;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\Tugas;
use App\Models\User;
use App\Notifications\RemedialTersedia;
use App\Notifications\RisikoTinggiDetected;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemedialService
{
     
    protected int $maksTopikLemah = 3;

    protected int $maksPercobaanAi = 3;

    protected AIProviderService $ai;

    public function __construct()
    {
        $this->ai = app(AIProviderService::class);
    }

     
    public function prosesSiswaBerisiko(?int $siswaId = null): array
    {
        $berisiko = PrediksiAi::query()
            ->where('hasil', 'berisiko_tinggi')
            ->when($siswaId !== null, fn ($q) => $q->where('siswa_id', $siswaId))
            ->with('siswa')
            ->get();

        $dijadwalkan = 0;
        $dilewati = 0;

        foreach ($berisiko as $prediksi) {
            $alasan = $this->alasanDilewati($prediksi);

            if ($alasan !== null) {
                Log::info('Remedial dilewati, sudah ada yang lebih baru', [
                    'siswa_id' => $prediksi->siswa_id,
                    'alasan' => $alasan,
                ]);

                $dilewati++;

                continue;
            }

            GenerateRemedialAI::dispatch($prediksi->siswa_id, $prediksi->id);
            $dijadwalkan++;

            $this->notifyGuru($prediksi);
        }

        // Sumber kedua: nilai pertemuan di bawah ambang (tidak butuh prediksi
        // risiko global). Keduanya memicu, dengan idempotensi masing-masing.
        $pertemuan = $this->prosesPertemuan($siswaId);
        $dijadwalkan += $pertemuan['dijadwalkan'];
        $dilewati += $pertemuan['dilewati'];

        return [
            'dijadwalkan' => $dijadwalkan,
            'dilewati' => $dilewati,
        ];
    }

    /**
     * Daftar siswa_id yang punya pemicu remedial (risiko global ATAU nilai
     * pertemuan di bawah ambang). Dipakai halaman guru untuk menandai baris
     * yang tombol "Buat Remedial"-nya relevan. TIDAK memperhitungkan guard
     * idempotensi — itu urusan `prosesSiswaBerisiko()` saat tombol ditekan.
     *
     * @return array<int, int>
     */
    public function siswaButuhRemedial(): array
    {
        $ids = PrediksiAi::query()
            ->where('hasil', 'berisiko_tinggi')
            ->pluck('siswa_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($this->kandidatPertemuanLemah() as $kandidat) {
            $ids[] = $kandidat['siswa_id'];
        }

        return array_values(array_unique($ids));
    }


     
    public function alasanDilewati(PrediksiAi $prediksi): ?string
    {
        if (! $prediksi->siswa) {
            return 'data siswa tidak ditemukan';
        }

        $masihDikerjakan = MateriRemedial::where('siswa_id', $prediksi->siswa_id)
            ->where('status', '!=', 'selesai')
            ->exists();

        if ($masihDikerjakan) {
            return 'masih ada remedial yang belum diselesaikan';
        }

        $cooldown = (int) config('services.remedial.cooldown_hari', 7);

        if ($cooldown > 0) {
            $terbaruBaru = MateriRemedial::where('siswa_id', $prediksi->siswa_id)
                ->whereNotNull('generated_at')
                ->where('generated_at', '>=', now()->subDays($cooldown))
                ->exists();

            if ($terbaruBaru) {
                return "remedial dibuat dalam {$cooldown} hari terakhir";
            }
        }

        return null;
    }

     
    // -----------------------------------------------------------------------
    // Remedial per-pertemuan
    // -----------------------------------------------------------------------

    /**
     * Pindai semua (siswa, pertemuan) yang skor gabungannya di bawah ambang,
     * lalu jadwalkan remedial pertemuan untuk yang belum punya.
     *
     * @return array{dijadwalkan: int, dilewati: int}
     */
    protected function prosesPertemuan(?int $siswaId = null): array
    {
        $dijadwalkan = 0;
        $dilewati = 0;

        foreach ($this->kandidatPertemuanLemah($siswaId) as $kandidat) {
            $alasan = $this->alasanDilewatiPertemuan($kandidat['siswa_id'], $kandidat['pertemuan_id']);

            if ($alasan !== null) {
                $dilewati++;

                continue;
            }

            GenerateRemedialPertemuanAI::dispatch(
                $kandidat['siswa_id'],
                $kandidat['pertemuan_id'],
                $kandidat['skor']
            );
            $dijadwalkan++;
        }

        return ['dijadwalkan' => $dijadwalkan, 'dilewati' => $dilewati];
    }

    /**
     * Daftar (siswa, pertemuan) dengan skor gabungan di bawah ambang.
     *
     * @return array<int, array{siswa_id: int, pertemuan_id: int, skor: float}>
     */
    protected function kandidatPertemuanLemah(?int $siswaId = null): array
    {
        $tugas = DB::table('pengumpulan_tugas')
            ->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')
            ->whereNotNull('pengumpulan_tugas.nilai')
            ->whereNotNull('tugas.pertemuan_id')
            ->when($siswaId !== null, fn ($q) => $q->where('pengumpulan_tugas.siswa_id', $siswaId))
            ->groupBy('pengumpulan_tugas.siswa_id', 'tugas.pertemuan_id')
            ->selectRaw('pengumpulan_tugas.siswa_id as siswa_id, tugas.pertemuan_id as pertemuan_id, AVG(pengumpulan_tugas.nilai) as rata')
            ->get();

        $kuis = DB::table('kuis_hasil')
            ->join('kuis', 'kuis.id', '=', 'kuis_hasil.kuis_id')
            ->whereNotNull('kuis.pertemuan_id')
            ->when($siswaId !== null, fn ($q) => $q->where('kuis_hasil.siswa_id', $siswaId))
            ->groupBy('kuis_hasil.siswa_id', 'kuis.pertemuan_id')
            ->selectRaw('kuis_hasil.siswa_id as siswa_id, kuis.pertemuan_id as pertemuan_id, AVG(kuis_hasil.skor) as rata')
            ->get();

        $peta = [];
        foreach ($tugas as $b) {
            $peta[(int) $b->siswa_id][(int) $b->pertemuan_id]['tugas'] = (float) $b->rata;
        }
        foreach ($kuis as $b) {
            $peta[(int) $b->siswa_id][(int) $b->pertemuan_id]['kuis'] = (float) $b->rata;
        }

        $kandidat = [];
        foreach ($peta as $siswaId => $pertemuans) {
            foreach ($pertemuans as $pertemuanId => $komponen) {
                $skor = $this->gabungSkorPertemuan($komponen['tugas'] ?? null, $komponen['kuis'] ?? null);

                if ($skor !== null && $skor < $this->ambangPertemuan()) {
                    $kandidat[] = [
                        'siswa_id' => $siswaId,
                        'pertemuan_id' => $pertemuanId,
                        'skor' => $skor,
                    ];
                }
            }
        }

        return $kandidat;
    }

    /**
     * Bobot tugas 80% + kuis 20% bila keduanya sudah dinilai; kalau hanya satu
     * komponen yang dinilai, komponen itu dipakai apa adanya. Menghitung yang
     * belum dinilai sebagai 0 akan menghukum siswa atas pekerjaan guru.
     */
    protected function gabungSkorPertemuan(?float $rataTugas, ?float $rataKuis): ?float
    {
        if ($rataTugas === null && $rataKuis === null) {
            return null;
        }

        if ($rataTugas !== null && $rataKuis !== null) {
            return $this->bobotTugas() * $rataTugas + $this->bobotKuis() * $rataKuis;
        }

        return $rataTugas ?? $rataKuis;
    }

    /**
     * Skor gabungan satu siswa pada satu pertemuan (0-100), atau null bila belum
     * ada satu pun komponen yang dinilai.
     */
    public function skorPertemuan(int $siswaId, int $pertemuanId): ?float
    {
        $rataTugas = DB::table('pengumpulan_tugas')
            ->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')
            ->where('pengumpulan_tugas.siswa_id', $siswaId)
            ->where('tugas.pertemuan_id', $pertemuanId)
            ->whereNotNull('pengumpulan_tugas.nilai')
            ->avg('pengumpulan_tugas.nilai');

        $rataKuis = DB::table('kuis_hasil')
            ->join('kuis', 'kuis.id', '=', 'kuis_hasil.kuis_id')
            ->where('kuis_hasil.siswa_id', $siswaId)
            ->where('kuis.pertemuan_id', $pertemuanId)
            ->avg('kuis_hasil.skor');

        return $this->gabungSkorPertemuan(
            $rataTugas === null ? null : (float) $rataTugas,
            $rataKuis === null ? null : (float) $rataKuis
        );
    }

    /**
     * Periksa satu (siswa, pertemuan) dan jadwalkan remedial bila perlu.
     * Dipakai jalur real-time "penilaian tersimpan".
     */
    public function periksaPertemuan(int $siswaId, int $pertemuanId): bool
    {
        $skor = $this->skorPertemuan($siswaId, $pertemuanId);

        if ($skor === null || $skor >= $this->ambangPertemuan()) {
            return false;
        }

        if ($this->alasanDilewatiPertemuan($siswaId, $pertemuanId) !== null) {
            return false;
        }

        GenerateRemedialPertemuanAI::dispatch($siswaId, $pertemuanId, $skor);

        return true;
    }

    /**
     * Dipanggil setelah siswa mengumpulkan kuis.
     */
    public function periksaSetelahKuis(Kuis $kuis, int $siswaId): bool
    {
        if ($kuis->pertemuan_id === null) {
            return false;
        }

        return $this->periksaPertemuan($siswaId, (int) $kuis->pertemuan_id);
    }

    /**
     * Dipanggil setelah guru menyimpan nilai tugas. Mengembalikan jumlah
     * remedial yang dijadwalkan.
     */
    public function periksaSetelahNilaiTugas(Tugas $tugas): int
    {
        if ($tugas->pertemuan_id === null) {
            return 0;
        }

        $siswaIds = DB::table('pengumpulan_tugas')
            ->where('tugas_id', $tugas->id)
            ->whereNotNull('nilai')
            ->distinct()
            ->pluck('siswa_id');

        $jumlah = 0;
        foreach ($siswaIds as $siswaId) {
            if ($this->periksaPertemuan((int) $siswaId, (int) $tugas->pertemuan_id)) {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /**
     * Idempotensi khusus per-pertemuan: tidak dipengaruhi remedial risiko
     * global, supaya satu pertemuan lemah tidak diblokir remedial siswa lain.
     */
    public function alasanDilewatiPertemuan(int $siswaId, int $pertemuanId): ?string
    {
        if (! Pertemuan::whereKey($pertemuanId)->exists()) {
            return 'data pertemuan tidak ditemukan';
        }

        $masihDikerjakan = MateriRemedial::where('siswa_id', $siswaId)
            ->where('pertemuan_id', $pertemuanId)
            ->where('status', '!=', 'selesai')
            ->exists();

        if ($masihDikerjakan) {
            return 'masih ada remedial pertemuan yang belum diselesaikan';
        }

        $cooldown = (int) config('services.remedial.cooldown_hari', 7);

        if ($cooldown > 0) {
            $terbaruBaru = MateriRemedial::where('siswa_id', $siswaId)
                ->where('pertemuan_id', $pertemuanId)
                ->whereNotNull('generated_at')
                ->where('generated_at', '>=', now()->subDays($cooldown))
                ->exists();

            if ($terbaruBaru) {
                return "remedial pertemuan dibuat dalam {$cooldown} hari terakhir";
            }
        }

        return null;
    }

    protected function ambangPertemuan(): float
    {
        return (float) config('services.remedial.ambang_pertemuan', 75);
    }

    protected function bobotTugas(): float
    {
        return (float) config('services.remedial.bobot_tugas', 0.8);
    }

    protected function bobotKuis(): float
    {
        return (float) config('services.remedial.bobot_kuis', 0.2);
    }

    protected function notifyGuru(PrediksiAi $prediksi): void
    {
        $guru = $this->guruPengampu($prediksi->siswa_id);

        if (! $guru) {
            return;
        }



        $guru->notify(new RisikoTinggiDetected(
            $prediksi->siswa?->name ?: 'Siswa',
            (float) ($prediksi->skor_risiko ?? 0),
            $prediksi->id,
        ));
    }

     
    protected function guruPengampu(int $siswaId): ?User
    {








        $kelasIds = DB::table('kelas_siswa')
            ->where('siswa_id', $siswaId)
            ->where('status', 'active')
            ->pluck('kelas_id');

        if ($kelasIds->isEmpty()) {
            return null;
        }

        return User::where('role', 'pengajar')
            ->whereHas('kelas', fn ($q) => $q->whereIn('kelas.id', $kelasIds))
            ->orderBy('id')
            ->first();
    }

     
    public function generateForPrediksi(PrediksiAi $prediksi): ?MateriRemedial
    {
        if ($prediksi->hasil !== 'berisiko_tinggi') {
            return null;
        }

        $siswa = $prediksi->siswa;
        if (! $siswa) {
            return null;
        }

        $alasan = $this->alasanDilewati($prediksi);
        if ($alasan !== null) {
            Log::info('generateForPrediksi dilewati (DB guard)', [
                'siswa_id' => $prediksi->siswa_id,
                'alasan' => $alasan,
            ]);

            return null;
        }

        $topikLemah = $this->getWeakTopics($siswa->id);
        $context = $this->buildContext($prediksi, $topikLemah);

        $prompt = <<<PROMPT
Anda adalah asisten AI untuk membuat materi remedial bagi siswa yang teridentifikasi berisiko tinggi.

Data siswa:
- Skor risiko: {$context['skor_risiko']}
- Fitur: {$context['feature_text']}
- Topik lemah: {$context['topik_text']}
- Nilai terendah: {$context['nilai_terendah']}

Buatkan materi remedial yang mencakup:
1. Ringkasan singkat (maks 200 kata)
2. Latihan soal (3-5 soal) dengan jawaban

Format JSON:
{"ringkasan": "...", "latihan": "soal dan jawaban"}
PROMPT;






        $providerUsed = 'ai_provider';
        try {
            $aiOutput = $this->ai->send($prompt);
        } catch (\Exception $e) {
            try {
                $aiOutput = $this->ai->sendLocalPreferred($prompt);
                $providerUsed = 'ollama';
            } catch (\Exception $e2) {
                Log::warning('Remedial: semua provider AI gagal.', [
                    'msg' => $e2->getMessage(),
                ]);

                return null;
            }
        }

        $parsedRemedial = $this->parseResponse($aiOutput);

        if ($parsedRemedial === null) {


            return null;
        }

        $alasanAkhir = $this->alasanDilewati($prediksi);
        if ($alasanAkhir !== null) {
            Log::info('generateForPrediksi dilewati sebelum create (DB guard final)', [
                'siswa_id' => $siswa->id,
                'alasan' => $alasanAkhir,
            ]);

            return null;
        }

        $remedial = MateriRemedial::create([
            'siswa_id' => $siswa->id,
            'prediksi_ai_id' => $prediksi->id,
            'topik_lemah' => $topikLemah,
            'ringkasan' => $parsedRemedial['ringkasan'],
            'latihan' => $parsedRemedial['latihan'],
            'provider_llm' => $providerUsed,
            'status' => 'belum_dibaca',
            'generated_at' => now(),
        ]);

        $siswa->notify(new RemedialTersedia($remedial->id));

        return $remedial;
    }

     
    /**
     * Remedial khusus satu pertemuan: konteks dibatasi materi pertemuan itu.
     */
    public function generateForPertemuan(int $siswaId, int $pertemuanId, ?float $skorPertemuan): ?MateriRemedial
    {
        $siswa = User::find($siswaId);
        $pertemuan = Pertemuan::find($pertemuanId);

        if (! $siswa || ! $pertemuan) {
            return null;
        }

        $alasan = $this->alasanDilewatiPertemuan($siswaId, $pertemuanId);
        if ($alasan !== null) {
            Log::info('generateForPertemuan dilewati (DB guard)', [
                'siswa_id' => $siswaId,
                'pertemuan_id' => $pertemuanId,
                'alasan' => $alasan,
            ]);

            return null;
        }

        $topikLemah = $this->topikPertemuan($siswaId, $pertemuanId, $pertemuan);

        $namaPertemuan = (string) ($pertemuan->nama_pertemuan ?? 'Pertemuan');
        $skorText = $skorPertemuan === null ? 'belum ada nilai' : round($skorPertemuan, 1).' dari 100';
        $ambang = round($this->ambangPertemuan(), 1);

        $prompt = <<<PROMPT
Anda adalah asisten AI untuk membuat materi remedial bagi siswa yang nilainya rendah pada SATU pertemuan tertentu.

Data:
- Pertemuan: {$namaPertemuan}
- Skor siswa pada pertemuan ini: {$skorText} (ambang batas {$ambang})
- Topik lemah pada pertemuan ini: {$topikLemah}

Buatkan materi remedial yang mencakup:
1. Ringkasan singkat (maks 200 kata) yang fokus pada topik pertemuan ini
2. Latihan soal (3-5 soal) dengan jawaban

Format JSON:
{"ringkasan": "...", "latihan": "soal dan jawaban"}
PROMPT;

        // Retry: model kadang membalas prosa/JSON terpotong, jadi output yang
        // gagal di-parse tidak boleh langsung membuang remedial siswa.
        $providerUsed = 'ai_provider';
        $parsedRemedial = null;

        for ($percobaan = 1; $percobaan <= $this->maksPercobaanAi; $percobaan++) {
            try {
                $aiOutput = $this->ai->send($prompt);
                $providerUsed = 'ai_provider';
            } catch (\Exception $e) {
                try {
                    $aiOutput = $this->ai->sendLocalPreferred($prompt);
                    $providerUsed = 'ollama';
                } catch (\Exception $e2) {
                    Log::warning('Remedial pertemuan: semua provider AI gagal.', [
                        'siswa_id' => $siswaId,
                        'pertemuan_id' => $pertemuanId,
                        'percobaan' => $percobaan,
                        'msg' => $e2->getMessage(),
                    ]);

                    continue;
                }
            }

            $parsedRemedial = $this->parseResponse($aiOutput);

            if ($parsedRemedial !== null) {
                break;
            }

            Log::warning('Remedial pertemuan: output AI tidak sesuai format, mencoba lagi.', [
                'siswa_id' => $siswaId,
                'pertemuan_id' => $pertemuanId,
                'percobaan' => $percobaan,
                'cuplikan' => mb_substr(trim((string) $aiOutput), 0, 160),
            ]);
        }

        if ($parsedRemedial === null) {
            Log::warning('Remedial pertemuan: gagal setelah semua percobaan, materi dibatalkan.', [
                'siswa_id' => $siswaId,
                'pertemuan_id' => $pertemuanId,
            ]);

            return null;
        }

        $alasanAkhir = $this->alasanDilewatiPertemuan($siswaId, $pertemuanId);
        if ($alasanAkhir !== null) {
            Log::info('generateForPertemuan dilewati sebelum create (DB guard final)', [
                'siswa_id' => $siswaId,
                'pertemuan_id' => $pertemuanId,
                'alasan' => $alasanAkhir,
            ]);

            return null;
        }

        $remedial = MateriRemedial::create([
            'siswa_id' => $siswaId,
            'prediksi_ai_id' => null,
            'pertemuan_id' => $pertemuanId,
            'sumber' => 'pertemuan',
            'skor_pertemuan' => $skorPertemuan,
            'topik_lemah' => $topikLemah,
            'ringkasan' => $parsedRemedial['ringkasan'],
            'latihan' => $parsedRemedial['latihan'],
            'provider_llm' => $providerUsed,
            'status' => 'belum_dibaca',
            'generated_at' => now(),
        ]);

        $siswa->notify(new RemedialTersedia($remedial->id));

        return $remedial;
    }

    /**
     * Topik lemah dari materi yang benar-benar ada di pertemuan itu; kalau tak
     * ada satupun yang punya nilai, pakai daftar materi pertemuan sebagai
     * konteks, lalu nama pertemuan sebagai jalan terakhir.
     */
    protected function topikPertemuan(int $siswaId, int $pertemuanId, Pertemuan $pertemuan): string
    {
        try {
            $label = $this->labelSkorTerendah(
                $this->skorPerMateriPertemuan($siswaId, $pertemuanId),
                'materi'
            );

            if ($label !== null) {
                return $label;
            }
        } catch (\Throwable $e) {
            Log::warning('Remedial pertemuan: gagal menghitung topik lemah: {msg}', [
                'siswa_id' => $siswaId,
                'pertemuan_id' => $pertemuanId,
                'msg' => $e->getMessage(),
            ]);
        }

        $judul = Materi::where('pertemuan_id', $pertemuanId)
            ->orderBy('id')
            ->limit($this->maksTopikLemah)
            ->pluck('judul')
            ->all();

        if ($judul !== []) {
            return implode(', ', $judul);
        }

        return trim((string) ($pertemuan->nama_pertemuan ?? '')) ?: 'Belum teridentifikasi';
    }

    /**
     * Rata-rata skor per materi, dibatasi pada pertemuan tertentu.
     *
     * @return array<int, float>
     */
    protected function skorPerMateriPertemuan(int $siswaId, int $pertemuanId): array
    {
        $skorKuis = $this->rataPerMateri(
            DB::table('kuis_hasil')
                ->join('kuis', 'kuis.id', '=', 'kuis_hasil.kuis_id')
                ->leftJoin('kuis_materi', 'kuis_materi.kuis_id', '=', 'kuis_hasil.kuis_id')
                ->where('kuis_hasil.siswa_id', $siswaId)
                ->where('kuis.pertemuan_id', $pertemuanId)
                ->selectRaw('kuis.materi_id as materi_utama, kuis_materi.materi_id as materi_pendukung, kuis_hasil.skor as skor')
                ->get()
        );

        $skorTugas = $this->rataPerMateri(
            DB::table('pengumpulan_tugas')
                ->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')
                ->leftJoin('tugas_materi', 'tugas_materi.tugas_id', '=', 'pengumpulan_tugas.tugas_id')
                ->where('pengumpulan_tugas.siswa_id', $siswaId)
                ->where('tugas.pertemuan_id', $pertemuanId)
                ->whereNotNull('pengumpulan_tugas.nilai')
                ->selectRaw('tugas.materi_id as materi_utama, tugas_materi.materi_id as materi_pendukung, pengumpulan_tugas.nilai as skor')
                ->get()
        );

        return $this->gabungRata($skorKuis, $skorTugas);
    }

    protected function getWeakTopics(int $siswaId): string
    {
        foreach (['materi', 'pertemuan'] as $sumber) {
            try {
                $label = $this->labelSkorTerendah(
                    $sumber === 'materi'
                        ? $this->skorPerMateri($siswaId)
                        : $this->skorPerPertemuan($siswaId),
                    $sumber
                );

                if ($label !== null) {
                    return $label;
                }
            } catch (\Throwable $e) {
                Log::warning('RemedialService: gagal menghitung topik lemah dari nilai {sumber}: {msg}', [
                    'sumber' => $sumber,
                    'msg' => $e->getMessage(),
                ]);
            }
        }

        return $this->materiYangDibahas($siswaId);
    }

     
    protected function labelSkorTerendah(array $skorPerId, string $sumber): ?string
    {
        if ($skorPerId === []) {
            return null;
        }

        asort($skorPerId);

        $nama = $sumber === 'materi'
            ? Materi::whereIn('id', array_keys($skorPerId))->pluck('judul', 'id')
            : Pertemuan::whereIn('id', array_keys($skorPerId))->pluck('nama_pertemuan', 'id');

        $hasil = [];

        foreach (array_slice($skorPerId, 0, $this->maksTopikLemah, true) as $id => $skor) {
            $judul = $nama[$id] ?? null;

            if ($judul) {
                $hasil[] = $judul.' (rata-rata '.round((float) $skor, 1).')';
            }
        }

        return $hasil === [] ? null : implode(', ', $hasil);
    }

     
    protected function skorPerMateri(int $siswaId): array
    {
        $skorKuis = $this->rataPerMateri(
            DB::table('kuis_hasil')
                ->join('kuis', 'kuis.id', '=', 'kuis_hasil.kuis_id')
                ->leftJoin('kuis_materi', 'kuis_materi.kuis_id', '=', 'kuis_hasil.kuis_id')
                ->where('kuis_hasil.siswa_id', $siswaId)
                ->selectRaw('kuis.materi_id as materi_utama, kuis_materi.materi_id as materi_pendukung, kuis_hasil.skor as skor')
                ->get()
        );

        $skorTugas = $this->rataPerMateri(
            DB::table('pengumpulan_tugas')
                ->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')
                ->leftJoin('tugas_materi', 'tugas_materi.tugas_id', '=', 'pengumpulan_tugas.tugas_id')
                ->where('pengumpulan_tugas.siswa_id', $siswaId)
                ->whereNotNull('pengumpulan_tugas.nilai')
                ->selectRaw('tugas.materi_id as materi_utama, tugas_materi.materi_id as materi_pendukung, pengumpulan_tugas.nilai as skor')
                ->get()
        );

        return $this->gabungRata($skorKuis, $skorTugas);
    }

     
    protected function rataPerMateri($baris): array
    {
        $kumpulan = [];

        foreach ($baris as $b) {
            if ($b->skor === null) {
                continue;
            }

            $skor = (float) $b->skor;
            $ids = array_unique(array_filter(
                [(int) $b->materi_utama, (int) $b->materi_pendukung],
                static fn (int $id): bool => $id > 0
            ));

            foreach ($ids as $id) {
                $kumpulan[$id][] = $skor;
            }
        }

        $rata = [];
        foreach ($kumpulan as $id => $skor) {
            $rata[$id] = array_sum($skor) / count($skor);
        }

        return $rata;
    }

     
    protected function gabungRata(array $skorKuis, array $skorTugas): array
    {
        $gabungan = [];

        foreach ($skorKuis as $id => $skor) {
            $gabungan[$id]['sum'] = (float) $skor;
            $gabungan[$id]['jumlah'] = 1;
        }

        foreach ($skorTugas as $id => $skor) {
            if (! isset($gabungan[$id])) {
                $gabungan[$id]['sum'] = 0.0;
                $gabungan[$id]['jumlah'] = 0;
            }
            $gabungan[$id]['sum'] += (float) $skor;
            $gabungan[$id]['jumlah']++;
        }

        $rata = [];
        foreach ($gabungan as $id => $data) {
            if ($data['jumlah'] > 0) {
                $rata[(int) $id] = $data['sum'] / $data['jumlah'];
            }
        }

        return $rata;
    }

     
    protected function skorPerPertemuan(int $siswaId): array
    {
        $skorKuis = DB::table('kuis_hasil')
            ->join('kuis', 'kuis.id', '=', 'kuis_hasil.kuis_id')
            ->where('kuis_hasil.siswa_id', $siswaId)
            ->whereNotNull('kuis.pertemuan_id')
            ->groupBy('kuis.pertemuan_id')
            ->selectRaw('kuis.pertemuan_id as pertemuan_id, AVG(kuis_hasil.skor) as skor')
            ->pluck('skor', 'pertemuan_id')
            ->all();

        $skorTugas = DB::table('pengumpulan_tugas')
            ->join('tugas', 'tugas.id', '=', 'pengumpulan_tugas.tugas_id')
            ->where('pengumpulan_tugas.siswa_id', $siswaId)
            ->whereNotNull('pengumpulan_tugas.nilai')
            ->whereNotNull('tugas.pertemuan_id')
            ->groupBy('tugas.pertemuan_id')
            ->selectRaw('tugas.pertemuan_id as pertemuan_id, AVG(pengumpulan_tugas.nilai) as skor')
            ->pluck('skor', 'pertemuan_id')
            ->all();

        return $this->gabungRata($skorKuis, $skorTugas);
    }

     
    protected function materiYangDibahas(int $siswaId): string
    {
        try {
            $judul = DB::table('chat_logs')
                ->join('materi', 'materi.id', '=', 'chat_logs.materi_id')
                ->where('chat_logs.user_id', $siswaId)
                ->whereNotNull('chat_logs.materi_id')
                ->groupBy('chat_logs.materi_id', 'materi.judul')
                ->orderByDesc(DB::raw('COUNT(chat_logs.id)'))
                ->limit($this->maksTopikLemah)
                ->pluck('materi.judul')
                ->all();
        } catch (\Throwable $e) {
            Log::warning('RemedialService: gagal membaca materi yang dibahas: {msg}', ['msg' => $e->getMessage()]);

            return 'Belum teridentifikasi';
        }

        return $judul !== [] ? implode(', ', $judul) : 'Belum teridentifikasi';
    }

     
    protected function buildContext(PrediksiAi $prediksi, string $topikLemah): array
    {
        $features = $prediksi->feature_vector ?? [];
        $featureLabels = array_values(MLService::FEATURE_LABELS);

        $featureLines = [];
        foreach ($features as $i => $nilaiFitur) {
            $label = $featureLabels[$i] ?? "fitur_{$i}";
            $featureLines[] = "{$label}: {$nilaiFitur}";
        }

        $nilaiTerendah = NilaiHarian::where('siswa_id', $prediksi->siswa_id)
            ->min('nilai');

        return [
            'skor_risiko' => (string) ($prediksi->skor_risiko ?? 0),
            'feature_text' => implode(', ', $featureLines),
            'topik_text' => $topikLemah,
            'nilai_terendah' => (string) ($nilaiTerendah ?? 'Belum ada nilai'),
        ];
    }

     
    protected function parseResponse(string $llmOutput): ?array
    {
        $teksBersih = trim($llmOutput);

        if ($teksBersih === '') {
            Log::warning('Remedial: output AI kosong, materi dibatalkan.');

            return null;
        }

        if (preg_match('/\{.*"ringkasan".*\}/s', $teksBersih, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $ringkasan = trim((string) ($json['ringkasan'] ?? ''));

                if ($ringkasan === '') {
                    Log::warning('Remedial: JSON tanpa isi ringkasan, materi dibatalkan.');

                    return null;
                }

                return [
                    'ringkasan' => $ringkasan,
                    'latihan' => $this->flattenLatihan($json['latihan'] ?? ''),
                ];
            }
        }

        $ringkasan = '';
        $latihan = '';

        if (preg_match('/Ringkasan[:\s](.*?)(?=Latihan|$)/su', $teksBersih, $m)) {
            $ringkasan = trim($m[1]);
        }
        if (preg_match('/Latihan[:\s](.*?)$/su', $teksBersih, $m)) {
            $latihan = trim($m[1]);
        }

        if ($ringkasan === '') {
            Log::warning('Remedial: output AI tidak sesuai format JSON maupun "Ringkasan:". Materi dibatalkan.', [
                'cuplikan' => mb_substr($teksBersih, 0, 160),
            ]);

            return null;
        }

        return [
            'ringkasan' => $ringkasan,
            'latihan' => $latihan,
        ];
    }

     
    protected function flattenLatihan(mixed $latihan): string
    {
        if (is_string($latihan)) {
            return trim($latihan);
        }

        if (! is_array($latihan)) {
            return '';
        }

        $bagian = [];
        foreach ($latihan as $kunci => $nilai) {
            if (is_array($nilai) || is_object($nilai)) {
                $nilai = json_encode($nilai, JSON_UNESCAPED_UNICODE);
            }
            $nilai = trim((string) $nilai);

            if ($nilai !== '') {
                $bagian[] = $nilai;
            }
        }

        return implode("\n", $bagian);
    }
}

