<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomizeController as AdminCustomizeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Web\CategoryController as WebCategoryController;
use App\Http\Controllers\Web\ProductController as WebProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

// ==================== ROUTE ĐỂ PHỤC VỤ ẢNH (THÊM MỚI) ====================
Route::get('/image/{filename}', function ($filename) {
    $path = base_path('image/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    return response($file, 200)->header('Content-Type', $type);
})->where('filename', '.*');

// ==================== WEB ROUTES (Frontend - Cho người dùng) ====================

Route::get('/', function () {
    return Inertia::render('Web/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/tim-kiem', function (Request $request) {
    return Inertia::render('Web/Category', ['search' => $request->get('q')]);
})->name('search');

// Product routes - now using controllers
Route::get('/san-pham/{id}', [WebProductController::class, 'show'])->name('product.detail');
Route::get('/danh-muc/{slug}', [WebCategoryController::class, 'show'])->name('category');

// Other frontend routes
Route::get('/mua-si', function () {
    return Inertia::render('Web/Wholesale');
})->name('wholesale');

Route::get('/khuyen-mai', function () {
    return Inertia::render('Web/Promotion');
})->name('promotion');

Route::get('/tuy-chinh', function () {
    return Inertia::render('Web/Customize');
})->name('customize');

Route::get('/gio-hang', function () {
    return Inertia::render('Web/Cart');
})->name('cart');

Route::get('/thanh-toan', function () {
    return Inertia::render('Web/Checkout');
})->name('checkout');

// ==================== AUTHENTICATED WEB ROUTES ====================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== ADMIN ROUTES (Backend - Cho quản trị viên) ====================

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
        Route::post('/export', [AdminOrderController::class, 'export'])->name('orders.export');
    });
    
    // Products Management
    Route::prefix('products')->group(function () {
        Route::get('/{type?}', [AdminProductController::class, 'index'])
            ->where('type', 'normal|preorder')
            ->name('products.index');
        Route::post('/', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/{id}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    });
    
    // Categories Management
    Route::resource('categories', CategoryController::class)->except(['show']);
    
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
    
    // Customize Management (Admin)
    Route::prefix('customize')->group(function () {
        Route::get('/', [AdminCustomizeController::class, 'index'])->name('customize.index');
        Route::put('/{id}/status', [AdminCustomizeController::class, 'updateStatus'])->name('customize.update-status');
        Route::put('/{id}/approve', [AdminCustomizeController::class, 'approve'])->name('customize.approve');
        Route::post('/send-quote', [AdminCustomizeController::class, 'sendQuote'])->name('customize.send-quote');
    });
    
    // Promotions
    Route::get('/promotions', function () {
        return Inertia::render('Admin/Promotions');
    })->name('promotions.index');
    
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

require __DIR__.'/auth.php';