<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Http\Requests\Guru\Concerns\ValidatesKelasDanPertemuan;
use App\Http\Requests\Guru\Concerns\ValidatesMateriUtama;
use App\Rules\MathLatexSoalValid;
use Illuminate\Foundation\Http\FormRequest;

class KuisRequest extends FormRequest
{
    use ValidatesKelasDanPertemuan;
    use ValidatesMateriUtama;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'kelas_id' => $this->kelasGuruRule(),
            'pertemuan_id' => $this->pertemuanGuruRule(true),
            'soal' => ['required', 'json', new MathLatexSoalValid],
            'durasi_menit' => 'required|integer|min:1',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after:tanggal_mulai',
        ] + $this->materiUtamaRules();
    }
}
