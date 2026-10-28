<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

      
    public function rules(): array
    {
        return [
            'materi_id' => ['nullable', 'integer', 'exists:materi,id'],
            'pertemuan_id' => ['required', 'integer', 'exists:pertemuan,id'],
        ];
    }
}

