<?php

namespace App\Http\Requests\CashRegisterSession;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCashRegisterSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'closing_amount' => 'required_if:status,closed|numeric|min:0',
            'closing_note' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:open,closed',
        ];
    }
}
