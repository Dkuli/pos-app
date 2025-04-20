<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'rate',
        'type',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the tax.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the products for the tax.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Calculate the tax amount for a given value.
     *
     * @param float $value
     * @return float
     */
    public function calculateTax($value)
    {
        if ($this->type === 'percentage') {
            return ($value * $this->rate) / 100;
        }

        return $this->rate; // Fixed amount
    }
}
