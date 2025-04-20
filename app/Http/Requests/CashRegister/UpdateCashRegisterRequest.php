<?php

namespace App\Http\Requests\CashRegister;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCashRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('cash_registers', 'code')
                    ->ignore($this->route('cash_register'))
                    ->where(function ($query) {
                        return $query->where('store_id', $this->store_id ?? $this->route('cash_register')->store_id);
                    }),
            ],
            'is_active' => 'nullable|boolean',
            'details' => 'nullable|string|max:1000',
        ];
    }
}
