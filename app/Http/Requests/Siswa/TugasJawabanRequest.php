<?php

declare(strict_types=1);

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class TugasJawabanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tugas = $this->route('tugas');
        $rules = [];

        if ($tugas && $tugas->tipe === 'upload') {
            $rules['file'] = 'required|file|mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png|max:10240';
        } else {
            $rules['jawaban'] = 'required|array';
            $rules['jawaban.*'] = 'required|string|max:8000';
        }

        return $rules;
    }
}
