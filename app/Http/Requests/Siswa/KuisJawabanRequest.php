<?php

declare(strict_types=1);

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class KuisJawabanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable|string',
        ];
    }
}
