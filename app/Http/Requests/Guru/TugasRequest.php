<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesMateriUtama;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TugasRequest extends FormRequest
{
    use ValidatesMateriUtama;

     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kelas_id' => 'required|exists:kelas,id',
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'tipe' => ['required', Rule::in(['upload', 'pilihan_ganda', 'essai'])],
            'soal' => ['nullable', 'json', Rule::requiredIf($this->input('tipe') === 'essai')],
            'deadline' => 'nullable|date',
            'batas_jam' => 'nullable|integer|min:1|max:24',
        ] + $this->materiUtamaRules();
    }
}

