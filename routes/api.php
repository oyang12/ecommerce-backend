<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AuthController; // Controller Login

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- 1. AUTHENTICATION ---
Route::post('/login', [AuthController::class, 'login']);

// --- 2. PUBLIC ROUTES (Bisa diakses tanpa login) ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'productReviews']);

Route::get('/test', function () {
    return response()->json(['message' => 'API OK']);
});

// --- 3. PROTECTED ROUTES (Harus bawa Token / Login) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // === AREA ADMIN (CRUD) ===
    Route::post('/products', [ProductController::class, 'store']); 
    Route::post('/upload', [ProductController::class, 'upload']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::delete('/product-images/{id}', [ProductController::class, 'destroyImage']);
    Route::post('/categories', [CategoryController::class, 'store']);

    // === AREA PENGGUNA (Cart & Order) ===
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove', [CartController::class, 'removeFromCart']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
    Route::get('/orders', [CartController::class, 'listOrders']);
    Route::get('/orders/{order}', [CartController::class, 'viewOrder']);
    Route::patch('/cart/update', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    // === WISHLIST & REVIEWS ===
    Route::post('/wishlist/add', [WishlistController::class, 'add']);
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::delete('/wishlist/remove', [WishlistController::class, 'remove']);
    Route::post('/reviews', [ReviewController::class, 'store']);
});
