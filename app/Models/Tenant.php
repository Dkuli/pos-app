<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'tax_number',
        'logo',
        'currency',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the stores for the tenant.
     */
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the categories for the tenant.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the units for the tenant.
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the taxes for the tenant.
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    /**
     * Get the products for the tenant.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the warehouses for the tenant.
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get the suppliers for the tenant.
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Get the customers for the tenant.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the transactions for the tenant.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the purchases for the tenant.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the stock movements for the tenant.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the stock adjustments for the tenant.
     */
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Get the activity logs for the tenant.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the settings for the tenant.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    /**
     * Get the discounts for the tenant.
     */
    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    /**
     * Get the expenses for the tenant.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the expense categories for the tenant.
     */
    public function expenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * Get the cash registers for the tenant.
     */
    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    /**
     * Get the main store for the tenant.
     */
    public function mainStore()
    {
        return $this->hasOne(Store::class)->where('is_main', true);
    }
}
