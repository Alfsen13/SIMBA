<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,csv,txt|extensions:xlsx,csv,txt|max:5120',
        ];
    }
}
