<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist = $request->user()->wishlist()->with('product')->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $wishlist->each(function ($item) {
                $item->product->makeHidden('buying_price');
            });
        }

        return response()->json([
            'success' => true,
            'wishlist' => $wishlist
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        $wishlist->load('product');

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $wishlist->product->makeHidden('buying_price');
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
            'wishlist' => $wishlist
        ], 201);
    }

    public function destroy(Request $request, $product_id)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->first();

        if (! $wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed successfully.'
        ]);
    }
}
