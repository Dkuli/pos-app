<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ReportingService;
use App\Services\ExpenseService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportingService;
    protected $expenseService;
    protected $paymentService;

    public function __construct(
        ReportingService $reportingService,
        ExpenseService $expenseService,
        PaymentService $paymentService
    ) {
        $this->reportingService = $reportingService;
        $this->expenseService = $expenseService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display reports dashboard.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display sales report.
     */
    public function salesReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $storeId = $request->filled('store_id') ? $request->input('store_id') : null;

        // Get stores for filter
        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate report
        $report = $this->reportingService->getSalesReport($tenantId, $dateFrom, $dateTo, $storeId);

        return view('reports.sales', compact('report', 'stores', 'dateFrom', 'dateTo', 'storeId'));
    }

    /**
     * Display product sales report.
     */
    public function productSalesReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $categoryId = $request->filled('category_id') ? $request->input('category_id') : null;

        // Get categories for filter
        $categories = Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate report
        $report = $this->reportingService->getProductSalesReport($tenantId, $dateFrom, $dateTo, $categoryId);

        return view('reports.product-sales', compact('report', 'categories', 'dateFrom', 'dateTo', 'categoryId'));
    }

    /**
     * Display profit/loss report.
     */
    public function profitReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $storeId = $request->filled('store_id') ? $request->input('store_id') : null;

        // Get stores for filter
        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate report
        $report = $this->reportingService->getProfitReport($tenantId, $dateFrom, $dateTo, $storeId);

        return view('reports.profit', compact('report', 'stores', 'dateFrom', 'dateTo', 'storeId'));
    }

    /**
     * Display inventory valuation report.
     */
    public function inventoryValuationReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Get filter values
        $warehouseId = $request->filled('warehouse_id') ? $request->input('warehouse_id') : null;
        $categoryId = $request->filled('category_id') ? $request->input('category_id') : null;

        // Get warehouses for filter
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get categories for filter
        $categories = Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate report
        $report = $this->reportingService->getInventoryValuationReport($tenantId, $warehouseId, $categoryId);

        return view('reports.inventory-valuation', compact(
            'report',
            'warehouses',
            'categories',
            'warehouseId',
            'categoryId'
        ));
    }

    /**
     * Display expense report.
     */
    public function expenseReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $storeId = $request->filled('store_id') ? $request->input('store_id') : null;

        // Get stores for filter
        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get expense categories for summary
        $expenseCategories = ExpenseCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate expense list
        $expenses = $this->expenseService->getExpensesByDateRange($tenantId, $dateFrom, $dateTo, $storeId);

        // Generate expense summary by category
        $expenseSummary = $this->expenseService->getExpenseSummaryByCategory($tenantId, $dateFrom, $dateTo, $storeId);

        return view('reports.expenses', compact(
            'expenses',
            'expenseSummary',
            'stores',
            'expenseCategories',
            'dateFrom',
            'dateTo',
            'storeId'
        ));
    }

    /**
     * Display payment methods report.
     */
    public function paymentMethodsReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Generate payment summary
        $paymentSummary = $this->paymentService->getPaymentSummaryByMethod($tenantId, $dateFrom, $dateTo);

        // Calculate total
        $total = array_sum($paymentSummary);

        return view('reports.payment-methods', compact('paymentSummary', 'total', 'dateFrom', 'dateTo'));
    }

    /**
     * Display customer report.
     */
    public function customerReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Get customer ID
        $customerId = $request->input('customer_id');

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Get customers for filter
        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        // If customer selected, get customer data
        $customerData = null;
        if ($customerId) {
            $customer = Customer::findOrFail($customerId);

            // Get transactions for this customer in the date range
            $transactions = $customer->transactions()
                ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->with(['items.product', 'payments'])
                ->latest()
                ->get();

            $customerData = [
                'customer' => $customer,
                'transactions' => $transactions,
                'total_spent' => $transactions->sum('total_amount'),
                'transaction_count' => $transactions->count(),
                'average_purchase' => $transactions->count() > 0
                    ? $transactions->sum('total_amount') / $transactions->count()
                    : 0,
            ];
        }

        return view('reports.customers', compact('customers', 'customerData', 'customerId', 'dateFrom', 'dateTo'));
    }

    /**
     * Display supplier report.
     */
    public function supplierReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Get supplier ID
        $supplierId = $request->input('supplier_id');

        // Default to current month if no dates provided
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        // Get suppliers for filter
        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        // If supplier selected, get supplier data
        $supplierData = null;
        if ($supplierId) {
            $supplier = Supplier::findOrFail($supplierId);

            // Get purchases for this supplier in the date range
            $purchases = $supplier->purchases()
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->with(['items.product', 'warehouse'])
                ->latest()
                ->get();

            $supplierData = [
                'supplier' => $supplier,
                'purchases' => $purchases,
                'total_amount' => $purchases->sum('total_amount'),
                'purchase_count' => $purchases->count(),
                'average_purchase' => $purchases->count() > 0
                    ? $purchases->sum('total_amount') / $purchases->count()
                    : 0,
            ];
        }

        return view('reports.suppliers', compact('suppliers', 'supplierData', 'supplierId', 'dateFrom', 'dateTo'));
    }

    /**
     * Export report as CSV.
     */
    public function exportCsv(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $reportType = $request->input('report_type');
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $storeId = $request->filled('store_id') ? $request->input('store_id') : null;
        $categoryId = $request->filled('category_id') ? $request->input('category_id') : null;
        $warehouseId = $request->filled('warehouse_id') ? $request->input('warehouse_id') : null;

        // Generate filename
        $fileName = $reportType . '_report_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($tenantId, $reportType, $dateFrom, $dateTo, $storeId, $categoryId, $warehouseId) {
            $file = fopen('php://output', 'w');

            switch ($reportType) {
                case 'sales':
                    $this->exportSalesReport($file, $tenantId, $dateFrom, $dateTo, $storeId);
                    break;

                case 'product_sales':
                    $this->exportProductSalesReport($file, $tenantId, $dateFrom, $dateTo, $categoryId);
                    break;

                case 'inventory':
                    $this->exportInventoryReport($file, $tenantId, $warehouseId, $categoryId);
                    break;

                case 'profit':
                    $this->exportProfitReport($file, $tenantId, $dateFrom, $dateTo, $storeId);
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales report data to CSV.
     */
    private function exportSalesReport($file, $tenantId, $dateFrom, $dateTo, $storeId)
    {
        // Define headers
        $headers = [
            'Date', 'Transaction #', 'Customer', 'Store', 'Payment Status', 'Payment Method',
            'Total Items', 'Tax Amount', 'Discount', 'Total Amount'
        ];

        fputcsv($file, $headers);

        // Get transaction data
        $transactions = \App\Models\Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

        if ($storeId) {
            $transactions->where('store_id', $storeId);
        }

        $transactions = $transactions->with(['customer', 'store', 'payments', 'items'])
            ->orderBy('transaction_date')
            ->get();

        foreach ($transactions as $transaction) {
            $row = [
                $transaction->transaction_date,
                $transaction->transaction_number,
                $transaction->customer ? $transaction->customer->name : 'Walk-in Customer',
                $transaction->store->name,
                $transaction->payment_status,
                $transaction->payments->isNotEmpty() ? $transaction->payments->first()->payment_method : 'N/A',
                $transaction->items->sum('quantity'),
                $transaction->tax_amount,
                $transaction->discount_amount,
                $transaction->total_amount
            ];

            fputcsv($file, $row);
        }
    }

    /**
     * Export product sales report data to CSV.
     */
    private function exportProductSalesReport($file, $tenantId, $dateFrom, $dateTo, $categoryId)
    {
        // Define headers
        $headers = [
            'Product ID', 'Product Name', 'Category', 'Quantity Sold', 'Total Sales'
        ];

        fputcsv($file, $headers);

        // Get report data
        $report = $this->reportingService->getProductSalesReport($tenantId, $dateFrom, $dateTo, $categoryId);

        foreach ($report['products'] as $product) {
            $row = [
                $product->product_id,
                $product->product_name,
                $product->category_name,
                $product->quantity_sold,
                $product->total_sales
            ];

            fputcsv($file, $row);
        }
    }

    /**
     * Export inventory report data to CSV.
     */
    private function exportInventoryReport($file, $tenantId, $warehouseId, $categoryId)
    {
        // Define headers
        $headers = [
            'Product ID', 'Product Name', 'SKU', 'Category', 'Quantity', 'Cost Price', 'Value'
        ];

        fputcsv($file, $headers);

        // Get report data
        $report = $this->reportingService->getInventoryValuationReport($tenantId, $warehouseId, $categoryId);

        foreach ($report['inventory'] as $item) {
            $row = [
                $item['id'],
                $item['name'],
                $item['sku'],
                $item['category'],
                $item['quantity'],
                $item['cost_price'],
                $item['value']
            ];

            fputcsv($file, $row);
        }
    }

    /**
     * Export profit report data to CSV.
     */
    private function exportProfitReport($file, $tenantId, $dateFrom, $dateTo, $storeId)
    {
        // Define headers
        $headers = [
            'Metric', 'Amount'
        ];

        fputcsv($file, $headers);

        // Get report data
        $report = $this->reportingService->getProfitReport($tenantId, $dateFrom, $dateTo, $storeId);

        // Write report data
        fputcsv($file, ['Total Sales', $report['total_sales']]);
        fputcsv($file, ['Cost of Goods', $report['cost_of_goods']]);
        fputcsv($file, ['Gross Profit', $report['gross_profit']]);
        fputcsv($file, ['Expenses', $report['expenses']]);
        fputcsv($file, ['Net Profit', $report['net_profit']]);
        fputcsv($file, ['Profit Margin (%)', $report['profit_margin']]);
    }
}
