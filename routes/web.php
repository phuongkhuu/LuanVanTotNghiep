<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Client pages
Route::get('/trang-chu', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/mua-si', function () {
    return Inertia::render('Wholesale');
})->name('wholesale');

Route::get('/khuyen-mai', function () {
    return Inertia::render('Promotion');
})->name('promotion');

Route::get('/tuy-chinh', function () {
    return Inertia::render('Customize');
})->name('customize');

Route::get('/gio-hang', function () {
    return Inertia::render('Cart');
})->name('cart');

Route::get('/thanh-toan', function () {
    return Inertia::render('Checkout');
})->name('checkout');

Route::get('/san-pham/{id}', function ($id) {
    return Inertia::render('ProductDetail', ['id' => $id]);
})->name('product.detail');

Route::get('/danh-muc/{slug}', function ($slug) {
    return Inertia::render('Category', ['slug' => $slug]);
})->name('category');

// Admin routes (protected by auth; you can add admin middleware later)
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    Route::get('/orders', function () {
        return Inertia::render('Admin/Orders', ['type' => request('type', 'retail')]);
    })->name('admin.orders');

    Route::get('/products', function () {
        return Inertia::render('Admin/Products', ['type' => request('type', 'retail')]);
    })->name('admin.products');

    Route::get('/customers', function () {
        return Inertia::render('Admin/Customers', ['type' => request('type', 'retail')]);
    })->name('admin.customers');

    Route::get('/customize', function () {
        return Inertia::render('Admin/Customize');
    })->name('admin.customize');

    Route::get('/promotions', function () {
        return Inertia::render('Admin/Promotions');
    })->name('admin.promotions');

    Route::get('/reports', function () {
        return Inertia::render('Admin/Reports');
    })->name('admin.reports');

    Route::get('/settings', function () {
        return Inertia::render('Admin/Settings');
    })->name('admin.settings');
});

require __DIR__.'/auth.php';