<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$userId,
            'role' => 'required|in:pengajar,pelajar,operator',
        ];
    }
}

