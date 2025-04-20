<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseService
{
    /**
     * Create a new expense
     *
     * @param array $data
     * @return Expense
     */
    public function create(array $data): Expense
    {
        // Handle receipt upload
        if (isset($data['receipt'])) {
            $data['receipt_path'] = $this->uploadReceipt($data['receipt']);
            unset($data['receipt']);
        }

        return Expense::create($data);
    }

    /**
     * Update an existing expense
     *
     * @param Expense $expense
     * @param array $data
     * @return Expense
     */
    public function update(Expense $expense, array $data): Expense
    {
        // Handle receipt upload
        if (isset($data['receipt'])) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $this->uploadReceipt($data['receipt']);
            unset($data['receipt']);
        }

        $expense->update($data);
        return $expense;
    }

    /**
     * Delete an expense
     *
     * @param Expense $expense
     * @return bool
     */
    public function delete(Expense $expense): bool
    {
        // Delete receipt if exists
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        return $expense->delete();
    }

    /**
     * Upload expense receipt
     *
     * @param $receipt
     * @return string
     */
    private function uploadReceipt($receipt): string
    {
        $fileName = 'expense_' . Str::random(10) . '.' . $receipt->getClientOriginalExtension();
        $path = $receipt->storeAs('expenses/receipts', $fileName, 'public');
        return $path;
    }

    /**
     * Get expenses by date range
     *
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $storeId
     * @param int|null $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpensesByDateRange(
        int $tenantId,
        string $dateFrom,
        string $dateTo,
        ?int $storeId = null,
        ?int $categoryId = null
    ) {
        $query = Expense::where('tenant_id', $tenantId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        return $query->with(['category', 'user', 'store'])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get expense summary by category
     *
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $storeId
     * @return array
     */
    public function getExpenseSummaryByCategory(
        int $tenantId,
        string $dateFrom,
        string $dateTo,
        ?int $storeId = null
    ): array {
        $query = Expense::where('tenant_id', $tenantId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $expenses = $query->with('category')->get();

        $summary = [];
        $categories = ExpenseCategory::where('tenant_id', $tenantId)->get()->keyBy('id');

        foreach ($expenses as $expense) {
            $categoryId = $expense->expense_category_id;
            $categoryName = $categories[$categoryId]->name ?? 'Uncategorized';

            if (!isset($summary[$categoryName])) {
                $summary[$categoryName] = 0;
            }

            $summary[$categoryName] += $expense->amount;
        }

        return $summary;
    }
}
