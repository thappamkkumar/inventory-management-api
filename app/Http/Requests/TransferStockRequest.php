<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
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
            'from_inventory_id' => [
                'required',
                'integer',
                'exists:inventories,id',
            ],

            'to_inventory_id' => [
                'required',
                'integer',
                'exists:inventories,id',
                'different:from_inventory_id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
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
