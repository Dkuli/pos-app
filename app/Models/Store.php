<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'receipt_header',
        'receipt_footer',
        'is_main',
        'is_active',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the store.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The users that belong to the store.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'store_user')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * Get the warehouses for the store.
     */
    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get the transactions for the store.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the cash registers for the store.
     */
    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    /**
     * Get the expenses for the store.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the default warehouse for the store.
     */
    public function defaultWarehouse()
    {
        return $this->hasOne(Warehouse::class)->where('is_default', true);
    }
}
