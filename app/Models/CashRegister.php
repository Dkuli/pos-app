<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'store_id',
        'user_id',
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

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(CashRegisterSession::class);
    }

    public function activeSession()
    {
        return $this->sessions()->where('is_active', true)->first();
    }

    public function hasActiveSession()
    {
        return $this->sessions()->where('is_active', true)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLastClosingBalanceAttribute()
    {
        $lastSession = $this->sessions()->where('is_active', false)->latest()->first();

        return $lastSession ? $lastSession->closing_amount : 0;
    }
}
