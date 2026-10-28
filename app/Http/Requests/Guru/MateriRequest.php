<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class MateriRequest extends FormRequest
{
     
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
            'file' => 'nullable|file|max:10240',
        ];
    }
}

