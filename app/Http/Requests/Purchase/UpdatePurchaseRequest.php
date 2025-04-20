<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
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
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'reference' => 'sometimes|required|string|max:100',
            'date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'status' => 'sometimes|required|in:pending,received,partially_received,canceled',
            'notes' => 'nullable|string|max:1000',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'shipping_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ];
    }
}
