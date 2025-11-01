<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\RecentlyViewed;
use App\Models\Brand;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Public: list all products
    public function index(Request $request)
    {
        // validate incoming filters
        $request->validate([
            'search'        => 'nullable|string|min:1',
            'category_id'   => 'nullable|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:subcategories,id',
            'brand_id'      => 'nullable|integer|exists:brands,id',
            'min_price'     => 'nullable|numeric|min:0',
            'max_price'     => 'nullable|numeric|min:0',
            'sort'          => 'nullable|in:low_to_high,high_to_low',
            'per_page'      => 'nullable|integer|min:1|max:100',
            'page'          => 'nullable|integer|min:1',
            'is_trending'   => 'nullable|boolean',
            'is_new'        => 'nullable|boolean',
            'hot_offer'     => 'nullable|boolean',
            'tags'          => 'nullable|string',
        ]);

        $perPage = (int) $request->input('per_page', 12);

        $query = Product::with(['categories','subcategory','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }])
            ->when($request->filled('category_id'),
                fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $request->integer('category_id'))))
            ->when($request->filled('subcategory_id'),
                fn($q) => $q->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('brand_id'),
                fn($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('is_trending'),
                fn($q) => $q->where('is_trending', $request->boolean('is_trending')))
            ->when($request->filled('is_new'),
                fn($q) => $q->where('is_new', $request->boolean('is_new')))
            ->when($request->filled('hot_offer'),
                fn($q) => $q->where('hot_offer', $request->boolean('hot_offer')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->input('search'));
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('desc', 'like', "%{$s}%")
                        ->orWhere('tags', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('tags'), function ($q) use ($request) {
                $tags = trim($request->input('tags'));
                $q->where('tags', 'like', "%{$tags}%");
            })
            // price filters on selling_price (the price you display to users)
            ->when($request->filled('min_price') && $request->filled('max_price'), function ($q) use ($request) {
                $min = (float) $request->input('min_price');
                $max = (float) $request->input('max_price');
                if ($min > $max) { [$min, $max] = [$max, $min]; }
                $q->whereBetween('selling_price', [$min, $max]);
            })
            ->when($request->filled('min_price') && ! $request->filled('max_price'),
                fn($q) => $q->where('selling_price', '>=', (float) $request->input('min_price')))
            ->when(! $request->filled('min_price') && $request->filled('max_price'),
                fn($q) => $q->where('selling_price', '<=', (float) $request->input('max_price')));

        // sorting
        $sort = $request->input('sort', 'low_to_high');
        $query->when(true, function ($q) use ($sort) {
            if ($sort === 'high_to_low') {
                $q->orderBy('selling_price', 'desc');
            } else {
                $q->orderBy('selling_price', 'asc');
            }
        });

        // paginate (adds meta: current_page, last_page, total, etc.)
        $products = $query->paginate($perPage)->appends($request->query());

        // hide buying_price for guests/non-admins
        $user = auth('sanctum')->user();
        if (! $user || $user->role_id !== 1) {
            $products->getCollection()->makeHidden('buying_price');
            // Also hide buying_price from variants
            $products->getCollection()->each(function($product) {
                $product->variants->makeHidden('buying_price');
            });
        }

        // Return paginator structure (your FE already handles data/last_page/total)
        return response()->json($products);
    }

    public function show(Request $request, Product $product)
    {
        $product->load(['categories','subcategory','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }]);

        // Hide buying price from non-admins or guests
        $user = auth('sanctum')->user();
        if (!$user || $user->role_id !== 1) {
            $product->makeHidden('buying_price');
            // Also hide buying_price from variants
            $product->variants->makeHidden('buying_price');
        }

        // Log to recently_viewed if user is logged in
        if ($user) {
            RecentlyViewed::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id],
                ['updated_at' => now()]
            );
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    // Admin: create product with variants and images
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string',
            'sku'           => 'nullable|string|unique:products,sku',
            'desc'          => 'nullable|string',
            'category_ids'  => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'brand_id'      => 'required|exists:brands,id',
            'buying_price'  => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0|max:100',
            'quantity'      => 'required_without:variants|integer|min:0',
            'weight'        => 'nullable|string|max:100',
            'ingredients'   => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'is_trending'   => 'sometimes|boolean',
            'is_new'        => 'sometimes|boolean',
            'hot_offer'     => 'sometimes|boolean',
            'new_until'     => 'nullable|date',
            'tags'          => 'nullable|string|max:500',
            'tags'          => 'nullable|string|max:500',
            // NEW: allow a single top-level image
            'image'         => 'required_without:variants|nullable|image|max:2048', 
            // Variant rules remain for multi-variant products
            'variants'      => 'nullable|array',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.size' => 'nullable|string|max:20',
            'variants.*.hex_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.buying_price' => 'nullable|numeric|min:0',
            'variants.*.regular_price' => 'nullable|numeric|min:0',
            'variants.*.discount' => 'nullable|numeric|min:0|max:100',
            'variants.*.selling_price' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|string|max:100',
            'variants.*.images' => 'required_with:variants|array|min:1',
            'variants.*.images.*' => 'required_with:variants|image|max:2048',
        ]);

        $regularPrice   = $request->regular_price;
        $discount       = $request->discount ?? 0;
        $sellingPrice   = $regularPrice - ($regularPrice * $discount / 100);

        $product = Product::create([
            'name'          => $request->name,
            'sku'           => $request->sku,
            'desc'          => $request->desc,
            'subcategory_id' => $request->subcategory_id,
            'brand_id'      => $request->brand_id,
            'buying_price'  => $request->buying_price,
            'regular_price' => $regularPrice,
            'discount'      => $discount,
            'selling_price' => $sellingPrice,
            'quantity'      => $request->has('variants') ? 0 : $request->quantity, // Set to 0 if variants exist
            'weight'        => $request->weight,
            'ingredients'   => $request->ingredients,
            'usage_instructions' => $request->usage_instructions,
            'is_trending'   => $request->has('is_trending') ? $request->boolean('is_trending') : false,
            'is_new'        => $request->has('is_new') ? $request->boolean('is_new') : false,
            'hot_offer'     => $request->has('hot_offer') ? $request->boolean('hot_offer') : false,
            'new_until'     => $request->new_until,
            'tags'          => $request->tags,
        ]);

        // Attach categories to the product
        $product->categories()->attach($request->category_ids);

        // Handle explicit variants upload
        if ($request->has('variants') && is_array($request->variants) && count($request->variants) > 0) {
            foreach ($request->variants as $index => $variantData) {
                // Calculate variant pricing
                $variantRegularPrice = $variantData['regular_price'] ?? $regularPrice;
                $variantDiscount = $variantData['discount'] ?? $discount;
                $variantSellingPrice = $variantData['selling_price'] ?? ($variantRegularPrice - ($variantRegularPrice * $variantDiscount / 100));

                $variantDataArray = [
                    'color' => $variantData['color'] ?? null,
                    'size' => $variantData['size'] ?? null,
                    'hex_color' => $variantData['hex_color'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'quantity' => $variantData['quantity'] ?? 0,
                    'sort_order' => $variantData['sort_order'] ?? $index,
                    'buying_price' => $variantData['buying_price'] ?? $request->buying_price,
                    'regular_price' => $variantRegularPrice,
                    'discount' => $variantDiscount,
                    'selling_price' => $variantSellingPrice,
                    'weight' => $variantData['weight'] ?? null,
                ];

                $variant = $product->variants()->create($variantDataArray);

                // Store images for this variant
                if (isset($variantData['images'])) {
                    foreach ($variantData['images'] as $image) {
                        $path = $image->store('products', 'public');
                        $variant->images()->create(['path' => $path]);
                    }
                }
            }
            
            // Sync product quantity with variant quantities
            $product->syncQuantityFromVariants();
        }
        // Handle single product image when no variants are provided
        elseif ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->update(['image' => $path]);
        }


        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product->load('variants.images','categories','subcategory','brand')
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'          => 'required|string',
            'sku'           => 'nullable|string|unique:products,sku,' . $product->id,
            'desc'          => 'nullable|string',
            'category_ids'  => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'brand_id'      => 'required|exists:brands,id',
            'buying_price'  => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0|max:100',
            'quantity'      => 'required_without:variants|integer|min:0',
            'weight'        => 'nullable|string|max:100',
            'ingredients'   => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'is_trending'   => 'sometimes|boolean',
            // UPDATED: Use 'image' for single file upload
            'image'         => 'nullable|image|max:2048', 
            'is_new'        => 'sometimes|boolean',
            'hot_offer'     => 'sometimes|boolean',
            'new_until'     => 'nullable|date',
            // Variant rules remain
            'variants'      => 'nullable|array',
            'variants.*.color' => 'nullable|string|max:50',
            'variants.*.size' => 'nullable|string|max:20',
            'variants.*.hex_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.sort_order' => 'nullable|integer|min:0',
            'variants.*.buying_price' => 'nullable|numeric|min:0',
            'variants.*.regular_price' => 'nullable|numeric|min:0',
            'variants.*.discount' => 'nullable|numeric|min:0|max:100',
            'variants.*.selling_price' => 'nullable|numeric|min:0',
            'variants.*.weight' => 'nullable|string|max:100',
            'variants.*.images' => 'required_with:variants|array|min:1',
            'variants.*.images.*' => 'required_with:variants|image|max:2048',
        ]);

        $regularPrice   = $request->regular_price;
        $discount       = $request->discount ?? 0;
        $sellingPrice   = $regularPrice - ($regularPrice * $discount / 100);

        $updateData = [
            'name'          => $request->name,
            'sku'           => $request->sku,
            'desc'          => $request->desc,
            'subcategory_id' => $request->subcategory_id,
            'brand_id'      => $request->brand_id,
            'buying_price'  => $request->buying_price,
            'regular_price' => $regularPrice,
            'discount'      => $discount,
            'selling_price' => $sellingPrice,
            'weight'        => $request->weight,
            'ingredients'   => $request->ingredients,
            'usage_instructions' => $request->usage_instructions,
            'is_trending'   => $request->has('is_trending') ? $request->boolean('is_trending') : $product->is_trending,
            'is_new'        => $request->has('is_new') ? $request->boolean('is_new') : $product->is_new,
            'hot_offer'     => $request->has('hot_offer') ? $request->boolean('hot_offer') : $product->hot_offer,
            'new_until'     => $request->has('new_until') ? $request->new_until : $product->new_until,
            'tags'          => $request->has('tags') ? $request->tags : $product->tags,
        ];

        // Only update quantity if product doesn't have variants
        if (!$request->has('variants') || !is_array($request->variants) || count($request->variants) === 0) {
            $updateData['quantity'] = $request->quantity;
        }

        $product->update($updateData);

        // Sync categories (this will detach old ones and attach new ones)
        $product->categories()->sync($request->category_ids);

        // Update/Replace variants if explicitly provided
        if ($request->has('variants') && is_array($request->variants) && count($request->variants) > 0) {
            // Delete existing variants and their images (FULL REPLACEMENT strategy)
            foreach ($product->variants as $variant) {
                foreach ($variant->images as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
                $variant->delete();
            }

            // Create new variants
            foreach ($request->variants as $index => $variantData) {
                // Calculate variant pricing
                $variantRegularPrice = $variantData['regular_price'] ?? $regularPrice;
                $variantDiscount = $variantData['discount'] ?? $discount;
                $variantSellingPrice = $variantData['selling_price'] ?? ($variantRegularPrice - ($variantRegularPrice * $variantDiscount / 100));

                $variantDataArray = [
                    'color' => $variantData['color'] ?? null,
                    'size' => $variantData['size'] ?? null,
                    'hex_color' => $variantData['hex_color'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'quantity' => $variantData['quantity'] ?? 0,
                    'sort_order' => $variantData['sort_order'] ?? $index,
                    'buying_price' => $variantData['buying_price'] ?? $request->buying_price,
                    'regular_price' => $variantRegularPrice,
                    'discount' => $variantDiscount,
                    'selling_price' => $variantSellingPrice,
                    'weight' => $variantData['weight'] ?? null,
                ];

                $variant = $product->variants()->create($variantDataArray);

                if (isset($variantData['images'])) {
                    foreach ($variantData['images'] as $image) {
                        $path = $image->store('products', 'public');
                        $variant->images()->create(['path' => $path]);
                    }
                }
            }
            
            // Sync product quantity with variant quantities
            $product->syncQuantityFromVariants();
        }
        // Handle single product image update when no variants are provided
        elseif ($request->hasFile('image')) {
            // Delete existing product image if it exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            // Store the new image
            $path = $request->file('image')->store('products', 'public');
            $product->update(['image' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product->load('variants.images','categories','subcategory','brand')
        ]);
    }
    

    // Admin: delete product and all related variants and images
    public function destroy(Product $product)
    {
        // Delete all variant images
        foreach ($product->variants as $variant) {
            foreach ($variant->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function related(Product $product)
    {
        // Get all subcategories that belong to the same categories as the selected product
        $subcategoryIds = Subcategory::whereIn('category_id', $product->categories->pluck('id'))
            ->pluck('id');

        $related = Product::whereIn('subcategory_id', $subcategoryIds)
            ->where('id', '!=', $product->id)
            ->with(['categories','subcategory','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }])
            ->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $related->makeHidden('buying_price');
            // Also hide buying_price from variants
            $related->each(function($product) {
                $product->variants->makeHidden('buying_price');
            });
        }

        return response()->json([
            'success' => true,
            'related_products' => $related
        ]);
    }

    // Get all trending products
    public function trending()
    {
        $products = Product::with(['categories','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }])
            ->where('is_trending', true)
            ->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $products->makeHidden('buying_price');
            // Also hide buying_price from variants
            $products->each(function($product) {
                $product->variants->makeHidden('buying_price');
            });
        }

        return response()->json([
            'success' => true,
            'trending_products' => $products
        ]);
    }

    // NEW: قائمة المنتجات الجديدة (فعّالة بحسب is_new/new_until)
    public function newProducts()
    {
        $products = Product::with(['categories','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }])
            ->newActive()
            ->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $products->makeHidden('buying_price');
            // Also hide buying_price from variants
            $products->each(function($product) {
                $product->variants->makeHidden('buying_price');
            });
        }

        return response()->json([
            'success' => true,
            'new_products' => $products
        ]);
    }

    // Get all hot offer products
    public function hotOffers()
    {
        $products = Product::with(['categories','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }])
            ->where('hot_offer', true)
            ->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $products->makeHidden('buying_price');
            // Also hide buying_price from variants
            $products->each(function($product) {
                $product->variants->makeHidden('buying_price');
            });
        }

        return response()->json([
            'success' => true,
            'hot_offer_products' => $products
        ]);
    }

    public function recentlyViewed(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => true,
                'recently_viewed' => []
            ]);
        }

        $productIds = RecentlyViewed::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->pluck('product_id');

        $products = Product::with(['categories','brand','variants' => function($query) {
                $query->with('images')->ordered();
            }])
            ->whereIn('id', $productIds)
            ->get();

        if ($user->role_id !== 1) {
            $products->makeHidden('buying_price');
            // Also hide buying_price from variants
            $products->each(function($product) {
                $product->variants->makeHidden('buying_price');
            });
        }

        return response()->json([
            'success' => true,
            'recently_viewed' => $products
        ]);
    }
    
    public function search(Request $request)
{
    // validation: لازم تبعت واحد على الأقل من q أو category_id أو brand_id
    $request->validate([
        'q'           => 'nullable|string|min:2',
        'category_id' => 'nullable|integer|exists:categories,id',
        'subcategory_id' => 'nullable|integer|exists:subcategories,id',
        'brand_id'    => 'nullable|integer|exists:brands,id',
        'per_page'    => 'nullable|integer|min:1|max:100',
        'page'        => 'nullable|integer|min:1',
    ]);

    if (! $request->filled('q') && ! $request->filled('category_id') && ! $request->filled('subcategory_id') && ! $request->filled('brand_id')) {
        return response()->json([
            'success' => false,
            'message' => 'Provide at least one of: q, category_id, subcategory_id, brand_id.',
            'errors'  => ['filters' => ['At least one filter is required.']],
        ], 422);
    }

    $perPage = (int) $request->input('per_page', 20);

    $query = Product::with(['categories','subcategory','brand','variants.images'])
        ->when($request->filled('q'), function ($qq) use ($request) {
            $q = trim($request->input('q'));
            $qq->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('desc', 'like', "%{$q}%")
                  ->orWhere('tags', 'like', "%{$q}%")
                  ->orWhereHas('categories', function ($cq) use ($q) {
                      $cq->where('name', 'like', "%{$q}%");
                  })
                  ->orWhereHas('subcategory', function ($sq) use ($q) {
                      $sq->where('name', 'like', "%{$q}%");
                  })
                  ->orWhereHas('brand', function ($bq) use ($q) {
                      $bq->where('name', 'like', "%{$q}%");
                  });
            });
        })
        ->when($request->filled('category_id'), fn($qq) => $qq->whereHas('categories', fn($qqq) => $qqq->where('categories.id', $request->integer('category_id'))))
        ->when($request->filled('subcategory_id'), fn($qq) => $qq->where('subcategory_id', $request->integer('subcategory_id')))
        ->when($request->filled('brand_id'), fn($qq) => $qq->where('brand_id', $request->integer('brand_id')))
        ->orderByDesc('id');

    $products = $query->paginate($perPage);

    // اخفاء buying_price عن غير الأدمن
    $user = auth('sanctum')->user();
    if (!$user || $user->role_id !== 1) {
        $products->getCollection()->makeHidden('buying_price');
    }

    return response()->json([
        'success'  => true,
        'filters'  => $request->only(['q','category_id','subcategory_id','brand_id']),
        'products' => $products, // مع meta/links للباجينيشن
    ]);
}

// Public: discounted products (offers)
public function discounted(Request $request)
{
    $request->validate([
        'min_discount' => 'nullable|numeric|min:0|max:100', // حد أدنى للخصم (افتراضي 0+)
        'per_page'     => 'nullable|integer|min:1|max:100',
        'page'         => 'nullable|integer|min:1',
        // اختياري: ترتيب حسب نسبة الخصم أو السعر
        'sort'         => 'nullable|in:discount_high,discount_low,price_low,price_high,latest',
        // فلاتر اختيارية (نفس ستايل index)
        'category_id'  => 'nullable|integer|exists:categories,id',
        'brand_id'     => 'nullable|integer|exists:brands,id',
        'search'       => 'nullable|string|min:1',
        'tags'         => 'nullable|string',
    ]);

    $perPage     = (int) $request->input('per_page', 12);
    $minDiscount = (float) $request->input('min_discount', 0);

    $query = Product::with(['categories','brand','variants.images'])
        ->where('discount', '>', $minDiscount)
        // (اختياري) نتأكد إنّ السعر المبيع أقل من العادي فعلاً
        ->whereColumn('selling_price', '<', 'regular_price')
        ->when($request->filled('category_id'),
            fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $request->integer('category_id'))))
        ->when($request->filled('brand_id'),
            fn($q) => $q->where('brand_id', $request->integer('brand_id')))
        ->when($request->filled('search'), function ($q) use ($request) {
            $s = trim($request->input('search'));
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('desc', 'like', "%{$s}%")
                   ->orWhere('tags', 'like', "%{$s}%");
            });
        })
        ->when($request->filled('tags'), function ($q) use ($request) {
            $tags = trim($request->input('tags'));
            $q->where('tags', 'like', "%{$tags}%");
        });

    // الترتيب
    $sort = $request->input('sort', 'discount_high');
    $query->when(true, function ($q) use ($sort) {
        switch ($sort) {
            case 'discount_low':
                $q->orderBy('discount', 'asc');
                break;
            case 'price_low':
                $q->orderBy('selling_price', 'asc');
                break;
            case 'price_high':
                $q->orderBy('selling_price', 'desc');
                break;
            case 'latest':
                $q->orderBy('id', 'desc');
                break;
            case 'discount_high':
            default:
                $q->orderBy('discount', 'desc');
                break;
        }
    });

    $products = $query->paginate($perPage)->appends($request->query());

    // إخفاء buying_price لغير الأدمن
    $user = auth('sanctum')->user();
    if (! $user || $user->role_id !== 1) {
        $products->getCollection()->makeHidden('buying_price');
    }

    return response()->json([
        'success'             => true,
        'filters'             => $request->only(['min_discount','category_id','brand_id','search','sort']),
        'discounted_products' => $products, // paginator with meta/links
    ]);
}

