<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class NilaiTugasRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return [
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|integer|min:0|max:100',
        ];
    }
}

