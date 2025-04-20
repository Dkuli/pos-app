<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductKitItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_kit_id',
        'product_id',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'product_kit_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the product kit that owns the item.
     */
    public function productKit()
    {
        return $this->belongsTo(ProductKit::class);
    }

    /**
     * Get the product that is part of this kit item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the subtotal for this kit item.
     *
     * @return float
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->product->purchase_price;
    }

    /**
     * Get the selling subtotal for this kit item.
     *
     * @return float
     */
    public function getSellingSubtotalAttribute()
    {
        return $this->quantity * $this->product->selling_price;
    }
}
