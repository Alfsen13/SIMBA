<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru\Concerns;

use App\Models\Kelas;
use App\Support\KonteksBatas;
use Illuminate\Validation\Rule;

trait ValidatesKonteksSoal
{
    protected function konteksSoalRules(): array
    {
        $kelasIds = $this->kelasMilikGuru();

        return [
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
        ];
    }

    public function sumberKonteks(): array
    {
        return [
            'pertemuan_ids' => $this->idValid($this->input('konteks_pertemuan_ids')),
            'materi_ids' => $this->idValid($this->input('konteks_materi_ids')),
        ];
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
        unset($validated['konteks_pertemuan_ids'], $validated['konteks_materi_ids']);

        $sumber = $this->sumberKonteks();

        $kosong = $sumber['pertemuan_ids'] === [] && $sumber['materi_ids'] === [];

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
