<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendChatRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

      
    public function rules(): array
    {
        return [
            'sesi_id' => ['required', 'uuid'],
            'materi_id' => ['nullable', 'integer', 'exists:materi,id'],
            'pertemuan_id' => ['required', 'integer', 'exists:pertemuan,id'],
            'pesan' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pesan.max' => 'Pesan maksimal 2000 karakter.',
        ];
    }
}

