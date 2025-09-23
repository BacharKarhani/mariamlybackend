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
        $variants = $product->variants()->with('images')->get();

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
            'color' => 'required|string|max:50',
            'hex_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|max:2048',
        ]);

        $variantData = [
            'color' => $request->color
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
            'color' => 'required|string|max:50',
            'hex_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'sometimes|image|max:2048',
        ]);

        $updateData = [
            'color' => $request->color
        ];

        // Handle hex color update if provided
        if ($request->has('hex_color')) {
            $updateData['hex_color'] = $request->hex_color;
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

        $variant->delete();

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
}