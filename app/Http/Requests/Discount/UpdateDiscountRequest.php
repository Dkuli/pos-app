<?php

namespace App\Http\Requests\Discount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountRequest extends FormRequest
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
                'nullable',
                'string',
                'max:50',
                Rule::unique('discounts', 'code')->ignore($this->route('discount')),
            ],
            'type' => 'sometimes|required|in:percentage,fixed',
            'value' => 'sometimes|required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'applies_to' => 'sometimes|required|in:all,products,categories',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'products' => 'sometimes|required_if:applies_to,products|array',
            'products.*' => 'exists:products,id',
            'categories' => 'sometimes|required_if:applies_to,categories|array',
            'categories.*' => 'exists:categories,id',
        ];
    }
}