public function totalCount(Request $request)
{
    // زيادة أمان: الجروب فيه is_admin بس منضيف الحارس كمان
    if ($request->user()->role_id != 1) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // لو بتستعمل SoftDeletes وبدك تشمل المحذوفين: Product::withTrashed()->count();
    $count = Product::count();

    return response()->json(['total' => $count], 200);
}

// Admin: Get all products with full details (including buying_price)
public function indexAdmin(Request $request)
{
    // Validate incoming filters
    $request->validate([
        'search'      => 'nullable|string|min:1',
        'category_id' => 'nullable|integer|exists:categories,id',
        'brand_id'    => 'nullable|integer|exists:brands,id',
        'min_price'   => 'nullable|numeric|min:0',
        'max_price'   => 'nullable|numeric|min:0',
        'sort'        => 'nullable|in:low_to_high,high_to_low,newest,oldest',
        'per_page'    => 'nullable|integer|min:1|max:100',
        'page'        => 'nullable|integer|min:1',
        'is_trending' => 'nullable|boolean',
        'is_new'      => 'nullable|boolean',
        'hot_offer'   => 'nullable|boolean',
        'show_inactive' => 'nullable|boolean', // Show products with 0 quantity
        'tags'        => 'nullable|string',
    ]);

    $perPage = (int) $request->input('per_page', 20);

    $query = Product::with(['categories','subcategory','brand','variants.images'])
        ->when($request->filled('category_id'),
            fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $request->integer('category_id'))))
        ->when($request->filled('subcategory_id'),
            fn($q) => $q->where('subcategory_id', $request->integer('subcategory_id')))
        ->when($request->filled('brand_id'),
            fn($q) => $q->where('brand_id', $request->integer('brand_id')))
        ->when($request->filled('is_trending'),
            fn($q) => $q->where('is_trending', $request->boolean('is_trending')))
        ->when($request->filled('is_new'),
            fn($q) => $q->where('is_new', $request->boolean('is_new')))
        ->when($request->filled('hot_offer'),
            fn($q) => $q->where('hot_offer', $request->boolean('hot_offer')))
        ->when($request->filled('search'), function ($q) use ($request) {
            $s = trim($request->input('search'));
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('desc', 'like', "%{$s}%")
                   ->orWhere('tags', 'like', "%{$s}%");
            });
        })
        ->when($request->filled('tags'), function ($q) use ($request) {
            $tags = trim($request->input('tags'));
            $q->where('tags', 'like', "%{$tags}%");
        })
        // Price filters on selling_price
        ->when($request->filled('min_price') && $request->filled('max_price'), function ($q) use ($request) {
            $min = (float) $request->input('min_price');
            $max = (float) $request->input('max_price');
            if ($min > $max) { [$min, $max] = [$max, $min]; }
            $q->whereBetween('selling_price', [$min, $max]);
        })
        ->when($request->filled('min_price') && ! $request->filled('max_price'),
            fn($q) => $q->where('selling_price', '>=', (float) $request->input('min_price')))
        ->when(! $request->filled('min_price') && $request->filled('max_price'),
            fn($q) => $q->where('selling_price', '<=', (float) $request->input('max_price')))
        // Show/hide inactive products (quantity = 0)
        ->when(!$request->boolean('show_inactive'), 
            fn($q) => $q->where('quantity', '>', 0));

    // Sorting
    $sort = $request->input('sort', 'newest');
    $query->when(true, function ($q) use ($sort) {
        switch ($sort) {
            case 'low_to_high':
                $q->orderBy('selling_price', 'asc');
                break;
            case 'high_to_low':
                $q->orderBy('selling_price', 'desc');
                break;
            case 'oldest':
                $q->orderBy('id', 'asc');
                break;
            case 'newest':
            default:
                $q->orderBy('id', 'desc');
                break;
        }
    });

    // Paginate
    $products = $query->paginate($perPage)->appends($request->query());

    // Admin sees all fields including buying_price
    return response()->json([
        'success' => true,
        'products' => $products
    ]);
}

