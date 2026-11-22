<?php

namespace App\Http\Requests;

use App\Models\Criterion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlternativeRequest extends FormRequest
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
        $criteria = Criterion::orderBy('id')->get();
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'scores' => [$criteria->isEmpty() ? 'sometimes' : 'required', Rule::array($criteria->modelKeys()), 'size:'.$criteria->count()],
            'scores.*' => ['required', 'numeric', 'between:1,100', 'decimal:0,4'],
        ];

        foreach ($criteria as $criterion) {
            $rules['scores.'.$criterion->id] = ['required', 'numeric', 'between:1,100', 'decimal:0,4'];
        }

        return $rules;
    }

    protected function updateRules(): array
    {
        return $this->createRules();
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama alternatif wajib diisi.',
            'name.string' => 'Nama alternatif harus berupa teks.',
            'name.max' => 'Nama alternatif maksimal 150 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'scores.required' => 'Isi skor untuk seluruh kriteria.',
            'scores.size' => 'Isi skor sesuai seluruh kriteria yang tersedia.',
            'scores.array' => 'Daftar kriteria tidak valid. Muat ulang formulir dan coba lagi.',
            'scores.*.required' => 'Skor kriteria wajib diisi.',
            'scores.*.numeric' => 'Skor harus berupa angka.',
            'scores.*.between' => 'Skor harus antara 1 sampai 100.',
            'scores.*.decimal' => 'Skor maksimal 4 angka di belakang koma.',
        ];
    }
}
