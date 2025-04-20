<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    /**
     * Create a new discount
     *
     * @param array $data
     * @return Discount
     */
    public function create(array $data): Discount
    {
        DB::beginTransaction();
        try {
            $discount = Discount::create([
                'tenant_id' => $data['tenant_id'],
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'type' => $data['type'],
                'value' => $data['value'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'applies_to' => $data['applies_to'],
                'min_purchase_amount' => $data['min_purchase_amount'] ?? null,
                'max_discount_amount' => $data['max_discount_amount'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Associate products if discount applies to specific products
            if ($data['applies_to'] === 'products' && isset($data['products'])) {
                $discount->products()->sync($data['products']);
            }

            // Associate categories if discount applies to specific categories
            if ($data['applies_to'] === 'categories' && isset($data['categories'])) {
                $discount->categories()->sync($data['categories']);
            }

            DB::commit();
            return $discount->load(['products', 'categories']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update an existing discount
     *
     * @param Discount $discount
     * @param array $data
     * @return Discount
     */
    public function update(Discount $discount, array $data): Discount
    {
        DB::beginTransaction();
        try {
            $discount->update([
                'name' => $data['name'] ?? $discount->name,
                'code' => $data['code'] ?? $discount->code,
                'type' => $data['type'] ?? $discount->type,
                'value' => $data['value'] ?? $discount->value,
                'start_date' => $data['start_date'] ?? $discount->start_date,
                'end_date' => $data['end_date'] ?? $discount->end_date,
                'applies_to' => $data['applies_to'] ?? $discount->applies_to,
                'min_purchase_amount' => $data['min_purchase_amount'] ?? $discount->min_purchase_amount,
                'max_discount_amount' => $data['max_discount_amount'] ?? $discount->max_discount_amount,
                'is_active' => $data['is_active'] ?? $discount->is_active,
            ]);

            // Update product associations
            if (isset($data['applies_to'])) {
                if ($data['applies_to'] === 'products' && isset($data['products'])) {
                    $discount->products()->sync($data['products']);
                    $discount->categories()->sync([]);
                } elseif ($data['applies_to'] === 'categories' && isset($data['categories'])) {
                    $discount->categories()->sync($data['categories']);
                    $discount->products()->sync([]);
                } elseif ($data['applies_to'] === 'all') {
                    $discount->products()->sync([]);
                    $discount->categories()->sync([]);
                }
            }

            DB::commit();
            return $discount->fresh(['products', 'categories']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete a discount
     *
     * @param Discount $discount
     * @return bool
     */
    public function delete(Discount $discount): bool
    {
        DB::beginTransaction();
        try {
            // Detach all products and categories
            $discount->products()->detach();
            $discount->categories()->detach();

            // Delete the discount
            $result = $discount->delete();

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get active discounts
     *
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveDiscounts(int $tenantId)
    {
        $now = now();

        return Discount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->with(['products', 'categories'])
            ->get();
    }

    /**
     * Calculate discount amount for a product
     *
     * @param Product $product
     * @param float $quantity
     * @param float $totalAmount
     * @return array
     */
    public function calculateProductDiscount(Product $product, float $quantity, float $totalAmount): array
    {
        $now = now();
        $tenantId = $product->tenant_id;

        // Get applicable discounts
        $discounts = Discount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
            })
            ->where(function ($query) use ($product, $totalAmount) {
                // Check for min purchase amount
                $query->whereNull('min_purchase_amount')
                    ->orWhere('min_purchase_amount', '<=', $totalAmount);

                // Check discount type and product/category applicability
                $query->where(function ($innerQuery) use ($product) {
                    // All products
                    $innerQuery->where('applies_to', 'all');

                    // Specific product
                    $innerQuery->orWhere(function ($productQuery) use ($product) {
                        $productQuery->where('applies_to', 'products')
                            ->whereHas('products', function ($q) use ($product) {
                                $q->where('product_id', $product->id);
                            });
                    });

                    // Product category
                    $innerQuery->orWhere(function ($categoryQuery) use ($product) {
                        $categoryQuery->where('applies_to', 'categories')
                            ->whereHas('categories', function ($q) use ($product) {
                                $q->where('category_id', $product->category_id);
                            });
                    });
                });
            })
            ->get();

        // Find the best discount
        $bestDiscountAmount = 0;
        $bestDiscount = null;

        foreach ($discounts as $discount) {
            $discountAmount = 0;

            if ($discount->type === 'percentage') {
                $discountAmount = $totalAmount * ($discount->value / 100);
            } else {
                $discountAmount = $discount->value;
            }

            // Apply max discount limit if set
            if ($discount->max_discount_amount && $discountAmount > $discount->max_discount_amount) {
                $discountAmount = $discount->max_discount_amount;
            }

            // Check if this is the best discount so far
            if ($discountAmount > $bestDiscountAmount) {
                $bestDiscountAmount = $discountAmount;
                $bestDiscount = $discount;
            }
        }

        return [
            'discount' => $bestDiscount,
            'amount' => $bestDiscountAmount,
        ];
    }
}
