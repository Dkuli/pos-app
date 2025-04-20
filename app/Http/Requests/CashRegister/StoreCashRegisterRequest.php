<?php

namespace App\Http\Requests\CashRegister;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashRegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'is_active' => 'nullable|boolean',
            'details' => 'nullable|string|max:1000',
        ];
    }
}
