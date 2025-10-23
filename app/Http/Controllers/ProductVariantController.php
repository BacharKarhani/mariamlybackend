<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductVariantController extends Controller
{
    /**
     * Get all variants for a specific product
     */
    public function index(Product $product)
    {
        $variants = $product->variants()->with('images')->ordered()->get();

        return response()->json([
            'success' => true,
            'variants' => $variants
        ]);
    }

    /**
     * Get a specific variant with its images
     */
    public function show(ProductVariant $variant)
    {
        $variant->load('images', 'product');

        return response()->json([
            'success' => true,
            'variant' => $variant
        ]);
    }

    /**
     * Create a new variant for a product
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:20',
            'hex_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku',
            'quantity' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'buying_price' => 'nullable|numeric|min:0',
            'regular_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'selling_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|string|max:100',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|max:2048',
        ]);

        // Calculate pricing if not provided
        $regularPrice = $request->regular_price ?? $product->regular_price;
        $discount = $request->discount ?? $product->discount;
        $sellingPrice = $request->selling_price ?? ($regularPrice - ($regularPrice * $discount / 100));

        $variantData = [
            'color' => $request->color,
            'size' => $request->size,
            'sku' => $request->sku,
            'quantity' => $request->quantity ?? 0,
            'sort_order' => $request->sort_order ?? $product->variants()->max('sort_order') + 1,
            'buying_price' => $request->buying_price ?? $product->buying_price,
            'regular_price' => $regularPrice,
            'discount' => $discount,
            'selling_price' => $sellingPrice,
            'weight' => $request->weight,
        ];

        // Handle hex color if provided
        if ($request->has('hex_color')) {
            $variantData['hex_color'] = $request->hex_color;
        }

        $variant = $product->variants()->create($variantData);

        // Store images for this variant
        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            $variant->images()->create(['path' => $path]);
        }

        // Sync product quantity with variant quantities
        $product->syncQuantityFromVariants();

        return response()->json([
            'success' => true,
            'message' => 'Variant created successfully',
            'variant' => $variant->load('images')
        ], 201);
    }

    /**
     * Update a variant
     */
    public function update(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:20',
            'hex_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku,' . $variant->id,
            'quantity' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'buying_price' => 'nullable|numeric|min:0',
            'regular_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'selling_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|string|max:100',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'sometimes|image|max:2048',
        ]);

        // Calculate pricing if provided
        $regularPrice = $request->regular_price ?? $variant->regular_price ?? $variant->product->regular_price;
        $discount = $request->discount ?? $variant->discount ?? $variant->product->discount;
        $sellingPrice = $request->selling_price ?? ($regularPrice - ($regularPrice * $discount / 100));

        $updateData = [
            'color' => $request->color,
            'size' => $request->size,
            'sku' => $request->sku,
            'quantity' => $request->quantity ?? $variant->quantity,
            'buying_price' => $request->buying_price ?? $variant->buying_price ?? $variant->product->buying_price,
            'regular_price' => $regularPrice,
            'discount' => $discount,
            'selling_price' => $sellingPrice,
            'weight' => $request->weight ?? $variant->weight,
        ];

        // Handle hex color update if provided
        if ($request->has('hex_color')) {
            $updateData['hex_color'] = $request->hex_color;
        }

        // Handle sort order update if provided
        if ($request->has('sort_order')) {
            $updateData['sort_order'] = $request->sort_order;
        }

        $variant->update($updateData);

        // Update images if provided
        if ($request->hasFile('images')) {
            // Delete existing images
            foreach ($variant->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            // Store new images
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $variant->images()->create(['path' => $path]);
            }
        }

        // Sync product quantity with variant quantities
        $variant->product->syncQuantityFromVariants();

        return response()->json([
            'success' => true,
            'message' => 'Variant updated successfully',
            'variant' => $variant->load('images')
        ]);
    }

    /**
     * Delete a variant and all its images
     */
    public function destroy(ProductVariant $variant)
    {
        // Delete all variant images
        foreach ($variant->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product = $variant->product; // Store reference before deletion
        $variant->delete();

        // Sync product quantity with remaining variant quantities
        $product->syncQuantityFromVariants();

        return response()->json([
            'success' => true,
            'message' => 'Variant deleted successfully'
        ]);
    }

    /**
     * Add images to an existing variant
     */
    public function addImages(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|max:2048',
        ]);

        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            $variant->images()->create(['path' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Images added successfully',
            'variant' => $variant->load('images')
        ]);
    }

    /**
     * Remove a specific image from a variant
     */
    public function removeImage(ProductVariant $variant, ProductImage $image)
    {
        // Verify the image belongs to this variant
        if ($image->variant_id !== $variant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Image does not belong to this variant'
            ], 400);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image removed successfully'
        ]);
    }

    /**
     * Reorder variants for a product
     */
    public function reorder(Request $request, Product $product)
    {
        $request->validate([
            'variants' => 'required|array',
            'variants.*.id' => 'required|integer|exists:product_variants,id',
            'variants.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->variants as $variantData) {
            $variant = $product->variants()->find($variantData['id']);
            if ($variant) {
                $variant->update(['sort_order' => $variantData['sort_order']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Variants reordered successfully',
            'variants' => $product->variants()->with('images')->ordered()->get()
        ]);
    }

    /**
     * Move variant up in order
     */
    public function moveUp(ProductVariant $variant)
    {
        $previousVariant = $variant->product->variants()
            ->where('sort_order', '<', $variant->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousVariant) {
            $tempOrder = $variant->sort_order;
            $variant->update(['sort_order' => $previousVariant->sort_order]);
            $previousVariant->update(['sort_order' => $tempOrder]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Variant moved up successfully',
            'variant' => $variant->load('images')
        ]);
    }

    /**
     * Move variant down in order
     */
    public function moveDown(ProductVariant $variant)
    {
        $nextVariant = $variant->product->variants()
            ->where('sort_order', '>', $variant->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextVariant) {
            $tempOrder = $variant->sort_order;
            $variant->update(['sort_order' => $nextVariant->sort_order]);
            $nextVariant->update(['sort_order' => $tempOrder]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Variant moved down successfully',
            'variant' => $variant->load('images')
        ]);
    }


    /**
     * Get variants with stock
     */
    public function inStock(Product $product)
    {
        $variants = $product->variants()->with('images')->inStock()->ordered()->get();

        return response()->json([
            'success' => true,
            'variants' => $variants
        ]);
    }

    /**
     * Get out of stock variants
     */
    public function outOfStock(Product $product)
    {
        $variants = $product->variants()->with('images')->outOfStock()->ordered()->get();

        return response()->json([
            'success' => true,
            'variants' => $variants
        ]);
    }
}