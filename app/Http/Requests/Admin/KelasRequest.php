<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        $mapelRule = ['nullable', 'integer', Rule::exists('mata_pelajaran', 'id')];

        if ($user && $user->role === 'operator' && $user->kategori_mapel) {
            $mapelRule[] = Rule::exists('mata_pelajaran', 'id')->where(function ($q) use ($user) {
                $q->where('kategori', $user->kategori_mapel);
            });
        }

        return [
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'nullable|string|max:100',
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
}
