<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'tax_number',
        'bank_name',
        'bank_account',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the supplier.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The products that belong to the supplier.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot('supplier_code', 'purchase_price', 'minimum_order', 'is_preferred')
            ->withTimestamps();
    }

    /**
     * Get the purchases for the supplier.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the total amount spent with this supplier.
     *
     * @return float
     */
    public function getTotalPurchaseAmount()
    {
        return $this->purchases()->sum('grand_total');
    }

    /**
     * Get the total outstanding balance with this supplier.
     *
     * @return float
     */
    public function getOutstandingBalance()
    {
        return $this->purchases()->where('payment_status', '!=', 'paid')->sum(DB::raw('grand_total - paid_amount'));
    }
}
