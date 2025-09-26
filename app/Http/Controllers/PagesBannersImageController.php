<?php

namespace App\Http\Controllers;

use App\Models\PagesBannersImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PagesBannersImageController extends Controller
{
    /**
     * Display a listing of all banner images
     */
    public function index()
    {
        $banners = PagesBannersImage::orderBy('page_name')->get();

        return response()->json([
            'success' => true,
            'banners' => $banners
        ]);
    }

    /**
     * Get banner for a specific page
     */
    public function getByPage($pageName)
    {
        $banner = PagesBannersImage::forPage($pageName)->active()->first();

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found for this page'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'banner' => $banner
        ]);
    }

    /**
     * Store a newly created banner image
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'page_name' => 'required|string|max:100|unique:pages_banners_images,page_name',
            'image' => 'required|image|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        // Store the image
        $path = $request->file('image')->store('banners', 'public');

        $banner = PagesBannersImage::create([
            'title' => $request->title,
            'page_name' => $request->page_name,
            'image_path' => $path,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner image created successfully',
            'banner' => $banner
        ], 201);
    }

    /**
     * Display the specified banner
     */
    public function show(PagesBannersImage $banner)
    {
        return response()->json([
            'success' => true,
            'banner' => $banner
        ]);
    }

    /**
     * Update the specified banner
     */
    public function update(Request $request, PagesBannersImage $banner)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'page_name' => 'required|string|max:100|unique:pages_banners_images,page_name,' . $banner->id,
            'image' => 'sometimes|image|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = [
            'title' => $request->title,
            'page_name' => $request->page_name,
        ];

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            
            // Store new image
            $updateData['image_path'] = $request->file('image')->store('banners', 'public');
        }

        // Handle is_active update
        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }

        $banner->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'banner' => $banner
        ]);
    }

    /**
     * Remove the specified banner
     */
    public function destroy(PagesBannersImage $banner)
    {
        // Delete the image file
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }

    /**
     * Toggle banner active status
     */
    public function toggleStatus(PagesBannersImage $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Banner status updated successfully',
            'banner' => $banner
        ]);
    }
}
