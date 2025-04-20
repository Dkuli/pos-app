<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
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

        $query = Supplier::where('tenant_id', $tenantId);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $supplier = Supplier::create($data);

            return redirect()->route('suppliers.show', $supplier->id)
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        // Get supplier purchase history
        $history = $this->purchaseService->getSupplierPurchaseHistory($supplier);

        return view('suppliers.show', compact('supplier', 'history'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        try {
            $supplier->update($request->validated());

            return redirect()->route('suppliers.show', $supplier->id)
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating supplier: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        try {
            // Check if supplier has purchases
            if ($supplier->purchases()->count() > 0) {
                return back()->with('error', 'Cannot delete supplier with purchase history.');
            }

            $supplier->delete();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting supplier: ' . $e->getMessage());
        }
    }

    /**
     * Toggle supplier active status.
     */
    public function toggleStatus(Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $supplier->is_active = !$supplier->is_active;
        $supplier->save();

        return back()->with('success', 'Supplier status updated.');
    }

    /**
     * Search suppliers (AJAX).
     */
    public function search(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $term = $request->input('term');

        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'email', 'phone')
            ->limit(10)
            ->get();

        return response()->json($suppliers);
    }
}
