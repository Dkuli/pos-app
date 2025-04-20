<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'short_name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'short_name')
                    ->ignore($this->route('unit'))
                    ->where(function ($query) {
                        return $query->where('tenant_id', $this->tenant_id ?? $this->route('unit')->tenant_id);
                    }),
            ],
            'base_unit_id' => 'nullable|exists:units,id',
            'operator' => 'nullable|string|in:multiply,divide',
            'operation_value' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
