<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|string|in:cash,card,bank_transfer,e-wallet,other',
            'reference' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
