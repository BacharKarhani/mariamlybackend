<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     */
    public function index()
    {
        // فيك تبدّل all() بـ paginate() إذا بتحب
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * GET /api/categories/{category}
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    /**
     * POST /api/categories
     * Body: form-data (name, image?)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255', 'unique:categories,name'],
            'image' => ['nullable', 'image', 'max:2048'], // 2MB
        ]);

        $data = ['name' => $validated['name']];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * PUT /api/categories/{category}
     * Body: form-data (name, image?)
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'  => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($category->id)
            ],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $update = ['name' => $validated['name']];

        if ($request->hasFile('image')) {
            // احذف الصورة القديمة إذا موجودة
            if (!empty($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $update['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($update);

        return response()->json([
            'success'  => true,
            'message'  => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * DELETE /api/categories/{category}
     */
    public function destroy(Category $category)
    {
        // احذف الصورة من التخزين إذا موجودة
        if (!empty($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
