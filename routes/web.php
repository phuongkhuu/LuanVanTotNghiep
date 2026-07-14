<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomizeController as AdminCustomizeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;

use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController as WebCategoryController;
use App\Http\Controllers\ProductController as WebProductController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ReviewController;

// ==================== ROUTE ĐỂ PHỤC VỤ ẢNH ====================
Route::get('/image/{filename}', function ($filename) {
    $path = base_path('image/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    return response($file, 200)->header('Content-Type', $type);
})->where('filename', '.*');

// ==================== WEB ROUTES (Frontend - Public) ====================

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tim-kiem', function (Request $request) {
    return Inertia::render('Web/Category', ['search' => $request->get('q')]);
})->name('search');

// Product routes - PUBLIC
Route::get('/san-pham/{slug}', [WebProductController::class, 'show'])->name('product.detail');
Route::get('/danh-muc/{slug}', [WebCategoryController::class, 'show'])->name('category');

// Other public frontend routes
Route::get('/mua-si', function () {
    return Inertia::render('Web/Wholesale');
})->name('wholesale');

// Promotion route - Sử dụng PromotionController (Web)
Route::get('/khuyen-mai', [PromotionController::class, 'index'])->name('promotion');

Route::get('/tuy-chinh', function () {
    return Inertia::render('Web/Customize');
})->name('customize');

// ==================== LỊCH SỬ ĐƠN HÀNG ROUTES (Yêu cầu đăng nhập) ====================
Route::middleware(['auth'])->group(function () {
    // Trang lịch sử đơn hàng
    Route::get('/lich-su-don-hang', [OrderHistoryController::class, 'index'])->name('orders.history');
    // API lấy dữ liệu lịch sử đơn hàng
    Route::get('/lich-su-don-hang/data', [OrderHistoryController::class, 'getOrders'])->name('orders.history.data');
});

// ==================== CART ROUTES (Yêu cầu đăng nhập) ====================

// Cart page
Route::get('/gio-hang', function () {
    return Inertia::render('Web/Cart');
})->name('cart')->middleware('auth');

Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{variantId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    
    Route::post('/pre-order/session', function (Request $request) {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        // Lưu thông tin pre-order vào session
        session([
            'pre_order_checkout' => true,
            'pre_order_variant_id' => $request->variant_id,
            'pre_order_quantity' => $request->quantity,
        ]);
        session()->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Pre-order session saved'
        ]);
    });
});

// ==================== CHECKOUT ROUTES (Yêu cầu đăng nhập) ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/thanh-toan', [PaymentController::class, 'index'])->name('checkout');
    Route::post('/thanh-toan', [PaymentController::class, 'store'])->name('checkout.store');
    Route::get('/thanh-toan/thanh-cong', [PaymentController::class, 'success'])->name('checkout.success');
});

// ==================== AUTHENTICATED WEB ROUTES ====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/don-hang', [OrderController::class, 'history'])->name('orders.history.old');
    Route::get('/don-hang/{id}', [OrderController::class, 'show'])->name('orders.show');
});

