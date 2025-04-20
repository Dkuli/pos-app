<?php

namespace App\Http\Controllers;

use App\Http\Requests\Discount\StoreDiscountRequest;
use App\Http\Requests\Discount\UpdateDiscountRequest;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Category;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class DiscountController extends Controller
{
    use AuthorizesRequests;

    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $query = Discount::where('tenant_id', $tenantId);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $discounts = $query->orderBy('name')->paginate(15);

        return view('discounts.index', compact('discounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('discounts.create', compact('products', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiscountRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $discount = $this->discountService->create($data);

            return redirect()->route('discounts.show', $discount->id)
                ->with('success', 'Discount created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating discount: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        $this->authorize('view', $discount);

        $discount->load(['products', 'categories']);

        return view('discounts.show', compact('discount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        $this->authorize('update', $discount);

        $tenantId = Auth::user()->tenant_id;

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $discount->load(['products', 'categories']);

        $selectedProducts = $discount->products->pluck('id')->toArray();
        $selectedCategories = $discount->categories->pluck('id')->toArray();

        return view('discounts.edit', compact(
            'discount',
            'products',
            'categories',
            'selectedProducts',
            'selectedCategories'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        $this->authorize('update', $discount);

        try {
            $data = $request->validated();

            $discount = $this->discountService->update($discount, $data);

            return redirect()->route('discounts.show', $discount->id)
                ->with('success', 'Discount updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating discount: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        $this->authorize('delete', $discount);

        try {
            $this->discountService->delete($discount);

            return redirect()->route('discounts.index')
                ->with('success', 'Discount deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting discount: ' . $e->getMessage());
        }
    }

    /**
     * Toggle discount active status.
     */
    public function toggleStatus(Discount $discount)
    {
        $this->authorize('update', $discount);

        $discount->is_active = !$discount->is_active;
        $discount->save();

        return back()->with('success', 'Discount status updated.');
    }
}
