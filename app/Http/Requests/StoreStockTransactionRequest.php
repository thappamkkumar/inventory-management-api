<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'inventory_id' => [
                'required',
                'integer',
                'exists:inventories,id',
            ],

            'type' => [
                'required',
                Rule::in([
                    'purchase',
                    'sale',
                    'adjustment',
                    'damage',
                    'return',
                ]),
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
