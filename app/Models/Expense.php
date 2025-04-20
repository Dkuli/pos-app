<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'expense_category_id',
        'user_id',
        'store_id',
        'reference',
        'date',
        'amount',
        'notes',
        'attachment',
        'is_recurring',
        'recurring_frequency',
        'next_recurring_date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'next_recurring_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function cashRegisterTransactions()
    {
        return $this->morphMany(CashRegisterTransaction::class, 'reference');
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeDueForRecurrence($query)
    {
        return $query->where('is_recurring', true)
            ->whereNotNull('next_recurring_date')
            ->where('next_recurring_date', '<=', now());
    }

    public function updateNextRecurringDate()
    {
        if (!$this->is_recurring || !$this->recurring_frequency) {
            return $this;
        }

        $nextDate = null;

        switch ($this->recurring_frequency) {
            case 'daily':
                $nextDate = now()->addDay();
                break;
            case 'weekly':
                $nextDate = now()->addWeek();
                break;
            case 'monthly':
                $nextDate = now()->addMonth();
                break;
            case 'quarterly':
                $nextDate = now()->addMonths(3);
                break;
            case 'yearly':
                $nextDate = now()->addYear();
                break;
        }

        if ($nextDate) {
            $this->next_recurring_date = $nextDate;
            $this->save();
        }

        return $this;
    }
}
