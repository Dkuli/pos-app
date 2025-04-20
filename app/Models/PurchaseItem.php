<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'received_quantity',
        'unit_price',
        'discount',
        'tax',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateSubtotal()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $subtotal = $subtotal - $this->discount;
        $subtotal = $subtotal + $this->tax;
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }
}
