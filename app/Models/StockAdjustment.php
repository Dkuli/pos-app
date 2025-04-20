<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'user_id',
        'reference',
        'date',
        'type',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the tenant that owns the stock adjustment.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the warehouse that owns the stock adjustment.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user that owns the stock adjustment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the stock adjustment.
     */
    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    /**
     * Get the stock movements for the stock adjustment.
     */
    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
