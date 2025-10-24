<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactInfoController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferSectionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PagesBannersImageController;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/ping', [AuthController::class, 'ping']);

// Test email route (remove in production)
Route::get('/test-email/{email}', function($email) {
    try {
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\PasswordResetMail(
            $verificationCode,
            $user->fname . ' ' . $user->lname,
            $email
        ));
        
        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully',
            'verification_code' => $verificationCode // Only for testing
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to send email: ' . $e->getMessage()
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Public Product & General Routes
|--------------------------------------------------------------------------
*/
// Categories (public list + public show)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Subcategories (public list + public show)
Route::get('/subcategories', [SubcategoryController::class, 'index']);
Route::get('/subcategories/{subcategory}', [SubcategoryController::class, 'show']);
Route::get('/categories/{categoryId}/subcategories', [SubcategoryController::class, 'getByCategory']);

// Brands (public list + public show)
Route::get('/brands', [BrandController::class, 'index']);

// Products (public browsing)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/trending', [ProductController::class, 'trending']);
Route::get('/products/new', [ProductController::class, 'newProducts']);
Route::get('/products/hot-offers', [ProductController::class, 'hotOffers']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->whereNumber('product'); // 👈 المهم: قيّد {product} يكون رقم
Route::get('/products/{product}/related', [ProductController::class, 'related'])
    ->whereNumber('product'); // (اختياري بس لثبات السلوك)

// Zones
Route::get('/zones', [ZoneController::class, 'index']);

// Banners (public)
Route::get('/banners', [BannerController::class, 'publicIndex']);

// Pages Banners (public)
Route::get('/pages-banners/{pageName}', [PagesBannersImageController::class, 'getByPage']);

// Contact
Route::post('/contact', [ContactController::class, 'store']);

Route::get('/contact-info', [ContactInfoController::class, 'index']);

Route::get('/products/discounted', [ProductController::class, 'discounted']);

// Newsletter Subscription (Public)
Route::post('/newsletter/subscribe', [NewsletterSubscriptionController::class, 'store']);
Route::post('/newsletter/unsubscribe', [NewsletterSubscriptionController::class, 'unsubscribe']);

// Offer (Public - Get only)
Route::get('/offer', [OfferController::class, 'get']);

// Offer Sections (Public - Get active offer sections)
Route::get('/offer-sections', [OfferSectionController::class, 'publicIndex']);

// Reviews (Public - Get approved reviews only)
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'index'])->whereNumber('product');

/*
|--------------------------------------------------------------------------
| Whish Payment (Open Routes)
|--------------------------------------------------------------------------
*/
Route::get('/payment/whish/callback/success/{order_id}', [PaymentController::class, 'whishCallbackSuccess'])->name('api.whish.callback.success');
Route::get('/payment/whish/callback/failure/{order_id}', [PaymentController::class, 'whishCallbackFailure'])->name('api.whish.callback.failure');
Route::get('/payment/whish/redirect/success/{order_id}', [PaymentController::class, 'whishRedirectSuccess'])->name('api.whish.redirect.success');
Route::get('/payment/whish/redirect/failure/{order_id}', [PaymentController::class, 'whishRedirectFailure'])->name('api.whish.redirect.failure');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Recently viewed products (two paths for convenience)
    Route::get('/products/recently-viewed', [ProductController::class, 'recentlyViewed']);
    Route::get('/recently-viewed', [ProductController::class, 'recentlyViewed']);

    // User profile
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product_id}', [WishlistController::class, 'destroy'])->whereNumber('product_id');

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{product_id}', [CartController::class, 'update'])->whereNumber('product_id');
    Route::delete('/cart/{product_id}', [CartController::class, 'destroy'])->whereNumber('product_id');

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->whereNumber('address');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->whereNumber('address');
    Route::get('/addresses/{address}', [AddressController::class, 'show'])->whereNumber('address');

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'store']);

    // Orders (self)
    Route::get('/orders/my', [OrderController::class, 'myOrders']);

    // Reviews (User can submit reviews)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show'])->whereNumber('review');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/users/count', [AuthController::class, 'totalUsers']);
    Route::get('/products/count', [ProductController::class, 'totalCount']);
    Route::get('/products/admin', [ProductController::class, 'indexAdmin']);
    Route::get('/products/admin/search', [ProductController::class, 'adminSearch']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);

    // Categories (admin CRUD)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->whereNumber('category');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->whereNumber('category');

    // Subcategories (admin CRUD)
    Route::post('/subcategories', [SubcategoryController::class, 'store']);
    Route::put('/subcategories/{subcategory}', [SubcategoryController::class, 'update'])->whereNumber('subcategory');
    Route::delete('/subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->whereNumber('subcategory');

    // Brands (admin CRUD)
    Route::post('/brands', [BrandController::class, 'store']);
    Route::match(['put', 'patch'], '/brands/{brand}', [BrandController::class, 'update'])->whereNumber('brand');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->whereNumber('brand');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->whereNumber('brand');
    
    // Brand ordering routes
    Route::post('/brands/reorder', [BrandController::class, 'reorder']);
    Route::post('/brands/{brand}/move-up', [BrandController::class, 'moveUp'])->whereNumber('brand');
    Route::post('/brands/{brand}/move-down', [BrandController::class, 'moveDown'])->whereNumber('brand');

    // Products (admin CRUD)
    Route::get('/admin/products', [ProductController::class, 'indexAdmin']); // Admin products list

    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update'])->whereNumber('product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->whereNumber('product');

    // Product Variants (admin CRUD)
    Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])->whereNumber('product');
    Route::get('/variants/{variant}', [ProductVariantController::class, 'show'])->whereNumber('variant');
    Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->whereNumber('product');
    Route::put('/variants/{variant}', [ProductVariantController::class, 'update'])->whereNumber('variant');
    Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy'])->whereNumber('variant');
    Route::post('/variants/{variant}/images', [ProductVariantController::class, 'addImages'])->whereNumber('variant');
    Route::delete('/variants/{variant}/images/{image}', [ProductVariantController::class, 'removeImage'])->whereNumber('variant')->whereNumber('image');
    
    // Variant ordering routes
    Route::post('/products/{product}/variants/reorder', [ProductVariantController::class, 'reorder'])->whereNumber('product');
    Route::post('/variants/{variant}/move-up', [ProductVariantController::class, 'moveUp'])->whereNumber('variant');
    Route::post('/variants/{variant}/move-down', [ProductVariantController::class, 'moveDown'])->whereNumber('variant');
    
    // Variant stock management routes
    Route::get('/products/{product}/variants/in-stock', [ProductVariantController::class, 'inStock'])->whereNumber('product');
    Route::get('/products/{product}/variants/out-of-stock', [ProductVariantController::class, 'outOfStock'])->whereNumber('product');

    // Orders (admin)
    Route::get('/orders', [OrderController::class, 'indexPaginated']);
    Route::get('/orders/{order_id}', [OrderController::class, 'show'])->whereNumber('order_id');
    Route::put('/orders/{order_id}/update-status', [OrderController::class, 'updateStatus'])->whereNumber('order_id');
    Route::get('/orders/{order_id}/profit', [OrderController::class, 'getOrderProfit'])->whereNumber('order_id');

    // NEW: Orders stats
    Route::get('/orders/stats/count-by-month', [OrderController::class, 'countByMonth']);
    Route::get('/orders/stats/profit-by-month', [OrderController::class, 'profitByMonth']);
    // Users (admin)
    Route::get('/users/search-by-name', [AuthController::class, 'searchUserByName']);
   
    Route::put('/users/{userId}/promote', [AuthController::class, 'promoteToAdmin'])->whereNumber('userId');

    // Banners (admin)
    Route::get('/banners/admin', [BannerController::class, 'index']);
    Route::post('/banners', [BannerController::class, 'store']);
    Route::get('/banners/{banner}', [BannerController::class, 'show'])->whereNumber('banner');
    Route::put('/banners/{banner}', [BannerController::class, 'update'])->whereNumber('banner');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->whereNumber('banner');
    Route::post('/banners/reorder', [BannerController::class, 'reorder']);

    Route::get('/admin/contact-info', [ContactInfoController::class, 'adminIndex']);
    Route::put('/admin/contact-info', [ContactInfoController::class, 'update']);

    // Newsletter Subscriptions (Admin)
    Route::get('/newsletter-subscriptions', [NewsletterSubscriptionController::class, 'index']);
    Route::get('/newsletter-subscriptions/{id}', [NewsletterSubscriptionController::class, 'show']);
    Route::put('/newsletter-subscriptions/{id}', [NewsletterSubscriptionController::class, 'update']);
    Route::delete('/newsletter-subscriptions/{id}', [NewsletterSubscriptionController::class, 'destroy']);
    Route::get('/newsletter-subscriptions/stats/overview', [NewsletterSubscriptionController::class, 'stats']);

    // Offer Management (Admin Only)
    Route::get('/admin/offer', [OfferController::class, 'show']);
    Route::post('/admin/offer', [OfferController::class, 'store']);
    Route::delete('/admin/offer', [OfferController::class, 'destroy']);

    // Offer Sections Management (Admin Only)
    Route::get('/admin/offer-sections', [OfferSectionController::class, 'index']);
    Route::post('/admin/offer-sections', [OfferSectionController::class, 'store']);
    Route::get('/admin/offer-sections/{offerSection}', [OfferSectionController::class, 'show'])->whereNumber('offerSection');
    Route::put('/admin/offer-sections/{offerSection}', [OfferSectionController::class, 'update'])->whereNumber('offerSection');
    Route::delete('/admin/offer-sections/{offerSection}', [OfferSectionController::class, 'destroy'])->whereNumber('offerSection');
    Route::post('/admin/offer-sections/reorder', [OfferSectionController::class, 'reorder']);

    // Reviews Management (Admin Only)
    Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
    Route::get('/admin/products/{product}/reviews', [ReviewController::class, 'adminIndex'])->whereNumber('product');
    Route::put('/admin/reviews/{review}', [ReviewController::class, 'update'])->whereNumber('review');
    Route::delete('/admin/reviews/{review}', [ReviewController::class, 'destroy'])->whereNumber('review');

    // Pages Banners Images Management (Admin Only)
    Route::get('/admin/pages-banners', [PagesBannersImageController::class, 'index']);
    Route::post('/admin/pages-banners', [PagesBannersImageController::class, 'store']);
    Route::get('/admin/pages-banners/{banner}', [PagesBannersImageController::class, 'show'])->whereNumber('banner');
    Route::put('/admin/pages-banners/{banner}', [PagesBannersImageController::class, 'update'])->whereNumber('banner');
    Route::delete('/admin/pages-banners/{banner}', [PagesBannersImageController::class, 'destroy'])->whereNumber('banner');
    Route::post('/admin/pages-banners/{banner}/toggle-status', [PagesBannersImageController::class, 'toggleStatus'])->whereNumber('banner');

});
