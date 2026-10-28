<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesKonteksSoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateSoalTugasRequest extends FormRequest
{
    use ValidatesKonteksSoal;

     
    public function authorize(): bool
    {
        return true;
    }

     
    public function rules(): array
    {
        return array_merge([
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'tipe' => ['required', Rule::in(['pilihan_ganda', 'essai'])],
            'jumlah' => 'required|integer|min:1|max:12',
            'kesulitan' => 'required|in:mudah,sedang,sulit',
            'topik' => 'nullable|string|max:150',
        ], $this->konteksSoalRules());
    }
}

