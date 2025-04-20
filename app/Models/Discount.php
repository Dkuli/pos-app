<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'value',
        'apply_to_all',
        'start_date',
        'end_date',
        'min_purchase_qty',
        'min_purchase_amount',
        'buy_qty',
        'get_qty',
        'active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'apply_to_all' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'min_purchase_qty' => 'integer',
        'min_purchase_amount' => 'decimal:2',
        'buy_qty' => 'integer',
        'get_qty' => 'integer',
        'active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'discount_category');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('apply_to_all', true)
            ->orWhereHas('products', function ($q) use ($productId) {
                $q->where('products.id', $productId);
            })
            ->orWhereHas('categories', function ($q) use ($productId) {
                $q->whereHas('products', function ($subQ) use ($productId) {
                    $subQ->where('products.id', $productId);
                });
            });
    }

    public function calculateDiscount($price, $quantity = 1)
    {
        if (!$this->active) {
            return 0;
        }

        // Check if discount is applicable
        if ($this->min_purchase_qty && $quantity < $this->min_purchase_qty) {
            return 0;
        }

        if ($this->min_purchase_amount && ($price * $quantity) < $this->min_purchase_amount) {
            return 0;
        }

        // Calculate discount based on type
        switch ($this->type) {
            case 'percentage':
                return ($price * $quantity) * ($this->value / 100);

            case 'fixed':
                return $this->value;

            case 'buy_x_get_y':
                if ($this->buy_qty && $this->get_qty && $quantity >= $this->buy_qty) {
                    $sets = floor($quantity / ($this->buy_qty + $this->get_qty));
                    $freeItems = $sets * $this->get_qty;
                    return $freeItems * $price;
                }
                return 0;

            default:
                return 0;
        }
    }
}
