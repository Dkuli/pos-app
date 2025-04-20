<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'store_id' => 'required|exists:stores,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'user_id' => 'required|exists:users,id',
            'reference' => 'nullable|string|max:100',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'payment_method' => 'nullable|string|max:50',
        ];
    }
}
