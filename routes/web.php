<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomizeController as AdminCustomizeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ==================== WEB ROUTES (Frontend - Cho người dùng) ====================

Route::get('/', function () {
    return Inertia::render('Web/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Product routes
Route::get('/san-pham/{id}', function ($id) {
    return Inertia::render('Web/ProductDetail', ['id' => $id]);
})->name('product.detail');

Route::get('/danh-muc/{slug}', function ($slug) {
    return Inertia::render('Web/Category', ['slug' => $slug]);
})->name('category');

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
        Route::get('/', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/retail', [AdminOrderController::class, 'retail'])->name('orders.retail');
        Route::get('/wholesale', [AdminOrderController::class, 'wholesale'])->name('orders.wholesale');
        Route::get('/preorder', [AdminOrderController::class, 'preorder'])->name('orders.preorder');
        Route::get('/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::post('/export', [AdminOrderController::class, 'export'])->name('orders.export');
    });
    
    // Products Management
    Route::prefix('products')->group(function () {
        Route::get('/', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/retail', [AdminProductController::class, 'retail'])->name('products.retail');
        Route::get('/wholesale', [AdminProductController::class, 'wholesale'])->name('products.wholesale');
        Route::get('/preorder', [AdminProductController::class, 'preorder'])->name('products.preorder');
        Route::post('/', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/{id}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');
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
    
    // Reports - SỬA LỖI: dùng closure thay vì controller
    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports');
    })->name('reports.index');
    
    // Settings
    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings');
    })->name('settings.index');
});

require __DIR__.'/auth.php';