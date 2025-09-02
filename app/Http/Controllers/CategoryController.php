<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('brands:id,name')->get();

        return response()->json([
            'success'     => true,
            'categories'  => $categories
        ]);
    }

    public function show(Category $category)
    {
        $category->load('brands:id,name');

        return response()->json([
            'success'  => true,
            'category' => $category
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255','unique:categories,name'],
            'image'       => ['nullable','image','max:2048'],
            'brand_ids'   => ['nullable','array'],
            'brand_ids.*' => ['integer','exists:brands,id'],
        ]);

        $data = ['name' => $validated['name']];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        if (!empty($validated['brand_ids'])) {
            $category->brands()->sync($validated['brand_ids']);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Category created successfully',
            'category' => $category->load('brands:id,name'),
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('categories','name')->ignore($category->id)],
            'image'       => ['nullable','image','max:2048'],
            'brand_ids'   => ['nullable','array'],
            'brand_ids.*' => ['integer','exists:brands,id'],
        ]);

        $update = ['name' => $validated['name']];

        if ($request->hasFile('image')) {
            if (!empty($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $update['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($update);

        if ($request->has('brand_ids')) {
            $category->brands()->sync($validated['brand_ids'] ?? []);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Category updated successfully',
            'category' => $category->load('brands:id,name'),
        ]);
    }

    public function destroy(Category $category)
    {
        $category->brands()->detach();

        if (!empty($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
