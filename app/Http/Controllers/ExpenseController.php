<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Store;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    use AuthorizesRequests;
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        $query = Expense::where('tenant_id', $tenantId)
            ->whereIn('store_id', $storeIds);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('store')) {
            $query->where('store_id', $request->input('store'));
        }

        if ($request->filled('category')) {
            $query->where('expense_category_id', $request->input('category'));
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->input('date_from'), $request->input('date_to')]);
        }

        $expenses = $query->with(['category', 'store', 'user'])
            ->orderBy('date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = ExpenseCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expenses.index', compact('expenses', 'stores', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $storeIds = Auth::user()->stores->pluck('id')->toArray();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = ExpenseCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expenses.create', compact('stores', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;
            $data['user_id'] = Auth::id();

            $expense = $this->expenseService->create($data);

            return redirect()->route('expenses.show', $expense->id)
                ->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating expense: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        $expense->load(['category', 'store', 'user']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);

        $tenantId = Auth::user()->tenant_id;
        $storeIds = Auth::user()->stores->pluck('id')->toArray();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = ExpenseCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('expenses.edit', compact('expense', 'stores', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        try {
            $data = $request->validated();

            $expense = $this->expenseService->update($expense, $data);

            return redirect()->route('expenses.show', $expense->id)
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating expense: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        try {
            $this->expenseService->delete($expense);

            return redirect()->route('expenses.index')
                ->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting expense: ' . $e->getMessage());
        }
    }

    /**
     * Export expenses to CSV/Excel
     */
    public function export(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        // Get date range
        $dateFrom = $request->input('date_from', date('Y-m-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $storeId = $request->filled('store') ? $request->input('store') : null;
        $categoryId = $request->filled('category') ? $request->input('category') : null;

        $expenses = $this->expenseService->getExpensesByDateRange(
            $tenantId,
            $dateFrom,
            $dateTo,
            $storeId,
            $categoryId
        );

        // Generate export filename
        $fileName = 'expenses_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Date', 'Reference', 'Category', 'Store', 'Amount', 'Payment Method', 'Notes'];

        $callback = function() use ($expenses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date,
                    $expense->reference,
                    $expense->category->name,
                    $expense->store->name,
                    $expense->amount,
                    $expense->payment_method,
                    $expense->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
