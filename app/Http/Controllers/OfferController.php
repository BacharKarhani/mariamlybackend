<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    /**
     * Get the current offer text (public access)
     */
    public function get(): JsonResponse
    {
        $offer = Offer::first();
        
        return response()->json([
            'success' => true,
            'offer' => $offer ? $offer->offer_text : null
        ]);
    }

    /**
     * Create or update offer text (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'offer_text' => 'required|string|max:1000'
        ]);

        // Check if an offer already exists
        $existingOffer = Offer::first();
        
        if ($existingOffer) {
            // Update existing offer
            $existingOffer->update([
                'offer_text' => $request->offer_text
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Offer updated successfully',
                'offer' => $existingOffer
            ]);
        } else {
            // Create new offer
            $offer = Offer::create([
                'offer_text' => $request->offer_text
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Offer created successfully',
                'offer' => $offer
            ], 201);
        }
    }

    /**
     * Delete the offer (admin only)
     */
    public function destroy(): JsonResponse
    {
        $offer = Offer::first();
        
        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'No offer found to delete'
            ], 404);
        }

        $offer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offer deleted successfully'
        ]);
    }

    /**
     * Get offer details for admin (admin only)
     */
    public function show(): JsonResponse
    {
        $offer = Offer::first();
        
        return response()->json([
            'success' => true,
            'offer' => $offer
        ]);
    }
}
