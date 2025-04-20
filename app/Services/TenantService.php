<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant
     *
     * @param array $data
     * @return Tenant
     */
    public function create(array $data): Tenant
    {
        if (isset($data['logo'])) {
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        return Tenant::create($data);
    }

    /**
     * Update an existing tenant
     *
     * @param Tenant $tenant
     * @param array $data
     * @return Tenant
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        if (isset($data['logo'])) {
            // Delete old logo if exists
            if ($tenant->logo) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        $tenant->update($data);
        return $tenant;
    }

    /**
     * Delete a tenant
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function delete(Tenant $tenant): bool
    {
        // Delete logo if exists
        if ($tenant->logo) {
            Storage::disk('public')->delete($tenant->logo);
        }

        return $tenant->delete();
    }

    /**
     * Upload tenant logo
     *
     * @param $logo
     * @return string
     */
    private function uploadLogo($logo): string
    {
        $fileName = 'tenant_' . Str::random(10) . '.' . $logo->getClientOriginalExtension();
        $path = $logo->storeAs('tenants/logos', $fileName, 'public');
        return $path;
    }

    /**
     * Get all active tenants
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTenants()
    {
        return Tenant::where('status', true)->get();
    }

    /**
     * Check tenant subscription status and update if needed
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function checkSubscriptionStatus(Tenant $tenant): bool
    {
        if (!$tenant->subscription_ends_at) {
            return true;
        }

        if (now()->gt($tenant->subscription_ends_at)) {
            $tenant->update(['status' => false]);
            return false;
        }

        return true;
    }
}
