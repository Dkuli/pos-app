<?php

namespace App\Http\Requests\ProductKit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductKitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:product_kit_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ];
    }
}
