<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

require __DIR__.'/auth.php';
