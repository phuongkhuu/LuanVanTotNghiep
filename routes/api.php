<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use App\Http\Controllers\OrderHistoryController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // ==================== PUBLIC ROUTES (No Auth Required) ====================
    

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Route lấy lịch sử đơn hàng cho user đã đăng nhập
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders/history', [OrderHistoryController::class, 'getOrders'])
            ->name('api.orders.history');
    });



    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/featured', [ProductController::class, 'getFeatured']);
        Route::get('/new', [ProductController::class, 'getNew']);
        Route::get('/hot-sale', [ProductController::class, 'getHotSale']);
        Route::get('/best-seller', [ProductController::class, 'getBestSeller']);
        Route::get('/{slug}', [ProductController::class, 'show']);
        Route::get('/{id}/related', [ProductController::class, 'getRelated']);
    });

   


    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
        Route::get('/{slug}/products', [CategoryController::class, 'getProducts']);
        Route::get('/{slug}/subcategories', [CategoryController::class, 'getSubcategories']);
    });

    // Brands
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/featured', [BrandController::class, 'getFeatured']);
        Route::get('/{slug}', [BrandController::class, 'show']);
        Route::get('/{slug}/products', [BrandController::class, 'getProducts']);
    });

    // News/Blog
    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/latest', [NewsController::class, 'latest']);
        Route::get('/featured', [NewsController::class, 'getFeatured']);
        Route::get('/categories', [NewsController::class, 'getCategories']);
        Route::get('/category/{slug}', [NewsController::class, 'getByCategory']);
        Route::get('/{slug}', [NewsController::class, 'show']);
    });

    // Banners/Sliders
    Route::prefix('banners')->group(function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::get('/active', [BannerController::class, 'getActive']);
        Route::get('/home', [BannerController::class, 'getHomeBanners']);
        Route::get('/{id}', [BannerController::class, 'show']);
    });

    // Campaigns (Promotions/Events)
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::get('/active', [CampaignController::class, 'getActive']);
        Route::get('/upcoming', [CampaignController::class, 'getUpcoming']);
        Route::get('/{id}', [CampaignController::class, 'show']);
        Route::get('/{id}/products', [CampaignController::class, 'getProducts']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/products/{productId}', [ReviewController::class, 'getProductReviews']);
        Route::get('/products/{productId}/stats', [ReviewController::class, 'getReviewStats']);
    });

    // Search
    Route::get('/search', [ProductController::class, 'search'])->name('api.search');
    
    // ==================== AUTHENTICATED ROUTES (Requires Sanctum Token) ====================
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // User Profile
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'profile']);
            Route::put('/profile', [UserController::class, 'updateProfile']);
            Route::put('/change-password', [UserController::class, 'changePassword']);
            Route::get('/addresses', [UserController::class, 'getAddresses']);
            Route::post('/addresses', [UserController::class, 'addAddress']);
            Route::put('/addresses/{id}', [UserController::class, 'updateAddress']);
            Route::delete('/addresses/{id}', [UserController::class, 'deleteAddress']);
        });
        
        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::put('/update/{id}', [CartController::class, 'update']);
            Route::delete('/remove/{id}', [CartController::class, 'remove']);
            Route::delete('/clear', [CartController::class, 'clear']);
            Route::post('/apply-coupon', [CartController::class, 'applyCoupon']);
            Route::post('/remove-coupon', [CartController::class, 'removeCoupon']);
        });
        
        // Checkout & Orders
        Route::prefix('checkout')->group(function () {
            Route::post('/', [CheckoutController::class, 'process']);
            Route::post('/validate', [CheckoutController::class, 'validate']);
            Route::get('/shipping-methods', [CheckoutController::class, 'getShippingMethods']);
            Route::get('/payment-methods', [CheckoutController::class, 'getPaymentMethods']);
        });
        
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'myOrders']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::post('/', [OrderController::class, 'store']);
            Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
            Route::get('/{id}/tracking', [OrderController::class, 'tracking']);
            Route::post('/{id}/reorder', [OrderController::class, 'reorder']);
        });
        
        // Quote Requests (Wholesale)
        Route::prefix('quote-requests')->group(function () {
            Route::get('/', [QuoteRequestController::class, 'myRequests']);
            Route::get('/{id}', [QuoteRequestController::class, 'show']);
            Route::post('/', [QuoteRequestController::class, 'store']);
            Route::post('/{id}/cancel', [QuoteRequestController::class, 'cancel']);
        });
        
        // Reviews (Authenticated)
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('/{id}', [ReviewController::class, 'update']);
            Route::delete('/{id}', [ReviewController::class, 'destroy']);
            Route::post('/{id}/helpful', [ReviewController::class, 'markHelpful']);
        });
        
        // Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [ProductController::class, 'getWishlist']);
            Route::post('/add/{productId}', [ProductController::class, 'addToWishlist']);
            Route::delete('/remove/{productId}', [ProductController::class, 'removeFromWishlist']);
        });
    });
    
    // ==================== ADMIN ROUTES (Requires Admin Token) ====================
    
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        
        // Dashboard stats
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
        Route::get('/dashboard/revenue-chart', [DashboardController::class, 'getRevenueChart']);
        
        // Orders Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index']);
            Route::get('/{id}', [AdminOrderController::class, 'show']);
            Route::put('/{id}/status', [AdminOrderController::class, 'updateStatus']);
            Route::put('/{id}/payment-status', [AdminOrderController::class, 'updatePaymentStatus']);
            Route::post('/export', [AdminOrderController::class, 'export']);
            Route::delete('/{id}', [AdminOrderController::class, 'destroy']);
        });
        
        // Products Management
        Route::apiResource('products', AdminProductController::class);
        Route::post('/products/{id}/variants', [AdminProductController::class, 'addVariant']);
        Route::put('/products/variants/{id}', [AdminProductController::class, 'updateVariant']);
        Route::delete('/products/variants/{id}', [AdminProductController::class, 'deleteVariant']);
        
        // Categories Management
        Route::apiResource('categories', AdminCategoryController::class);
        
        // Brands Management
        Route::apiResource('brands', AdminBrandController::class);
        
        // Campaigns Management
        Route::apiResource('campaigns', AdminCampaignController::class);
        
        // Banners Management
        Route::apiResource('banners', AdminBannerController::class);
        
        // News Management
        Route::apiResource('news', AdminNewsController::class);
        
        // Customers Management
        Route::prefix('customers')->group(function () {
            Route::get('/', [AdminCustomerController::class, 'index']);
            Route::get('/{id}', [AdminCustomerController::class, 'show']);
            Route::put('/{id}', [AdminCustomerController::class, 'update']);
            Route::post('/{id}/ban', [AdminCustomerController::class, 'ban']);
            Route::post('/{id}/unban', [AdminCustomerController::class, 'unban']);
        });
        
        // Quote Requests Management
        Route::prefix('quote-requests')->group(function () {
            Route::get('/', [AdminQuoteRequestController::class, 'index']);
            Route::get('/{id}', [AdminQuoteRequestController::class, 'show']);
            Route::put('/{id}/status', [AdminQuoteRequestController::class, 'updateStatus']);
            Route::post('/{id}/send-quote', [AdminQuoteRequestController::class, 'sendQuote']);
        });
        
        // Logo Print Requests Management
        Route::prefix('logo-print-requests')->group(function () {
            Route::get('/', [AdminLogoPrintController::class, 'index']);
            Route::get('/{id}', [AdminLogoPrintController::class, 'show']);
            Route::put('/{id}/status', [AdminLogoPrintController::class, 'updateStatus']);
        });
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/revenue', [AdminReportController::class, 'revenue']);
            Route::get('/top-products', [AdminReportController::class, 'topProducts']);
            Route::get('/top-customers', [AdminReportController::class, 'topCustomers']);
            Route::get('/order-stats', [AdminReportController::class, 'orderStats']);
        });
    });
});