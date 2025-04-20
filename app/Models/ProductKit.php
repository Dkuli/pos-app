<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductKit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(ProductKitItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getItemsCountAttribute()
    {
        return $this->items->count();
    }

    public function getTotalCostAttribute()
    {
        $total = 0;

        foreach ($this->items as $item) {
            if ($item->product) {
                $total += $item->product->purchase_price * $item->quantity;
            }
        }

        return $total;
    }
}
