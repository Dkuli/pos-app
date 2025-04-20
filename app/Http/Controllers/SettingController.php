<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Store;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Display general settings.
     */
    public function general()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get general settings
        $settings = Setting::where('tenant_id', $tenantId)
            ->whereIn('key', ['company_name', 'company_email', 'company_phone', 'company_address', 'currency', 'default_tax_rate', 'logo'])
            ->get()
            ->keyBy('key');

        return view('settings.general', compact('settings'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Validate data
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:30',
            'company_address' => 'nullable|string|max:500',
            'currency' => 'required|string|size:3',
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Get old logo if exists
            $oldLogo = Setting::where('tenant_id', $tenantId)
                ->where('key', 'logo')
                ->first();

            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');

            // Update or create logo setting
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => 'logo'],
                ['value' => $logoPath]
            );
        }

        // Update other settings
        foreach (['company_name', 'company_email', 'company_phone', 'company_address', 'currency', 'default_tax_rate'] as $key) {
            if ($request->filled($key)) {
                Setting::updateOrCreate(
                    ['tenant_id' => $tenantId, 'key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        return redirect()->route('settings.general')->with('success', 'General settings updated successfully.');
    }

    /**
     * Display POS settings.
     */
    public function pos()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get POS settings
        $settings = Setting::where('tenant_id', $tenantId)
            ->whereIn('key', ['pos_show_logo', 'pos_receipt_header', 'pos_receipt_footer', 'pos_default_discount', 'pos_default_tax'])
            ->get()
            ->keyBy('key');

        return view('settings.pos', compact('settings'));
    }

    /**
     * Update POS settings.
     */
    public function updatePos(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Validate data
        $request->validate([
            'pos_show_logo' => 'nullable|boolean',
            'pos_receipt_header' => 'nullable|string|max:500',
            'pos_receipt_footer' => 'nullable|string|max:500',
            'pos_default_discount' => 'nullable|numeric|min:0|max:100',
            'pos_default_tax' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update settings
        foreach (['pos_receipt_header', 'pos_receipt_footer', 'pos_default_discount', 'pos_default_tax'] as $key) {
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => $key],
                ['value' => $request->input($key)]
            );
        }

        // Handle checkbox
        Setting::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => 'pos_show_logo'],
            ['value' => $request->input('pos_show_logo') ? '1' : '0']
        );

        return redirect()->route('settings.pos')->with('success', 'POS settings updated successfully.');
    }

    /**
     * Display email settings.
     */
    public function email()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get email settings
        $settings = Setting::where('tenant_id', $tenantId)
            ->whereIn('key', ['mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_name', 'mail_from_address'])
            ->get()
            ->keyBy('key');

        return view('settings.email', compact('settings'));
    }

    /**
     * Update email settings.
     */
    public function updateEmail(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Validate data
        $request->validate([
            'mail_driver' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark,log,array',
            'mail_host' => 'required_if:mail_driver,smtp|string|max:255',
            'mail_port' => 'required_if:mail_driver,smtp|numeric',
            'mail_username' => 'required_if:mail_driver,smtp|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl',
            'mail_from_name' => 'required|string|max:255',
            'mail_from_address' => 'required|email|max:255',
        ]);

        // Update settings
        foreach (['mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_name', 'mail_from_address'] as $key) {
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => $key],
                ['value' => $request->input($key)]
            );
        }

        // Handle password separately (only update if provided)
        if ($request->filled('mail_password')) {
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => 'mail_password'],
                ['value' => $request->input('mail_password')]
            );
        }

        return redirect()->route('settings.email')->with('success', 'Email settings updated successfully.');
    }

    /**
     * Display expense categories.
     */
    public function expenseCategories()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get expense categories
        $categories = ExpenseCategory::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('settings.expense-categories', compact('categories'));
    }

    /**
     * Store new expense category.
     */
    public function storeExpenseCategory(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,NULL,id,tenant_id,' . $tenantId,
            'description' => 'nullable|string|max:500',
        ]);

        ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'is_active' => true,
        ]);

        return redirect()->route('settings.expense-categories')
            ->with('success', 'Expense category created successfully.');
    }

    /**
     * Update expense category.
     */
    public function updateExpenseCategory(Request $request, ExpenseCategory $category)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $category->id . ',id,tenant_id,' . $tenantId,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('settings.expense-categories')
            ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Delete expense category.
     */
    public function deleteExpenseCategory(ExpenseCategory $category)
    {
        // Check if category has expenses
        if ($category->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated expenses.');
        }

        $category->delete();

        return redirect()->route('settings.expense-categories')
            ->with('success', 'Expense category deleted successfully.');
    }

    /**
     * Toggle expense category active status.
     */
    public function toggleExpenseCategoryStatus(ExpenseCategory $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return back()->with('success', 'Category status updated successfully.');
    }

    /**
     * Display payment methods settings.
     */
    public function paymentMethods()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get payment methods
        $paymentMethods = Setting::where('tenant_id', $tenantId)
            ->where('key', 'payment_methods')
            ->first();

        $methods = [];
        if ($paymentMethods) {
            $methods = json_decode($paymentMethods->value, true) ?: [];
        }

        return view('settings.payment-methods', compact('methods'));
    }

    /**
     * Update payment methods.
     */
    public function updatePaymentMethods(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $request->validate([
            'methods' => 'required|array',
            'methods.*.name' => 'required|string|max:255',
            'methods.*.is_active' => 'boolean',
        ]);

        $methods = $request->input('methods');

        // Add IDs if not present
        foreach ($methods as $key => $method) {
            if (!isset($method['id'])) {
                $methods[$key]['id'] = Str::random(10);
            }
        }

        Setting::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => 'payment_methods'],
            ['value' => json_encode($methods)]
        );

        return redirect()->route('settings.payment-methods')
            ->with('success', 'Payment methods updated successfully.');
    }

    /**
     * Display store settings.
     */
    public function stores()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get stores
        $stores = Store::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('settings.stores', compact('stores'));
    }

    /**
     * Display notification settings.
     */
    public function notifications()
    {
        $tenantId = Auth::user()->tenant_id;

        // Get notification settings
        $settings = Setting::where('tenant_id', $tenantId)
            ->whereIn('key', [
                'notify_low_stock',
                'notify_out_of_stock',
                'notify_expiring_products',
                'expiry_notification_days',
                'notify_sales_target',
                'sales_target_amount',
                'sales_target_period'
            ])
            ->get()
            ->keyBy('key');

        return view('settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        // Validate data
        $request->validate([
            'notify_low_stock' => 'nullable|boolean',
            'notify_out_of_stock' => 'nullable|boolean',
            'notify_expiring_products' => 'nullable|boolean',
            'expiry_notification_days' => 'required_if:notify_expiring_products,1|numeric|min:1|max:90',
            'notify_sales_target' => 'nullable|boolean',
            'sales_target_amount' => 'required_if:notify_sales_target,1|numeric|min:0',
            'sales_target_period' => 'required_if:notify_sales_target,1|in:daily,weekly,monthly,quarterly',
        ]);

        // Update checkbox settings
        foreach (['notify_low_stock', 'notify_out_of_stock', 'notify_expiring_products', 'notify_sales_target'] as $key) {
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => $key],
                ['value' => $request->input($key) ? '1' : '0']
            );
        }

        // Update numeric and string settings
        foreach (['expiry_notification_days', 'sales_target_amount', 'sales_target_period'] as $key) {
            if ($request->filled($key)) {
                Setting::updateOrCreate(
                    ['tenant_id' => $tenantId, 'key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        return redirect()->route('settings.notifications')
            ->with('success', 'Notification settings updated successfully.');
    }
}
