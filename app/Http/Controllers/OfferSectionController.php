<?php

namespace App\Http\Controllers;

use App\Models\OfferSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferSectionController extends Controller
{
    // Public: Get active offer sections ordered by sort_order
    public function publicIndex()
    {
        $offerSections = OfferSection::active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->each->setAppends(['image_url']);

        return response()->json([
            'success' => true,
            'data'    => $offerSections
        ]);
    }

    // Admin list
    public function index(Request $request)
    {
        $q = OfferSection::query()
            ->when($request->filled('active'), fn($qq) => $qq->where('is_active',$request->boolean('active')))
            ->orderBy('sort_order')->orderByDesc('id');

        return response()->json([
            'success' => true,
            'data'    => $q->paginate($request->get('per_page', 20))
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'alt_text' => 'nullable|string|max:255',
                'discount_percentage' => 'nullable|string|max:50',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'button_text' => 'nullable|string|max:100',
                'button_link' => 'nullable|string|max:255',
                'is_active' => 'boolean',
                'sort_order' => 'integer|min:0'
            ]);

            $data = $request->only([
                'alt_text', 
                'discount_percentage', 
                'title', 
                'description', 
                'button_text', 
                'button_link', 
                'is_active', 
                'sort_order'
            ]);

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('offer-sections','public');
            }

            $offerSection = OfferSection::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Offer section created',
                'data'  => $offerSection
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create offer section',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, OfferSection $offerSection)
    {
        try {
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'alt_text' => 'nullable|string|max:255',
                'discount_percentage' => 'nullable|string|max:50',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'button_text' => 'nullable|string|max:100',
                'button_link' => 'nullable|string|max:255',
                'is_active' => 'boolean',
                'sort_order' => 'integer|min:0'
            ]);

            if ($request->hasFile('image')) {
                if ($offerSection->image_path && Storage::disk('public')->exists($offerSection->image_path)) {
                    Storage::disk('public')->delete($offerSection->image_path);
                }
                $offerSection->image_path = $request->file('image')->store('offer-sections','public');
            }

            $offerSection->fill($request->only([
                'alt_text', 
                'discount_percentage', 
                'title', 
                'description', 
                'button_text', 
                'button_link', 
                'is_active', 
                'sort_order'
            ]))->save();

            return response()->json([
                'success' => true,
                'message' => 'Offer section updated',
                'data'  => $offerSection
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update offer section',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(OfferSection $offerSection)
    {
        try {
            if ($offerSection->image_path && Storage::disk('public')->exists($offerSection->image_path)) {
                Storage::disk('public')->delete($offerSection->image_path);
            }
            $offerSection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Offer section deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete offer section',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'ids' => ['required','array','min:1'],
                'ids.*' => ['integer','exists:offer_sections,id']
            ]);
            foreach ($request->ids as $i => $id) {
                OfferSection::where('id',$id)->update(['sort_order'=>$i]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Reordered'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder offer sections',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(OfferSection $offerSection)
    {
        return response()->json([
            'success' => true,
            'data'  => $offerSection->setAppends(['image_url'])
        ]);
    }
}
