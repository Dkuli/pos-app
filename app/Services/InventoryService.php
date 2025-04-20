<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get product stock levels across all warehouses
     *
     * @param Product $product
     * @return array
     */
    public function getProductStock(Product $product): array
    {
        $inventories = $product->inventories()
            ->with('warehouse')
            ->get()
            ->map(function ($inventory) {
                return [
                    'warehouse_id' => $inventory->warehouse_id,
                    'warehouse_name' => $inventory->warehouse->name,
                    'quantity' => $inventory->quantity,
                ];
            })
            ->toArray();

        $totalStock = $product->inventories()->sum('quantity');

        return [
            'inventories' => $inventories,
            'total_stock' => $totalStock,
        ];
    }

    /**
     * Update product stock
     *
     * @param Product $product
     * @param int $warehouseId
     * @param float $quantity
     * @param string $type
     * @param string $reference
     * @param string $note
     * @return ProductInventory
     */
    public function updateStock(
        Product $product,
        int $warehouseId,
        float $quantity,
        string $type,
        string $reference = '',
        string $note = ''
    ): ProductInventory {
        DB::beginTransaction();
        try {
            // If the product doesn't track inventory, return without updates
            if (!$product->track_inventory) {
                DB::commit();
                return new ProductInventory();
            }

            $inventory = ProductInventory::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
                ['quantity' => 0]
            );

            $oldQuantity = $inventory->quantity;
            $newQuantity = $oldQuantity;

            if ($type === 'add') {
                $newQuantity += $quantity;
            } elseif ($type === 'subtract') {
                $newQuantity -= $quantity;

                // Prevent negative stock if configured to do so
                // This could be a setting in your app
                if ($newQuantity < 0 && config('app.prevent_negative_stock', true)) {
                    throw new \Exception("Cannot reduce stock below zero for product {$product->name}");
                }
            }

            $inventory->quantity = $newQuantity;
            $inventory->save();

            // Record stock movement
            StockMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'quantity_change' => $type === 'add' ? $quantity : -$quantity,
                'quantity_before' => $oldQuantity,
                'quantity_after' => $newQuantity,
                'reference_type' => $reference,
                'notes' => $note,
            ]);

            DB::commit();
            return $inventory;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Process stock adjustment
     *
     * @param array $data
     * @return StockAdjustment
     */
    public function processStockAdjustment(array $data): StockAdjustment
    {
        DB::beginTransaction();
        try {
            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'tenant_id' => $data['tenant_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $data['user_id'],
                'reference' => $data['reference'],
                'date' => $data['date'],
                'adjustment_type' => $data['adjustment_type'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Process each item
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Add item to adjustment
                $adjustment->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'type' => $itemData['type'],
                    'reason' => $itemData['reason'] ?? null,
                ]);

                // Update stock
                $this->updateStock(
                    $product,
                    $data['warehouse_id'],
                    $itemData['quantity'],
                    $itemData['type'],
                    'stock_adjustment',
                    "Stock adjustment: {$data['reference']}"
                );
            }

            DB::commit();
            return $adjustment->load('items.product');
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Transfer stock between warehouses
     *
     * @param int $tenantId
     * @param int $sourceWarehouseId
     * @param int $destinationWarehouseId
     * @param int $productId
     * @param float $quantity
     * @param string $reference
     * @param string $notes
     * @return array
     */
    public function transferStock(
        int $tenantId,
        int $sourceWarehouseId,
        int $destinationWarehouseId,
        int $productId,
        float $quantity,
        string $reference = '',
        string $notes = ''
    ): array {
        if ($sourceWarehouseId === $destinationWarehouseId) {
            throw new \Exception("Source and destination warehouses cannot be the same");
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($productId);

            // Get source inventory
            $sourceInventory = ProductInventory::where('product_id', $product->id)
                ->where('warehouse_id', $sourceWarehouseId)
                ->first();

            if (!$sourceInventory || $sourceInventory->quantity < $quantity) {
                throw new \Exception("Insufficient stock in source warehouse");
            }

            // Subtract from source warehouse
            $this->updateStock(
                $product,
                $sourceWarehouseId,
                $quantity,
                'subtract',
                'stock_transfer_out',
                "Transfer to warehouse ID: {$destinationWarehouseId}, Ref: {$reference}"
            );

            // Add to destination warehouse
            $destinationInventory = $this->updateStock(
                $product,
                $destinationWarehouseId,
                $quantity,
                'add',
                'stock_transfer_in',
                "Transfer from warehouse ID: {$sourceWarehouseId}, Ref: {$reference}"
            );

            DB::commit();

            return [
                'source_inventory' => $sourceInventory->fresh(),
                'destination_inventory' => $destinationInventory,
            ];
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get stock value by warehouse
     *
     * @param int $warehouseId
     * @return array
     */
    public function getStockValue(int $warehouseId): array
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        $inventories = ProductInventory::where('warehouse_id', $warehouseId)
            ->with('product')
            ->get();

        $totalValue = 0;
        $stockItems = [];

        foreach ($inventories as $inventory) {
            $product = $inventory->product;
            $stockValue = $product->cost_price * $inventory->quantity;
            $totalValue += $stockValue;

            $stockItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $inventory->quantity,
                'cost_price' => $product->cost_price,
                'stock_value' => $stockValue,
            ];
        }

        return [
            'warehouse' => $warehouse,
            'total_value' => $totalValue,
            'items' => $stockItems,
        ];
    }
}
