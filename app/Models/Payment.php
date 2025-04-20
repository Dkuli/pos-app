<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'reference_type',
        'reference_id',
        'user_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('payment_method', $type);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public static function createPayment($data)
    {
        $payment = self::create($data);

        if ($payment->reference && method_exists($payment->reference, 'updatePaymentStatus')) {
            $payment->reference->updatePaymentStatus();
        }

        return $payment;
    }
}
