<?php

namespace App\Http\Controllers;

use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    use AuthorizesRequests;

    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $warehouses = Warehouse::where('tenant_id', $tenantId)->get();
        return view('warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('warehouses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $warehouse = Warehouse::create($data);

            return redirect()->route('warehouses.show', $warehouse->id)
                ->with('success', 'Warehouse created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        $this->authorize('view', $warehouse);

        // Get stock value and inventory information
        $stockInfo = $this->inventoryService->getStockValue($warehouse->id);

        return view('warehouses.show', compact('warehouse', 'stockInfo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);
        return view('warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        try {
            $warehouse->update($request->validated());

            return redirect()->route('warehouses.show', $warehouse->id)
                ->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete', $warehouse);

        try {
            // Check if warehouse has inventory
            if ($warehouse->inventories()->count() > 0) {
                return back()->with('error', 'Cannot delete warehouse with inventory.');
            }

            $warehouse->delete();

            return redirect()->route('warehouses.index')
                ->with('success', 'Warehouse deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Toggle warehouse active status.
     */
    public function toggleStatus(Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $warehouse->is_active = !$warehouse->is_active;
        $warehouse->save();

        return back()->with('success', 'Warehouse status updated.');
    }

    /**
     * Show stock transfer form.
     */
    public function showTransferForm(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $warehouses = Warehouse::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        return view('warehouses.transfer', compact('warehouses'));
    }

    /**
     * Process stock transfer between warehouses.
     */
    public function processTransfer(Request $request)
    {
        $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $tenantId = $request->user()->tenant_id;

            $transfer = $this->inventoryService->transferStock(
                $tenantId,
                $request->source_warehouse_id,
                $request->destination_warehouse_id,
                $request->product_id,
                $request->quantity,
                $request->reference,
                $request->notes
            );

            return redirect()->route('warehouses.index')
                ->with('success', 'Stock transferred successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error transferring stock: ' . $e->getMessage());
        }
    }
}