// ==================== ADMIN ROUTES ====================
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    
    // Orders Management
    Route::prefix('orders')->group(function () {
        Route::get('/{type?}', [AdminOrderController::class, 'index'])
            ->where('type', 'retail|wholesale|preorder')
            ->name('orders.index');
        Route::get('/{id}', [AdminOrderController::class, 'show'])
            ->where('id', '[0-9]+')
            ->name('orders.show');
        Route::put('/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/export', [AdminOrderController::class, 'export'])->name('orders.export');
        Route::get('/export/filtered', [AdminOrderController::class, 'exportWithFilters'])->name('orders.export-filtered');
    });
    
    // Products Management
    Route::prefix('products')->group(function () {
        Route::get('/{type?}', [AdminProductController::class, 'index'])
            ->where('type', 'normal|preorder')
            ->name('products.index');
        Route::post('/', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    });
    
    // Categories Management
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Colors Management
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::get('/colors/data', [ColorController::class, 'getColors'])->name('colors.data');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::put('/colors/{id}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{id}', [ColorController::class, 'destroy'])->name('colors.destroy');
        
    // Brands Management
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/data', [BrandController::class, 'getBrands'])->name('brands.data');
        Route::post('/', [BrandController::class, 'store'])->name('brands.store');
        Route::put('/{id}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::get('/search', [BrandController::class, 'search'])->name('brands.search');
    });
    
    // Customers Management
    Route::prefix('customers')->group(function () {
        Route::get('/', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/retail', [AdminCustomerController::class, 'retail'])->name('customers.retail');
        Route::get('/business', [AdminCustomerController::class, 'business'])->name('customers.business');
        Route::get('/{id}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::put('/{id}', [AdminCustomerController::class, 'update'])->name('customers.update');
        Route::post('/export', [AdminCustomerController::class, 'export'])->name('customers.export');
    });
    
    // Customize Management
    Route::prefix('customize')->group(function () {
        Route::get('/', [AdminCustomizeController::class, 'index'])->name('customize.index');
        Route::put('/{id}/status', [AdminCustomizeController::class, 'updateStatus'])->name('customize.update-status');
        Route::put('/{id}/approve', [AdminCustomizeController::class, 'approve'])->name('customize.approve');
        Route::post('/send-quote', [AdminCustomizeController::class, 'sendQuote'])->name('customize.send-quote');
    });

    // News Management
    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index'])->name('news.index');
        Route::get('/data', [NewsController::class, 'getNews'])->name('news.data');
        Route::post('/', [NewsController::class, 'store'])->name('news.store');
        Route::put('/{id}', [NewsController::class, 'update'])->name('news.update');
        Route::delete('/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
        Route::patch('/{id}/status', [NewsController::class, 'updateStatus'])->name('news.update-status');
    });

    // Reviews Management
    Route::prefix('reviews')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('/{id}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    // Banner routes
    Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/banners/data', [BannerController::class, 'getBanners'])->name('banners.data');
    Route::get('/banners/campaigns', [BannerController::class, 'getCampaigns'])->name('banners.campaigns');
    Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::patch('/banners/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.status');
    Route::patch('/banners/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.order');
    Route::post('/banners/check-status', [BannerController::class, 'checkAndUpdateStatus'])->name('banners.check-status');

    // ==================== PROMOTION ROUTES (FIXED) ====================
    Route::prefix('promotions')->group(function () {
        // Index - Danh sách khuyến mãi
        Route::get('/', [AdminPromotionController::class, 'index'])->name('promotions.index');
        
        // ⭐ Campaign routes
        Route::post('/campaign', [AdminPromotionController::class, 'storeCampaign'])->name('promotions.campaign.store');
        Route::put('/campaign/{id}', [AdminPromotionController::class, 'updateCampaign'])->name('promotions.campaign.update');
        Route::delete('/campaign/{id}', [AdminPromotionController::class, 'deleteCampaign'])->name('promotions.campaign.delete');
        Route::put('/campaign/{id}/status', [AdminPromotionController::class, 'updateCampaignStatus'])->name('promotions.campaign.status');
        Route::get('/campaigns/list', [AdminPromotionController::class, 'getCampaignsList'])->name('promotions.campaigns.list');

        // ⭐ Voucher routes
        Route::post('/voucher', [AdminPromotionController::class, 'storeVoucher'])->name('promotions.voucher.store');
        Route::put('/voucher/{id}', [AdminPromotionController::class, 'updateVoucher'])->name('promotions.voucher.update');
        Route::delete('/voucher/{id}', [AdminPromotionController::class, 'deleteVoucher'])->name('promotions.voucher.delete');
        Route::put('/voucher/{id}/toggle', [AdminPromotionController::class, 'toggleVoucher'])->name('promotions.voucher.toggle');

        // ⭐ Pre-order routes
        Route::post('/preorder', [AdminPromotionController::class, 'storePreorder'])->name('promotions.preorder.store');
        Route::put('/preorder/{id}', [AdminPromotionController::class, 'updatePreorder'])->name('promotions.preorder.update');
        Route::delete('/preorder/{id}', [AdminPromotionController::class, 'deletePreorder'])->name('promotions.preorder.delete');
        Route::put('/preorder/{id}/toggle', [AdminPromotionController::class, 'togglePreorder'])->name('promotions.preorder.toggle');
        
        // Discount routes
        Route::post('/discount', [AdminPromotionController::class, 'storeDiscount'])->name('admin.promotions.discount.store');
        Route::put('/discount/{id}', [AdminPromotionController::class, 'updateDiscount'])->name('admin.promotions.discount.update');
        Route::delete('/discount/{id}', [AdminPromotionController::class, 'deleteDiscount'])->name('admin.promotions.discount.delete');
        Route::put('/discount/{id}/toggle', [AdminPromotionController::class, 'toggleDiscount'])->name('admin.promotions.discount.toggle');


        // ⭐ THÊM: Route increment buyers cho pre-order
        Route::post('/preorder/{id}/increment-buyers', [AdminPromotionController::class, 'incrementPreorderBuyers'])
            ->name('promotions.preorder.increment');

        // ⭐ API Check Promotion (dùng AdminPromotionController)
        Route::post('/check', [AdminPromotionController::class, 'checkPromotion'])->name('promotions.check');
        Route::get('/preorder-info', [AdminPromotionController::class, 'getPreorderInfo'])->name('promotions.preorder.info');
    });

    // Reports
    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports');
    })->name('reports.index');
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.updateGeneral');
    Route::put('/settings/password', [SettingController::class, 'changePassword'])->name('settings.changePassword');

    // User Management
    Route::get('/settings/users', [SettingController::class, 'getUsers'])->name('settings.users');
    Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.storeUser');
    Route::put('/settings/users/{id}', [SettingController::class, 'updateUser'])->name('settings.updateUser');
    Route::delete('/settings/users/{id}', [SettingController::class, 'destroyUser'])->name('settings.destroyUser');
    Route::patch('/settings/users/{id}/toggle', [SettingController::class, 'toggleUserStatus'])->name('settings.toggleUser');
});

Route::get('/media/{path}', function ($path) {
    $fullPath = base_path('media/' . $path);
    if (!File::exists($fullPath)) {
        abort(404);
    }
    $mime = File::mimeType($fullPath);
    return Response::file($fullPath, ['Content-Type' => $mime]);
})->where('path', '.*');

// Review
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('auth');

require __DIR__.'/auth.php';