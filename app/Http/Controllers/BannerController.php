<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;

class BannerController extends Controller
{
    // Public: رجّع كل الصور المفعّلة مرتّبة
    public function publicIndex()
    {
        $banners = Banner::active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['id','image_path','sort_order','is_active'])
            ->each->setAppends(['image_url']); // يضمن image_url

        return response()->json([
            'success' => true,
            'data'    => $banners
        ]);
    }

    // Admin list
    public function index(Request $request)
    {
        $q = Banner::query()
            ->when($request->filled('active'), fn($qq) => $qq->where('is_active',$request->boolean('active')))
            ->orderBy('sort_order')->orderByDesc('id');

        return response()->json([
            'success' => true,
            'data'    => $q->paginate($request->get('per_page', 20))
        ]);
    }

    public function store(StoreBannerRequest $request)
    {
        try {
            $path = $request->file('image')->store('banners','public');

            $banner = Banner::create([
                'image_path'  => $path,
                'sort_order'  => $request->input('sort_order', 0),
                'is_active'   => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Banner created',
                'banner'  => $banner
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        try {
            if ($request->hasFile('image')) {
                if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                    Storage::disk('public')->delete($banner->image_path);
                }
                $banner->image_path = $request->file('image')->store('banners','public');
            }

            $banner->fill($request->only(['sort_order','is_active']))->save();

            return response()->json([
                'success' => true,
                'message' => 'Banner updated',
                'banner'  => $banner
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Banner $banner)
    {
        try {
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'ids' => ['required','array','min:1'],
                'ids.*' => ['integer','exists:banners,id']
            ]);
            foreach ($request->ids as $i => $id) {
                Banner::where('id',$id)->update(['sort_order'=>$i]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Reordered'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder banners',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Banner $banner)
    {
        return response()->json([
            'success' => true,
            'banner'  => $banner
        ]);
    }
}
