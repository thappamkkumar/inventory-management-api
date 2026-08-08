<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
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
        $supplier = $this->route('supplier');

        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')
                    ->ignore($supplier),
            ],

            'phone' => ['nullable', 'string', 'max:30'],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'address' => ['nullable', 'string', 'max:1000'],

            'city' => ['nullable', 'string', 'max:100'],

            'state' => ['nullable', 'string', 'max:100'],

            'country' => ['nullable', 'string', 'max:100'],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Supplier name is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This supplier email already exists.',
        ];
    }

    public function attributes(): array
    {
        return [
            'contact_person' => 'contact person',
            'postal_code' => 'postal code',
            'is_active' => 'active status',
        ];
    }
    
}
