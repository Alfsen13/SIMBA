<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesKelasDanPertemuan;
use App\Http\Requests\Guru\Concerns\ValidatesKonteksSoal;
use App\Http\Requests\Guru\Concerns\ValidatesMateriUtama;
use App\Rules\MathLatexValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TugasRequest extends FormRequest
{
    use ValidatesKelasDanPertemuan;
    use ValidatesKonteksSoal;
    use ValidatesMateriUtama;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'deskripsi' => ['nullable', 'string', new MathLatexValid],
            'kelas_id' => $this->kelasGuruRule(),
            'pertemuan_id' => $this->pertemuanGuruRule(true),
            'tipe' => ['required', Rule::in(['upload', 'pilihan_ganda', 'essai'])],
            'soal' => ['nullable', 'json', Rule::requiredIf($this->input('tipe') === 'essai')],
            'deadline' => 'nullable|date',
            'batas_jam' => 'nullable|integer|min:1|max:24',
        ] + $this->materiUtamaRules() + $this->konteksSoalRules();
    }
}
