<?php

namespace App\Http\Requests\CashRegisterTransaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashRegisterTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'cash_register_session_id' => 'required|exists:cash_register_sessions,id',
            'transaction_type' => 'required|in:add,subtract',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
        ];
    }
}
