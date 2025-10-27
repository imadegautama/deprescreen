<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScreeningAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Screening adalah publik
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'answers' => 'required|array|min:1',
            'answers.*.symptom_id' => [
                'required',
                'integer',
                'exists:symptoms,symptom_id',
            ],
            'answers.*.value' => [
                'required',
                'integer',
                'between:0,4',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'Jawaban screening diperlukan',
            'answers.array' => 'Format jawaban tidak valid',
            'answers.min' => 'Minimal harus menjawab satu gejala',
            'answers.*.symptom_id.required' => 'ID gejala diperlukan',
            'answers.*.symptom_id.exists' => 'Gejala tidak ditemukan',
            'answers.*.value.required' => 'Nilai jawaban diperlukan',
            'answers.*.value.integer' => 'Nilai harus angka',
            'answers.*.value.between' => 'Nilai harus antara 0 dan 4',
        ];
    }
}
