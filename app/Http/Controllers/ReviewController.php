<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of approved reviews for a specific product.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Review::where('status', true)
            ->with(['user:id,fname,lname', 'product:id,name']);

        // If product_id is provided via query parameter or route parameter
        $productId = $request->get('product_id') ?? $request->route('product');
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $reviews = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Store a newly created review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'comment' => 'required|string|max:1000',
            'stars_count' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product'
            ], 409);
        }
        
        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'comment' => $request->comment,
            'stars_count' => $request->stars_count,
            'status' => false // Default status is false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. It will be reviewed by admin.',
            'data' => $review->load(['user:id,fname,lname', 'product:id,name'])
        ], 201);
    }

    /**
     * Display the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $review = Review::with(['user:id,fname,lname', 'product:id,name'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    /**
     * Update the specified review (Admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|boolean',
            'comment' => 'sometimes|string|max:1000',
            'stars_count' => 'sometimes|integer|min:1|max:5',
            'product_id' => 'sometimes|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only(['status', 'comment', 'stars_count', 'product_id']));

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified review (Admin only).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Get all reviews for admin (including pending ones).
     *
     * @return \Illuminate\Http\Response
     */
    public function adminIndex(Request $request)
    {
        $query = Review::with(['user:id,fname,lname', 'product:id,name']);

        // If product_id is provided via query parameter or route parameter
        $productId = $request->get('product_id') ?? $request->route('product');
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $reviews = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }
}
