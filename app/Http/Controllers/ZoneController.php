<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display a listing of zones
     */
    public function index()
    {
        $zones = Zone::withCount('addresses')->get();
        
        return response()->json([
            'success' => true,
            'zones' => $zones
        ]);
    }

    /**
     * Store a newly created zone
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:zones,name',
            'shipping_price' => 'required|numeric|min:0'
        ]);

        $zone = Zone::create([
            'name' => $request->name,
            'shipping_price' => $request->shipping_price
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Zone created successfully',
            'zone' => $zone
        ], 201);
    }

    /**
     * Display the specified zone
     */
    public function show($id)
    {
        $zone = Zone::withCount('addresses')->find($id);

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'zone' => $zone
        ]);
    }

    /**
     * Update the specified zone
     */
    public function update(Request $request, $id)
    {
        $zone = Zone::find($id);

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:zones,name,' . $id,
            'shipping_price' => 'required|numeric|min:0'
        ]);

        $zone->update([
            'name' => $request->name,
            'shipping_price' => $request->shipping_price
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Zone updated successfully',
            'zone' => $zone
        ]);
    }

    /**
     * Remove the specified zone
     */
    public function destroy($id)
    {
        $zone = Zone::find($id);

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Zone not found'
            ], 404);
        }

        // Check if zone has addresses
        if ($zone->addresses()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete zone that has addresses assigned to it'
            ], 400);
        }

        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Zone deleted successfully'
        ]);
    }

    /**
     * Get shipping price for a specific address
     */
    public function getShippingPrice(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        $shippingPrice = Zone::getShippingPriceForAddress($request->address_id);

        return response()->json([
            'success' => true,
            'shipping_price' => $shippingPrice
        ]);
    }
}
