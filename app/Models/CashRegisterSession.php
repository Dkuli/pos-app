<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'user_id',
        'opening_amount',
        'closing_amount',
        'cash_sales',
        'cash_refunds',
        'expected_closing',
        'difference',
        'notes',
        'opened_at',
        'closed_at',
        'is_active',
    ];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'cash_refunds' => 'decimal:2',
        'expected_closing' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_active', false)->whereNotNull('closed_at');
    }

    public function close($closingAmount, $notes = null)
    {
        $this->calculateExpectedClosing();

        $this->closing_amount = $closingAmount;
        $this->difference = $closingAmount - $this->expected_closing;
        $this->notes = $notes;
        $this->closed_at = now();
        $this->is_active = false;
        $this->save();

        return $this;
    }

    public function calculateExpectedClosing()
    {
        $cashSales = $this->transactions()
            ->where('type', 'sale')
            ->sum('amount');

        $cashRefunds = $this->transactions()
            ->where('type', 'refund')
            ->sum('amount');

        $cashExpenses = $this->transactions()
            ->where('type', 'expense')
            ->sum('amount');

        $cashDeposits = $this->transactions()
            ->where('type', 'deposit')
            ->sum('amount');

        $cashWithdrawals = $this->transactions()
            ->where('type', 'withdrawal')
            ->sum('amount');

        $this->cash_sales = $cashSales;
        $this->cash_refunds = $cashRefunds;
        $this->expected_closing = $this->opening_amount + $cashSales - $cashRefunds - $cashExpenses + $cashDeposits - $cashWithdrawals;
        $this->save();

        return $this;
    }

    public function getDurationAttribute()
    {
        $openedAt = $this->opened_at;
        $closedAt = $this->closed_at ?? now();

        return $openedAt->diffForHumans($closedAt, true);
    }
}
