<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;


Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::post('/upload', [ProductController::class, 'upload']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::delete('/product-images/{id}', [ProductController::class, 'deleteImage']);
Route::get('/categories',[CategoryController::class,'index']);
Route::post('/categories',[CategoryController::class,'store']);



Route::middleware('auth:sanctum')->group(function () {

    // Cart
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove', [CartController::class, 'removeFromCart']);

    // Checkout / Orders
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
    Route::get('/orders', [CartController::class, 'listOrders']);
    Route::get('/orders/{order}', [CartController::class, 'viewOrder']);
    
    Route::patch('/cart/update', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    Route::post('/wishlist/add',[WishlistController::class,'add']);
    Route::get('/wishlist',[WishlistController::class,'index']);
    Route::delete('/wishlist/remove',[WishlistController::class,'remove']);
    Route::post('/reviews',[ReviewController::class,'store']);
});

Route::get('/products/{id}/reviews',[ReviewController::class,'productReviews']);