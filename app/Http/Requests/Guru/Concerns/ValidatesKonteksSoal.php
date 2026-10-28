<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru\Concerns;

use App\Models\Kelas;
use Illuminate\Validation\Rule;

 
trait ValidatesKonteksSoal
{
     
    protected function konteksSoalRules(): array
    {
        $kelasIds = $this->kelasMilikGuru();

        return [
            'konteks_pertemuan_ids' => ['nullable', 'array', 'max:12'],
            'konteks_pertemuan_ids.*' => [
                'integer',
                Rule::exists('pertemuan', 'id')->whereIn('kelas_id', $kelasIds),
            ],
            'konteks_materi_ids' => ['nullable', 'array', 'max:60'],
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

