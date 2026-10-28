<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return [
            'message' => 'required|string',
        ];
    }
}

