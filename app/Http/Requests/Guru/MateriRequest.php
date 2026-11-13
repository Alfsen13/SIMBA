<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesKelasDanPertemuan;
use App\Rules\MathLatexValid;
use Illuminate\Foundation\Http\FormRequest;

class MateriRequest extends FormRequest
{
    use ValidatesKelasDanPertemuan;

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
            'file' => 'nullable|file|mimes:pdf,txt,md,csv,docx,pptx,xlsx|extensions:pdf,txt,md,csv,docx,pptx,xlsx|max:10240',
        ];
    }
}
