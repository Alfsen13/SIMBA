<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Models\Kehadiran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KehadiranRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return [
            'status' => 'required|array',
            'status.*' => ['required', Rule::in(Kehadiran::STATUS)],
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:255',
        ];
    }
}

