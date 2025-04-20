<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Dynamic rules based on which setting group is being updated
        $group = $this->input('group', '');

        switch ($group) {
            case 'general':
                return [
                    'company_name' => 'required|string|max:255',
                    'company_email' => 'required|email|max:255',
                    'currency_code' => 'required|string|max:10',
                    'timezone' => 'required|string|max:100',
                    'date_format' => 'required|string|max:50',
                    'time_format' => 'required|string|max:50',
                    'default_language' => 'required|string|max:50',
                    'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ];

            case 'invoice':
                return [
                    'invoice_prefix' => 'nullable|string|max:50',
                    'next_invoice_number' => 'nullable|integer|min:1',
                    'invoice_terms' => 'nullable|string',
                    'invoice_footer' => 'nullable|string',
                    'show_tax_on_invoice' => 'nullable|boolean',
                    'show_discount_on_invoice' => 'nullable|boolean',
                ];

            case 'pos':
                return [
                    'default_discount_type' => 'nullable|string|in:percentage,fixed',
                    'default_tax_id' => 'nullable|exists:taxes,id',
                    'default_customer_id' => 'nullable|exists:customers,id',
                    'show_recent_transactions' => 'nullable|boolean',
                    'receipt_header' => 'nullable|string',
                    'receipt_footer' => 'nullable|string',
                    'enable_rounding' => 'nullable|boolean',
                ];

            default:
                return [
                    'settings' => 'required|array',
                    'settings.*.key' => 'required|string|max:255',
                    'settings.*.value' => 'nullable',
                ];
        }
    }
}
