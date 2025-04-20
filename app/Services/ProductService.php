<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create a new product
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        DB::beginTransaction();
        try {
            $product = Product::create([
                'tenant_id' => $data['tenant_id'],
                'category_id' => $data['category_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'barcode' => $data['barcode'] ?? null,
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'stock_alert_quantity' => $data['stock_alert_quantity'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'is_service' => $data['is_service'] ?? false,
                'is_featured' => $data['is_featured'] ?? false,
                'track_inventory' => $data['track_inventory'] ?? true,
            ]);

            // Handle product images
            if (isset($data['images']) && !empty($data['images'])) {
                $primaryIndex = $data['primary_image'] ?? 0;
                $this->handleProductImages($product, $data['images'], $primaryIndex);
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update an existing product
     *
     * @param Product $product
     * @param array $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
            $data['slug'] = Str::slug($data['name']);
        }

        DB::beginTransaction();
        try {
            $product->update([
                'category_id' => $data['category_id'] ?? $product->category_id,
                'unit_id' => $data['unit_id'] ?? $product->unit_id,
                'tax_id' => $data['tax_id'] ?? $product->tax_id,
                'name' => $data['name'] ?? $product->name,
                'slug' => $data['slug'] ?? $product->slug,
                'barcode' => $data['barcode'] ?? $product->barcode,
                'sku' => $data['sku'] ?? $product->sku,
                'description' => $data['description'] ?? $product->description,
                'cost_price' => $data['cost_price'] ?? $product->cost_price,
                'selling_price' => $data['selling_price'] ?? $product->selling_price,
                'stock_alert_quantity' => $data['stock_alert_quantity'] ?? $product->stock_alert_quantity,
                'is_active' => $data['is_active'] ?? $product->is_active,
                'is_service' => $data['is_service'] ?? $product->is_service,
                'is_featured' => $data['is_featured'] ?? $product->is_featured,
                'track_inventory' => $data['track_inventory'] ?? $product->track_inventory,
            ]);

            // Handle image removal
            if (isset($data['remove_images']) && !empty($data['remove_images'])) {
                $this->removeProductImages($product, $data['remove_images']);
            }

            // Handle product images
            if (isset($data['images']) && !empty($data['images'])) {
                $primaryIndex = $data['primary_image'] ?? 0;
                $this->handleProductImages($product, $data['images'], $primaryIndex);
            }

            DB::commit();
            return $product->fresh(['images']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete a product
     *
     * @param Product $product
     * @return bool
     */
    public function delete(Product $product): bool
    {
        // Check if product has inventory, transactions or other relations
        if ($product->inventories()->count() > 0 ||
            $product->transactionItems()->count() > 0 ||
            $product->purchaseItems()->count() > 0) {
            throw new \Exception('Cannot delete product with inventory or transactions.');
        }

        // Delete all product images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        return $product->delete();
    }

    /**
     * Handle product images
     *
     * @param Product $product
     * @param array $images
     * @param int $primaryIndex
     * @return void
     */
    private function handleProductImages(Product $product, array $images, int $primaryIndex = 0): void
    {
        $order = 0;
        foreach ($images as $index => $image) {
            $fileName = 'product_' . $product->id . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products/images', $fileName, 'public');

            $product->images()->create([
                'path' => $path,
                'is_primary' => ($index == $primaryIndex),
                'order' => $order++,
            ]);
        }
    }

    /**
     * Remove product images
     *
     * @param Product $product
     * @param array $imageIds
     * @return void
     */
    private function removeProductImages(Product $product, array $imageIds): void
    {
        $images = $product->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }

    /**
     * Get low stock products
     *
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(int $tenantId)
    {
        return Product::where('tenant_id', $tenantId)
            ->where('track_inventory', true)
            ->where('is_active', true)
            ->whereRaw('stock_alert_quantity >= (SELECT COALESCE(SUM(quantity), 0) FROM product_inventories WHERE product_id = products.id)')
            ->with(['category', 'unit'])
            ->get();
    }

    /**
     * Search products by term
     *
     * @param int $tenantId
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchProducts(int $tenantId, string $term)
    {
        return Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('barcode', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->with(['category', 'unit', 'tax', 'images' => function($query) {
                $query->where('is_primary', true);
            }])
            ->get();
    }
}
