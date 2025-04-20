<?php

namespace App\Http\Requests\Tax;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_compound' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }
}
