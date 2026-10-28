<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EndChatRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

      
    public function rules(): array
    {
        return [
            'sesi_id' => ['required', 'uuid'],
        ];
    }
}

