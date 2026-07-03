# Thêm banner tin tức

**Created:** 06/23/2026 22:46  
**Updated:** 06/25/2026 18:06  
**Exported:** 06/26/2026 02:37  
**Link:** [https://chat.deepseek.com/a/chat/s/0f1da057-0e76-43d6-b5e6-4adf84ac2988](https://chat.deepseek.com/a/chat/s/0f1da057-0e76-43d6-b5e6-4adf84ac2988)  

## Prompt:
06/23/2026 22:59

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// Sidebar state
const sidebarCollapsed = ref(false);

// Submenu states
const orderSubmenuOpen = ref(false);
const productSubmenuOpen = ref(false);
const customerSubmenuOpen = ref(false);
const attributeSubmenuOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const currentRoute = computed(() => page.component);

const isActive = (routeNames) => {
    if (typeof routeNames === 'string') {
        return route().current(routeNames);
    }
    if (Array.isArray(routeNames)) {
        return routeNames.some(name => route().current(name));
    }
    return false;
};

const updateSubmenuState = () => {
    const route = currentRoute.value;
    orderSubmenuOpen.value = (route === 'Admin/Orders' || route?.includes('Orders'));
    productSubmenuOpen.value = (route === 'Admin/Products' || route?.includes('Products'));
    customerSubmenuOpen.value = (route === 'Admin/Customers' || route?.includes('Customers'));
    attributeSubmenuOpen.value = (route === 'Admin/Categories' || route === 'Admin/Colors' || route === 'Admin/Brands' || 
        route?.includes('Categories') || route?.includes('Colors') || route?.includes('Brands'));
};

const toggleOrderSubmenu = () => { orderSubmenuOpen.value = !orderSubmenuOpen.value; };
const toggleProductSubmenu = () => { productSubmenuOpen.value = !productSubmenuOpen.value; };
const toggleCustomerSubmenu = () => { customerSubmenuOpen.value = !customerSubmenuOpen.value; };
const toggleAttributeSubmenu = () => { attributeSubmenuOpen.value = !attributeSubmenuOpen.value; };

watch(currentRoute, () => {
    updateSubmenuState();
}, { immediate: true });

onMounted(() => {
    updateSubmenuState();
});
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <aside 
            class="w-72 bg-white border-r border-border-light fixed h-full z-20 transition-all duration-300 flex flex-col"
            :class="{ 'hidden lg:block': sidebarCollapsed, 'block': !sidebarCollapsed }"
        >
            <div class="p-6 border-b border-border-light flex-shrink-0">
                <h1 class="text-2xl font-bold text-primary">BigBag<span class="text-on-surface">.vn</span></h1>
                <p class="text-xs text-on-surface-variant mt-1">Admin Portal</p>
            </div>
            
            <div class="flex-1 overflow-y-auto">
                <nav class="p-4 space-y-1">
                    <!-- Dashboard -->
                    <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.dashboard') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span class="flex-1 text-sm font-medium">Dashboard</span>
                    </Link>
                    
                    <!-- Đơn hàng -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleOrderSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.orders.index', 'admin.orders.show']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">receipt_long</span>
                            <span class="flex-1 text-sm font-medium">Đơn hàng</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': orderSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="orderSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.orders.index', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'retail' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🛒 Đơn bán lẻ</Link>
                            <Link :href="route('admin.orders.index', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'wholesale' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🏭 Đơn bán sỉ</Link>
                            <Link :href="route('admin.orders.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.orders.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">⏳ Đơn Pre-order</Link>
                        </div>
                    </div>

                    <!-- Sản phẩm -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleProductSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.products.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">inventory_2</span>
                            <span class="flex-1 text-sm font-medium">Sản phẩm</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': productSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="productSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.products.index', { type: 'normal' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.products.index') && route().params?.type === 'normal' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🎒 Sản phẩm thường</Link>
                            <Link :href="route('admin.products.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.products.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🔮 Sản phẩm Pre-order</Link>
                        </div>
                    </div>

                    <!-- Thuộc tính sản phẩm -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleAttributeSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.categories.index', 'admin.colors.index', 'admin.brands.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">settings_input_component</span>
                            <span class="flex-1 text-sm font-medium">Thuộc tính</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': attributeSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="attributeSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.categories.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.categories.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">📁 Danh mục</Link>
                            <Link :href="route('admin.colors.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.colors.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🎨 Màu sắc</Link>
                            <Link :href="route('admin.brands.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.brands.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🏷️ Thương hiệu</Link>
                        </div>
                    </div>

                    <!-- Khách hàng -->
                    <div class="space-y-1">
                        <div 
                            @click="toggleCustomerSubmenu" 
                            class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
                            :class="isActive(['admin.customers.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
                        >
                            <span class="material-symbols-outlined">group</span>
                            <span class="flex-1 text-sm font-medium">Khách hàng</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': customerSubmenuOpen }">keyboard_arrow_down</span>
                        </div>
                        <div v-show="customerSubmenuOpen" class="ml-8 space-y-1">
                            <Link :href="route('admin.customers.index', { type: 'retail' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'retail' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">👤 Khách lẻ</Link>
                            <Link :href="route('admin.customers.index', { type: 'wholesale' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'wholesale' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">🏢 Khách doanh nghiệp</Link>
                            <Link :href="route('admin.customers.index', { type: 'preorder' })" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="route().current('admin.customers.index') && route().params?.type === 'preorder' ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">⏳ Pre-order</Link>
                        </div>
                    </div>

                    <!-- Tùy chỉnh -->
                    <Link :href="route('admin.customize.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.customize.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">palette</span>
                        <span class="flex-1 text-sm font-medium">Tùy chỉnh</span>
                        <span class="text-xs bg-primary text-white px-2 py-0.5 rounded-full">3 mới</span>
                    </Link>

                    <!-- Khuyến mãi -->
                    <Link :href="route('admin.promotions.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.promotions.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">local_offer</span>
                        <span class="flex-1 text-sm font-medium">Khuyến mãi</span>
                    </Link>

                    <!-- Báo cáo -->
                    <Link :href="route('admin.reports.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.reports.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">bar_chart</span>
                        <span class="flex-1 text-sm font-medium">Báo cáo</span>
                    </Link>

                    <!-- Cài đặt -->
                    <Link :href="route('admin.settings.index')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all" :class="isActive('admin.settings.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="flex-1 text-sm font-medium">Cài đặt</span>
                    </Link>

                    <!-- Về trang chủ -->
                    <Link :href="route('home')" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-on-surface-variant hover:bg-hover-bg hover:text-primary">
                        <span class="material-symbols-outlined">home</span>
                        <span class="flex-1 text-sm font-medium">Về trang chủ</span>
                    </Link>
                </nav>
            </div>
            
            <!-- User info -->
            <div class="flex-shrink-0 p-4 border-t border-border-light bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                        {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-on-surface truncate">{{ user?.name || 'Admin User' }}</p>
                        <p class="text-xs text-outline truncate">{{ user?.email || 'admin@bigbag.vn' }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary transition-colors flex-shrink-0">
                        logout
                    </Link>
                </div>
            </div>
        </aside>

        <div v-if="!sidebarCollapsed" @click="sidebarCollapsed = true" class="fixed inset-0 bg-black/50 z-10 lg:hidden"></div>

        <main class="flex-1 lg:ml-72 transition-all duration-300">
            <header class="sticky top-0 z-10 glass-header border-b border-border-light px-4 md:px-8 py-4 flex justify-between items-center bg-white/95 backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="text-on-surface-variant hover:text-primary lg:hidden">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="relative hidden md:block">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg">search</span>
                        <input type="text" placeholder="Tìm kiếm..." class="pl-10 pr-4 py-2 bg-white border border-border-light rounded-full w-80 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 text-sm">
                    </div>
                </div>
                <div class="flex items-center gap-4">               
                    <div class="flex items-center gap-2 pl-2 border-l border-border-light">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                            {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                        </div>
                        <span class="text-sm font-medium text-on-surface hidden md:block">{{ user?.name || 'Admin' }}</span>
                    </div>
                </div>
            </header>
            <slot />
        </main>
    </div>
</template>

<style scoped>
.sidebar-item-active {
    background-color: #fff5f2;
    color: #ff6b00;
}
.rotate-180 {
    transform: rotate(180deg);
}
.glass-header {
    backdrop-filter: blur(12px);
    background-color: rgba(251, 249, 245, 0.95);
}
aside .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}
aside .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}
aside .overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
aside .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #ff6b00;
}
</style>
Thêm tab quản lý banner và tin tức.
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
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\CategoryController as WebCategoryController;
use App\Http\Controllers\ProductController as WebProductController;
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



// Thanh toán
Route::get('/thanh-toan', [PaymentController::class, 'index'])->name('checkout');
Route::post('/thanh-toan', [PaymentController::class, 'store'])->name('checkout.store');
Route::get('/thanh-toan/thanh-cong', [PaymentController::class, 'success'])->name('checkout.success');

// ==================== AUTHENTICATED WEB ROUTES ====================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== ADMIN ROUTES (Backend - Cho quản trị viên) ====================

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    //guest: chưa đăng nhập, auth: đã đăng nhập, admin: đã đăng nhập và có role là admin
    //Prefix là cái phía trước của URL, vd: admin/dashboard, admin/orders, admin/products. 
    // Name phía trước name của route.
    //localhost:8000/dashboard, vì có prefix nên sẽ phải nhập localhost:8000/admin/dashboard mới vào được
    //Trước name của các route bên trong sẽ phải có admin.'...', VD: route('admin.dashboard')
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
        Route::put('/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
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
Thêm route


## Response:
06/23/2026 22:59

> **Thinking:**
> Chúng ta cần thêm tab quản lý banner và tin tức vào sidebar và routes tương ứng.
> 
> Đầu tiên, cập nhật sidebar: thêm các mục mới (Banner, Tin tức) vào menu. Có thể nhóm vào một submenu hoặc để riêng. Theo yêu cầu "Thêm tab quản lý banner và tin tức" – thường là các mục menu riêng biệt hoặc dưới một nhóm. Tôi sẽ thêm riêng biệt, có thể nằm dưới "Cài đặt" hoặc tạo một nhóm mới "Nội dung" chẳng hạn. Nhưng yêu cầu chỉ đơn giản là thêm tab. Tôi sẽ thêm vào menu chính, không cần submenu (nhưng có thể có để mở rộng sau). Tôi sẽ thêm hai mục riêng: "Banner" và "Tin tức" với icon tương ứng.
> 
> Trong template sidebar, sau phần "Khuyến mãi" hoặc trước "Báo cáo" có thể thêm.
> 
> Đồng thời cần thêm routes cho quản lý banner và tin tức (CRUD). Trong route web.php, thêm vào group admin.
> 
> Tôi sẽ tạo các controller cho Banner và News (hoặc dùng resource). Cũng cần thêm logic để hiển thị dữ liệu.
> 
> Vì chưa có model, controller cụ thể, tôi sẽ tạo giả định với resource controllers. Tuy nhiên, vì không có yêu cầu chi tiết, tôi sẽ thêm route resource và các chỉ dẫn để sau này phát triển.
> 
> Trong sidebar, tôi sẽ thêm hai mục:
> 
> - Banner: route('admin.banners.index')
> - Tin tức: route('admin.news.index')
> 
> Có thể nhóm thành một submenu "Quản lý nội dung" để gọn, nhưng yêu cầu là "thêm tab quản lý banner và tin tức" – có thể hiểu là thêm hai tab riêng lẻ. Tôi sẽ thêm như vậy.
> 
> Tôi sẽ thêm submenu mới hoặc thêm trực tiếp vào menu chính. Để phù hợp với cấu trúc hiện tại, tôi sẽ tạo một nhóm "Nội dung" với submenu chứa banner và tin tức, hoặc thêm trực tiếp. Tôi sẽ thêm trực tiếp hai mục sau "Khuyến mãi" và trước "Báo cáo".
> 
> Cần cập nhật các state submenu nếu nhóm.
> 
> Tôi sẽ thêm:
> 
> - Biến `contentSubmenuOpen` và toggle, và submenu items: Banner, Tin tức.
> 
> Tuy nhiên, yêu cầu chỉ là thêm tab, không nhất thiết phải nhóm. Tôi có thể thêm hai mục độc lập, không có submenu. Tốt hơn là tạo submenu "Nội dung" để có thể mở rộng.
> 
> Vậy tôi sẽ thêm một submenu mới: "Nội dung" với các item "Banner" và "Tin tức".
> 
> Trong script, thêm `contentSubmenuOpen = ref(false)`, toggle function, updateSubmenuState: kiểm tra route có chứa 'Banners' hoặc 'News' không.
> 
> Cập nhật template: thêm phần mới.
> 
> Sau đó thêm routes: tạo resource cho Banner và News trong admin group.
> 
> Tôi sẽ viết code thêm vào.
> 
> Lưu ý: các controller có thể chưa tồn tại, nhưng sẽ tạo sau. Tôi sẽ thêm route resource.
> 
> Cũng có thể thêm chức năng hiển thị trang index của banner và news (Inertia render). Giả định có sẵn.
> 
> Đồng thời cần thêm trong route: use các controller mới.
> 
> Vậy tôi sẽ chỉnh sửa file Sidebar và routes.
> 
> Bắt đầu với Sidebar: thêm phần mới sau mục Khuyến mãi hoặc trước Báo cáo. Tôi sẽ thêm trước Báo cáo.
> 
> Trong sidebar template, tôi sẽ thêm:
> 
> ```html
> <!-- Nội dung -->
> <div class="space-y-1">
>     <div @click="toggleContentSubmenu" class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
>         :class="isActive(['admin.banners.index', 'admin.news.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
>         <span class="material-symbols-outlined">description</span>
>         <span class="flex-1 text-sm font-medium">Nội dung</span>
>         <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': contentSubmenuOpen }">keyboard_arrow_down</span>
>     </div>
>     <div v-show="contentSubmenuOpen" class="ml-8 space-y-1">
>         <Link :href="route('admin.banners.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.banners.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
>             <span class="material-symbols-outlined">image</span> Banner
>         </Link>
>         <Link :href="route('admin.news.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.news.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
>             <span class="material-symbols-outlined">newspaper</span> Tin tức
>         </Link>
>     </div>
> </div>
> ```
> 
> Cần khai báo `contentSubmenuOpen` trong script và toggle function, cập nhật updateSubmenuState.
> 
> Bây giờ routes: thêm vào group admin:
> 
> ```php
> // Banner Management
> Route::resource('banners', BannerController::class)->except(['show']);
> // News Management
> Route::resource('news', NewsController::class)->except(['show']);
> ```
> 
> Cần import các controller: `use App\Http\Controllers\Admin\BannerController;` và `use App\Http\Controllers\Admin\NewsController;`.
> 
> Ngoài ra, cần thêm các model và migration sau, nhưng hiện tại chỉ cần thêm route.
> 
> Tôi sẽ thêm vào file routes/web.php.
> 
> Cần đảm bảo các route tên chính xác: admin.banners.index, v.v.
> 
> Đồng thời, tôi có thể thêm active route detection cho các route này trong isActive.
> 
> Trong script, cần cập nhật `updateSubmenuState` để mở submenu content khi ở route banners hoặc news.
> 
> ```js
> contentSubmenuOpen.value = (route === 'Admin/Banners' || route === 'Admin/News' || route?.includes('Banners') || route?.includes('News'));
> ```
> 
> Tôi sẽ thêm vào.
> 
> Vậy chỉnh sửa Sidebar: thêm biến contentSubmenuOpen và toggle, cập nhật isActive và updateSubmenuState.
> 
> Đồng thời thêm phần template.
> 
> Và thêm routes.
> 
> Đây là những thay đổi cần thiết. Tôi sẽ tạo câu trả lời với code cụ thể cho cả hai file: Sidebar component và web.php routes.
> 
> Tôi sẽ trả về nội dung cần thêm và chỉnh sửa.

Chúng ta sẽ thêm hai tab quản lý **Banner** và **Tin tức** vào sidebar admin, đồng thời tạo các route cần thiết.

## 1. Cập nhật Sidebar (Vue component)

### Thêm biến trạng thái và hàm xử lý cho submenu "Nội dung"

Trong phần `<script setup>`, thêm:

```javascript
const contentSubmenuOpen = ref(false);
const toggleContentSubmenu = () => { contentSubmenuOpen.value = !contentSubmenuOpen.value; };
```

Cập nhật hàm `updateSubmenuState`:

```javascript
const updateSubmenuState = () => {
    const route = currentRoute.value;
    orderSubmenuOpen.value = (route === 'Admin/Orders' || route?.includes('Orders'));
    productSubmenuOpen.value = (route === 'Admin/Products' || route?.includes('Products'));
    customerSubmenuOpen.value = (route === 'Admin/Customers' || route?.includes('Customers'));
    attributeSubmenuOpen.value = (route === 'Admin/Categories' || route === 'Admin/Colors' || route === 'Admin/Brands' || 
        route?.includes('Categories') || route?.includes('Colors') || route?.includes('Brands'));
    // Thêm dòng cho content
    contentSubmenuOpen.value = (route === 'Admin/Banners' || route === 'Admin/News' || 
        route?.includes('Banners') || route?.includes('News'));
};
```

### Thêm phần menu "Nội dung" vào `<template>`

Chèn đoạn sau vào sau phần "Khuyến mãi" (hoặc trước "Báo cáo"):

```html
<!-- Nội dung (Banner & Tin tức) -->
<div class="space-y-1">
    <div 
        @click="toggleContentSubmenu" 
        class="flex items-center gap-3 px-4 py-3 rounded-lg cursor-pointer transition-all"
        :class="isActive(['admin.banners.index', 'admin.news.index']) ? 'text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'"
    >
        <span class="material-symbols-outlined">description</span>
        <span class="flex-1 text-sm font-medium">Nội dung</span>
        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': contentSubmenuOpen }">keyboard_arrow_down</span>
    </div>
    <div v-show="contentSubmenuOpen" class="ml-8 space-y-1">
        <Link :href="route('admin.banners.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.banners.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
            <span class="material-symbols-outlined">image</span> Banner
        </Link>
        <Link :href="route('admin.news.index')" class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-all" :class="isActive('admin.news.index') ? 'sidebar-item-active text-primary' : 'text-on-surface-variant hover:bg-hover-bg hover:text-primary'">
            <span class="material-symbols-outlined">newspaper</span> Tin tức
        </Link>
    </div>
</div>
```

---

## 2. Thêm route cho Banner và Tin tức (web.php)

Trong file `routes/web.php`, bên trong group admin, thêm các dòng sau (sau phần khuyến mãi hoặc trước báo cáo):

```php
// Banner Management
Route::resource('banners', BannerController::class)->except(['show']);

// News Management
Route::resource('news', NewsController::class)->except(['show']);
```

Đồng thời, import các controller ở đầu file:

```php
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsController;
```

---

## Kết quả

Sau khi áp dụng các thay đổi, sidebar sẽ hiển thị nhóm **Nội dung** với hai mục con **Banner** và **Tin tức**, các route tương ứng sẽ được định nghĩa sẵn để phát triển chức năng quản lý sau.


## Prompt:
06/23/2026 23:07

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
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\CategoryController as WebCategoryController;
use App\Http\Controllers\ProductController as WebProductController;
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



// Thanh toán
Route::get('/thanh-toan', [PaymentController::class, 'index'])->name('checkout');
Route::post('/thanh-toan', [PaymentController::class, 'store'])->name('checkout.store');
Route::get('/thanh-toan/thanh-cong', [PaymentController::class, 'success'])->name('checkout.success');

// ==================== AUTHENTICATED WEB ROUTES ====================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==================== ADMIN ROUTES (Backend - Cho quản trị viên) ====================

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    //guest: chưa đăng nhập, auth: đã đăng nhập, admin: đã đăng nhập và có role là admin
    //Prefix là cái phía trước của URL, vd: admin/dashboard, admin/orders, admin/products. 
    // Name phía trước name của route.
    //localhost:8000/dashboard, vì có prefix nên sẽ phải nhập localhost:8000/admin/dashboard mới vào được
    //Trước name của các route bên trong sẽ phải có admin.'...', VD: route('admin.dashboard')
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
        Route::put('/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
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

bổ sung route


## Response:
06/23/2026 23:07

> **Thinking:**
> Chúng ta đã có các controller import và đã thêm route cho banner và news. Cần bổ sung thêm các route chi tiết hơn nếu cần, nhưng hiện tại trong code đã có `Route::resource('banners', BannerController::class)->except(['show']);` và `Route::resource('news', NewsController::class)->except(['show']);`. 
> 
> Tuy nhiên, cần kiểm tra xem các controller này đã tồn tại chưa, và có thể cần thêm route cho các action đặc biệt như thay đổi trạng thái, upload ảnh, v.v. Vì yêu cầu chỉ là "bổ sung route", tôi sẽ đề xuất thêm các route cần thiết cho banner và news, bao gồm cả các route cho API dạng data, hoặc route để cập nhật trạng thái.
> 
> Đề xuất bổ sung:
> 
> - Cho Banner: có thể cần route để cập nhật trạng thái (active/inactive), sắp xếp thứ tự.
> - Cho News: có thể cần route để cập nhật trạng thái (published/draft), hoặc route để upload thumbnail.
> 
> Vì chưa biết cấu trúc cụ thể, tôi sẽ thêm các route mẫu phổ biến:
> 
> ```php
> // Banner Management
> Route::prefix('banners')->group(function () {
>     Route::get('/', [BannerController::class, 'index'])->name('banners.index');
>     Route::get('/data', [BannerController::class, 'getBanners'])->name('banners.data'); // lấy dữ liệu cho datatable
>     Route::post('/', [BannerController::class, 'store'])->name('banners.store');
>     Route::put('/{id}', [BannerController::class, 'update'])->name('banners.update');
>     Route::delete('/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
>     Route::patch('/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.update-status');
>     Route::patch('/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.update-order');
> });
> 
> // News Management
> Route::prefix('news')->group(function () {
>     Route::get('/', [NewsController::class, 'index'])->name('news.index');
>     Route::get('/data', [NewsController::class, 'getNews'])->name('news.data');
>     Route::post('/', [NewsController::class, 'store'])->name('news.store');
>     Route::put('/{id}', [NewsController::class, 'update'])->name('news.update');
>     Route::delete('/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
>     Route::patch('/{id}/status', [NewsController::class, 'updateStatus'])->name('news.update-status');
> });
> ```
> 
> Tuy nhiên, vì đã dùng `Route::resource` trước đó, nhưng tôi thấy trong code các resource khác thường dùng prefix riêng (ví dụ orders, products...). Nên tôi sẽ bỏ resource và định nghĩa tường minh để đồng nhất với các route khác.
> 
> Tôi sẽ đưa ra giải pháp thay thế các dòng resource bằng các route cụ thể như trên, và import các controller đã có.
> 
> Vì vậy, câu trả lời sẽ là:
> - Sửa lại phần route banner và news thành các route chi tiết, có thêm các route cho data, status, order.
> - Đảm bảo import controller.
> 
> Tôi sẽ trình bày trong câu trả lời.

Chúng ta sẽ bổ sung route chi tiết cho **Banner** và **Tin tức**, thay vì dùng `Route::resource` đơn giản, để đồng bộ với các module khác (như orders, products, categories...). Các route này sẽ bao gồm:

- Lấy danh sách (có phân trang, lọc) – dùng cho DataTable.
- Tạo mới, cập nhật, xóa.
- Cập nhật trạng thái (active/inactive, published/draft).
- Sắp xếp thứ tự (cho banner).

---

## Cập nhật trong `routes/web.php`

**Thay thế** hai dòng `Route::resource` cũ:

```php
Route::resource('banners', BannerController::class)->except(['show']);
Route::resource('news', NewsController::class)->except(['show']);
```

**Bằng** các route chi tiết sau (đặt trong group admin, sau phần `customize` và trước `promotions`):

```php
// Banner Management
Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index'])->name('banners.index');
    Route::get('/data', [BannerController::class, 'getBanners'])->name('banners.data');
    Route::post('/', [BannerController::class, 'store'])->name('banners.store');
    Route::put('/{id}', [BannerController::class, 'update'])->name('banners.update');
    Route::delete('/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::patch('/{id}/status', [BannerController::class, 'updateStatus'])->name('banners.update-status');
    Route::patch('/{id}/order', [BannerController::class, 'updateOrder'])->name('banners.update-order');
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
```

---

## Import controller (đã có)

Hai dòng `use` ở đầu file đã có:

```php
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsController;
```

---

## Danh sách route đầy đủ sau khi bổ sung

| Method | URI | Name | Mô tả |
|--------|-----|------|-------|
| GET | `/admin/banners` | `admin.banners.index` | Hiển thị trang danh sách banner |
| GET | `/admin/banners/data` | `admin.banners.data` | Trả về JSON danh sách banner (cho DataTable) |
| POST | `/admin/banners` | `admin.banners.store` | Tạo banner mới |
| PUT | `/admin/banners/{id}` | `admin.banners.update` | Cập nhật banner |
| DELETE | `/admin/banners/{id}` | `admin.banners.destroy` | Xóa banner |
| PATCH | `/admin/banners/{id}/status` | `admin.banners.update-status` | Cập nhật trạng thái (bật/tắt) |
| PATCH | `/admin/banners/{id}/order` | `admin.banners.update-order` | Cập nhật thứ tự hiển thị |
| GET | `/admin/news` | `admin.news.index` | Hiển thị trang danh sách tin tức |
| GET | `/admin/news/data` | `admin.news.data` | Trả về JSON danh sách tin tức |
| POST | `/admin/news` | `admin.news.store` | Tạo tin tức mới |
| PUT | `/admin/news/{id}` | `admin.news.update` | Cập nhật tin tức |
| DELETE | `/admin/news/{id}` | `admin.news.destroy` | Xóa tin tức |
| PATCH | `/admin/news/{id}/status` | `admin.news.update-status` | Cập nhật trạng thái (xuất bản/nháp) |

---

## Lưu ý

- Các controller `BannerController` và `NewsController` cần được tạo với đầy đủ các phương thức tương ứng.
- Đảm bảo middleware `admin` vẫn áp dụng cho toàn bộ group, nên các route này chỉ dành cho quản trị viên.
- Các route `data` thường được sử dụng bởi các component DataTable ở frontend để tải dữ liệu bất đồng bộ.

Sau khi thêm các route này, bạn có thể sử dụng chúng trong sidebar với các tên route như `admin.banners.index` và `admin.news.index` đã có trong cập nhật sidebar phía trên.


## Prompt:
06/23/2026 23:10

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_variant_id', 'author_id', 'title', 'slug', 'thumbnail', 'content'
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['campaign_id', 'image', 'link', 'status'];

    protected $casts = [
        'status' => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}

Dựa vào model sau, hãy tạo 2 trang vue cho banner và news. Mẫu giao diện như sau.
<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

// Sắp xếp brands theo ID giảm dần (mới nhất lên đầu)
const sortedBrands = computed(() => {
    return [...brands.value].sort((a, b) => b.id - a.id)
})

const brands = ref(props.brands)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('') 

// Chọn phương thức nhập logo: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Xem trước logo
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.logo) return form.value.logo
    return null
})

