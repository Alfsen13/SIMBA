<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesKelasDanPertemuan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriRequest extends FormRequest
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
            'bab' => 'nullable|string|max:255',
            'kelas_id' => $this->kelasGuruRule(),
            'pertemuan_id' => $this->pertemuanGuruRule(false),
            'file' => 'nullable|file|mimes:pdf,txt,md,csv,docx,pptx,xlsx|extensions:pdf,txt,md,csv,docx,pptx,xlsx|max:10240',
        ];
    }
}
