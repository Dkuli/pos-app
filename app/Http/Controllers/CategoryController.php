<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)->get();

        // Organize categories in a hierarchical structure
        $categoryHierarchy = $this->categoryService->getCategoryHierarchy($tenantId);

        return view('categories.index', compact('categories', 'categoryHierarchy'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenantId = Auth::user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)->get();
        return view('categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $category = $this->categoryService->create($data);

            return redirect()->route('categories.index')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);

        $products = $category->products()->paginate(15);
        return view('categories.show', compact('category', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        $tenantId = Auth::user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)
            ->where('id', '!=', $category->id)
            ->get();

        return view('categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('update', $category);

        try {
            $this->categoryService->update($category, $request->validated());

            return redirect()->route('categories.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        try {
            $this->categoryService->delete($category);
            return redirect()->route('categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle category active status.
     */
    public function toggleStatus(Category $category)
    {
        $this->authorize('update', $category);

        $category->is_active = !$category->is_active;
        $category->save();

        return back()->with('success', 'Category status updated.');
    }
}
