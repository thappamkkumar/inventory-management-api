<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id' => ['nullable', 'exists:categories,id'],
            
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
        
    }

    public function messages(): array
    {
        return [ 
            'name.required' => 'Product name is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'SKU already exists.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
        ];
    }
}
