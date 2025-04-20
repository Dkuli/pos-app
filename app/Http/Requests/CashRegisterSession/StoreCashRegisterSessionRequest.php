<?php

namespace App\Http\Requests\CashRegisterSession;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashRegisterSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'cash_register_id' => 'required|exists:cash_registers,id',
            'user_id' => 'required|exists:users,id',
            'opening_amount' => 'required|numeric|min:0',
            'opening_note' => 'nullable|string|max:1000',
            'status' => 'required|in:open,closed',
        ];
    }
}
