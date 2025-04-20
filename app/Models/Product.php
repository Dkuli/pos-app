<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'unit_id',
        'tax_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'alert_quantity',
        'product_type',
        'is_featured',
        'is_taxable',
        'track_inventory',
        'image',
        'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'alert_quantity' => 'integer',
        'is_featured' => 'boolean',
        'is_taxable' => 'boolean',
        'track_inventory' => 'boolean',
    ];

    /**
     * Get the tenant that owns the product.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the unit that owns the product.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tax that owns the product.
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Get the images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the primary image for the product.
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the inventory records for the product.
     */
    public function inventories()
    {
        return $this->hasMany(ProductInventory::class);
    }

    /**
     * Get the stock movements for the product.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * The suppliers that belong to the product.
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot('supplier_code', 'purchase_price', 'minimum_order', 'is_preferred')
            ->withTimestamps();
    }

    /**
     * Get the product's preferred supplier.
     */
    public function preferredSupplier()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->wherePivot('is_preferred', true)
            ->first();
    }

    /**
     * Get the transaction items for the product.
     */
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Get the purchase items for the product.
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the stock adjustment items for the product.
     */
    public function stockAdjustmentItems()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    /**
     * The discounts that belong to the product.
     */
    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_product');
    }

    /**
     * Get the product kit where this product is the main product.
     */
    public function kit()
    {
        return $this->hasOne(ProductKit::class);
    }

    /**
     * Get the product kits where this product is included as an item.
     */
    public function kitItems()
    {
        return $this->hasMany(ProductKitItem::class);
    }

    /**
     * Get inventory quantity for a specific warehouse.
     *
     * @param int $warehouseId
     * @return float
     */
    public function getQuantityInWarehouse($warehouseId)
    {
        $inventory = $this->inventories()->where('warehouse_id', $warehouseId)->first();

        return $inventory ? $inventory->quantity : 0;
    }

    /**
     * Get total inventory quantity across all warehouses.
     *
     * @return float
     */
    public function getTotalQuantity()
    {
        return $this->inventories()->sum('quantity');
    }

    /**
     * Check if the product is low in stock.
     *
     * @return bool
     */
    public function isLowStock()
    {
        if (!$this->alert_quantity) {
            return false;
        }

        return $this->getTotalQuantity() <= $this->alert_quantity;
    }

    /**
     * Calculate the product price with tax.
     *
     * @return float
     */
    public function getPriceWithTax()
    {
        if (!$this->is_taxable || !$this->tax) {
            return $this->selling_price;
        }

        $taxAmount = $this->tax->calculateTax($this->selling_price);

        return $this->selling_price + $taxAmount;
    }
}
