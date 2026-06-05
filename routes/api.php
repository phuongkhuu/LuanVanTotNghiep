<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteRequestController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'getFeatured']);
    Route::get('/products/new', [ProductController::class, 'getNew']);
    Route::get('/products/hot-sale', [ProductController::class, 'getHotSale']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/products/{id}/related', [ProductController::class, 'getRelated']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/categories/{slug}/products', [CategoryController::class, 'getProducts']);

    // Brands
    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{slug}/products', [BrandController::class, 'getProducts']);

    // News
    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/latest', [NewsController::class, 'latest']);
    Route::get('/news/{slug}', [NewsController::class, 'show']);

    // Banners
    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/banners/active', [BannerController::class, 'getActive']);
    Route::get('/banners/{id}', [BannerController::class, 'show']);

    // Campaigns (nếu có)
    Route::get('/campaigns/active', [CampaignController::class, 'getActive']);
    Route::get('/campaigns/{id}', [CampaignController::class, 'show']);

    // Reviews
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'getProductReviews']);
});

// Protected routes (cần authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Quote Requests
    Route::post('/quote-requests', [QuoteRequestController::class, 'store']);
    Route::get('/quote-requests/my-requests', [QuoteRequestController::class, 'myRequests']);
    
    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});