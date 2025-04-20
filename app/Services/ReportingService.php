<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Get sales report by date range
     *
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $storeId
     * @return array
     */
    public function getSalesReport(int $tenantId, string $dateFrom, string $dateTo, ?int $storeId = null): array
    {
        $query = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $transactions = $query->get();

        $totalSales = $transactions->sum('total_amount');
        $totalTax = $transactions->sum('tax_amount');
        $totalDiscount = $transactions->sum('discount_amount');
        $netSales = $totalSales - $totalTax;

        // Group sales by date
        $dailySales = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->transaction_date)->format('Y-m-d');
        })->map(function ($dayTransactions) {
            return $dayTransactions->sum('total_amount');
        });

        // Payment methods
        $paymentMethods = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->toArray();

        return [
            'total_sales' => $totalSales,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
            'net_sales' => $netSales,
            'transaction_count' => $transactions->count(),
            'daily_sales' => $dailySales,
            'payment_methods' => $paymentMethods,
        ];
    }

    /**
     * Get product sales report
     *
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $categoryId
     * @return array
     */
    public function getProductSalesReport(int $tenantId, string $dateFrom, string $dateTo, ?int $categoryId = null): array
    {
        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.transaction_date', [$dateFrom, $dateTo]);

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $productSales = $query->select(
                'products.id as product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(transaction_items.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_items.total) as total_sales')
            )
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('total_sales')
            ->get();

        $totalQuantitySold = $productSales->sum('quantity_sold');
        $totalSales = $productSales->sum('total_sales');

        return [
            'products' => $productSales,
            'total_quantity_sold' => $totalQuantitySold,
            'total_sales' => $totalSales,
        ];
    }

    /**
     * Get profit report
     *
     * @param int $tenantId
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|null $storeId
     * @return array
     */
    public function getProfitReport(int $tenantId, string $dateFrom, string $dateTo, ?int $storeId = null): array
    {
        // Get sales with cost
        $salesQuery = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.transaction_date', [$dateFrom, $dateTo]);

        if ($storeId) {
            $salesQuery->where('transactions.store_id', $storeId);
        }

        $salesData = $salesQuery->select(
                DB::raw('SUM(transaction_items.total) as total_sales'),
                DB::raw('SUM(transaction_items.cost * transaction_items.quantity) as total_cost')
            )
            ->first();

        $totalSales = $salesData->total_sales ?? 0;
        $costOfGoods = $salesData->total_cost ?? 0;

        // Get expenses
        $expenseQuery = Expense::where('tenant_id', $tenantId)
            ->whereBetween('date', [$dateFrom, $dateTo]);

        if ($storeId) {
            $expenseQuery->where('store_id', $storeId);
        }

        $totalExpenses = $expenseQuery->sum('amount');

        // Calculate profit
        $grossProfit = $totalSales - $costOfGoods;
        $netProfit = $grossProfit - $totalExpenses;
        $profitMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;

        return [
            'total_sales' => $totalSales,
            'cost_of_goods' => $costOfGoods,
            'gross_profit' => $grossProfit,
            'expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
        ];
    }

    /**
     * Get inventory valuation report
     *
     * @param int $tenantId
     * @param int|null $warehouseId
     * @param int|null $categoryId
     * @return array
     */
    public function getInventoryValuationReport(int $tenantId, ?int $warehouseId = null, ?int $categoryId = null): array
    {
        $query = DB::table('product_inventories')
            ->join('products', 'product_inventories.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.tenant_id', $tenantId)
            ->where('products.track_inventory', true);

        if ($warehouseId) {
            $query->where('product_inventories.warehouse_id', $warehouseId);
        }

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $inventoryData = $query->select(
                'products.id',
                'products.name',
                'products.sku',
                'categories.name as category',
                'product_inventories.warehouse_id',
                'product_inventories.quantity',
                'products.cost_price'
            )
            ->get();

        $totalItems = $inventoryData->count();
        $totalQuantity = $inventoryData->sum('quantity');
        $totalValue = $inventoryData->sum(function ($item) {
            return $item->quantity * $item->cost_price;
        });

        // Group by product
        $groupedInventory = $inventoryData->groupBy('id')->map(function ($items) {
            $first = $items->first();
            return [
                'id' => $first->id,
                'name' => $first->name,
                'sku' => $first->sku,
                'category' => $first->category,
                'quantity' => $items->sum('quantity'),
                'cost_price' => $first->cost_price,
                'value' => $items->sum('quantity') * $first->cost_price,
            ];
        })->values();

        return [
            'inventory' => $groupedInventory,
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
        ];
    }

    /**
     * Get dashboard summary
     *
     * @param int $tenantId
     * @param int|null $storeId
     * @return array
     */
    public function getDashboardSummary(int $tenantId, ?int $storeId = null): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Today's sales
        $todaySalesQuery = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today);

        if ($storeId) {
            $todaySalesQuery->where('store_id', $storeId);
        }

        $todaySales = $todaySalesQuery->sum('total_amount');
        $todayTransactions = $todaySalesQuery->count();

        // Yesterday's sales
        $yesterdaySalesQuery = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $yesterday);

        if ($storeId) {
            $yesterdaySalesQuery->where('store_id', $storeId);
        }

        $yesterdaySales = $yesterdaySalesQuery->sum('total_amount');

        // Weekly sales
        $weeklySalesQuery = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('transaction_date', '>=', $startOfWeek);

        if ($storeId) {
            $weeklySalesQuery->where('store_id', $storeId);
        }

        $weeklySales = $weeklySalesQuery->sum('total_amount');

        // Monthly sales
        $monthlySalesQuery = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('transaction_date', '>=', $startOfMonth);

        if ($storeId) {
            $monthlySalesQuery->where('store_id', $storeId);
        }

        $monthlySales = $monthlySalesQuery->sum('total_amount');

        // Low stock products
        $lowStockQuery = Product::where('tenant_id', $tenantId)
            ->where('track_inventory', true)
            ->whereRaw('stock_alert_quantity >= (SELECT COALESCE(SUM(quantity), 0) FROM product_inventories WHERE product_id = products.id)');

        $lowStockCount = $lowStockQuery->count();

        // Top selling products (this month)
        $topProductsQuery = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.tenant_id', $tenantId)
            ->where('transactions.status', 'completed')
            ->where('transactions.transaction_date', '>=', $startOfMonth);

        if ($storeId) {
            $topProductsQuery->where('transactions.store_id', $storeId);
        }

        $topProducts = $topProductsQuery->select(
                'products.id',
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as quantity_sold'),
                DB::raw('SUM(transaction_items.total) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();

        return [
            'today_sales' => $todaySales,
            'today_transactions' => $todayTransactions,
            'yesterday_sales' => $yesterdaySales,
            'weekly_sales' => $weeklySales,
            'monthly_sales' => $monthlySales,
            'sales_change_percent' => $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0,
            'low_stock_count' => $lowStockCount,
            'top_products' => $topProducts,
        ];
    }
}
