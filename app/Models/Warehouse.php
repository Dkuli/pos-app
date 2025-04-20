<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'name',
        'code',
        'phone',
        'email',
        'address',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the warehouse.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the store that owns the warehouse.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the product inventories for the warehouse.
     */
    public function productInventories()
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Get the stock movements for the warehouse.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the stock adjustments for the warehouse.
     */
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Get the purchases for the warehouse.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get quantity of a specific product in this warehouse.
     *
     * @param int $productId
     * @return float
     */
    public function getProductQuantity($productId)
    {
        $inventory = $this->productInventories()->where('product_id', $productId)->first();

        return $inventory ? $inventory->quantity : 0;
    }
}
