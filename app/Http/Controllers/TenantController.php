<?php

namespace App\Http\Controllers;



use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::all();
        return view('tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request)
    {
        try {
            $tenant = $this->tenantService->create($request->validated());
            return redirect()->route('tenants.show', $tenant->id)
                ->with('success', 'Tenant created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating tenant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        try {
            $this->tenantService->update($tenant, $request->validated());
            return redirect()->route('tenants.show', $tenant->id)
                ->with('success', 'Tenant updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating tenant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        try {
            $this->tenantService->delete($tenant);
            return redirect()->route('tenants.index')
                ->with('success', 'Tenant deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting tenant: ' . $e->getMessage());
        }
    }

    /**
     * Toggle tenant status.
     */
    public function toggleStatus(Tenant $tenant)
    {
        $tenant->status = !$tenant->status;
        $tenant->save();

        return back()->with('success', 'Tenant status updated.');
    }
}
