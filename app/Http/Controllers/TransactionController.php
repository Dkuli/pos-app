<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Services\TransactionService;
use App\Services\DiscountService;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use AuthorizesRequests;
    protected $transactionService;
    protected $discountService;

    public function __construct(TransactionService $transactionService, DiscountService $discountService)
    {
        $this->transactionService = $transactionService;
        $this->discountService = $discountService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $storeIds = $request->user()->stores->pluck('id')->toArray();

        $query = Transaction::where('tenant_id', $tenantId)
            ->whereIn('store_id', $storeIds);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('store')) {
            $query->where('store_id', $request->input('store'));
        }

        if ($request->filled('customer')) {
            $query->where('customer_id', $request->input('customer'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transaction_date', [$request->input('date_from'), $request->input('date_to')]);
        }

        $transactions = $query->with(['store', 'customer', 'user'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        return view('transactions.index', compact('transactions', 'stores', 'customers'));
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

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $activeDiscounts = $this->discountService->getActiveDiscounts($tenantId);

        return view('transactions.create', compact('stores', 'customers', 'activeDiscounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;
            $data['user_id'] = Auth::id();

            $transaction = $this->transactionService->create($data);

            return redirect()->route('transactions.show', $transaction->id)
                ->with('success', 'Transaction created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        $transaction->load(['store', 'customer', 'user', 'items.product', 'payments']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $tenantId = Auth::user()->tenant_id;
        $storeIds = Auth::user()->stores->pluck('id')->toArray();

        $stores = Store::where('tenant_id', $tenantId)
            ->whereIn('id', $storeIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $transaction->load('items.product', 'payments');

        $activeDiscounts = $this->discountService->getActiveDiscounts($tenantId);

        return view('transactions.edit', compact('transaction', 'stores', 'customers', 'activeDiscounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        try {
            $data = $request->validated();

            $transaction = $this->transactionService->update($transaction, $data);

            return redirect()->route('transactions.show', $transaction->id)
                ->with('success', 'Transaction updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating transaction: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        try {
            $this->transactionService->delete($transaction);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting transaction: ' . $e->getMessage());
        }
    }

    /**
     * Change transaction status.
     */
    public function changeStatus(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $request->validate([
            'status' => 'required|in:completed,pending,canceled',
        ]);

        try {
            $this->transactionService->changeStatus($transaction, $request->status);

            return back()->with('success', 'Transaction status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating transaction status: ' . $e->getMessage());
        }
    }

    /**
     * Print transaction receipt.
     */
    public function printReceipt(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        $transaction->load(['store', 'customer', 'items.product', 'payments']);

        return view('transactions.receipt', compact('transaction'));
    }

    /**
     * Calculate product discount (AJAX).
     */
    public function calculateDiscount(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'total_amount' => 'required|numeric|min:0',
        ]);

        try {
            $product = Product::where('id', $request->product_id)->firstOrFail();
            $discount = $this->discountService->calculateProductDiscount(
                $product,
                $request->quantity,
                $request->total_amount
            );

            return response()->json($discount);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
