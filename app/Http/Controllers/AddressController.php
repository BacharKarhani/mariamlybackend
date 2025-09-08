<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct()
    {
        // تأكد أن كل هالمسارات محمية بالتوكن (Sanctum)
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        return response()->json([
            'success'    => true,
            'addresses'  => $request->user()->addresses()->with('zone')->get(),
        ]);
    }

    public function show(Request $request, Address $address)
    {
        // ✅ إصلاح المقارنة: نضمن أن العنوان فعلاً لصاحب التوكن
        if ((int) $address->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'address' => $address->load('zone'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'   => 'required|string',
            'last_name'    => 'required|string',
            'phone_number' => ['required', 'digits:8'],
            'zone_id'      => 'required|exists:zones,id',
            'full_address' => 'required|string',
            'more_details' => 'nullable|string',
        ]);

        // ممنوع نفس الرقم يكون مستخدم عند مستخدم آخر
        $existsForOthers = Address::where('phone_number', $validated['phone_number'])
            ->where('user_id', '!=', $request->user()->id)
            ->exists();

        if ($existsForOthers) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already used by another user',
            ], 422);
        }

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'address' => $address->load('zone'),
        ], 201);
    }

    public function update(Request $request, Address $address)
    {
        // ✅ إصلاح المقارنة
        if ((int) $address->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'first_name'   => 'required|string',
            'last_name'    => 'required|string',
            'phone_number' => ['required', 'digits:8'],
            'zone_id'      => 'required|exists:zones,id',
            'full_address' => 'required|string',
            'more_details' => 'nullable|string',
        ]);

        // ممنوع الرقم يكون مستخدم عند غيره (وباستثناء نفس العنوان الحالي)
        $existsForOthers = Address::where('phone_number', $validated['phone_number'])
            ->where('user_id', '!=', $request->user()->id)
            ->where('id', '!=', $address->id)
            ->exists();

        if ($existsForOthers) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number already used by another user',
            ], 422);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'address' => $address->load('zone'),
        ]);
    }

    public function destroy(Request $request, Address $address)
    {
        // ✅ إصلاح المقارنة
        if ((int) $address->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }
}
