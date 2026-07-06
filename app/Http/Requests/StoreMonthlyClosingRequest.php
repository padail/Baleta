<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyClosingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ships' => ['required', 'array', 'min:1'],
            'ships.*.captain_percentage' => ['required', 'numeric', 'between:0,100'],
            'ships.*.operational_expenses' => ['nullable', 'array'],
            'ships.*.operational_expenses.*.description' => ['nullable', 'string', 'max:255'],
            'ships.*.operational_expenses.*.amount' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ships.required' => 'Data rekap kapal tidak ditemukan. Pastikan ada invoice yang sudah diposting.',
            'ships.*.captain_percentage.required' => 'Persentase jasa kapten wajib diisi untuk setiap kapal.',
        ];
    }
}
