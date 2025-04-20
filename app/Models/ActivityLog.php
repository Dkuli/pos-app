<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'model_type',
        'model_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tenant_id' => 'integer',
        'user_id' => 'integer',
        'model_id' => 'integer',
        'properties' => 'json',
    ];

    /**
     * Get the tenant that owns the activity log.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning model.
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Create a new activity log entry.
     *
     * @param array $data
     * @return ActivityLog
     */
    public static function log($data)
    {
        return static::create(array_merge($data, [
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }

    /**
     * Scope a query to filter logs by action.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
