<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
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
            'short_name' => 'required|string|max:50',
            'base_unit_id' => 'nullable|exists:units,id',
            'operator' => 'nullable|string|in:multiply,divide',
            'operation_value' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
