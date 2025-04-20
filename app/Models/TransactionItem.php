<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'cost',
        'discount',
        'tax',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calculateSubtotal()
    {
        $subtotal = $this->quantity * $this->price;
        $subtotal = $subtotal - $this->discount;
        $subtotal = $subtotal + $this->tax;
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getProfitAttribute()
    {
        if ($this->cost) {
            return $this->subtotal - ($this->cost * $this->quantity);
        }

        return null;
    }
}
