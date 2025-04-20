<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'domain' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tenants', 'domain')->ignore($this->route('tenant')),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('tenants', 'email')->ignore($this->route('tenant')),
            ],
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
            'subscription_plan' => 'nullable|string|max:100',
            'subscription_ends_at' => 'nullable|date',
            'timezone' => 'nullable|string|max:100',
            'currency_code' => 'nullable|string|max:10',
        ];
    }
}
