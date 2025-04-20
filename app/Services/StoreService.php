<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreService
{
    /**
     * Create a new store
     *
     * @param array $data
     * @return Store
     */
    public function create(array $data): Store
    {
        if (isset($data['logo'])) {
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        return Store::create($data);
    }

    /**
     * Update an existing store
     *
     * @param Store $store
     * @param array $data
     * @return Store
     */
    public function update(Store $store, array $data): Store
    {
        if (isset($data['logo'])) {
            // Delete old logo if exists
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $this->uploadLogo($data['logo']);
        }

        $store->update($data);
        return $store;
    }

    /**
     * Delete a store
     *
     * @param Store $store
     * @return bool
     */
    public function delete(Store $store): bool
    {
        // Delete logo if exists
        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
        }

        return $store->delete();
    }

    /**
     * Upload store logo
     *
     * @param $logo
     * @return string
     */
    private function uploadLogo($logo): string
    {
        $fileName = 'store_' . Str::random(10) . '.' . $logo->getClientOriginalExtension();
        $path = $logo->storeAs('stores/logos', $fileName, 'public');
        return $path;
    }

    /**
     * Get all active stores for a tenant
     *
     * @param Tenant $tenant
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveStores(Tenant $tenant)
    {
        return $tenant->stores()->where('is_active', true)->get();
    }

    /**
     * Assign users to a store
     *
     * @param Store $store
     * @param array $userIds
     * @return Store
     */
    public function assignUsers(Store $store, array $userIds): Store
    {
        $store->users()->sync($userIds);
        return $store;
    }

    /**
     * Get store sales statistics
     *
     * @param Store $store
     * @param string $period day|week|month|year
     * @return array
     */
    public function getSalesStatistics(Store $store, string $period = 'day'): array
    {
        $query = $store->transactions()->completed();

        switch ($period) {
            case 'week':
                $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'year':
                $query->whereBetween('transaction_date', [now()->startOfYear(), now()->endOfYear()]);
                break;
            default:
                $query->whereDate('transaction_date', now());
        }

        $totalSales = $query->sum('total_amount');
        $transactionCount = $query->count();

        return [
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'average_sale' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
        ];
    }
}
