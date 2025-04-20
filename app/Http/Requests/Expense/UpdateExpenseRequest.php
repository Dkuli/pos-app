<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'sometimes|required|exists:stores,id',
            'expense_category_id' => 'sometimes|required|exists:expense_categories,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'reference' => 'nullable|string|max:100',
            'date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'payment_method' => 'nullable|string|max:50',
        ];
    }
}
