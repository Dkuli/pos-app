<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new purchase
     *
     * @param array $data
     * @return Purchase
     */
    public function create(array $data): Purchase
    {
        DB::beginTransaction();
        try {
            // Create purchase record
            $purchase = Purchase::create([
                'tenant_id' => $data['tenant_id'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $data['user_id'],
                'reference' => $data['reference'],
                'date' => $data['date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'shipping_amount' => $data['shipping_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
            ]);

            // Create purchase items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_amount' => $itemData['tax_amount'] ?? 0,
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'total' => $itemData['total'],
                ]);

                // Update product stock if purchase is received
                if ($data['status'] === 'received') {
                    $this->inventoryService->updateStock(
                        $product,
                        $data['warehouse_id'],
                        $itemData['quantity'],
                        'add',
                        'purchase',
                        "Purchase: {$data['reference']}"
                    );
                }
            }

            DB::commit();
            return $purchase->load(['items.product', 'supplier', 'warehouse']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update an existing purchase
     *
     * @param Purchase $purchase
     * @param array $data
     * @return Purchase
     */
    public function update(Purchase $purchase, array $data): Purchase
    {
        DB::beginTransaction();
        try {
            $oldStatus = $purchase->status;
            $newStatus = $data['status'] ?? $oldStatus;
            $oldWarehouseId = $purchase->warehouse_id;
            $newWarehouseId = $data['warehouse_id'] ?? $oldWarehouseId;

            // If purchase was already received, reverse the inventory entry
            if ($oldStatus === 'received' && ($newStatus !== 'received' || $oldWarehouseId !== $newWarehouseId)) {
                foreach ($purchase->items as $item) {
                    $this->inventoryService->updateStock(
                        $item->product,
                        $oldWarehouseId,
                        $item->quantity,
                        'subtract',
                        'purchase_updated',
                        "Purchase update reversal: {$purchase->reference}"
                    );
                }
            }

            // Update purchase record
            $purchase->update([
                'supplier_id' => $data['supplier_id'] ?? $purchase->supplier_id,
                'warehouse_id' => $newWarehouseId,
                'user_id' => $data['user_id'] ?? $purchase->user_id,
                'reference' => $data['reference'] ?? $purchase->reference,
                'date' => $data['date'] ?? $purchase->date,
                'due_date' => $data['due_date'] ?? $purchase->due_date,
                'status' => $newStatus,
                'notes' => $data['notes'] ?? $purchase->notes,
                'tax_amount' => $data['tax_amount'] ?? $purchase->tax_amount,
                'discount_amount' => $data['discount_amount'] ?? $purchase->discount_amount,
                'shipping_amount' => $data['shipping_amount'] ?? $purchase->shipping_amount,
                'total_amount' => $data['total_amount'] ?? $purchase->total_amount,
            ]);

            // Handle items
            if (isset($data['items'])) {
                // Delete existing items
                $purchase->items()->delete();

                // Create new items
                foreach ($data['items'] as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);

                    $purchase->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_amount' => $itemData['tax_amount'] ?? 0,
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'total' => $itemData['total'],
                    ]);

                    // Update product stock if new status is received
                    if ($newStatus === 'received') {
                        $this->inventoryService->updateStock(
                            $product,
                            $newWarehouseId,
                            $itemData['quantity'],
                            'add',
                            'purchase',
                            "Purchase update: {$purchase->reference}"
                        );
                    }
                }
            }

            DB::commit();
            return $purchase->fresh(['items.product', 'supplier', 'warehouse']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete a purchase
     *
     * @param Purchase $purchase
     * @return bool
     */
    public function delete(Purchase $purchase): bool
    {
        DB::beginTransaction();
        try {
            // If purchase was received, reverse the inventory entries
            if ($purchase->status === 'received') {
                foreach ($purchase->items as $item) {
                    $this->inventoryService->updateStock(
                        $item->product,
                        $purchase->warehouse_id,
                        $item->quantity,
                        'subtract',
                        'purchase_deleted',
                        "Purchase deletion: {$purchase->reference}"
                    );
                }
            }

            // Delete purchase items
            $purchase->items()->delete();

            // Delete purchase
            $result = $purchase->delete();

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Change purchase status
     *
     * @param Purchase $purchase
     * @param string $status
     * @return Purchase
     */
    public function changeStatus(Purchase $purchase, string $status): Purchase
    {
        DB::beginTransaction();
        try {
            $oldStatus = $purchase->status;

            // If previously received and now changing to something else
            if ($oldStatus === 'received' && $status !== 'received') {
                foreach ($purchase->items as $item) {
                    $this->inventoryService->updateStock(
                        $item->product,
                        $purchase->warehouse_id,
                        $item->quantity,
                        'subtract',
                        'purchase_status_change',
                        "Purchase status change from {$oldStatus} to {$status}: {$purchase->reference}"
                    );
                }
            }

            // If now receiving
            if ($status === 'received' && $oldStatus !== 'received') {
                foreach ($purchase->items as $item) {
                    $this->inventoryService->updateStock(
                        $item->product,
                        $purchase->warehouse_id,
                        $item->quantity,
                        'add',
                        'purchase_status_change',
                        "Purchase status change from {$oldStatus} to {$status}: {$purchase->reference}"
                    );
                }
            }

            $purchase->update(['status' => $status]);

            DB::commit();
            return $purchase->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get supplier purchase history
     *
     * @param Supplier $supplier
     * @return array
     */
    public function getSupplierPurchaseHistory(Supplier $supplier): array
    {
        $purchases = $supplier->purchases()
            ->with(['items.product'])
            ->latest()
            ->get();

        $totalAmount = $purchases->sum('total_amount');
        $purchaseCount = $purchases->count();

        return [
            'supplier' => $supplier,
            'purchases' => $purchases,
            'total_amount' => $totalAmount,
            'purchase_count' => $purchaseCount,
            'average_purchase' => $purchaseCount > 0 ? $totalAmount / $purchaseCount : 0,
        ];
    }
}
