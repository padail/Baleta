<?php

namespace App\Http\Requests;

use App\Models\OwnerExpense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnerExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'expense_date' => ['required', 'date'],
            'expense_type' => ['required', Rule::in([OwnerExpense::TYPE_OPERATIONAL, OwnerExpense::TYPE_NON_OPERATIONAL])],
            'ship_id' => ['nullable', 'integer'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
