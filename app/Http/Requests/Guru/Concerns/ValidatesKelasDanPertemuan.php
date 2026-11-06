<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesKelasDanPertemuan
{
    protected function kelasGuruRule(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('kelas', 'id')->where('guru_id', $this->user()?->id),
        ];
    }

    protected function pertemuanGuruRule(bool $required): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::exists('pertemuan', 'id')->where('kelas_id', $this->input('kelas_id')),
        ];

        return $rules;
    }
}
