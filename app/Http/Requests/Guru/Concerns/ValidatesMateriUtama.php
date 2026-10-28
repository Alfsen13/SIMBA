<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

 
trait ValidatesMateriUtama
{
     
    public function materiUtamaRules(): array
    {
        return [
            'materi_id' => $this->materiUtamaRule(),

            'materi_ids' => ['nullable', 'array', 'max:60'],
            'materi_ids.*' => $this->materiRule(),
        ];
    }

     
    protected function materiUtamaRule(): array
    {
        $rule = Rule::exists('materi', 'id')->whereIn('kelas_id', $this->kelasIdsGuru());

        if ($this->filled('pertemuan_id')) {
            $rule = $rule->where('pertemuan_id', $this->input('pertemuan_id'));
        }

        return ['nullable', 'integer', $rule];
    }

     
    protected function materiRule(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('materi', 'id')->whereIn('kelas_id', $this->kelasIdsGuru()),
        ];
    }

     
    protected function kelasIdsGuru()
    {
        return $this->user()?->kelas()->pluck('kelas.id') ?? collect();
    }

     
    public function materiPendukungIds(array $validated): array
    {
        $ids = $validated['materi_ids'] ?? [];

        if (! is_array($ids)) {
            return [];
        }

        $bersih = array_values(array_unique(array_filter(
            array_map(static fn ($id) => (int) $id, $ids),
            static fn (int $id) => $id > 0
        )));


        return array_values(array_diff($bersih, [(int) ($validated['materi_id'] ?? 0)]));
    }
}

