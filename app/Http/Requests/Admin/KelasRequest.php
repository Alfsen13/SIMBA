<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\MataPelajaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class KelasRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user?->role === 'operator' && $user->kategori_mapel && ! $this->filled('kategori')) {
            $this->merge(['kategori' => $user->kategori_mapel]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        $mapelRule = ['nullable', 'integer', Rule::exists('mata_pelajaran', 'id')];
        $kategoriRule = ['nullable', 'string', 'max:100'];

        if ($user && $user->role === 'operator') {
            $kategoriRule = ['required', 'string', 'max:100', Rule::in(array_filter([$user->kategori_mapel]))];

            if ($user->kategori_mapel) {
                $mapelRule[] = Rule::exists('mata_pelajaran', 'id')->where(function ($q) use ($user) {
                    $q->where('kategori', $user->kategori_mapel);
                });
            }
        }

        return [
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => $kategoriRule,
            'mata_pelajaran_id' => $mapelRule,
            'jalur_id' => ['nullable', 'integer', Rule::exists('jalur_pendidikan', 'id')],
            'jenjang_id' => ['required', 'integer', Rule::exists('jenjang_pendidikan', 'id')],
            'tingkat_id' => ['required', 'integer', Rule::exists('tingkat_pendidikan', 'id')],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'status' => ['nullable', Rule::in(['draft', 'dibuka', 'ditutup', 'arsip'])],
            'aturan_kelayakan' => ['nullable', Rule::in(['sesuai_profil', 'sesuai_jenjang', 'terbuka', 'khusus'])],
            'periode' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        if ($this->user()?->role === 'operator' && ! $this->user()->kategori_mapel) {
            return [
                'kategori.required' => __('operator.kelas_operator_tanpa_kategori'),
                'kategori.in' => __('operator.kelas_operator_tanpa_kategori'),
            ];
        }

        return [
            'kategori.in' => __('operator.kelas_kategori_di_luar_tugas'),
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('mata_pelajaran_id') || $validator->errors()->has('kategori')) {
                    return;
                }

                $mapelId = $this->input('mata_pelajaran_id');
                if (! $mapelId) {
                    return;
                }

                $mapel = MataPelajaran::find($mapelId);
                if ($mapel && $this->input('kategori') !== $mapel->kategori) {
                    $validator->errors()->add('kategori', 'Kategori kelas harus sesuai dengan kategori mata pelajaran.');
                }
            },
        ];
    }
}
