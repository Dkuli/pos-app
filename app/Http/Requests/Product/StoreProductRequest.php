<?php

namespace App\Http\Requests\Product;

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
     */
    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_alert_quantity' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'is_service' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'track_inventory' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_image' => 'nullable|integer|min:0',
        ];
    }
}
