<?php

namespace App\Http\Requests\ProductKit;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductKitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ];
    }
}
