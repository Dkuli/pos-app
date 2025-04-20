<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'group',
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tenant_id' => 'integer',
    ];

    /**
     * Get the tenant that owns the setting.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get a setting value.
     *
     * @param string $key
     * @param string $group
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $group = 'general', $default = null)
    {
        $tenantId = auth()->user()->tenant_id ?? null;

        $cacheKey = "settings_{$tenantId}_{$group}_{$key}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($tenantId, $group, $key, $default) {
            $setting = static::where('tenant_id', $tenantId)
                ->where('group', $group)
                ->where('key', $key)
                ->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @return Setting
     */
    public static function set($key, $value, $group = 'general')
    {
        $tenantId = auth()->user()->tenant_id ?? null;

        $setting = static::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
            ]
        );

        // Clear the cache
        $cacheKey = "settings_{$tenantId}_{$group}_{$key}";
        Cache::forget($cacheKey);

        return $setting;
    }

    /**
     * Get all settings for a specific group.
     *
     * @param string $group
     * @return array
     */
    public static function getGroup($group = 'general')
    {
        $tenantId = auth()->user()->tenant_id ?? null;

        $cacheKey = "settings_group_{$tenantId}_{$group}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($tenantId, $group) {
            return static::where('tenant_id', $tenantId)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}
