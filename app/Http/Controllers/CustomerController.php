<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerController extends Controller
{
    use AuthorizesRequests;
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $query = Customer::where('tenant_id', $tenantId);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $customer = Customer::create($data);

            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        // Get customer purchase history
        $history = $this->transactionService->getCustomerPurchaseHistory($customer);

        return view('customers.show', compact('customer', 'history'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        try {
            $customer->update($request->validated());

            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        try {
            // Check if customer has transactions
            if ($customer->transactions()->count() > 0) {
                return back()->with('error', 'Cannot delete customer with transaction history.');
            }

            $customer->delete();

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Search customers (AJAX).
     */
    public function search(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $term = $request->input('term');

        $customers = Customer::where('tenant_id', $tenantId)
            ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            })
            ->select('id', 'name', 'email', 'phone')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}
