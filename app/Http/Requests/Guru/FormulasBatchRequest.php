<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

abstract class FormulasBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected' => ['required', 'array', 'max:50'],
            'selected.*' => ['integer'],
        ];
    }

    /**
     * Id yang dicentang guru. Nilai non-angka dibuang supaya tidak bisa dipakai
     * sebagai kunci aturan validasi.
     *
     * @return list<int>
     */
    public function selectedIds(): array
    {
        $ids = [];

        foreach ((array) $this->input('selected', []) as $nilai) {
            if (is_numeric($nilai) && (int) $nilai > 0) {
                $ids[] = (int) $nilai;
            }
        }

        return array_values(array_unique($ids));
    }
}