public function adminSearch(Request $request)
{
    // Admin-only search (guarded by is_admin middleware in routes)
    $request->validate([
        'q'              => 'nullable|string|min:1',   // search by product name
        'id'             => 'nullable|integer',        // optional exact ID search
        'category_id'    => 'nullable|integer|exists:categories,id',
        'brand_id'       => 'nullable|integer|exists:brands,id',
        'per_page'       => 'nullable|integer|min:1|max:100',
        'page'           => 'nullable|integer|min:1',
        'sort'           => 'nullable|in:latest,price_low,price_high,name_asc,name_desc',
        'include_deleted'=> 'nullable|boolean',        // if you use SoftDeletes
    ]);

    $perPage = (int) $request->input('per_page', 20);

    // Start query
    $query = Product::with(['categories','brand','variants.images']);

    // If you use SoftDeletes and want to include trashed records
    if ($request->boolean('include_deleted') && in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses(Product::class))) {
        $query->withTrashed();
    }

    // Filters
    $query
        ->when($request->filled('id'), fn($q) => $q->where('id', $request->integer('id')))
        ->when($request->filled('q'), fn($q) => $q->where('name', 'like', '%'.$request->input('q').'%'))
        ->when($request->filled('category_id'), fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $request->integer('category_id'))))
        ->when($request->filled('brand_id'), fn($q) => $q->where('brand_id', $request->integer('brand_id')));

    // Sorting
    switch ($request->input('sort', 'latest')) {
        case 'price_low':
            $query->orderBy('selling_price', 'asc');
            break;
        case 'price_high':
            $query->orderBy('selling_price', 'desc');
            break;
        case 'name_asc':
            $query->orderBy('name', 'asc');
            break;
        case 'name_desc':
            $query->orderBy('name', 'desc');
            break;
        case 'latest':
        default:
            $query->orderBy('id', 'desc');
            break;
    }

    $products = $query->paginate($perPage)->appends($request->query());

    // IMPORTANT: Do NOT hide buying_price here — route is already protected by is_admin
    return response()->json([
        'success'  => true,
        'filters'  => $request->only(['q','id','category_id','brand_id','sort','include_deleted']),
        'products' => $products,
    ]);
}
// Admin: Get products with low stock (quantity < 3)
public function lowStock(Request $request)
{
    // Validate incoming filters
    $request->validate([
        'search'      => 'nullable|string|min:1',
        'category_id' => 'nullable|integer|exists:categories,id',
        'brand_id'    => 'nullable|integer|exists:brands,id',
        'sort'        => 'nullable|in:quantity_low,quantity_high,name_asc,name_desc,latest',
        'per_page'    => 'nullable|integer|min:1|max:100',
        'page'        => 'nullable|integer|min:1',
        'tags'        => 'nullable|string',
    ]);

    $perPage = (int) $request->input('per_page', 20);

    $query = Product::with(['categories','subcategory','brand','variants.images'])
        ->where('quantity', '<', 3)
        ->when($request->filled('category_id'),
            fn($q) => $q->whereHas('categories', fn($qq) => $qq->where('categories.id', $request->integer('category_id'))))
        ->when($request->filled('brand_id'),
            fn($q) => $q->where('brand_id', $request->integer('brand_id')))
        ->when($request->filled('search'), function ($q) use ($request) {
            $s = trim($request->input('search'));
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('desc', 'like', "%{$s}%")
                   ->orWhere('tags', 'like', "%{$s}%");
            });
        })
        ->when($request->filled('tags'), function ($q) use ($request) {
            $tags = trim($request->input('tags'));
            $q->where('tags', 'like', "%{$tags}%");
        });

    // Sorting
    $sort = $request->input('sort', 'quantity_low');
    $query->when(true, function ($q) use ($sort) {
        switch ($sort) {
            case 'quantity_high':
                $q->orderBy('quantity', 'desc');
                break;
            case 'name_asc':
                $q->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $q->orderBy('name', 'desc');
                break;
            case 'latest':
                $q->orderBy('id', 'desc');
                break;
            case 'quantity_low':
            default:
                $q->orderBy('quantity', 'asc');
                break;
        }
    });

    // Paginate
    $products = $query->paginate($perPage)->appends($request->query());

    return response()->json([
        'success' => true,
        'filters' => $request->only(['search','category_id','brand_id','sort','tags']),
        'low_stock_products' => $products
    ]);
}
}