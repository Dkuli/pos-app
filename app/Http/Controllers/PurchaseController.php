<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    use AuthorizesRequests;
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $query = Purchase::where('tenant_id', $tenantId);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->input('supplier'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->input('date_from'), $request->input('date_to')]);
        }

        $purchases = $query->with(['supplier', 'warehouse', 'user'])
            ->orderBy('date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;

        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('purchases.create', compact('suppliers', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;
            $data['user_id'] = Auth::id();

            $purchase = $this->purchaseService->create($data);

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Purchase created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $this->authorize('view', $purchase);

        $purchase->load(['supplier', 'warehouse', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $this->authorize('update', $purchase);

        $tenantId = Auth::user()->tenant_id;

        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $purchase->load('items.product');

        return view('purchases.edit', compact('purchase', 'suppliers', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        $this->authorize('update', $purchase);

        try {
            $data = $request->validated();

            $purchase = $this->purchaseService->update($purchase, $data);

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        $this->authorize('delete', $purchase);

        try {
            $this->purchaseService->delete($purchase);

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting purchase: ' . $e->getMessage());
        }
    }

    /**
     * Change purchase status.
     */
    public function changeStatus(Request $request, Purchase $purchase)
    {
        $this->authorize('update', $purchase);

        $request->validate([
            'status' => 'required|in:ordered,pending,received,canceled',
        ]);

        try {
            $this->purchaseService->changeStatus($purchase, $request->status);

            return back()->with('success', 'Purchase status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating purchase status: ' . $e->getMessage());
        }
    }
}
