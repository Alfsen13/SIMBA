<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Rules\MathLatexValid;
use Illuminate\Foundation\Http\FormRequest;

class PertemuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pertemuan' => 'required|string|max:255',
            'urutan' => 'required|integer|min:0',
            'deskripsi' => ['nullable', 'string', new MathLatexValid],
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'dibuka' => 'sometimes|boolean',
        ];
    }
}
