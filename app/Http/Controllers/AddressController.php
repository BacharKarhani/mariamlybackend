<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'addresses' => $request->user()->addresses()->with('zone')->get()
        ]);
    }

    public function show(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'address' => $address->load('zone')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => ['required', 'digits:8'],
            'zone_id' => 'required|exists:zones,id',
            'full_address' => 'required|string',
            'more_details' => 'nullable|string',
        ]);

        $existsForOthers = Address::where('phone_number', $request->phone_number)
            ->where('user_id', '!=', $request->user()->id)
            ->exists();

        if ($existsForOthers) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already used by another user'
            ], 422);
        }

        $address = $request->user()->addresses()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'address' => $address->load('zone')
        ], 201);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => ['required', 'digits:8'],
            'zone_id' => 'required|exists:zones,id',
            'full_address' => 'required|string',
            'more_details' => 'nullable|string',
        ]);

        $existsForOthers = Address::where('phone_number', $request->phone_number)
            ->where('user_id', '!=', $request->user()->id)
            ->where('id', '!=', $address->id)
            ->exists();

        if ($existsForOthers) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already used by another user'
            ], 422);
        }

        $address->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'address' => $address->load('zone')
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }
}
