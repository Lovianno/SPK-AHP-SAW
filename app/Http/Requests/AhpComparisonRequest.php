<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AhpComparisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'more_important' => ['required', Rule::in(['a', 'b', 'equal'])],
            'intensity' => ['required', 'integer', 'between:1,9'],
        ];

        if ($this->isMethod('post')) {
            $rules['criterion_a_id'] = ['required', 'integer', 'exists:criteria,id', 'different:criterion_b_id'];
            $rules['criterion_b_id'] = ['required', 'integer', 'exists:criteria,id', 'different:criterion_a_id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'criterion_a_id.required' => 'Kriteria pertama wajib dipilih.',
            'criterion_b_id.required' => 'Kriteria kedua wajib dipilih.',
            'criterion_a_id.exists' => 'Kriteria pertama tidak valid.',
            'criterion_b_id.exists' => 'Kriteria kedua tidak valid.',
            'criterion_a_id.different' => 'Kedua kriteria harus berbeda.',
            'criterion_b_id.different' => 'Kedua kriteria harus berbeda.',
            'more_important.required' => 'Pilih kriteria yang lebih penting.',
            'more_important.in' => 'Pilihan kepentingan tidak valid.',
            'intensity.required' => 'Intensitas wajib dipilih.',
            'intensity.integer' => 'Intensitas harus bilangan bulat.',
            'intensity.between' => 'Intensitas harus antara 1 sampai 9.',
        ];
    }
}
