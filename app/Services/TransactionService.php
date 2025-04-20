<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new transaction
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction
    {
        DB::beginTransaction();
        try {
            // Generate transaction number if not provided
            if (!isset($data['transaction_number']) || empty($data['transaction_number'])) {
                $data['transaction_number'] = $this->generateTransactionNumber($data['store_id']);
            }

            // Create transaction record
            $transaction = Transaction::create([
                'tenant_id' => $data['tenant_id'],
                'store_id' => $data['store_id'],
                'user_id' => $data['user_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'transaction_number' => $data['transaction_number'],
                'transaction_date' => $data['transaction_date'],
                'status' => $data['status'],
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
            ]);

            // Create transaction items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'cost' => $itemData['cost'] ?? null,
                    'discount' => $itemData['discount'] ?? 0,
                    'tax_amount' => $itemData['tax_amount'] ?? 0,
                    'total' => $itemData['total'],
                ]);

                // Update product stock if product tracks inventory and transaction is completed
                if ($product->track_inventory && $data['status'] === 'completed') {
                    $this->inventoryService->updateStock(
                        $product,
                        $this->getDefaultWarehouseId($data['store_id']),
                        $itemData['quantity'],
                        'subtract',
                        'sale',
                        "Sale: {$data['transaction_number']}"
                    );
                }
            }

            // Create payments if provided
            if (isset($data['payments']) && !empty($data['payments'])) {
                foreach ($data['payments'] as $paymentData) {
                    Payment::create([
                        'tenant_id' => $data['tenant_id'],
                        'transaction_id' => $transaction->id,
                        'amount' => $paymentData['amount'],
                        'payment_method' => $paymentData['payment_method'],
                        'reference' => $paymentData['reference'] ?? null,
                        'payment_date' => $data['transaction_date'],
                    ]);
                }
            }

            DB::commit();
            return $transaction->load(['items.product', 'customer', 'payments']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update an existing transaction
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        DB::beginTransaction();
        try {
            $oldStatus = $transaction->status;
            $newStatus = $data['status'] ?? $oldStatus;
            $storeId = $data['store_id'] ?? $transaction->store_id;

            // If transaction was completed, reverse inventory entries
            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                foreach ($transaction->items as $item) {
                    $product = $item->product;
                    if ($product->track_inventory) {
                        $this->inventoryService->updateStock(
                            $product,
                            $this->getDefaultWarehouseId($transaction->store_id),
                            $item->quantity,
                            'add',
                            'sale_updated',
                            "Sale update reversal: {$transaction->transaction_number}"
                        );
                    }
                }
            }

            // Update transaction record
            $transaction->update([
                'store_id' => $storeId,
                'user_id' => $data['user_id'] ?? $transaction->user_id,
                'customer_id' => $data['customer_id'] ?? $transaction->customer_id,
                'transaction_date' => $data['transaction_date'] ?? $transaction->transaction_date,
                'status' => $newStatus,
                'payment_status' => $data['payment_status'] ?? $transaction->payment_status,
                'notes' => $data['notes'] ?? $transaction->notes,
                'tax_amount' => $data['tax_amount'] ?? $transaction->tax_amount,
                'discount_amount' => $data['discount_amount'] ?? $transaction->discount_amount,
                'total_amount' => $data['total_amount'] ?? $transaction->total_amount,
            ]);

            // Handle items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $transaction->items()->delete();

                // Create new items
                foreach ($data['items'] as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);

                    $transaction->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                        'cost' => $itemData['cost'] ?? null,
                        'discount' => $itemData['discount'] ?? 0,
                        'tax_amount' => $itemData['tax_amount'] ?? 0,
                        'total' => $itemData['total'],
                    ]);

                    // Update product stock if new status is completed and product tracks inventory
                    if ($newStatus === 'completed' && $product->track_inventory) {
                        $this->inventoryService->updateStock(
                            $product,
                            $this->getDefaultWarehouseId($storeId),
                            $itemData['quantity'],
                            'subtract',
                            'sale',
                            "Sale update: {$transaction->transaction_number}"
                        );
                    }
                }
            }

            // Handle payments if provided
            if (isset($data['payments'])) {
                // Delete existing payments
                $transaction->payments()->delete();

                // Create new payments
                foreach ($data['payments'] as $paymentData) {
                    Payment::create([
                        'tenant_id' => $transaction->tenant_id,
                        'transaction_id' => $transaction->id,
                        'amount' => $paymentData['amount'],
                        'payment_method' => $paymentData['payment_method'],
                        'reference' => $paymentData['reference'] ?? null,
                        'payment_date' => $data['transaction_date'] ?? $transaction->transaction_date,
                    ]);
                }

                // Update payment status
                $totalPaid = $transaction->payments()->sum('amount');
                if ($totalPaid >= $transaction->total_amount) {
                    $transaction->update(['payment_status' => 'paid']);
                } elseif ($totalPaid > 0) {
                    $transaction->update(['payment_status' => 'partial']);
                } else {
                    $transaction->update(['payment_status' => 'unpaid']);
                }
            }

            DB::commit();
            return $transaction->fresh(['items.product', 'customer', 'payments']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete a transaction
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function delete(Transaction $transaction): bool
    {
        DB::beginTransaction();
        try {
            // If transaction was completed, reverse inventory entries
            if ($transaction->status === 'completed') {
                foreach ($transaction->items as $item) {
                    $product = $item->product;
                    if ($product->track_inventory) {
                        $this->inventoryService->updateStock(
                            $product,
                            $this->getDefaultWarehouseId($transaction->store_id),
                            $item->quantity,
                            'add',
                            'sale_deleted',
                            "Sale deletion: {$transaction->transaction_number}"
                        );
                    }
                }
            }

            // Delete transaction items
            $transaction->items()->delete();

            // Delete payments
            $transaction->payments()->delete();

            // Delete transaction
            $result = $transaction->delete();

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Change transaction status
     *
     * @param Transaction $transaction
     * @param string $status
     * @return Transaction
     */
    public function changeStatus(Transaction $transaction, string $status): Transaction
    {
        DB::beginTransaction();
        try {
            $oldStatus = $transaction->status;

            // If going from non-completed to completed
            if ($status === 'completed' && $oldStatus !== 'completed') {
                foreach ($transaction->items as $item) {
                    $product = $item->product;
                    if ($product->track_inventory) {
                        $this->inventoryService->updateStock(
                            $product,
                            $this->getDefaultWarehouseId($transaction->store_id),
                            $item->quantity,
                            'subtract',
                            'sale_status_change',
                            "Sale status change from {$oldStatus} to {$status}: {$transaction->transaction_number}"
                        );
                    }
                }
            }

            // If going from completed to something else
            if ($oldStatus === 'completed' && $status !== 'completed') {
                foreach ($transaction->items as $item) {
                    $product = $item->product;
                    if ($product->track_inventory) {
                        $this->inventoryService->updateStock(
                            $product,
                            $this->getDefaultWarehouseId($transaction->store_id),
                            $item->quantity,
                            'add',
                            'sale_status_change',
                            "Sale status change from {$oldStatus} to {$status}: {$transaction->transaction_number}"
                        );
                    }
                }
            }

            $transaction->update(['status' => $status]);

            DB::commit();
            return $transaction->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Generate unique transaction number
     *
     * @param int $storeId
     * @return string
     */
    private function generateTransactionNumber(int $storeId): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastTransaction = Transaction::where('store_id', $storeId)
            ->where('transaction_number', 'like', "{$prefix}{$date}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, strlen($prefix) + strlen($date)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get default warehouse ID for a store
     *
     * @param int $storeId
     * @return int
     */
    private function getDefaultWarehouseId(int $storeId): int
    {
        // This could be a setting or relationship in your database
        // For now, we'll assume each store has a default warehouse with the same ID
        return $storeId;
    }

    /**
     * Get customer purchase history
     *
     * @param Customer $customer
     * @return array
     */
    public function getCustomerPurchaseHistory(Customer $customer): array
    {
        $transactions = $customer->transactions()
            ->with(['items.product'])
            ->latest()
            ->get();

        $totalAmount = $transactions->sum('total_amount');
        $transactionCount = $transactions->count();

        return [
            'customer' => $customer,
            'transactions' => $transactions,
            'total_amount' => $totalAmount,
            'transaction_count' => $transactionCount,
            'average_purchase' => $transactionCount > 0 ? $totalAmount / $transactionCount : 0,
        ];
    }
}
