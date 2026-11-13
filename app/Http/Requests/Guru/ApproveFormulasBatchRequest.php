<?php

declare(strict_types=1);

namespace App\Http\Requests\Guru;

use App\Rules\MathLatexValid;

class ApproveFormulasBatchRequest extends FormulasBatchRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Hanya baris yang terpilih yang perlu LaTeX-nya diperiksa.
        foreach ($this->selectedIds() as $id) {
            $rules["reviews.$id.latex"] = ['nullable', 'string', 'max:2000', 'not_regex:/\\$/', new MathLatexValid(rumus: true)];
        }

        return $rules;
    }
}
