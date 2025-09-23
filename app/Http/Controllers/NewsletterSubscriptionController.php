<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource (Admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:active,inactive,all',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|min:1'
        ]);

        $perPage = $request->input('per_page', 20);
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = NewsletterSubscription::query();

        // Filter by status
        switch ($status) {
            case 'active':
                $query->active();
                break;
            case 'inactive':
                $query->inactive();
                break;
            // 'all' shows everything, no filter needed
        }

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        $subscriptions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $subscriptions
        ]);
    }

    /**
     * Store a newly created resource (Public endpoint).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255'
        ]);

        try {
            // Check if email already exists
            $existingSubscription = NewsletterSubscription::where('email', $request->email)->first();

            if ($existingSubscription) {
                if ($existingSubscription->isActive()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email is already subscribed to our newsletter.'
                    ], 409);
                } else {
                    // Resubscribe the user
                    $existingSubscription->resubscribe();
                    return response()->json([
                        'success' => true,
                        'message' => 'Welcome back! You have been resubscribed to our newsletter.',
                        'data' => $existingSubscription
                    ], 200);
                }
            }

            // Create new subscription
            $subscription = NewsletterSubscription::create([
                'email' => $request->email,
                'name' => $request->name,
                'subscribed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing to our newsletter!',
                'data' => $subscription
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    /**
     * Display the specified resource (Admin only).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $subscription = NewsletterSubscription::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subscription
        ]);
    }

    /**
     * Update the specified resource (Admin only).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $subscription = NewsletterSubscription::findOrFail($id);

        $request->validate([
            'email' => 'sometimes|required|email|max:255|unique:newsletter_subscriptions,email,' . $id,
            'name' => 'nullable|string|max:255'
        ]);

        $subscription->update($request->only(['email', 'name']));

        return response()->json([
            'success' => true,
            'message' => 'Subscription updated successfully',
            'data' => $subscription
        ]);
    }

    /**
     * Remove the specified resource from storage (Admin only).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $subscription = NewsletterSubscription::findOrFail($id);
        $subscription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription deleted successfully'
        ]);
    }

    /**
     * Unsubscribe from newsletter (Public endpoint).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $subscription = NewsletterSubscription::where('email', $request->email)->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found in our subscription list.'
            ], 404);
        }

        if (!$subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already unsubscribed.'
            ], 409);
        }

        $subscription->unsubscribe();

        return response()->json([
            'success' => true,
            'message' => 'You have been successfully unsubscribed from our newsletter.'
        ]);
    }

    /**
     * Get subscription statistics (Admin only).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(): JsonResponse
    {
        $totalSubscriptions = NewsletterSubscription::count();
        $activeSubscriptions = NewsletterSubscription::active()->count();
        $inactiveSubscriptions = NewsletterSubscription::inactive()->count();
        
        // Recent subscriptions (last 30 days)
        $recentSubscriptions = NewsletterSubscription::where('created_at', '>=', now()->subDays(30))->count();
        
        // Monthly subscriptions for the last 12 months
        $monthlyStats = NewsletterSubscription::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as count
        ')
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_subscriptions' => $totalSubscriptions,
                'active_subscriptions' => $activeSubscriptions,
                'inactive_subscriptions' => $inactiveSubscriptions,
                'recent_subscriptions' => $recentSubscriptions,
                'monthly_stats' => $monthlyStats
            ]
        ]);
    }
}
