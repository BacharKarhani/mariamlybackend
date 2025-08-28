<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\RecentlyViewed;

class ProductController extends Controller
{
    // Public: list all products
    public function index(Request $request)
    {
        $products = Product::with(['category', 'images'])->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $products->makeHidden('buying_price');
        }

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $product->load(['category', 'images']);

        // Hide buying price from non-admins or guests
        $user = auth('sanctum')->user();
        if (!$user || $user->role_id !== 1) {
            $product->makeHidden('buying_price');
        }

        // Log to recently_viewed if user is logged in
        if ($user) {
            \App\Models\RecentlyViewed::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id],
                ['updated_at' => now()]
            );
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }


    // Admin: create product with multiple images
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'buying_price' => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity' => 'required|integer|min:0',
            'is_trending' => 'sometimes|boolean',
            'images.*' => 'nullable|image|max:2048',
        ]);
    
        $regularPrice = $request->regular_price;
        $discount = $request->discount ?? 0;
        $sellingPrice = $regularPrice - ($regularPrice * $discount / 100);
    
        $product = Product::create([
            'name' => $request->name,
            'desc' => $request->desc,
            'category_id' => $request->category_id,
            'buying_price' => $request->buying_price,
            'regular_price' => $regularPrice,
            'discount' => $discount,
            'selling_price' => $sellingPrice,
            'quantity' => $request->quantity,
            'is_trending' => $request->has('is_trending') ? $request->is_trending : false,
        ]);
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product->load('images')
        ], 201);
    }
    
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'buying_price' => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity' => 'required|integer|min:0',
            'is_trending' => 'sometimes|boolean',
            'images.*' => 'nullable|image|max:2048',
        ]);
    
        $regularPrice = $request->regular_price;
        $discount = $request->discount ?? 0;
        $sellingPrice = $regularPrice - ($regularPrice * $discount / 100);
    
        $product->update([
            'name' => $request->name,
            'desc' => $request->desc,
            'category_id' => $request->category_id,
            'buying_price' => $request->buying_price,
            'regular_price' => $regularPrice,
            'discount' => $discount,
            'selling_price' => $sellingPrice,
            'quantity' => $request->quantity,
            'is_trending' => $request->has('is_trending') ? $request->is_trending : $product->is_trending,
        ]);
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create(['path' => $path]);
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product->load('images')
        ]);
    }
    

    // Admin: delete product and all related images
    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function related(Product $product)
    {
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'images'])
            ->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $related->makeHidden('buying_price');
        }

        return response()->json([
            'success' => true,
            'related_products' => $related
        ]);
    }

    // Get all trending products
    public function trending()
    {
        $products = Product::with(['category', 'images'])
            ->where('is_trending', true)
            ->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $products->makeHidden('buying_price');
        }

        return response()->json([
            'success' => true,
            'trending_products' => $products
        ]);
    }

    public function recentlyViewed(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => true,
                'recently_viewed' => []
            ]);
        }

        $productIds = \App\Models\RecentlyViewed::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->pluck('product_id');

        $products = Product::with(['category', 'images'])
            ->whereIn('id', $productIds)
            ->get();

        if ($user->role_id !== 1) {
            $products->makeHidden('buying_price');
        }

        return response()->json([
            'success' => true,
            'recently_viewed' => $products
        ]);
    }
}
