<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class FiturSiswaRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return [
            'rata_nilai' => 'nullable|numeric|min:0|max:100',
            'persen_terlambat' => 'nullable|numeric|min:0|max:100',
            'persen_terlewat' => 'nullable|numeric|min:0|max:100',
            'nilai_keseluruhan' => 'nullable|numeric|min:0|max:100',
        ];
    }
}