// Hàm tạo slug từ name
const generateSlug = (name) => {
    if (!name) return ''
    return name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
    if (isLoading.value) return
    
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands/data')
        if (response.data && Array.isArray(response.data)) {
            brands.value = response.data
        } else {
            brands.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
        brands.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', logo: '', description: '' }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = '' 
    showModal.value = true
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = '' // Reset lỗi file trước khi kiểm tra
    if (!file) return
    
    // Kiểm tra định dạng ảnh
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg)'
        return
    }
    // Kiểm tra kích thước
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        return
    }
    // Hợp lệ
    selectedFile.value = file
    // Tạo preview
    const reader = new FileReader()
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result }
    reader.readAsDataURL(file)
    // Xóa logo cũ nếu có
    form.value.logo = ''
}

// Reset chọn file
const clearFile = () => {
    selectedFile.value = null
    imagePreviewUrl.value = ''
    fileError.value = '' 
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput')
        if (fileInput) fileInput.value = ''
    }
}

const saveBrand = async () => {
    // Kiểm tra tên
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên thương hiệu'
        return
    }

    // Kiểm tra lỗi file trước khi gửi
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    // Nếu đang ở chế độ file mà chưa chọn file thì không sao (logo không bắt buộc)
    // Nhưng nếu đã chọn file thì phải hợp lệ (đã được kiểm tra)

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        
        if (isEdit.value) {
            // Cập nhật
            if (selectedFile.value) {
                // Có file upload -> dùng FormData
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                
                response = await axios.post(`/admin/brands/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                // Không có file -> dùng JSON
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            }
            
            if (response.data && response.data.success) {
                // Cập nhật brand trong danh sách
                const index = brands.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    brands.value[index] = response.data.data
                }
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            // Thêm mới
            if (selectedFile.value) {
                // Có file upload -> dùng FormData
                const formData = new FormData()
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                formData.append('slug', generateSlug(form.value.name))
                
                response = await axios.post('/admin/brands', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                // Không có file -> dùng JSON
                const dataToSave = {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                }
                response = await axios.post('/admin/brands', dataToSave)
            }
            
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
        
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
            if (index !== -1) {
                brands.value.splice(index, 1)
            }
            selectedBrand.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
        
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <p class="text-gray-500 mt-1">Thêm, sửa hoặc xóa các thương hiệu</p>
            </div>

            <div class="mb-6">
                <button 
                    @click="openCreateModal" 
                    class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition"
                    :disabled="isSaving"
                >
                    + Thêm thương hiệu mới
                </button>
            </div>

            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(brand, index) in sortedBrands" 
                            :key="brand.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ brand.slug }}</td>
                            <td class="p-4 text-gray-500">
                                <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto object-contain" alt="logo">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(brand)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                        :disabled="isSaving"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(brand)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                        :disabled="isSaving"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedBrands.length === 0 && !isLoading">
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                Chưa có thương hiệu nào
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới' }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="VD: BigBag, Solo, KingBag"
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        
                        <!-- Tab chọn phương thức nhập -->
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button 
                                type="button" 
                                @click="imageInputMode = 'url'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                🔗 Nhập URL
                            </button>
                            <button 
                                type="button" 
                                @click="imageInputMode = 'file'" 
                                :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']"
                            >
                                📁 Tải ảnh lên
                            </button>
                        </div>
                        
                        <!-- Nhập URL -->
                        <div v-if="imageInputMode === 'url'">
                            <input 
                                v-model="form.logo" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                                placeholder="https://example.com/logo.png"
                                :disabled="isSaving"
                            >
                            <p class="text-xs text-gray-400 mt-1">Nhập đường dẫn ảnh logo</p>
                        </div>
                        
                        <!-- Upload file -->
                        <div v-else>
                            <input 
                                id="fileInput" 
                                type="file" 
                                accept="image/*" 
                                @change="handleFileChange" 
                                class="w-full"
                                :disabled="isSaving"
                            >
                            <!-- Hiển thị lỗi file -->
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button 
                                v-if="selectedFile" 
                                @click="clearFile" 
                                class="text-red-500 text-xs mt-1 hover:underline"
                                type="button"
                            >
                                Xóa file đã chọn
                            </button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG. Kích thước tối đa 2MB</p>
                        </div>
                        
                        <!-- Xem trước ảnh -->
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-24 h-24 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img 
                                    :src="imagePreview" 
                                    class="max-w-full max-h-full object-contain" 
                                    @error="imagePreviewUrl = ''; form.logo = ''"
                                    alt="Logo preview"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none resize-none" 
                            placeholder="Mô tả về thương hiệu..."
                            :disabled="isSaving"
                        ></textarea>
                    </div>
                    
                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveBrand" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2"
                        :disabled="isSaving || !!fileError"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa thương hiệu <strong>{{ selectedBrand?.name }}</strong>?</p>
                
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteBrand" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                        :disabled="isSaving"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] }
});

const search = ref('');

// Modal state
const showModal = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);

// Chọn phương thức nhập ảnh: 'url' hoặc 'file'
const imageInputMode = ref('url');
const selectedFile = ref(null);
const imagePreviewUrl = ref('');

const form = ref({
    name: '',
    description: '',
    image: ''
});

const modalTitle = computed(() => editingId.value ? 'Sửa danh mục' : 'Thêm danh mục mới');

// Xử lý đường dẫn ảnh hiển thị (cho đường dẫn từ DB, đã được lưu dạng /image/...)
const getImageUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    // Nếu đã bắt đầu bằng /image thì giữ nguyên
    if (path.startsWith('/image')) return path;
    // Nếu bắt đầu bằng image/ (không có slash đầu) thì thêm slash
    if (path.startsWith('image/')) return '/' + path;
    // Fallback: coi như đường dẫn tương đối từ gốc
    return '/' + path;
};

// Xem trước ảnh trong modal (ưu tiên preview từ file mới chọn, nếu không thì dùng form.image)
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value;
    if (form.value.image) return getImageUrl(form.value.image);
    return null;
});

// Lọc danh mục
const filteredCategories = computed(() => {
    if (!props.categories.length) return [];
    if (!search.value) return props.categories;
    const kw = search.value.toLowerCase();
    return props.categories.filter(c => 
        c.name.toLowerCase().includes(kw) || 
        (c.description && c.description.toLowerCase().includes(kw))
    );
});

// Mở modal (thêm hoặc sửa)
const openModal = (category = null) => {
    editingId.value = category?.id || null;
    selectedFile.value = null;
    imagePreviewUrl.value = '';
    imageInputMode.value = 'url';
    
    if (category) {
        form.value = {
            name: category.name,
            description: category.description || '',
            image: category.image || ''
        };
    } else {
        form.value = { name: '', description: '', image: '' };
    }
    showModal.value = true;
};

const editCategory = (category) => openModal(category);

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('Vui lòng chọn file ảnh (jpg, png, ...)');
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert('Kích thước ảnh không quá 2MB');
        return;
    }
    selectedFile.value = file;
    // Tạo preview
    const reader = new FileReader();
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result; };
    reader.readAsDataURL(file);
    // Xóa image cũ nếu có
    form.value.image = '';
};

// Reset chọn file
const clearFile = () => {
    selectedFile.value = null;
    imagePreviewUrl.value = '';
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';
    }
};

// Lưu danh mục (hỗ trợ upload file)
const saveCategory = async () => {
    if (!form.value.name.trim()) {
        alert('Vui lòng nhập tên danh mục');
        return;
    }
    
    isSubmitting.value = true;
    
    let url, method, submitData, headers = {};
    
    if (editingId.value) {
        url = route('admin.categories.update', editingId.value);
        method = 'post';
        submitData = { _method: 'put', ...form.value };
    } else {
        url = route('admin.categories.store');
        method = 'post';
        submitData = { ...form.value };
    }
    
    // Nếu có file upload thì chuyển sang FormData
    if (selectedFile.value) {
        const formData = new FormData();
        formData.append('_method', editingId.value ? 'PUT' : 'POST');
        formData.append('name', form.value.name);
        formData.append('description', form.value.description);
        formData.append('image_file', selectedFile.value);
        // Không gửi trường image vì sẽ được xử lý từ file
        submitData = formData;
        headers = { 'Content-Type': 'multipart/form-data' };
    }
    
    try {
        await router[method](url, submitData, {
            preserveScroll: true,
            headers: headers,
            onSuccess: () => {
                alert(editingId.value ? 'Cập nhật thành công!' : 'Thêm danh mục thành công!');
                showModal.value = false;
                clearFile();
            },
            onError: (errors) => {
                console.error('Lỗi:', errors);
                const msg = errors.name?.[0] || errors.image_file?.[0] || 'Có lỗi xảy ra';
                alert(msg);
            }
        });
    } catch (error) {
        console.error(error);
        alert('Có lỗi xảy ra khi gửi dữ liệu');
    } finally {
        isSubmitting.value = false;
    }
};

// Xóa danh mục
const confirmDelete = (id, name) => {
    if (confirm(`Xóa danh mục "${name}"? Các sản phẩm liên quan sẽ mất danh mục.`)) {
        router.delete(route('admin.categories.destroy', id), { preserveScroll: true });
    }
};

const closeModal = () => {
    showModal.value = false;
    clearFile();
};
</script>

<template>
    <Head title="Quản lý danh mục" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý danh mục</h1>
                    <p class="text-gray-600 text-sm mt-1">Quản lý danh mục sản phẩm</p>
                </div>
                <button @click="openModal()" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm danh mục
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input v-model="search" type="text" placeholder="Tìm danh mục..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                </div>
            </div>

            <!-- Bảng danh mục -->
            <div class="bg-white rounded-xl border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">HÌNH ẢNH</th>
                                <th class="px-4 py-3 text-left">TÊN</th>
                                <th class="px-4 py-3 text-left">SLUG</th>
                                <th class="px-4 py-3 text-left">MÔ TẢ</th>
                                <th class="px-4 py-3 text-center">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cat in filteredCategories" :key="cat.id" class="border-t hover:bg-orange-50">
                                <td class="px-4 py-3">{{ cat.id }}</td>
                                <td class="px-4 py-3">
                                    <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                        <img 
                                            v-if="cat.image" 
                                            :src="getImageUrl(cat.image)" 
                                            @error="$event.target.src = 'https://placehold.co/400x400?text=No+Image'"
                                            class="w-full h-full object-cover"
                                        >
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                                            No img
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ cat.slug }}</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ cat.description || '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button @click="editCategory(cat)" class="text-green-600 hover:bg-green-100 px-2 py-1 rounded">Sửa</button>
                                    <button @click="confirmDelete(cat.id, cat.name)" class="text-red-600 hover:bg-red-100 px-2 py-1 rounded ml-1">Xóa</button>
                                </td>
                            </tr>
                            <tr v-if="!filteredCategories.length">
                                <td colspan="6" class="text-center py-8 text-gray-500">Không có danh mục nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">{{ modalTitle }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium block mb-1">Tên danh mục <span class="text-red-500">*</span></label>
                        <input v-model="form.name" class="w-full border rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20" placeholder="Ví dụ: Balo, Túi xách...">
                        <p class="text-xs text-gray-500 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Mô tả</label>
                        <textarea v-model="form.description" rows="2" class="w-full border rounded-lg px-3 py-2" placeholder="Mô tả ngắn (không bắt buộc)"></textarea>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-2">Hình ảnh</label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">🔗 Nhập URL</button>
                            <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">📁 Tải ảnh lên</button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input v-model="form.image" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="https://example.com/image.jpg">
                        </div>
                        <div v-else>
                            <input id="fileInput" type="file" accept="image/*" @change="handleFileChange" class="w-full">
                            <button v-if="selectedFile" @click="clearFile" class="text-red-500 text-xs mt-1">Xóa file đã chọn</button>
                        </div>
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600">Xem trước:</p>
                            <div class="w-32 h-32 border rounded overflow-hidden bg-gray-100">
                                <img :src="imagePreview" class="w-full h-full object-cover" @error="imagePreviewUrl = ''; form.image = ''">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button @click="saveCategory" :disabled="isSubmitting" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        {{ isSubmitting ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
