<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $ownerId = auth()->user()->activeOwnerId();

        return [
            'code' => ['nullable', 'string', 'max:50', Rule::unique('ships', 'code')->where('owner_id', $ownerId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'captain_name' => ['required', 'string', 'max:255'],
            'captain_phone' => ['nullable', 'string', 'max:30'],
            'captain_start_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'captain_name.required' => 'Nama kapten wajib diisi saat menambah kapal.',
        ];
    }
}
