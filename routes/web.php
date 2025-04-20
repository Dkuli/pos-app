<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ProductController,
    CategoryController,
    UnitController,
    WarehouseController,
    TransactionController,
    PaymentController,
    CustomerController,
    SupplierController,
    PurchaseController,
    ExpenseController,
    DiscountController,
    ReportController,
    SettingController,
    UserController,
    TenantController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page
Route::get('/', function () {
    return view('welcome');
});

// Auth routes (login, register, etc.)
require __DIR__.'/auth.php';

// Protected routes - require authentication
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/set-store', [DashboardController::class, 'setStore'])->name('dashboard.set-store');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Units
    Route::resource('units', UnitController::class);
    Route::post('/units/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');

    // Warehouses
    Route::resource('warehouses', WarehouseController::class);
    Route::post('/warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');
    Route::get('/stock-transfer', [WarehouseController::class, 'showTransferForm'])->name('warehouses.transfer');
    Route::post('/stock-transfer', [WarehouseController::class, 'processTransfer'])->name('warehouses.process-transfer');

    // Transactions
    Route::resource('transactions', TransactionController::class);
    Route::get('/transactions/{transaction}/print', [TransactionController::class, 'printReceipt'])->name('transactions.print');

    // Payments
    Route::resource('transactions.payments', PaymentController::class)->shallow();
    Route::get('/payments/{payment}/print', [PaymentController::class, 'printReceipt'])->name('payments.print');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/change-status', [PurchaseController::class, 'changeStatus'])->name('purchases.change-status');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::get('/expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');

    // Discounts
    Route::resource('discounts', DiscountController::class);
    Route::post('/discounts/{discount}/toggle-status', [DiscountController::class, 'toggleStatus'])->name('discounts.toggle-status');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('/products', [ReportController::class, 'productSalesReport'])->name('products');
        Route::get('/inventory', [ReportController::class, 'inventoryValuationReport'])->name('inventory');
        Route::get('/profit', [ReportController::class, 'profitReport'])->name('profit');
        Route::get('/expenses', [ReportController::class, 'expenseReport'])->name('expenses');
        Route::get('/customers', [ReportController::class, 'customerReport'])->name('customers');
        Route::get('/suppliers', [ReportController::class, 'supplierReport'])->name('suppliers');
        Route::get('/export', [ReportController::class, 'exportCsv'])->name('export');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::put('/general', [SettingController::class, 'updateGeneral']);
        Route::get('/pos', [SettingController::class, 'pos'])->name('pos');
        Route::put('/pos', [SettingController::class, 'updatePos']);
        Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [SettingController::class, 'updateNotifications']);
    });

    // User Management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Tenant Management
    Route::resource('tenants', TenantController::class);
    Route::post('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');
});
