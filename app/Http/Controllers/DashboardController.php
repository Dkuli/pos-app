<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Store;
use App\Services\ReportingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $reportingService;
    protected $notificationService;

    public function __construct(ReportingService $reportingService, NotificationService $notificationService)
    {
        $this->reportingService = $reportingService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get store IDs accessible to the user
        $storeIds = $user->stores->pluck('id')->toArray();

        // Get user's selected store if set (or first available)
        $selectedStoreId = $request->session()->get('selected_store_id');
        if (!$selectedStoreId || !in_array($selectedStoreId, $storeIds)) {
            $selectedStoreId = count($storeIds) > 0 ? $storeIds[0] : null;
            if ($selectedStoreId) {
                $request->session()->put('selected_store_id', $selectedStoreId);
            }
        }

        // Get dashboard summary
        $summary = $this->reportingService->getDashboardSummary($tenantId, $selectedStoreId);

        // Get low stock products
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->where('track_inventory', true)
            ->whereRaw('stock_alert_quantity >= (SELECT COALESCE(SUM(quantity), 0) FROM product_inventories WHERE product_id = products.id)')
            ->take(10)
            ->get();

        // Get recent sales
        $recentSales = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed');

        if ($selectedStoreId) {
            $recentSales->where('store_id', $selectedStoreId);
        }

        $recentSales = $recentSales->with(['customer', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        // Get recent purchases
        $recentPurchases = Purchase::where('tenant_id', $tenantId);

        if ($selectedStoreId) {
            $storeWarehouseIds = Store::find($selectedStoreId)->warehouses()->pluck('id')->toArray();
            if (count($storeWarehouseIds) > 0) {
                $recentPurchases->whereIn('warehouse_id', $storeWarehouseIds);
            }
        }

        $recentPurchases = $recentPurchases->with(['supplier'])
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        // Get recent expenses
        $recentExpenses = Expense::where('tenant_id', $tenantId);

        if ($selectedStoreId) {
            $recentExpenses->where('store_id', $selectedStoreId);
        }

        $recentExpenses = $recentExpenses->with(['category'])
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        // Get sales chart data (last 30 days)
        $salesChartData = $this->getSalesChartData($tenantId, $selectedStoreId);

        // Get user's unread notifications
        $notifications = $this->notificationService->getUnreadNotificationsForUser($user, 5);

        // Get stores for store selector
        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->get();

        return view('dashboard', compact(
            'summary',
            'lowStockProducts',
            'recentSales',
            'recentPurchases',
            'recentExpenses',
            'salesChartData',
            'notifications',
            'stores',
            'selectedStoreId'
        ));
    }

    /**
     * Set selected store in session.
     */
    public function setStore(Request $request)
    {
        $storeId = $request->input('store_id');
        $request->session()->put('selected_store_id', $storeId);

        return redirect()->route('dashboard');
    }

    /**
     * Get sales chart data for the last 30 days.
     */
    private function getSalesChartData($tenantId, $storeId = null)
    {
        $startDate = Carbon::today()->subDays(29);
        $endDate = Carbon::today();

        $query = Transaction::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $salesData = $query->selectRaw('DATE(transaction_date) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];

        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            $data[] = isset($salesData[$dateString]) ? $salesData[$dateString]->total : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get summary data (for AJAX requests)
     */
    public function getSummaryData(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Get selected store ID from session
        $selectedStoreId = $request->session()->get('selected_store_id');

        // Get updated summary
        $summary = $this->reportingService->getDashboardSummary($tenantId, $selectedStoreId);

        return response()->json($summary);
    }
}
