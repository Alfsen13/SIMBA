<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriRemedial extends Model
{
    protected $table = 'materi_remedial';

    protected $fillable = [
        'siswa_id',
        'prediksi_ai_id',
        'pertemuan_id',
        'sumber',
        'skor_pertemuan',
        'topik_lemah',
        'ringkasan',
        'latihan',
        'bloom_tujuan',
        'tugas_remedial',
        'jawaban_remedial',
        'hasil_remedial',
        'bloom_tercapai',
        'mastery_alasan',
        'dinilai_at',
        'provider_llm',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'bloom_tujuan' => 'array',
            'tugas_remedial' => 'array',
            'jawaban_remedial' => 'array',
            'hasil_remedial' => 'array',
            'bloom_tercapai' => 'boolean',
            'skor_pertemuan' => 'float',
            'dinilai_at' => 'datetime',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * Isi kolom `latihan` (JSON per baris / array JSON) sebagai daftar tanya-jawab.
     * Mengembalikan [] bila teksnya bukan JSON, supaya view bisa jatuh ke teks mentah.
     *
     * @return array<int, array{soal: string, jawaban: string}>
     */
    public function getLatihanItemsAttribute(): array
    {
        $raw = trim((string) ($this->attributes['latihan'] ?? ''));

        if ($raw === '') {
            return [];
        }

        $baris = [];

        if (preg_match('/^[\[{]/', $raw) === 1) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $baris = array_is_list($decoded) ? $decoded : [$decoded];
            }
        }

        if ($baris === []) {
            foreach (preg_split('/\R/', $raw) ?: [] as $line) {
                $decoded = json_decode(trim($line), true);
                if (is_array($decoded)) {
                    $baris[] = $decoded;
                }
            }
        }

        $ambil = static fn ($nilai): string => is_scalar($nilai) ? trim((string) $nilai) : '';

        $hasil = [];
        $dilihat = [];
        foreach ($baris as $row) {
            if (! is_array($row)) {
                continue;
            }

            $soal = $ambil($row['soal'] ?? $row['pertanyaan'] ?? $row['question'] ?? '');
            $jawaban = $ambil($row['jawaban'] ?? $row['jawaban_referensi'] ?? $row['kunci'] ?? $row['answer'] ?? '');

            if ($soal === '' && $jawaban === '') {
                continue;
            }

            $kunci = mb_strtolower($soal)."\x1f".mb_strtolower($jawaban);
            if (isset($dilihat[$kunci])) {
                continue;
            }
            $dilihat[$kunci] = true;

            $hasil[] = ['soal' => $soal, 'jawaban' => $jawaban];
        }

        return $hasil;
    }

     
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

     
    public function prediksiAi(): BelongsTo
    {
        return $this->belongsTo(PrediksiAi::class);
    }

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class);
    }
}

