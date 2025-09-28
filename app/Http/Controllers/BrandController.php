<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::with('categories:id,name')->ordered()->get();

        return response()->json([
            'success' => true,
            'brands'  => $brands,
        ]);
    }

    public function show(Brand $brand)
    {
        $brand->load('categories:id,name');

        return response()->json([
            'success' => true,
            'brand'   => $brand,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required','string','max:255','unique:brands,name'],
            'image'          => ['nullable','image','max:2048'],
            'is_active'      => ['nullable','boolean'],
            'sort_order'     => ['nullable','integer','min:0'],
            'category_ids'   => ['nullable','array'],
            'category_ids.*' => ['integer','exists:categories,id'],
        ]);

        $data = [
            'name'      => $validated['name'],
            'is_active' => (bool)($validated['is_active'] ?? true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand = Brand::create($data);

        if (!empty($validated['category_ids'])) {
            $brand->categories()->sync($validated['category_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'brand'   => $brand->load('categories:id,name'),
        ], 201);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'           => ['required','string','max:255', Rule::unique('brands','name')->ignore($brand->id)],
            'image'          => ['nullable','image','max:2048'],
            'is_active'      => ['nullable','boolean'],
            'sort_order'     => ['nullable','integer','min:0'],
            'category_ids'   => ['nullable','array'],
            'category_ids.*' => ['integer','exists:categories,id'],
        ]);

        $update = ['name' => $validated['name']];

        if (array_key_exists('is_active', $validated)) {
            $update['is_active'] = (bool)$validated['is_active'];
        }

        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = $validated['sort_order'];
        }

        if ($request->hasFile('image')) {
            if (!empty($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
            $update['image'] = $request->file('image')->store('brands', 'public');
        }

        $brand->update($update);

        if ($request->has('category_ids')) {
            $brand->categories()->sync($validated['category_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'brand'   => $brand->load('categories:id,name'),
        ]);
    }

    public function destroy(Brand $brand)
    {
        $brand->categories()->detach();

        if (!empty($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'brand_ids' => ['required', 'array'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
        ]);

        foreach ($validated['brand_ids'] as $index => $brandId) {
            Brand::where('id', $brandId)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brands reordered successfully',
        ]);
    }

    public function moveUp(Brand $brand)
    {
        $previousBrand = Brand::where('sort_order', '<', $brand->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousBrand) {
            $tempOrder = $brand->sort_order;
            $brand->update(['sort_order' => $previousBrand->sort_order]);
            $previousBrand->update(['sort_order' => $tempOrder]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand moved up successfully',
            'brand' => $brand->fresh(),
        ]);
    }

    public function moveDown(Brand $brand)
    {
        $nextBrand = Brand::where('sort_order', '>', $brand->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextBrand) {
            $tempOrder = $brand->sort_order;
            $brand->update(['sort_order' => $nextBrand->sort_order]);
            $nextBrand->update(['sort_order' => $tempOrder]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Brand moved down successfully',
            'brand' => $brand->fresh(),
        ]);
    }
}
