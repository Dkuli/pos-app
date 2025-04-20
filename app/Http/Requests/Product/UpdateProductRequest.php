<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($this->route('product'))
                    ->where(function ($query) {
                        return $query->where('tenant_id', $this->tenant_id ?? $this->route('product')->tenant_id);
                    }),
            ],
            'barcode' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'selling_price' => 'sometimes|required|numeric|min:0',
            'stock_alert_quantity' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'is_service' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'track_inventory' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_image' => 'nullable|integer|min:0',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:product_images,id',
        ];
    }
}
