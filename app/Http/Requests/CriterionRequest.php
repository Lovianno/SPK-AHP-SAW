<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => $this->createRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            default => [],
        };
    }

    protected function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'attribute' => ['required', Rule::in(['benefit', 'cost'])],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function updateRules(): array
    {
        $rules = $this->createRules();

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kriteria wajib diisi.',
            'name.string' => 'Nama kriteria harus berupa teks.',
            'name.max' => 'Nama kriteria maksimal 100 karakter.',
            'attribute.required' => 'Atribut kriteria wajib dipilih.',
            'attribute.in' => 'Atribut kriteria harus benefit atau cost.',
            'unit.string' => 'Satuan harus berupa teks.',
            'unit.max' => 'Satuan maksimal 50 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
