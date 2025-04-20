<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Create a new category
     *
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['image'])) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return Category::create($data);
    }

    /**
     * Update an existing category
     *
     * @param Category $category
     * @param array $data
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (isset($data['image'])) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $this->uploadImage($data['image']);
        }

        $category->update($data);
        return $category;
    }

    /**
     * Delete a category
     *
     * @param Category $category
     * @return bool
     */
    public function delete(Category $category): bool
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            throw new \Exception('Cannot delete category with associated products.');
        }

        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        return $category->delete();
    }

    /**
     * Upload category image
     *
     * @param $image
     * @return string
     */
    private function uploadImage($image): string
    {
        $fileName = 'category_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('categories/images', $fileName, 'public');
        return $path;
    }

    /**
     * Get all active categories for a tenant
     *
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCategories(int $tenantId)
    {
        return Category::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get categories as hierarchy
     *
     * @param int $tenantId
     * @return array
     */
    public function getCategoryHierarchy(int $tenantId): array
    {
        $categories = Category::where('tenant_id', $tenantId)
            ->where('parent_id', null)
            ->with('children')
            ->get();

        return $this->buildHierarchy($categories);
    }

    /**
     * Build category hierarchy
     *
     * @param \Illuminate\Database\Eloquent\Collection $categories
     * @return array
     */
    private function buildHierarchy($categories): array
    {
        $hierarchy = [];

        foreach ($categories as $category) {
            $hierarchyItem = [
                'id' => $category->id,
                'name' => $category->name,
                'children' => []
            ];

            if ($category->children->count() > 0) {
                $hierarchyItem['children'] = $this->buildHierarchy($category->children);
            }

            $hierarchy[] = $hierarchyItem;
        }

        return $hierarchy;
    }
}
