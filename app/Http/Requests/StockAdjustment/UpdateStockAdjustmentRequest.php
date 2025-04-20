<?php

namespace App\Http\Requests\StockAdjustment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'reference' => 'sometimes|required|string|max:100',
            'date' => 'sometimes|required|date',
            'adjustment_type' => 'sometimes|required|in:add,subtract,mixed',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:stock_adjustment_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.type' => 'required|in:add,subtract',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }
}
