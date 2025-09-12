<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Public Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/ping', [AuthController::class, 'ping']);

/*
|--------------------------------------------------------------------------
| Public Product & General Routes
|--------------------------------------------------------------------------
*/
// Categories (public list + public show)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Brands (public list + public show)
Route::get('/brands', [BrandController::class, 'index']);

// Products (public browsing)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/trending', [ProductController::class, 'trending']);
Route::get('/products/new', [ProductController::class, 'newProducts']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->whereNumber('product'); // 👈 المهم: قيّد {product} يكون رقم
Route::get('/products/{product}/related', [ProductController::class, 'related'])
    ->whereNumber('product'); // (اختياري بس لثبات السلوك)

// Zones
Route::get('/zones', [ZoneController::class, 'index']);

// Banners (public)
Route::get('/banners', [BannerController::class, 'publicIndex']);

// Contact
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/products/discounted', [ProductController::class, 'discounted']);

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
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/users/count', [AuthController::class, 'totalUsers']);
    Route::get('/products/count', [ProductController::class, 'totalCount']);

    // Categories (admin CRUD)
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->whereNumber('category');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->whereNumber('category');

    // Brands (admin CRUD)
    Route::post('/brands', [BrandController::class, 'store']);
    Route::match(['put', 'patch'], '/brands/{brand}', [BrandController::class, 'update'])->whereNumber('brand');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->whereNumber('brand');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->whereNumber('brand');

    // Products (admin CRUD)
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update'])->whereNumber('product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->whereNumber('product');

    // Orders (admin)
    Route::get('/orders', [OrderController::class, 'indexPaginated']);
    Route::get('/orders/{order_id}', [OrderController::class, 'show'])->whereNumber('order_id');
    Route::put('/orders/{order_id}/update-status', [OrderController::class, 'updateStatus'])->whereNumber('order_id');
    Route::get('/orders/{order_id}/profit', [OrderController::class, 'getOrderProfit'])->whereNumber('order_id');

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
});
