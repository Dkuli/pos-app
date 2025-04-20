<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tax\StoreTaxRequest;
use App\Http\Requests\Tax\UpdateTaxRequest;
use App\Models\Tax;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $taxes = Tax::where('tenant_id', $tenantId)->get();
        return view('taxes.index', compact('taxes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('taxes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaxRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $tax = Tax::create($data);

            return redirect()->route('taxes.index')
                ->with('success', 'Tax created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating tax: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax)
    {
        $this->authorize('view', $tax);
        return view('taxes.show', compact('tax'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tax $tax)
    {
        $this->authorize('update', $tax);
        return view('taxes.edit', compact('tax'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxRequest $request, Tax $tax)
    {
        $this->authorize('update', $tax);

        try {
            $tax->update($request->validated());

            return redirect()->route('taxes.index')
                ->with('success', 'Tax updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating tax: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        $this->authorize('delete', $tax);

        try {
            // Check if tax is used by products
            if ($tax->products()->count() > 0) {
                return back()->with('error', 'Cannot delete tax with associated products.');
            }

            $tax->delete();

            return redirect()->route('taxes.index')
                ->with('success', 'Tax deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting tax: ' . $e->getMessage());
        }
    }

    /**
     * Toggle tax active status.
     */
    public function toggleStatus(Tax $tax)
    {
        $this->authorize('update', $tax);

        $tax->is_active = !$tax->is_active;
        $tax->save();

        return back()->with('success', 'Tax status updated.');
    }
}
