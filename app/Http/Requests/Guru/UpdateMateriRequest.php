<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'bab' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'pertemuan_id' => 'nullable|exists:pertemuan,id',
            'file' => 'nullable|file|max:10240',
        ];
    }
}

