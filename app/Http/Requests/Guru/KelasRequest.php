<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

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
        return [
            'nama_kelas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'nullable|string|max:100',
            'aturan_kelayakan' => ['nullable', Rule::in(['sesuai_profil', 'sesuai_jenjang', 'terbuka', 'khusus'])],
        ];
    }
}
