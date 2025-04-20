<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'store_id' => 'sometimes|required|exists:stores,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'transaction_number' => 'nullable|string|max:100',
            'transaction_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:completed,pending,canceled',
            'payment_status' => 'sometimes|required|in:paid,partial,unpaid',
            'notes' => 'nullable|string|max:1000',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:transaction_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.cost' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.id' => 'nullable|exists:payments,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_method' => 'required|string|in:cash,card,bank_transfer,e-wallet,other',
            'payments.*.reference' => 'nullable|string|max:100',
        ];
    }
}
