<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $units = Unit::where('tenant_id', $tenantId)->get();
        return view('units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $baseUnits = Unit::where('tenant_id', $tenantId)
            ->whereNull('base_unit_id')
            ->get();

        return view('units.create', compact('baseUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $unit = Unit::create($data);

            return redirect()->route('units.index')
                ->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating unit: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        $this->authorize('view', $unit);

        // Get sub-units
        $subUnits = Unit::where('base_unit_id', $unit->id)->get();

        return view('units.show', compact('unit', 'subUnits'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        $this->authorize('update', $unit);

        $tenantId = Auth::user()->tenant_id;
        $baseUnits = Unit::where('tenant_id', $tenantId)
            ->whereNull('base_unit_id')
            ->where('id', '!=', $unit->id)
            ->get();

        return view('units.edit', compact('unit', 'baseUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->authorize('update', $unit);

        try {
            $unit->update($request->validated());

            return redirect()->route('units.index')
                ->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating unit: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $this->authorize('delete', $unit);

        try {
            // Check if unit has products or sub-units
            if ($unit->products()->count() > 0) {
                return back()->with('error', 'Cannot delete unit with associated products.');
            }

            if (Unit::where('base_unit_id', $unit->id)->exists()) {
                return back()->with('error', 'Cannot delete unit with sub-units.');
            }

            $unit->delete();

            return redirect()->route('units.index')
                ->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting unit: ' . $e->getMessage());
        }
    }

    /**
     * Toggle unit active status.
     */
    public function toggleStatus(Unit $unit)
    {
        $this->authorize('update', $unit);

        $unit->is_active = !$unit->is_active;
        $unit->save();

        return back()->with('success', 'Unit status updated.');
    }
}
