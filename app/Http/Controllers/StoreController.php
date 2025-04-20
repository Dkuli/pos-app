<?php

namespace App\Http\Controllers;

use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\Store;
use App\Models\Tenant;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
class StoreController extends Controller
{
    use AuthorizesRequests;

    protected $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $stores = Store::where('tenant_id', $tenantId)->get();
        return view('stores.index', compact('stores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = Auth::user()->tenant->users()->where('is_active', true)->get();
        return view('stores.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStoreRequest $request)
    {
        try {
            $store = $this->storeService->create($request->validated());

            // Assign users if selected
            if ($request->has('users')) {
                $this->storeService->assignUsers($store, $request->users);
            }

            return redirect()->route('stores.show', $store->id)
                ->with('success', 'Store created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating store: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        $this->authorize('view', $store);

        $statistics = $this->storeService->getSalesStatistics($store);
        return view('stores.show', compact('store', 'statistics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store)
    {
        $this->authorize('update', $store);

        $users = Auth::user()->tenant->users()->where('is_active', true)->get();
        $assignedUsers = $store->users->pluck('id')->toArray();

        return view('stores.edit', compact('store', 'users', 'assignedUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStoreRequest $request, Store $store)
    {
        $this->authorize('update', $store);

        try {
            $this->storeService->update($store, $request->validated());

            // Update user assignments
            if ($request->has('users')) {
                $this->storeService->assignUsers($store, $request->users);
            }

            return redirect()->route('stores.show', $store->id)
                ->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating store: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        $this->authorize('delete', $store);

        try {
            $this->storeService->delete($store);
            return redirect()->route('stores.index')
                ->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting store: ' . $e->getMessage());
        }
    }

    /**
     * Toggle store active status.
     */
    public function toggleStatus(Store $store)
    {
        $this->authorize('update', $store);

        $store->is_active = !$store->is_active;
        $store->save();

        return back()->with('success', 'Store status updated.');
    }
}
