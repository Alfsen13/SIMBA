<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru\Concerns;

use App\Models\Kelas;
use App\Rules\KonteksChunkMilikGuru;
use App\Support\KonteksBatas;
use Illuminate\Validation\Rule;

trait ValidatesKonteksSoal
{
    protected function konteksSoalRules(): array
    {
        $kelasIds = $this->kelasMilikGuru();

        return [
            'rag_top_k' => ['nullable', 'integer', 'min:1', 'max:10'],
            'konteks_pertemuan_ids' => ['nullable', 'array', 'max:'.KonteksBatas::PERTEMUAN],
            'konteks_pertemuan_ids.*' => [
                'integer',
                Rule::exists('pertemuan', 'id')->whereIn('kelas_id', $kelasIds),
            ],
            'konteks_materi_ids' => ['nullable', 'array', 'max:'.KonteksBatas::MATERI],
            'konteks_materi_ids.*' => [
                'integer',
                Rule::exists('materi', 'id')->whereIn('kelas_id', $kelasIds),
            ],
            'konteks_chunk_ids' => [
                'nullable',
                'array',
                'max:'.KonteksBatas::CHUNK,
                new KonteksChunkMilikGuru((int) $this->user()?->id),
            ],
        ];
    }

    public function sumberKonteks(): array
    {
        $sumber = [
            'pertemuan_ids' => $this->idValid($this->input('konteks_pertemuan_ids')),
            'materi_ids' => $this->idValid($this->input('konteks_materi_ids')),
            'chunk_ids' => $this->idValid($this->input('konteks_chunk_ids')),
        ];

        if ($this->validated('rag_top_k') !== null) {
            $sumber['rag_top_k'] = (int) $this->validated('rag_top_k');
        }

        return $sumber;
    }

    /**
     * Ganti field picker menjadi satu nilai kolom konteks_soal. Field mentah
     * tidak boleh ikut di-fill ke model karena tidak ada kolomnya.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function terapkanKonteksSoal(array $validated): array
    {
        unset(
            $validated['konteks_pertemuan_ids'],
            $validated['konteks_materi_ids'],
            $validated['konteks_chunk_ids'],
            $validated['rag_top_k'],
        );

        $sumber = $this->sumberKonteks();

        $kosong = ! isset($sumber['rag_top_k'])
            && $sumber['pertemuan_ids'] === []
            && $sumber['materi_ids'] === []
            && $sumber['chunk_ids'] === [];

        $validated['konteks_soal'] = $kosong ? null : $sumber;

        return $validated;
    }

    protected function idValid(mixed $nilai): array
    {
        if (! is_array($nilai)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $nilai),
            fn (int $id) => $id > 0
        )));
    }

    protected function kelasMilikGuru(): array
    {
        return Kelas::query()
            ->where('guru_id', $this->user()?->id)
            ->pluck('id')
            ->all();
    }
}
