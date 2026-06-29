<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $ownerId = auth()->user()->activeOwnerId();

        return [
            'invoice_date' => ['required', 'date'],
            'ship_id' => ['required', Rule::exists('ships', 'id')->where('owner_id', $ownerId)],
            'carrier_boat_name' => ['nullable', 'string', 'max:255'],
            'total_boxes' => ['required', 'integer', 'min:1'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'unloading_cost_per_box' => ['nullable', 'integer', 'min:0'],
            'additional_expense' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.buyer_id' => ['required', Rule::exists('buyers', 'id')->where('owner_id', $ownerId)],
            'items.*.fish_type' => ['nullable', 'string', 'max:255'],
            'items.*.box_count' => ['required', 'integer', 'min:1'],
            'items.*.price_per_box' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
