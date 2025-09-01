<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    /**
     * GET /api/brands
     */
    public function index()
    {
        $brands = Brand::with('categories:id,name')->get();

        return response()->json([
            'success' => true,
            'brands'  => $brands,
        ]);
    }

    /**
     * GET /api/brands/{brand}
     */
    public function show(Brand $brand)
    {
        $brand->load('categories:id,name');

        return response()->json([
            'success' => true,
            'brand'   => $brand,
        ]);
    }

    /**
     * POST /api/brands
     * Body: form-data (name, image?, is_active?, category_ids[]?)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255', 'unique:brands,name'],
            'image'         => ['nullable', 'image', 'max:2048'],
            'is_active'     => ['nullable', 'boolean'],
            'category_ids'  => ['nullable', 'array'],
            'category_ids.*'=> ['integer', 'exists:categories,id'],
        ]);

        $data = [
            'name'      => $validated['name'],
            'is_active' => (bool)($validated['is_active'] ?? true),
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

    /**
     * PUT/PATCH /api/brands/{brand}
     * Body: form-data (name, image?, is_active?, category_ids[]?)
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand->id)],
            'image'          => ['nullable', 'image', 'max:2048'],
            'is_active'      => ['nullable', 'boolean'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $update = ['name' => $validated['name']];

        if (array_key_exists('is_active', $validated)) {
            $update['is_active'] = (bool)$validated['is_active'];
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

    /**
     * DELETE /api/brands/{brand}
     */
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
}
