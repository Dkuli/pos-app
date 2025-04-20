<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'user_id',
        'customer_id',
        'transaction_number',
        'transaction_type',
        'transaction_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'change',
        'due',
        'payment_status',
        'payment_method',
        'card_number',
        'card_holder_name',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'change' => 'decimal:2',
        'due' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'reference');
    }

    public function cashRegisterTransactions()
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeSales($query)
    {
        return $query->where('transaction_type', 'sale');
    }

    public function scopeReturns($query)
    {
        return $query->where('transaction_type', 'return');
    }

    public function scopeQuotations($query)
    {
        return $query->where('transaction_type', 'quotation');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', '!=', 'paid');
    }

    public function calculateTotal()
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->total = $this->subtotal - $this->discount + $this->tax;
        $this->due = $this->total - $this->paid;

        if ($this->due <= 0) {
            $this->payment_status = 'paid';
            $this->change = abs($this->due);
            $this->due = 0;
        } elseif ($this->paid > 0) {
            $this->payment_status = 'partial';
            $this->change = 0;
        } else {
            $this->payment_status = 'pending';
            $this->change = 0;
        }

        return $this;
    }

    public function updatePaymentStatus()
    {
        if ($this->paid >= $this->total) {
            $this->payment_status = 'paid';
            $this->change = $this->paid - $this->total;
            $this->due = 0;
        } elseif ($this->paid > 0) {
            $this->payment_status = 'partial';
            $this->change = 0;
            $this->due = $this->total - $this->paid;
        } else {
            $this->payment_status = 'pending';
            $this->change = 0;
            $this->due = $this->total;
        }

        $this->save();
    }

    public function getIsCompleteAttribute()
    {
        return $this->payment_status === 'paid';
    }
}
