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
            'captain_id' => ['nullable', Rule::exists('captains', 'id')->where('owner_id', $ownerId)],
            'captain_start_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
