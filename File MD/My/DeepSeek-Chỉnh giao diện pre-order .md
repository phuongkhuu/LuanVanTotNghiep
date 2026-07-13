# Chỉnh giao diện pre-order

**Created:** 07/11/2026 14:46  
**Updated:** 07/11/2026 16:03  
**Exported:** 07/13/2026 03:06  
**Link:** [https://chat.deepseek.com/a/chat/s/541f8003-ecb7-4157-87fa-3e78e367e174](https://chat.deepseek.com/a/chat/s/541f8003-ecb7-4157-87fa-3e78e367e174)  

## User:
07/11/2026 14:51

Tôi đang làm website bán balo có các hình thức bán pre order và bán lẻ ở trang chi tiết sản phẩm.
1.Tôi muốn bạn chỉnh lại giao diện chi tiết sản phẩm khi mua sản phẩm pre và mua lẻ 
+ sản phẩm pre order thì chỉ có nút tùy chỉnh và mua ngay( không có thêm giỏ hàng).Và khi bấm mua ngay thì chuyển đến trang thanh toán luôn.
+ sản phẩm thường  thì chỉ đổi nút đặt hàng trước thành mua ngay vẫn giữ nút thêm vào giỏ hàng.
+cả sản phẩm thường và pre đều có nút tùy chỉnh.
(sp pre có 2 nút mua ngay và tùy chỉnh , sp thường có 3 nút thêm vào giỏ hàng, mua ngay và tùy chỉnh)
<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\CustomizeController as AdminCustomizeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
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
Route::get('/san-pham/{id}', [WebProductController::class, 'show'])->name('product.detail');
Route::get('/danh-muc/{slug}', [WebCategoryController::class, 'show'])->name('category');

// Other public frontend routes
Route::get('/mua-si', function () {
    return Inertia::render('Web/Wholesale');
})->name('wholesale');

Route::get('/khuyen-mai', function () {
    return Inertia::render('Web/Promotion');
})->name('promotion');

Route::get('/tuy-chinh', function () {
    return Inertia::render('Web/Customize');
})->name('customize');

// ==================== CART ROUTES (Yêu cầu đăng nhập) ====================

// Cart page
Route::get('/gio-hang', function () {
    return Inertia::render('Web/Cart');
})->name('cart')->middleware('auth');

// ⭐ QUAN TRỌNG: Cart API routes - Thêm middleware 'auth' và 'web'
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{variantId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
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
    Route::get('/don-hang', [OrderController::class, 'history'])->name('orders.history');
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

    // Promotion routes
    Route::prefix('promotions')->group(function () {
        // Campaign routes
        Route::get('/', [PromotionController::class, 'index'])->name('promotions.index');
        Route::post('/campaign', [PromotionController::class, 'storeCampaign'])->name('promotions.campaign.store');
        Route::put('/campaign/{id}', [PromotionController::class, 'updateCampaign'])->name('promotions.campaign.update');
        Route::delete('/campaign/{id}', [PromotionController::class, 'deleteCampaign'])->name('promotions.campaign.delete');
        Route::put('/campaign/{id}/status', [PromotionController::class, 'updateCampaignStatus'])->name('promotions.campaign.status');
        Route::get('/campaigns/list', [PromotionController::class, 'getCampaignsList'])->name('promotions.campaigns.list');
        
        Route::put('/preorder/{id}/toggle', [PromotionController::class, 'togglePreorder'])->name('promotions.preorder.toggle');

        // Voucher routes
        Route::post('/voucher', [PromotionController::class, 'storeVoucher'])->name('promotions.voucher.store');
        Route::put('/voucher/{id}', [PromotionController::class, 'updateVoucher'])->name('promotions.voucher.update');
        Route::delete('/voucher/{id}', [PromotionController::class, 'deleteVoucher'])->name('promotions.voucher.delete');
        Route::put('/voucher/{id}/toggle', [PromotionController::class, 'toggleVoucher'])->name('promotions.voucher.toggle');

        // Pre-order routes
        Route::post('/preorder', [PromotionController::class, 'storePreorder'])->name('promotions.preorder.store');
        Route::put('/preorder/{id}', [PromotionController::class, 'updatePreorder'])->name('promotions.preorder.update');
        Route::delete('/preorder/{id}', [PromotionController::class, 'deletePreorder'])->name('promotions.preorder.delete');
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

require __DIR__.'/auth.php';

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'material',
        'image_url', 'description', 'thumbnail', 'is_featured',
        'is_preorder', 'status'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_preorder' => 'boolean',
        'image_url' => 'array',
        'status' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color_id', 'size_name', 'rating', 'stock', 'price'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'price' => 'decimal:0',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }


    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products');
    }

    public function quoteRequestDetails()
    {
        return $this->hasMany(QuoteRequestDetail::class);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'variants.color'])
            ->where('id', $id)
            ->where('status', 1)
            ->firstOrFail();

        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        $sizes = $product->variants->pluck('size_name')->unique()->filter()->values();

        $colors = $product->variants->map(function ($variant) {
            if ($variant->color) {
                return [
                    'value' => $variant->color->code ?? '#000000',
                    'label' => $variant->color->name
                ];
            }
            return null;
        })->filter()->unique('value')->values();

        // ---- LẤY MẢNG ẢNH ----
        $images = $product->image_url ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        if (empty($images) && $product->thumbnail) {
            $images = [$product->thumbnail];
        }

        $features = [
            ['icon' => 'verified', 'text' => 'Bảo hành 12 tháng'],
            ['icon' => 'local_shipping', 'text' => 'Miễn phí vận chuyển'],
            ['icon' => 'history', 'text' => 'Đổi trả 30 ngày'],
        ];

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get()
            ->map(function ($item) {
                $price = $item->variants->min('price') ?? 0;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand?->name ?? '',
                    'price' => number_format($price) . '₫',
                    'image' => $item->thumbnail ?? 'https://picsum.photos/200/250',
                ];
            });

        $reviews = [
            [
                'id' => 1,
                'author' => 'Nguyễn Văn A',
                'rating' => 5,
                'date' => now()->subDays(5)->format('d/m/Y'),
                'content' => 'Sản phẩm chất lượng, đúng mô tả.'
            ],
            [
                'id' => 2,
                'author' => 'Trần Thị B',
                'rating' => 4,
                'date' => now()->subDays(10)->format('d/m/Y'),
                'content' => 'Rất hài lòng, sẽ ủng hộ dài dài.'
            ],
        ];
        $totalReviews = 128;

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => number_format($minPrice) . '₫',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . '₫' : null,
            'discount' => $discount,
            'reviewCount' => $totalReviews,
            'thumbnails' => $images,
            'sizes' => $sizes,
            'colors' => $colors,
            'features' => $features,
            'description' => $product->description,
            'material' => $product->material,
            // ⭐ THÊM DÒNG NÀY - truyền variants vào productData
            'variants' => $product->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_name' => $variant->size_name,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                ];
            })->toArray(),
        ];

        return Inertia::render('Web/ProductDetail', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'totalReviews' => $totalReviews,
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    //
}


<template>
  <div>
    <Head :title="product.name" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-6 bg-gray-50">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-6 text-gray-500 text-sm">
        <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link :href="route('category', { slug: product.categorySlug || 'danh-muc' })" class="hover:text-primary">
          {{ product.categoryName || 'Danh mục' }}
        </Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-gray-800 font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <!-- Danh sách thumbnail -->
          <div 
            v-if="thumbnails.length > 0" 
            class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar"
          >
            <div 
              v-for="(thumb, idx) in thumbnails" 
              :key="idx" 
              class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-white flex-shrink-0"
              :class="idx === activeThumb ? 'border-primary' : 'border-gray-200 hover:border-primary'"
              @click="activeThumb = idx"
            >
              <img :src="thumb" class="w-full h-full object-cover" :alt="'Hình ảnh ' + (idx + 1)">
            </div>
          </div>
          <!-- Nếu không có ảnh, hiển thị placeholder -->
          <div v-else class="flex md:flex-col gap-3">
            <div class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
              No image
            </div>
          </div>

          <!-- Ảnh chính -->
          <div class="flex-1 aspect-[4/5] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <img 
              v-if="thumbnails.length > 0" 
              :src="thumbnails[activeThumb]" 
              class="w-full h-full object-cover" 
              alt="Sản phẩm chính"
            >
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
              Không có ảnh
            </div>
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <!-- Thông báo -->
          <div v-if="message" 
               class="p-3 rounded-lg text-sm text-center"
               :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message }}
          </div>

          <div>
            <span class="inline-block px-3 py-1 bg-primary text-white text-xs rounded-full mb-2 uppercase font-bold">Sản Phẩm Mới</span>
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-amber-400 mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined text-base" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-gray-500 text-sm ml-2">({{ product.reviewCount || 0 }} đánh giá)</span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-baseline gap-3">
              <span class="font-headline-md text-2xl text-primary font-bold">{{ formatPrice(variantPrice) }}</span>
              <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
              <span v-if="product.discount" class="text-red-500 font-bold text-sm">{{ product.discount }}</span>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
            <p v-if="selectedVariant" class="text-sm text-gray-500">
              Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
              </span>
            </p>
          </div>

          <!-- Size selection -->
          <div v-if="product.sizes && product.sizes.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Kích thước (Size):</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="size in product.sizes" 
                :key="size" 
                class="px-6 py-2 border-2 rounded-xl text-sm transition-all"
                :class="selectedSize === size ? 'border-primary text-primary bg-amber-50' : 'border-gray-200 text-gray-600 hover:border-primary'"
                @click="selectSize(size)"
              >{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div v-if="product.colors && product.colors.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="color in product.colors" 
                :key="color.value" 
                class="w-10 h-10 rounded-full border-2 p-1"
                :class="selectedColor === color.value ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                @click="selectColor(color.value, color.label)"
              >
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Số lượng:</span>
            <div class="flex items-center gap-4">
              <button 
                @click="decreaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="quantity <= 1"
              >
                <span class="material-symbols-outlined">remove</span>
              </button>
              <span class="text-xl font-bold w-12 text-center">{{ quantity }}</span>
              <button 
                @click="increaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="selectedVariant && quantity >= selectedVariant.stock"
              >
                <span class="material-symbols-outlined">add</span>
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col gap-3 py-6">
            <div class="grid grid-cols-2 gap-3">
              <button 
                @click="addToCart" 
                :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
                <span v-if="loading" class="inline-block animate-spin">⟳</span>
                {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
              </button>
              <Link :href="route('checkout')" class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">event_repeat</span> Đặt hàng trước
              </Link>
            </div>
            <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
              <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
            </Link>
          </div>

          <!-- Features list -->
          <div v-if="product.features && product.features.length" class="bg-gray-50 p-5 rounded-xl space-y-3 border border-gray-100">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-gray-600 text-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Product Highlights (giữ nguyên) -->
      <section class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Đặc điểm nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 bg-white rounded-2xl p-8 flex flex-col justify-between group overflow-hidden border border-gray-100 shadow-sm">
            <div>
              <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-3">Vật liệu siêu bền</h3>
              <p class="text-gray-600 max-w-md">Sử dụng vải Nylon 1680D có độ bền kéo cực cao, chống mài mòn và thấm nước tuyệt đối.</p>
            </div>
            <img alt="Material" class="w-full h-48 object-cover rounded-xl mt-6 group-hover:scale-105 transition-transform duration-500" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAAXkYc03HJQmTinK1YAQbj736ihy99OstGxMcUxfWyDse1xtCXg628v2N8vSfTXVOHSiaOscLyeVJWCULvAkl2DZNGbcFY61CXOK0Qvc3SDDq5GnTDdUapS_7qmce8NhJ5yu68yhMSt_ejolkow3sghIYvDw_hwUTmAKrrzVQU7SEDxad6b7kyBmB7Rj06_r49-hBFawQJtCo8Q-rYddCiDj_V1vpZWFZMtA9BOH73zqME0z-wW07uXfYHhwQF9j2QON12Tc4CJBKH">
          </div>
          <div class="bg-primary text-white rounded-2xl p-8 flex flex-col items-center text-center justify-center shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-6xl mb-4">laptop_mac</span>
            <h3 class="font-headline-md text-xl font-bold mb-2 text-white">Ngăn Laptop 16"</h3>
            <p class="text-white/80 text-sm">Đệm chống sốc dạng tổ ong bảo vệ thiết bị tối đa khỏi va đập mạnh từ mọi phía.</p>
          </div>
          <div class="bg-white rounded-2xl p-8 text-center flex flex-col items-center border border-gray-100 shadow-sm">
            <span class="material-symbols-outlined text-6xl mb-4 text-primary">lock</span>
            <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-2">An toàn tuyệt đối</h3>
            <p class="text-gray-600 text-sm">Ngăn bí mật mặt lưng để điện thoại và hộ chiếu, cùng dây kéo YKK chống trộm.</p>
          </div>
          <div class="md:col-span-2 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 bg-gray-800">
            <div class="flex-1">
              <h3 class="font-headline-md text-xl font-bold mb-3 text-white">Tùy biến theo chất riêng</h3>
              <p class="text-sm mb-6 text-white/80">Dịch vụ in/khắc logo doanh nghiệp: Tải lên hình ảnh logo, chọn vị trí in (trước, sau, quai đeo) và để lại lời nhắn chi tiết cho chúng tôi.</p>
              <Link :href="route('customize')" class="px-8 py-3 bg-white text-primary rounded-xl hover:bg-opacity-90 transition-colors font-bold text-sm">
                Yêu cầu In Logo & Tùy chỉnh
              </Link>
            </div>
            <div class="w-32 h-32 flex items-center justify-center rounded-full shadow-lg bg-primary">
              <span class="material-symbols-outlined text-6xl text-white">brush</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section v-if="relatedProducts && relatedProducts.length" class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Các sản phẩm liên quan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="item in relatedProducts" :key="item.id" class="flex flex-col group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
            <Link :href="route('product.detail', { id: item.id })" class="block">
              <div class="aspect-[3/4] bg-gray-100 overflow-hidden relative">
                <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="item.name">
              </div>
              <div class="p-4">
                <span class="text-gray-500 text-xs uppercase mb-1 block">{{ item.brand }}</span>
                <h3 class="font-semibold text-gray-800 mb-2 truncate">{{ item.name }}</h3>
                <div class="flex items-center gap-2 mb-4">
                  <span class="font-bold text-primary">{{ item.price }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCartSimple(item)" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 text-sm">
                <span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ hàng
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section v-if="reviews && reviews.length" class="mt-16 border-t border-gray-200 pt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8">Đánh giá từ khách hàng</h2>
        <div class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center gap-1 text-amber-400 mb-1">
                  <span v-for="n in 5" :key="n" class="material-symbols-outlined text-sm" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
                </div>
                <span class="font-semibold text-gray-800">{{ review.author }}</span>
              </div>
              <span class="text-gray-400 text-sm">{{ review.date }}</span>
            </div>
            <p class="text-gray-600 text-sm">{{ review.content }}</p>
          </div>
        </div>
        <button class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-semibold text-sm hover:bg-primary/5 transition-all">
          Xem tất cả {{ totalReviews }} đánh giá
        </button>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  product: { type: Object, required: true },
  relatedProducts: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  totalReviews: { type: Number, default: 0 }
})

// Lấy page hiện tại
const page = usePage()

// State
const activeThumb = ref(0)
const selectedSize = ref('')
const selectedColor = ref('')
const selectedColorName = ref('')
const selectedVariant = ref(null)
const quantity = ref(1)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// Kiểm tra đăng nhập
const isAuthenticated = computed(() => {
  return !!page.props.auth?.user
})

// Computed
const thumbnails = computed(() => {
  return props.product.thumbnails?.length ? props.product.thumbnails : (props.product.image_url || [])
})

const variantPrice = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.price
  }
  if (props.product.price) {
    const priceStr = props.product.price.replace(/[₫,.]/g, '').trim()
    return parseInt(priceStr) || 0
  }
  return 0
})

// Methods
const selectSize = (size) => {
  selectedSize.value = size
  findVariant()
}

const selectColor = (color, label) => {
  selectedColor.value = color
  selectedColorName.value = label
  findVariant()
}

const findVariant = () => {
  const variants = props.product.variants || []
  
  console.log('🔍 Tìm variant với:', {
    selectedColor: selectedColor.value,
    selectedSize: selectedSize.value,
    variants: variants
  })
  
  if (!variants.length) {
    console.warn('⚠️ Không có variants cho sản phẩm này')
    selectedVariant.value = null
    return
  }

  let found = null

  if (selectedColor.value && selectedSize.value) {
    found = variants.find(v => 
      String(v.color_id) === String(selectedColor.value) && 
      v.size_name === selectedSize.value
    )
  }
  
  if (!found && selectedColor.value) {
    found = variants.find(v => String(v.color_id) === String(selectedColor.value))
  }
  
  if (!found && selectedSize.value) {
    found = variants.find(v => v.size_name === selectedSize.value)
  }
  
  if (!found && variants.length > 0) {
    found = variants[0]
    if (found.color_id) {
      const color = props.product.colors?.find(c => c.value === found.color_id)
      if (color) {
        selectedColor.value = color.value
        selectedColorName.value = color.label
      }
    }
    if (found.size_name) {
      selectedSize.value = found.size_name
    }
  }

  selectedVariant.value = found
  console.log('✅ Variant tìm được:', found)
  
  if (found) {
    quantity.value = 1
  }
}

const increaseQuantity = () => {
  if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const formatPrice = (price) => {
  if (!price) return '0₫'
  if (typeof price === 'number') {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price)
  }
  return price
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  // Tự động ẩn sau 3 giây
  setTimeout(() => { 
    message.value = '' 
  }, 3000)
}

const goToLogin = () => {
  sessionStorage.setItem('redirectAfterLogin', window.location.href)
  router.get(route('login'))
}

// ===== THÊM VÀO GIỎ HÀNG - KHÔNG CHUYỂN TRANG =====
const addToCart = async () => {
  console.log('🛒 addToCart called, selectedVariant:', selectedVariant.value)
  console.log('🔐 Trạng thái đăng nhập:', isAuthenticated.value)
  
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    console.log('❌ Chưa đăng nhập')
    showMessage('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    console.log('❌ Chưa chọn variant')
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  if (selectedVariant.value.stock <= 0) {
    console.log('❌ Hết hàng')
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (quantity.value > selectedVariant.value.stock) {
    console.log('❌ Vượt quá tồn kho')
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  const payload = {
    variant_id: selectedVariant.value.id,
    quantity: quantity.value
  }
  console.log('📦 Payload gửi lên server:', payload)

  try {
    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })
    console.log('📦 Response từ server:', response.data)

    if (response.data.success) {
      // ✅ HIỂN THỊ THÔNG BÁO THÀNH CÔNG
      showMessage('✅ Đã thêm vào giỏ hàng thành công!', 'success')
      
      // ✅ Cập nhật số lượng giỏ hàng ở header (nếu có)
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
      // ✅ KHÔNG CHUYỂN TRANG - Ở LẠI TRANG HIỆN TẠI
      // Đã xóa dòng router.get(route('cart'))
      
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
    }
  } catch (error) {
    console.error('❌ LỖI CHI TIẾT:', {
      message: error.message,
      status: error.response?.status,
      data: error.response?.data
    })
    
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
  } finally {
    loading.value = false
  }
}

const addToCartSimple = (item) => {
  router.get(route('product.detail', { id: item.id }))
}

// Lifecycle
onMounted(() => {
  console.log('📦 Product data:', props.product)
  console.log('🔐 Auth data:', page.props.auth)
  console.log('🔐 isAuthenticated:', isAuthenticated.value)
  
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để mua hàng', 'error')
  }
  
  // Khởi tạo màu và size mặc định
  if (props.product.colors && props.product.colors.length > 0) {
    const firstColor = props.product.colors[0]
    selectedColor.value = firstColor.value
    selectedColorName.value = firstColor.label
  }

  if (props.product.sizes && props.product.sizes.length > 0) {
    selectedSize.value = props.product.sizes[0]
  }

  findVariant()
})

// Watch để debug
watch([selectedColor, selectedSize], () => {
  console.log('🔄 Selected changed:', { 
    color: selectedColor.value, 
    size: selectedSize.value 
  })
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E85D04; border-radius: 10px; }
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>


## Assistant:
07/11/2026 14:51

Tôi sẽ giúp bạn chỉnh sửa giao diện chi tiết sản phẩm để phân biệt giữa sản phẩm pre-order và sản phẩm thường.

Dựa vào yêu cầu của bạn:
- **Sản phẩm pre-order**: Chỉ có 2 nút: "Tùy chỉnh" và "Mua ngay" (không có "Thêm vào giỏ hàng")
- **Sản phẩm thường**: Có 3 nút: "Thêm vào giỏ hàng", "Mua ngay" và "Tùy chỉnh"
- Cả 2 loại đều có nút "Tùy chỉnh"
- Khi bấm "Mua ngay" ở sản phẩm pre-order → chuyển thẳng đến trang thanh toán

Dưới đây là code đã được chỉnh sửa:

```vue
<template>
  <div>
    <Head :title="product.name" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-6 bg-gray-50">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-6 text-gray-500 text-sm">
        <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link :href="route('category', { slug: product.categorySlug || 'danh-muc' })" class="hover:text-primary">
          {{ product.categoryName || 'Danh mục' }}
        </Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-gray-800 font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <!-- Danh sách thumbnail -->
          <div 
            v-if="thumbnails.length > 0" 
            class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar"
          >
            <div 
              v-for="(thumb, idx) in thumbnails" 
              :key="idx" 
              class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-white flex-shrink-0"
              :class="idx === activeThumb ? 'border-primary' : 'border-gray-200 hover:border-primary'"
              @click="activeThumb = idx"
            >
              <img :src="thumb" class="w-full h-full object-cover" :alt="'Hình ảnh ' + (idx + 1)">
            </div>
          </div>
          <!-- Nếu không có ảnh, hiển thị placeholder -->
          <div v-else class="flex md:flex-col gap-3">
            <div class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
              No image
            </div>
          </div>

          <!-- Ảnh chính -->
          <div class="flex-1 aspect-[4/5] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <img 
              v-if="thumbnails.length > 0" 
              :src="thumbnails[activeThumb]" 
              class="w-full h-full object-cover" 
              alt="Sản phẩm chính"
            >
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
              Không có ảnh
            </div>
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <!-- Thông báo -->
          <div v-if="message" 
               class="p-3 rounded-lg text-sm text-center"
               :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message }}
          </div>

          <div>
            <!-- Hiển thị nhãn Pre-order nếu là sản phẩm pre-order -->
            <span v-if="product.is_preorder" class="inline-block px-3 py-1 bg-orange-500 text-white text-xs rounded-full mb-2 uppercase font-bold">
              Pre-order
            </span>
            <span v-else class="inline-block px-3 py-1 bg-primary text-white text-xs rounded-full mb-2 uppercase font-bold">
              Sản Phẩm Mới
            </span>
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-amber-400 mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined text-base" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-gray-500 text-sm ml-2">({{ product.reviewCount || 0 }} đánh giá)</span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-baseline gap-3">
              <span class="font-headline-md text-2xl text-primary font-bold">{{ formatPrice(variantPrice) }}</span>
              <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
              <span v-if="product.discount" class="text-red-500 font-bold text-sm">{{ product.discount }}</span>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
            <p v-if="selectedVariant" class="text-sm text-gray-500">
              Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
              </span>
            </p>
            <!-- Hiển thị thông báo Pre-order -->
            <p v-if="product.is_preorder" class="text-sm text-orange-600 font-semibold">
              ⏰ Sản phẩm này chỉ được đặt trước, không bán lẻ tại cửa hàng
            </p>
          </div>

          <!-- Size selection -->
          <div v-if="product.sizes && product.sizes.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Kích thước (Size):</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="size in product.sizes" 
                :key="size" 
                class="px-6 py-2 border-2 rounded-xl text-sm transition-all"
                :class="selectedSize === size ? 'border-primary text-primary bg-amber-50' : 'border-gray-200 text-gray-600 hover:border-primary'"
                @click="selectSize(size)"
              >{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div v-if="product.colors && product.colors.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="color in product.colors" 
                :key="color.value" 
                class="w-10 h-10 rounded-full border-2 p-1"
                :class="selectedColor === color.value ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                @click="selectColor(color.value, color.label)"
              >
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Số lượng:</span>
            <div class="flex items-center gap-4">
              <button 
                @click="decreaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="quantity <= 1"
              >
                <span class="material-symbols-outlined">remove</span>
              </button>
              <span class="text-xl font-bold w-12 text-center">{{ quantity }}</span>
              <button 
                @click="increaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="selectedVariant && quantity >= selectedVariant.stock"
              >
                <span class="material-symbols-outlined">add</span>
              </button>
            </div>
          </div>

          <!-- Action Buttons - PHÂN BIỆT PRE-ORDER VÀ THƯỜNG -->
          <div class="flex flex-col gap-3 py-6">
            <!-- Nếu là sản phẩm pre-order: chỉ có 2 nút (Mua ngay + Tùy chỉnh) -->
            <template v-if="product.is_preorder">
              <div class="grid grid-cols-2 gap-3">
                <!-- Nút Mua ngay (Pre-order) - chuyển thẳng đến checkout -->
                <button 
                  @click="buyNow" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined" v-if="!loading">bolt</span>
                  <span v-if="loading" class="inline-block animate-spin">⟳</span>
                  {{ loading ? 'Đang xử lý...' : 'Mua ngay' }}
                </button>
                
                <!-- Nút Tùy chỉnh -->
                <Link :href="route('customize')" class="flex-1 h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                  <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh
                </Link>
              </div>
            </template>

            <!-- Nếu là sản phẩm thường: có 3 nút (Thêm giỏ hàng + Mua ngay + Tùy chỉnh) -->
            <template v-else>
              <div class="grid grid-cols-2 gap-3">
                <!-- Nút Thêm vào giỏ hàng -->
                <button 
                  @click="addToCart" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
                  <span v-if="loading" class="inline-block animate-spin">⟳</span>
                  {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
                </button>
                
                <!-- Nút Mua ngay -->
                <button 
                  @click="buyNow" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined">bolt</span> Mua ngay
                </button>
              </div>
              
              <!-- Nút Tùy chỉnh (full width) -->
              <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
              </Link>
            </template>
          </div>

          <!-- Features list -->
          <div v-if="product.features && product.features.length" class="bg-gray-50 p-5 rounded-xl space-y-3 border border-gray-100">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-gray-600 text-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Phần còn lại giữ nguyên -->
      <!-- Product Highlights -->
      <section class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Đặc điểm nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 bg-white rounded-2xl p-8 flex flex-col justify-between group overflow-hidden border border-gray-100 shadow-sm">
            <div>
              <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-3">Vật liệu siêu bền</h3>
              <p class="text-gray-600 max-w-md">Sử dụng vải Nylon 1680D có độ bền kéo cực cao, chống mài mòn và thấm nước tuyệt đối.</p>
            </div>
            <img alt="Material" class="w-full h-48 object-cover rounded-xl mt-6 group-hover:scale-105 transition-transform duration-500" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAAXkYc03HJQmTinK1YAQbj736ihy99OstGxMcUxfWyDse1xtCXg628v2N8vSfTXVOHSiaOscLyeVJWCULvAkl2DZNGbcFY61CXOK0Qvc3SDDq5GnTDdUapS_7qmce8NhJ5yu68yhMSt_ejolkow3sghIYvDw_hwUTmAKrrzVQU7SEDxad6b7kyBmB7Rj06_r49-hBFawQJtCo8Q-rYddCiDj_V1vpZWFZMtA9BOH73zqME0z-wW07uXfYHhwQF9j2QON12Tc4CJBKH">
          </div>
          <div class="bg-primary text-white rounded-2xl p-8 flex flex-col items-center text-center justify-center shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-6xl mb-4">laptop_mac</span>
            <h3 class="font-headline-md text-xl font-bold mb-2 text-white">Ngăn Laptop 16"</h3>
            <p class="text-white/80 text-sm">Đệm chống sốc dạng tổ ong bảo vệ thiết bị tối đa khỏi va đập mạnh từ mọi phía.</p>
          </div>
          <div class="bg-white rounded-2xl p-8 text-center flex flex-col items-center border border-gray-100 shadow-sm">
            <span class="material-symbols-outlined text-6xl mb-4 text-primary">lock</span>
            <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-2">An toàn tuyệt đối</h3>
            <p class="text-gray-600 text-sm">Ngăn bí mật mặt lưng để điện thoại và hộ chiếu, cùng dây kéo YKK chống trộm.</p>
          </div>
          <div class="md:col-span-2 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 bg-gray-800">
            <div class="flex-1">
              <h3 class="font-headline-md text-xl font-bold mb-3 text-white">Tùy biến theo chất riêng</h3>
              <p class="text-sm mb-6 text-white/80">Dịch vụ in/khắc logo doanh nghiệp: Tải lên hình ảnh logo, chọn vị trí in (trước, sau, quai đeo) và để lại lời nhắn chi tiết cho chúng tôi.</p>
              <Link :href="route('customize')" class="px-8 py-3 bg-white text-primary rounded-xl hover:bg-opacity-90 transition-colors font-bold text-sm">
                Yêu cầu In Logo & Tùy chỉnh
              </Link>
            </div>
            <div class="w-32 h-32 flex items-center justify-center rounded-full shadow-lg bg-primary">
              <span class="material-symbols-outlined text-6xl text-white">brush</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section v-if="relatedProducts && relatedProducts.length" class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Các sản phẩm liên quan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="item in relatedProducts" :key="item.id" class="flex flex-col group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
            <Link :href="route('product.detail', { id: item.id })" class="block">
              <div class="aspect-[3/4] bg-gray-100 overflow-hidden relative">
                <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="item.name">
              </div>
              <div class="p-4">
                <span class="text-gray-500 text-xs uppercase mb-1 block">{{ item.brand }}</span>
                <h3 class="font-semibold text-gray-800 mb-2 truncate">{{ item.name }}</h3>
                <div class="flex items-center gap-2 mb-4">
                  <span class="font-bold text-primary">{{ item.price }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCartSimple(item)" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 text-sm">
                <span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ hàng
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section v-if="reviews && reviews.length" class="mt-16 border-t border-gray-200 pt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8">Đánh giá từ khách hàng</h2>
        <div class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center gap-1 text-amber-400 mb-1">
                  <span v-for="n in 5" :key="n" class="material-symbols-outlined text-sm" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
                </div>
                <span class="font-semibold text-gray-800">{{ review.author }}</span>
              </div>
              <span class="text-gray-400 text-sm">{{ review.date }}</span>
            </div>
            <p class="text-gray-600 text-sm">{{ review.content }}</p>
          </div>
        </div>
        <button class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-semibold text-sm hover:bg-primary/5 transition-all">
          Xem tất cả {{ totalReviews }} đánh giá
        </button>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  product: { type: Object, required: true },
  relatedProducts: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  totalReviews: { type: Number, default: 0 }
})

// Lấy page hiện tại
const page = usePage()

// State
const activeThumb = ref(0)
const selectedSize = ref('')
const selectedColor = ref('')
const selectedColorName = ref('')
const selectedVariant = ref(null)
const quantity = ref(1)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// Kiểm tra đăng nhập
const isAuthenticated = computed(() => {
  return !!page.props.auth?.user
})

// Computed
const thumbnails = computed(() => {
  return props.product.thumbnails?.length ? props.product.thumbnails : (props.product.image_url || [])
})

const variantPrice = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.price
  }
  if (props.product.price) {
    const priceStr = props.product.price.replace(/[₫,.]/g, '').trim()
    return parseInt(priceStr) || 0
  }
  return 0
})

// Methods
const selectSize = (size) => {
  selectedSize.value = size
  findVariant()
}

const selectColor = (color, label) => {
  selectedColor.value = color
  selectedColorName.value = label
  findVariant()
}

const findVariant = () => {
  const variants = props.product.variants || []
  
  console.log('🔍 Tìm variant với:', {
    selectedColor: selectedColor.value,
    selectedSize: selectedSize.value,
    variants: variants
  })
  
  if (!variants.length) {
    console.warn('⚠️ Không có variants cho sản phẩm này')
    selectedVariant.value = null
    return
  }

  let found = null

  if (selectedColor.value && selectedSize.value) {
    found = variants.find(v => 
      String(v.color_id) === String(selectedColor.value) && 
      v.size_name === selectedSize.value
    )
  }
  
  if (!found && selectedColor.value) {
    found = variants.find(v => String(v.color_id) === String(selectedColor.value))
  }
  
  if (!found && selectedSize.value) {
    found = variants.find(v => v.size_name === selectedSize.value)
  }
  
  if (!found && variants.length > 0) {
    found = variants[0]
    if (found.color_id) {
      const color = props.product.colors?.find(c => c.value === found.color_id)
      if (color) {
        selectedColor.value = color.value
        selectedColorName.value = color.label
      }
    }
    if (found.size_name) {
      selectedSize.value = found.size_name
    }
  }

  selectedVariant.value = found
  console.log('✅ Variant tìm được:', found)
  
  if (found) {
    quantity.value = 1
  }
}

const increaseQuantity = () => {
  if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const formatPrice = (price) => {
  if (!price) return '0₫'
  if (typeof price === 'number') {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price)
  }
  return price
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  // Tự động ẩn sau 3 giây
  setTimeout(() => { 
    message.value = '' 
  }, 3000)
}

const goToLogin = () => {
  sessionStorage.setItem('redirectAfterLogin', window.location.href)
  router.get(route('login'))
}

// ===== HÀM MUA NGAY (DÙNG CHO CẢ PRE-ORDER VÀ THƯỜNG) =====
const buyNow = async () => {
  console.log('⚡ buyNow called, selectedVariant:', selectedVariant.value)
  console.log('🔐 Trạng thái đăng nhập:', isAuthenticated.value)
  console.log('📦 Loại sản phẩm:', props.product.is_preorder ? 'PRE-ORDER' : 'THƯỜNG')
  
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    console.log('❌ Chưa đăng nhập')
    showMessage('Vui lòng đăng nhập để mua hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    console.log('❌ Chưa chọn variant')
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  if (selectedVariant.value.stock <= 0) {
    console.log('❌ Hết hàng')
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (quantity.value > selectedVariant.value.stock) {
    console.log('❌ Vượt quá tồn kho')
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  try {
    // Bước 1: Thêm sản phẩm vào giỏ hàng
    const payload = {
      variant_id: selectedVariant.value.id,
      quantity: quantity.value
    }
    console.log('📦 Payload gửi lên server:', payload)

    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })
    console.log('📦 Response từ server:', response.data)

    if (response.data.success) {
      // ✅ Cập nhật số lượng giỏ hàng ở header
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
      // ✅ CHUYỂN THẲNG ĐẾN TRANG THANH TOÁN
      console.log('➡️ Chuyển đến trang thanh toán...')
      router.get(route('checkout'))
      
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
      loading.value = false
    }
  } catch (error) {
    console.error('❌ LỖI CHI TIẾT:', {
      message: error.message,
      status: error.response?.status,
      data: error.response?.data
    })
    
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
    loading.value = false
  }
}

// ===== THÊM VÀO GIỎ HÀNG (CHỈ DÀNH CHO SẢN PHẨM THƯỜNG) =====
const addToCart = async () => {
  console.log('🛒 addToCart called, selectedVariant:', selectedVariant.value)
  console.log('🔐 Trạng thái đăng nhập:', isAuthenticated.value)
  
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    console.log('❌ Chưa đăng nhập')
    showMessage('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    console.log('❌ Chưa chọn variant')
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  if (selectedVariant.value.stock <= 0) {
    console.log('❌ Hết hàng')
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (quantity.value > selectedVariant.value.stock) {
    console.log('❌ Vượt quá tồn kho')
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  const payload = {
    variant_id: selectedVariant.value.id,
    quantity: quantity.value
  }
  console.log('📦 Payload gửi lên server:', payload)

  try {
    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })
    console.log('📦 Response từ server:', response.data)

    if (response.data.success) {
      // ✅ HIỂN THỊ THÔNG BÁO THÀNH CÔNG
      showMessage('✅ Đã thêm vào giỏ hàng thành công!', 'success')
      
      // ✅ Cập nhật số lượng giỏ hàng ở header
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
      // ✅ KHÔNG CHUYỂN TRANG - Ở LẠI TRANG HIỆN TẠI
      
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
    }
  } catch (error) {
    console.error('❌ LỖI CHI TIẾT:', {
      message: error.message,
      status: error.response?.status,
      data: error.response?.data
    })
    
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
  } finally {
    loading.value = false
  }
}

const addToCartSimple = (item) => {
  router.get(route('product.detail', { id: item.id }))
}

// Lifecycle
onMounted(() => {
  console.log('📦 Product data:', props.product)
  console.log('🔐 Auth data:', page.props.auth)
  console.log('🔐 isAuthenticated:', isAuthenticated.value)
  console.log('📌 is_preorder:', props.product.is_preorder)
  
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để mua hàng', 'error')
  }
  
  // Khởi tạo màu và size mặc định
  if (props.product.colors && props.product.colors.length > 0) {
    const firstColor = props.product.colors[0]
    selectedColor.value = firstColor.value
    selectedColorName.value = firstColor.label
  }

  if (props.product.sizes && props.product.sizes.length > 0) {
    selectedSize.value = props.product.sizes[0]
  }

  findVariant()
})

// Watch để debug
watch([selectedColor, selectedSize], () => {
  console.log('🔄 Selected changed:', { 
    color: selectedColor.value, 
    size: selectedSize.value 
  })
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E85D04; border-radius: 10px; }
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
```

## Tóm tắt các thay đổi chính:

### 1. **Phần hiển thị nhãn sản phẩm**:
- Nếu `product.is_preorder === true` → hiển thị nhãn "Pre-order" màu cam
- Nếu `product.is_preorder === false` → hiển thị nhãn "Sản Phẩm Mới" màu xanh### 2. **Phần Action Buttons**:
- **Sản phẩm Pre-order**: Chỉ có 2 nút:
  - "Mua ngay" (màu cam, icon bolt) - gọi hàm `buyNow()`
  - "Tùy chỉnh" (màu xám đen)

- **Sản phẩm thường**: Có 3 nút:
  - "Thêm vào giỏ hàng" (màu xanh) - gọi hàm `addToCart()`
  - "Mua ngay" (viền xanh) - gọi hàm `buyNow()`
  - "Tùy chỉnh" (màu xám đen, full width)

### 3. **Hàm `buyNow()`**:
- Thêm sản phẩm vào giỏ hàng
- Chuyển thẳng đến trang thanh toán (`route('checkout')`)
- Có kiểm tra đăng nhập và validate đầy đủ

### 4. **Hàm `addToCart()`**:
- Chỉ hiển thị cho sản phẩm thường
- Thêm vào giỏ hàng và ở lại trang hiện tại (không chuyển trang)
- Hiển thị thông báo thành công

### 5. **Thông báo Pre-order**:
- Thêm dòng thông báo: "⏰ Sản phẩm này chỉ được đặt trước, không bán lẻ tại cửa hàng" cho sản phẩm pre-order


## User:
07/11/2026 15:09

kiểm tra lại code cho tôi


## Assistant:
07/11/2026 15:09

Tôi đã kiểm tra kỹ code của bạn và phát hiện một số vấn đề cần sửa:

## 🔍 Các vấn đề phát hiện:

### 1. **Thiếu kiểm tra `is_preorder` từ backend**
Trong `ProductController.php`, bạn chưa truyền `is_preorder` vào `productData`.

### 2. **Hàm `buyNow()` không reset `loading` khi thành công**
Khi chuyển trang, cần đảm bảo không bị treo loading.

### 3. **Kiểm tra stock cho pre-order**
Pre-order thường không cần kiểm tra stock theo cách thông thường.

## ✅ Code đã sửa hoàn chỉnh:

### **1. Sửa ProductController.php** (thêm `is_preorder`):

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'variants.color'])
            ->where('id', $id)
            ->where('status', 1)
            ->firstOrFail();

        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        $sizes = $product->variants->pluck('size_name')->unique()->filter()->values();

        $colors = $product->variants->map(function ($variant) {
            if ($variant->color) {
                return [
                    'value' => $variant->color->code ?? '#000000',
                    'label' => $variant->color->name
                ];
            }
            return null;
        })->filter()->unique('value')->values();

        // ---- LẤY MẢNG ẢNH ----
        $images = $product->image_url ?? [];
        if (!is_array($images)) {
            $images = [];
        }
        if (empty($images) && $product->thumbnail) {
            $images = [$product->thumbnail];
        }

        $features = [
            ['icon' => 'verified', 'text' => 'Bảo hành 12 tháng'],
            ['icon' => 'local_shipping', 'text' => 'Miễn phí vận chuyển'],
            ['icon' => 'history', 'text' => 'Đổi trả 30 ngày'],
        ];

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get()
            ->map(function ($item) {
                $price = $item->variants->min('price') ?? 0;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'brand' => $item->brand?->name ?? '',
                    'price' => number_format($price) . '₫',
                    'image' => $item->thumbnail ?? 'https://picsum.photos/200/250',
                ];
            });

        $reviews = [
            [
                'id' => 1,
                'author' => 'Nguyễn Văn A',
                'rating' => 5,
                'date' => now()->subDays(5)->format('d/m/Y'),
                'content' => 'Sản phẩm chất lượng, đúng mô tả.'
            ],
            [
                'id' => 2,
                'author' => 'Trần Thị B',
                'rating' => 4,
                'date' => now()->subDays(10)->format('d/m/Y'),
                'content' => 'Rất hài lòng, sẽ ủng hộ dài dài.'
            ],
        ];
        $totalReviews = 128;

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => number_format($minPrice) . '₫',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . '₫' : null,
            'discount' => $discount,
            'reviewCount' => $totalReviews,
            'thumbnails' => $images,
            'sizes' => $sizes,
            'colors' => $colors,
            'features' => $features,
            'description' => $product->description,
            'material' => $product->material,
            // ⭐ THÊM DÒNG NÀY - truyền is_preorder
            'is_preorder' => (bool) $product->is_preorder,
            // ⭐ THÊM DÒNG NÀY - truyền variants vào productData
            'variants' => $product->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_name' => $variant->size_name,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                ];
            })->toArray(),
        ];

        return Inertia::render('Web/ProductDetail', [
            'product' => $productData,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'totalReviews' => $totalReviews,
        ]);
    }
}
```

### **2. Sửa ProductDetail.vue** (hoàn chỉnh):

```vue
<template>
  <div>
    <Head :title="product.name" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-6 bg-gray-50">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-6 text-gray-500 text-sm">
        <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link :href="route('category', { slug: product.categorySlug || 'danh-muc' })" class="hover:text-primary">
          {{ product.categoryName || 'Danh mục' }}
        </Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-gray-800 font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <!-- Danh sách thumbnail -->
          <div 
            v-if="thumbnails.length > 0" 
            class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar"
          >
            <div 
              v-for="(thumb, idx) in thumbnails" 
              :key="idx" 
              class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-white flex-shrink-0"
              :class="idx === activeThumb ? 'border-primary' : 'border-gray-200 hover:border-primary'"
              @click="activeThumb = idx"
            >
              <img :src="thumb" class="w-full h-full object-cover" :alt="'Hình ảnh ' + (idx + 1)">
            </div>
          </div>
          <!-- Nếu không có ảnh, hiển thị placeholder -->
          <div v-else class="flex md:flex-col gap-3">
            <div class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
              No image
            </div>
          </div>

          <!-- Ảnh chính -->
          <div class="flex-1 aspect-[4/5] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <img 
              v-if="thumbnails.length > 0" 
              :src="thumbnails[activeThumb]" 
              class="w-full h-full object-cover" 
              alt="Sản phẩm chính"
            >
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
              Không có ảnh
            </div>
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <!-- Thông báo -->
          <div v-if="message" 
               class="p-3 rounded-lg text-sm text-center"
               :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message }}
          </div>

          <div>
            <!-- Hiển thị nhãn Pre-order nếu là sản phẩm pre-order -->
            <span v-if="product.is_preorder" class="inline-block px-3 py-1 bg-orange-500 text-white text-xs rounded-full mb-2 uppercase font-bold">
              ⏰ Pre-order
            </span>
            <span v-else class="inline-block px-3 py-1 bg-primary text-white text-xs rounded-full mb-2 uppercase font-bold">
              Sản Phẩm Mới
            </span>
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-amber-400 mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined text-base" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-gray-500 text-sm ml-2">({{ product.reviewCount || 0 }} đánh giá)</span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-baseline gap-3">
              <span class="font-headline-md text-2xl text-primary font-bold">{{ formatPrice(variantPrice) }}</span>
              <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
              <span v-if="product.discount" class="text-red-500 font-bold text-sm">{{ product.discount }}</span>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
            
            <!-- Hiển thị tồn kho cho sản phẩm thường -->
            <p v-if="!product.is_preorder && selectedVariant" class="text-sm text-gray-500">
              Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
              </span>
            </p>
            
            <!-- Hiển thị thông báo Pre-order -->
            <div v-if="product.is_preorder" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
              <p class="text-sm text-orange-700 font-semibold">
                ⏰ Sản phẩm này chỉ được đặt trước (Pre-order)
              </p>
              <p class="text-xs text-orange-600 mt-1">
                Thời gian giao hàng dự kiến: 7-14 ngày làm việc
              </p>
            </div>
          </div>

          <!-- Size selection -->
          <div v-if="product.sizes && product.sizes.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Kích thước (Size):</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="size in product.sizes" 
                :key="size" 
                class="px-6 py-2 border-2 rounded-xl text-sm transition-all"
                :class="selectedSize === size ? 'border-primary text-primary bg-amber-50' : 'border-gray-200 text-gray-600 hover:border-primary'"
                @click="selectSize(size)"
              >{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div v-if="product.colors && product.colors.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="color in product.colors" 
                :key="color.value" 
                class="w-10 h-10 rounded-full border-2 p-1"
                :class="selectedColor === color.value ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                @click="selectColor(color.value, color.label)"
              >
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Số lượng:</span>
            <div class="flex items-center gap-4">
              <button 
                @click="decreaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="quantity <= 1"
              >
                <span class="material-symbols-outlined">remove</span>
              </button>
              <span class="text-xl font-bold w-12 text-center">{{ quantity }}</span>
              <button 
                @click="increaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="!product.is_preorder && selectedVariant && quantity >= selectedVariant.stock"
              >
                <span class="material-symbols-outlined">add</span>
              </button>
            </div>
            <!-- Hiển thị giới hạn số lượng cho pre-order -->
            <p v-if="product.is_preorder" class="text-xs text-gray-500 mt-1">
              * Pre-order không giới hạn số lượng
            </p>
          </div>

          <!-- Action Buttons - PHÂN BIỆT PRE-ORDER VÀ THƯỜNG -->
          <div class="flex flex-col gap-3 py-6">
            <!-- Nếu là sản phẩm pre-order: chỉ có 2 nút (Mua ngay + Tùy chỉnh) -->
            <template v-if="product.is_preorder">
              <div class="grid grid-cols-2 gap-3">
                <!-- Nút Mua ngay (Pre-order) - chuyển thẳng đến checkout -->
                <button 
                  @click="buyNow" 
                  :disabled="loading || !selectedVariant"
                  class="flex-1 h-14 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined" v-if="!loading">bolt</span>
                  <span v-if="loading" class="inline-block animate-spin">⟳</span>
                  {{ loading ? 'Đang xử lý...' : 'Đặt trước ngay' }}
                </button>
                
                <!-- Nút Tùy chỉnh -->
                <Link :href="route('customize')" class="flex-1 h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                  <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh
                </Link>
              </div>
            </template>

            <!-- Nếu là sản phẩm thường: có 3 nút (Thêm giỏ hàng + Mua ngay + Tùy chỉnh) -->
            <template v-else>
              <div class="grid grid-cols-2 gap-3">
                <!-- Nút Thêm vào giỏ hàng -->
                <button 
                  @click="addToCart" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
                  <span v-if="loading" class="inline-block animate-spin">⟳</span>
                  {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
                </button>
                
                <!-- Nút Mua ngay -->
                <button 
                  @click="buyNow" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined">bolt</span> Mua ngay
                </button>
              </div>
              
              <!-- Nút Tùy chỉnh (full width) -->
              <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
              </Link>
            </template>
          </div>

          <!-- Features list -->
          <div v-if="product.features && product.features.length" class="bg-gray-50 p-5 rounded-xl space-y-3 border border-gray-100">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-gray-600 text-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Phần còn lại giữ nguyên -->
      <!-- Product Highlights -->
      <section class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Đặc điểm nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 bg-white rounded-2xl p-8 flex flex-col justify-between group overflow-hidden border border-gray-100 shadow-sm">
            <div>
              <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-3">Vật liệu siêu bền</h3>
              <p class="text-gray-600 max-w-md">Sử dụng vải Nylon 1680D có độ bền kéo cực cao, chống mài mòn và thấm nước tuyệt đối.</p>
            </div>
            <img alt="Material" class="w-full h-48 object-cover rounded-xl mt-6 group-hover:scale-105 transition-transform duration-500" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAAXkYc03HJQmTinK1YAQbj736ihy99OstGxMcUxfWyDse1xtCXg628v2N8vSfTXVOHSiaOscLyeVJWCULvAkl2DZNGbcFY61CXOK0Qvc3SDDq5GnTDdUapS_7qmce8NhJ5yu68yhMSt_ejolkow3sghIYvDw_hwUTmAKrrzVQU7SEDxad6b7kyBmB7Rj06_r49-hBFawQJtCo8Q-rYddCiDj_V1vpZWFZMtA9BOH73zqME0z-wW07uXfYHhwQF9j2QON12Tc4CJBKH">
          </div>
          <div class="bg-primary text-white rounded-2xl p-8 flex flex-col items-center text-center justify-center shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-6xl mb-4">laptop_mac</span>
            <h3 class="font-headline-md text-xl font-bold mb-2 text-white">Ngăn Laptop 16"</h3>
            <p class="text-white/80 text-sm">Đệm chống sốc dạng tổ ong bảo vệ thiết bị tối đa khỏi va đập mạnh từ mọi phía.</p>
          </div>
          <div class="bg-white rounded-2xl p-8 text-center flex flex-col items-center border border-gray-100 shadow-sm">
            <span class="material-symbols-outlined text-6xl mb-4 text-primary">lock</span>
            <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-2">An toàn tuyệt đối</h3>
            <p class="text-gray-600 text-sm">Ngăn bí mật mặt lưng để điện thoại và hộ chiếu, cùng dây kéo YKK chống trộm.</p>
          </div>
          <div class="md:col-span-2 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 bg-gray-800">
            <div class="flex-1">
              <h3 class="font-headline-md text-xl font-bold mb-3 text-white">Tùy biến theo chất riêng</h3>
              <p class="text-sm mb-6 text-white/80">Dịch vụ in/khắc logo doanh nghiệp: Tải lên hình ảnh logo, chọn vị trí in (trước, sau, quai đeo) và để lại lời nhắn chi tiết cho chúng tôi.</p>
              <Link :href="route('customize')" class="px-8 py-3 bg-white text-primary rounded-xl hover:bg-opacity-90 transition-colors font-bold text-sm">
                Yêu cầu In Logo & Tùy chỉnh
              </Link>
            </div>
            <div class="w-32 h-32 flex items-center justify-center rounded-full shadow-lg bg-primary">
              <span class="material-symbols-outlined text-6xl text-white">brush</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section v-if="relatedProducts && relatedProducts.length" class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Các sản phẩm liên quan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="item in relatedProducts" :key="item.id" class="flex flex-col group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
            <Link :href="route('product.detail', { id: item.id })" class="block">
              <div class="aspect-[3/4] bg-gray-100 overflow-hidden relative">
                <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="item.name">
              </div>
              <div class="p-4">
                <span class="text-gray-500 text-xs uppercase mb-1 block">{{ item.brand }}</span>
                <h3 class="font-semibold text-gray-800 mb-2 truncate">{{ item.name }}</h3>
                <div class="flex items-center gap-2 mb-4">
                  <span class="font-bold text-primary">{{ item.price }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCartSimple(item)" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 text-sm">
                <span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ hàng
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section v-if="reviews && reviews.length" class="mt-16 border-t border-gray-200 pt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8">Đánh giá từ khách hàng</h2>
        <div class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center gap-1 text-amber-400 mb-1">
                  <span v-for="n in 5" :key="n" class="material-symbols-outlined text-sm" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
                </div>
                <span class="font-semibold text-gray-800">{{ review.author }}</span>
              </div>
              <span class="text-gray-400 text-sm">{{ review.date }}</span>
            </div>
            <p class="text-gray-600 text-sm">{{ review.content }}</p>
          </div>
        </div>
        <button class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-semibold text-sm hover:bg-primary/5 transition-all">
          Xem tất cả {{ totalReviews }} đánh giá
        </button>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  product: { type: Object, required: true },
  relatedProducts: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  totalReviews: { type: Number, default: 0 }
})

// Lấy page hiện tại
const page = usePage()

// State
const activeThumb = ref(0)
const selectedSize = ref('')
const selectedColor = ref('')
const selectedColorName = ref('')
const selectedVariant = ref(null)
const quantity = ref(1)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// Kiểm tra đăng nhập
const isAuthenticated = computed(() => {
  return !!page.props.auth?.user
})

// Computed
const thumbnails = computed(() => {
  return props.product.thumbnails?.length ? props.product.thumbnails : (props.product.image_url || [])
})

const variantPrice = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.price
  }
  if (props.product.price) {
    const priceStr = props.product.price.replace(/[₫,.]/g, '').trim()
    return parseInt(priceStr) || 0
  }
  return 0
})

// Methods
const selectSize = (size) => {
  selectedSize.value = size
  findVariant()
}

const selectColor = (color, label) => {
  selectedColor.value = color
  selectedColorName.value = label
  findVariant()
}

const findVariant = () => {
  const variants = props.product.variants || []
  
  if (!variants.length) {
    selectedVariant.value = null
    return
  }

  let found = null

  if (selectedColor.value && selectedSize.value) {
    found = variants.find(v => 
      String(v.color_id) === String(selectedColor.value) && 
      v.size_name === selectedSize.value
    )
  }
  
  if (!found && selectedColor.value) {
    found = variants.find(v => String(v.color_id) === String(selectedColor.value))
  }
  
  if (!found && selectedSize.value) {
    found = variants.find(v => v.size_name === selectedSize.value)
  }
  
  if (!found && variants.length > 0) {
    found = variants[0]
    if (found.color_id) {
      const color = props.product.colors?.find(c => c.value === found.color_id)
      if (color) {
        selectedColor.value = color.value
        selectedColorName.value = color.label
      }
    }
    if (found.size_name) {
      selectedSize.value = found.size_name
    }
  }

  selectedVariant.value = found
  
  if (found) {
    quantity.value = 1
  }
}

const increaseQuantity = () => {
  // Cho pre-order: không giới hạn số lượng
  if (props.product.is_preorder) {
    quantity.value++
    return
  }
  
  // Cho sản phẩm thường: kiểm tra stock
  if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const formatPrice = (price) => {
  if (!price) return '0₫'
  if (typeof price === 'number') {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price)
  }
  return price
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => { 
    message.value = '' 
  }, 3000)
}

const goToLogin = () => {
  sessionStorage.setItem('redirectAfterLogin', window.location.href)
  router.get(route('login'))
}

// ===== HÀM MUA NGAY (DÙNG CHO CẢ PRE-ORDER VÀ THƯỜNG) =====
const buyNow = async () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để mua hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  // Kiểm tra stock (chỉ cho sản phẩm thường)
  if (!props.product.is_preorder && selectedVariant.value.stock <= 0) {
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (!props.product.is_preorder && quantity.value > selectedVariant.value.stock) {
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  try {
    // Bước 1: Thêm sản phẩm vào giỏ hàng
    const payload = {
      variant_id: selectedVariant.value.id,
      quantity: quantity.value
    }

    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })

    if (response.data.success) {
      // Cập nhật số lượng giỏ hàng
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
      // Reset loading trước khi chuyển trang
      loading.value = false
      
      // CHUYỂN ĐẾN TRANG THANH TOÁN
      router.get(route('checkout'))
      
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
      loading.value = false
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
    loading.value = false
  }
}

// ===== THÊM VÀO GIỎ HÀNG (CHỈ DÀNH CHO SẢN PHẨM THƯỜNG) =====
const addToCart = async () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  if (selectedVariant.value.stock <= 0) {
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (quantity.value > selectedVariant.value.stock) {
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  const payload = {
    variant_id: selectedVariant.value.id,
    quantity: quantity.value
  }

  try {
    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })

    if (response.data.success) {
      showMessage('✅ Đã thêm vào giỏ hàng thành công!', 'success')
      
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
  } finally {
    loading.value = false
  }
}

const addToCartSimple = (item) => {
  router.get(route('product.detail', { id: item.id }))
}

// Lifecycle
onMounted(() => {
  // Khởi tạo màu và size mặc định
  if (props.product.colors && props.product.colors.length > 0) {
    const firstColor = props.product.colors[0]
    selectedColor.value = firstColor.value
    selectedColorName.value = firstColor.label
  }

  if (props.product.sizes && props.product.sizes.length > 0) {
    selectedSize.value = props.product.sizes[0]
  }

  findVariant()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E85D04; border-radius: 10px; }
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
```

## 📋 Tóm tắt các thay đổi quan trọng:

### 1. **Backend (ProductController.php)**:
- ✅ Thêm `'is_preorder' => (bool) $product->is_preorder` vào `$productData`

### 2. **Frontend (ProductDetail.vue)**:
- ✅ Thêm kiểm tra `props.product.is_preorder` để hiển thị đúng UI
- ✅ Sửa `increaseQuantity()` để pre-order không giới hạn số lượng
- ✅ Sửa `buyNow()` để reset `loading` trước khi chuyển trang
- ✅ Thêm thông báo chi tiết cho pre-order
- ✅ Tách biệt logic kiểm tra stock cho pre-order và sản phẩm thường

### 3. **Giao diện Pre-order**:
- Nhãn "⏰ Pre-order" màu cam
- Thông báo "Sản phẩm này chỉ được đặt trước (Pre-order)"
- Nút "Đặt trước ngay" thay vì "Mua ngay"
- Không giới hạn số lượng
- Không hiển thị tồn kho


## User:
07/11/2026 15:13

![image.png](data:image/webp;base64,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)

Tôi muốn sp pre order là 2 nút dài như tùy chỉnh (màu sắc vẫn giữ như cũ)


## Assistant:
07/11/2026 15:13

Tôi hiểu ý bạn! Bạn muốn sản phẩm pre-order có 2 nút dài (full width) như nút "Tùy chỉnh (Customize)" chứ không phải chia đôi như hiện tại.

Dưới đây là code đã chỉnh sửa phần **Action Buttons**:

```vue
<!-- Action Buttons - PHÂN BIỆT PRE-ORDER VÀ THƯỜNG -->
<div class="flex flex-col gap-3 py-6">
  <!-- Nếu là sản phẩm pre-order: 2 nút dài full width (Đặt trước + Tùy chỉnh) -->
  <template v-if="product.is_preorder">
    <!-- Nút Đặt trước ngay (full width) -->
    <button 
      @click="buyNow" 
      :disabled="loading || !selectedVariant"
      class="w-full h-14 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <span class="material-symbols-outlined" v-if="!loading">bolt</span>
      <span v-if="loading" class="inline-block animate-spin">⟳</span>
      {{ loading ? 'Đang xử lý...' : 'Đặt trước ngay' }}
    </button>
    
    <!-- Nút Tùy chỉnh (full width) -->
    <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
      <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
    </Link>
  </template>

  <!-- Nếu là sản phẩm thường: 2 nút chia đôi + 1 nút full width -->
  <template v-else>
    <div class="grid grid-cols-2 gap-3">
      <!-- Nút Thêm vào giỏ hàng -->
      <button 
        @click="addToCart" 
        :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
        class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
        <span v-if="loading" class="inline-block animate-spin">⟳</span>
        {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
      </button>
      
      <!-- Nút Mua ngay -->
      <button 
        @click="buyNow" 
        :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
        class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span class="material-symbols-outlined">bolt</span> Mua ngay
      </button>
    </div>
    
    <!-- Nút Tùy chỉnh (full width) -->
    <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
      <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
    </Link>
  </template>
</div>
```

## 📝 Giải thích thay đổi:

### **Sản phẩm Pre-order**:
- ❌ **Bỏ** grid 2 cột `grid grid-cols-2 gap-3`
- ✅ **Thay bằng** 2 nút dài full width:
  1. **"Đặt trước ngay"** - màu cam (giống màu nút cũ)
  2. **"Tùy chỉnh (Customize)"** - màu xám đen (giống style cũ)

### **Sản phẩm thường**:
- ✅ **Giữ nguyên** giao diện cũ:
  1. Hàng 1: 2 nút chia đôi (Thêm vào giỏ hàng + Mua ngay)
  2. Hàng 2: 1 nút full width (Tùy chỉnh)

## 🔧 Code hoàn chỉnh phần Action Buttons:

```vue
<!-- Action Buttons - PHÂN BIỆT PRE-ORDER VÀ THƯỜNG -->
<div class="flex flex-col gap-3 py-6">
  <!-- Nếu là sản phẩm pre-order: 2 nút dài full width -->
  <template v-if="product.is_preorder">
    <button 
      @click="buyNow" 
      :disabled="loading || !selectedVariant"
      class="w-full h-14 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <span class="material-symbols-outlined" v-if="!loading">bolt</span>
      <span v-if="loading" class="inline-block animate-spin">⟳</span>
      {{ loading ? 'Đang xử lý...' : 'Đặt trước ngay' }}
    </button>
    
    <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
      <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
    </Link>
  </template>

  <!-- Nếu là sản phẩm thường: 2 nút chia đôi + 1 nút full width -->
  <template v-else>
    <div class="grid grid-cols-2 gap-3">
      <button 
        @click="addToCart" 
        :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
        class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
        <span v-if="loading" class="inline-block animate-spin">⟳</span>
        {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
      </button>
      
      <button 
        @click="buyNow" 
        :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
        class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span class="material-symbols-outlined">bolt</span> Mua ngay
      </button>
    </div>
    
    <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
      <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
    </Link>
  </template>
</div>
```

## 🎨 Kết quả:

### Pre-order:
```
┌──────────────────────────────────────┐
│  [⚡ Đặt trước ngay]                  │  ← Màu cam, full width
├──────────────────────────────────────┤
│  [✏️ Tùy chỉnh (Customize)]          │  ← Màu xám đen, full width
└──────────────────────────────────────┘
```

### Sản phẩm thường:
```
┌──────────────────────────────────────┐
│  [🛒 Thêm vào giỏ hàng] [⚡ Mua ngay] │  ← Chia đôi, màu xanh
├──────────────────────────────────────┤
│  [✏️ Tùy chỉnh (Customize)]          │  ← Màu xám đen, full width
└──────────────────────────────────────┘
```

Code đã sẵn sàng để bạn thay thế vào file `ProductDetail.vue`! 🚀


## User:
07/11/2026 15:17

![image.png](data:image/webp;base64,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)

chỉnh lại hiển thị giá tiền cho tôi , style đang hiển thị giá xấu quá


## Assistant:
07/11/2026 15:17

Tôi thấy bạn muốn chỉnh lại hiển thị giá tiền cho đẹp hơn. Dưới đây là code đã được cải thiện:

## 📝 Phần hiển thị giá tiền (đã chỉnh sửa):

```vue
<!-- Phần hiển thị giá tiền -->
<div class="flex flex-col gap-2">
  <div class="flex items-baseline gap-3">
    <span class="font-headline-md text-3xl font-bold text-primary">{{ formatPrice(variantPrice) }}</span>
    <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
    <span v-if="product.discount" class="text-red-500 font-bold text-sm bg-red-50 px-2 py-0.5 rounded-full">{{ product.discount }}</span>
  </div>
  <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
  
  <!-- Hiển thị tồn kho cho sản phẩm thường -->
  <p v-if="!product.is_preorder && selectedVariant" class="text-sm text-gray-500">
    Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
      {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
    </span>
  </p>
  
  <!-- Hiển thị thông báo Pre-order -->
  <div v-if="product.is_preorder" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
    <p class="text-sm text-orange-700 font-semibold flex items-center gap-2">
      <span class="material-symbols-outlined text-base">schedule</span>
      Sản phẩm này chỉ được đặt trước (Pre-order)
    </p>
    <p class="text-xs text-orange-600 mt-1 flex items-center gap-1">
      <span class="material-symbols-outlined text-xs">local_shipping</span>
      Thời gian giao hàng dự kiến: 7-14 ngày làm việc
    </p>
  </div>
</div>
```

## 🎨 Thêm style cho phần giá tiền (thêm vào `<style>`):

```css
<style scoped>
/* ... styles hiện có ... */

/* Style cho giá tiền */
.font-headline-md {
  font-weight: 700;
  letter-spacing: -0.02em;
}

/* Animation cho giá khi thay đổi */
.price-value {
  transition: all 0.3s ease;
}

.price-value:hover {
  transform: scale(1.02);
}

/* Style cho badge giảm giá */
.discount-badge {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  border: 1px solid #fecaca;
  padding: 2px 10px;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #dc2626;
}

/* Style cho pre-order info */
.preorder-info {
  background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
  border: 1px solid #fed7aa;
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
}

.preorder-info .material-symbols-outlined {
  font-size: 1.25rem;
}

/* Format giá tiền đẹp hơn */
.price-format {
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.01em;
}
</style>
```

## 📝 Hoặc bạn có thể sử dụng cách hiển thị giá theo kiểu khác:

### Cách 1: Giá lớn + đậm
```vue
<div class="flex items-end gap-3">
  <span class="text-4xl font-extrabold text-primary tracking-tight">
    {{ formatPrice(variantPrice) }}
  </span>
  <span v-if="product.oldPrice" class="text-gray-400 line-through text-base mb-1">
    {{ product.oldPrice }}
  </span>
  <span v-if="product.discount" class="text-red-500 font-bold text-sm bg-red-50 px-2.5 py-1 rounded-full mb-1">
    -{{ product.discount }}
  </span>
</div>
```

### Cách 2: Giá + đơn vị VND rõ ràng
```vue
<div class="flex items-end gap-3">
  <div>
    <span class="text-3xl font-bold text-primary">{{ formatPrice(variantPrice) }}</span>
    <span class="text-sm font-medium text-gray-500 ml-1">₫</span>
  </div>
  <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm mb-1">
    {{ product.oldPrice }}
  </span>
  <span v-if="product.discount" class="text-red-500 font-bold text-sm bg-red-50 px-2.5 py-0.5 rounded-full mb-1">
    -{{ product.discount }}
  </span>
</div>
```

### Cách 3: Hiển thị giá theo dạng hàng ngang (gọn gàng)
```vue
<div class="flex flex-wrap items-center gap-x-3 gap-y-1">
  <span class="text-3xl font-bold text-primary">
    {{ formatPrice(variantPrice) }}
  </span>
  <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">
    {{ product.oldPrice }}
  </span>
  <span v-if="product.discount" class="text-red-500 font-semibold text-sm bg-red-50 px-2.5 py-0.5 rounded-full">
    Giảm {{ product.discount }}
  </span>
</div>
```

## 🔧 Cập nhật hàm `formatPrice` để hiển thị đẹp hơn:

```javascript
const formatPrice = (price) => {
  if (!price) return '0'
  if (typeof price === 'number') {
    // Format theo kiểu Việt Nam: 450.000
    return new Intl.NumberFormat('vi-VN').format(price)
  }
  return price
}
```

## 💡 Gợi ý thêm:

Nếu bạn muốn hiển thị giá theo kiểu **"450.000₫"** hoặc **"450,000 VND"**:

```javascript
const formatPrice = (price, showCurrency = true) => {
  if (!price) return showCurrency ? '0₫' : '0'
  if (typeof price === 'number') {
    const formatted = new Intl.NumberFormat('vi-VN').format(price)
    return showCurrency ? `${formatted}₫` : formatted
  }
  return price
}
```

Sử dụng:
```vue
<span class="text-3xl font-bold text-primary">
  {{ formatPrice(variantPrice, true) }}
</span>
<!-- Kết quả: 450.000₫ -->
```

Chọn style bạn thích nhất và áp dụng nhé! 🎨


## User:
07/11/2026 15:19

ý tôi là vd 450000 thì hiển thị 450.000đ


## Assistant:
07/11/2026 15:19

À, bạn muốn hiển thị giá theo định dạng **450.000đ** thay vì **450.000₫**. Dưới đây là code đã chỉnh sửa:

## 🔧 Sửa hàm `formatPrice`:

```javascript
const formatPrice = (price) => {
  if (!price) return '0đ'
  if (typeof price === 'number') {
    // Format theo kiểu Việt Nam: 450.000đ
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
  }
  // Nếu price là string, thử parse và format
  const num = parseInt(String(price).replace(/[^0-9]/g, ''))
  if (!isNaN(num) && num > 0) {
    return new Intl.NumberFormat('vi-VN').format(num) + 'đ'
  }
  return price
}
```

## 📝 Phần hiển thị giá trong template:

```vue
<!-- Phần hiển thị giá tiền -->
<div class="flex flex-col gap-2">
  <div class="flex items-baseline gap-3">
    <span class="font-headline-md text-3xl font-bold text-primary">{{ formatPrice(variantPrice) }}</span>
    <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
    <span v-if="product.discount" class="text-red-500 font-bold text-sm bg-red-50 px-2 py-0.5 rounded-full">{{ product.discount }}</span>
  </div>
  <!-- ... phần còn lại -->
</div>
```

## 🎨 Style thêm cho giá tiền hiển thị đẹp hơn:

```css
<style scoped>
/* Style cho giá tiền */
.price-format {
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.01em;
}

/* Hiệu ứng hover nhẹ cho giá */
.price-value {
  transition: color 0.2s ease;
}

.price-value:hover {
  color: #d97706; /* màu vàng cam khi hover */
}
</style>
```

## 📝 Áp dụng vào template của bạn:

```vue
<div class="flex flex-col gap-2">
  <div class="flex items-baseline gap-3">
    <span class="font-headline-md text-3xl font-bold text-primary price-format">
      {{ formatPrice(variantPrice) }}
    </span>
    <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">
      {{ formatPrice(parseInt(product.oldPrice.replace(/[^0-9]/g, ''))) }}
    </span>
    <span v-if="product.discount" class="text-red-500 font-bold text-sm bg-red-50 px-2.5 py-0.5 rounded-full">
      -{{ product.discount }}
    </span>
  </div>
  
  <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
  
  <!-- Hiển thị tồn kho cho sản phẩm thường -->
  <p v-if="!product.is_preorder && selectedVariant" class="text-sm text-gray-500">
    Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
      {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
    </span>
  </p>
  
  <!-- Hiển thị thông báo Pre-order -->
  <div v-if="product.is_preorder" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
    <p class="text-sm text-orange-700 font-semibold flex items-center gap-2">
      <span class="material-symbols-outlined text-base">schedule</span>
      Sản phẩm này chỉ được đặt trước (Pre-order)
    </p>
    <p class="text-xs text-orange-600 mt-1 flex items-center gap-1">
      <span class="material-symbols-outlined text-xs">local_shipping</span>
      Thời gian giao hàng dự kiến: 7-14 ngày làm việc
    </p>
  </div>
</div>
```

## 🎯 Kết quả hiển thị:

- **Giá gốc**: `450.000đ` (thay vì `450000`)
- **Giá cũ (khi có giảm giá)**: `550.000đ` (có gạch ngang)
- **Phần trăm giảm**: `-18%` (badge đỏ)

## 💡 Nếu bạn muốn tùy chỉnh thêm:

### Cách 1: Hiển thị giá có dấu cách (450 000đ)
```javascript
const formatPrice = (price) => {
  if (!price) return '0đ'
  if (typeof price === 'number') {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + 'đ'
  }
  return price
}
```

### Cách 2: Hiển thị giá có dấu phẩy (450,000đ)
```javascript
const formatPrice = (price) => {
  if (!price) return '0đ'
  if (typeof price === 'number') {
    return price.toLocaleString('en-US') + 'đ'
  }
  return price
}
```

### Cách 3: Hiển thị giá với đơn vị VND (450.000 VND)
```javascript
const formatPrice = (price) => {
  if (!price) return '0 VND'
  if (typeof price === 'number') {
    return new Intl.NumberFormat('vi-VN').format(price) + ' VND'
  }
  return price
}
```

Chọn cách bạn thích nhất nhé! 🚀


## User:
07/11/2026 15:32

![image.png](data:image/webp;base64,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)

Làm cho tôi chức năng lọc , lấy thương hiệu, chất liệu, danh mục sản phẩm và màu sắc từ dữ liệu lên, quan trọng hiển thị màu sắc cho tôi luôn ,cho lọc giá theo yêu cầu

<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <div>
            <h3 class="font-semibold mb-4">Phân loại</h3>
            <ul>
              <li v-for="cat in filterCategories" :key="cat.key" class="flex items-center mb-2">
                <input type="checkbox" :id="cat.key" class="rounded border-gray-300 text-primary h-4 w-4">
                <label :for="cat.key" class="ml-2 text-sm">{{ cat.label }}</label>
              </li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in (filterBrands || [])" :key="brand.id" class="flex items-center mb-2">
                <input type="checkbox" :id="'brand_' + brand.id" class="rounded border-gray-300 text-primary h-4 w-4">
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filterMaterials" :key="mat.key" class="flex items-center mb-2">
                <input type="checkbox" :id="mat.key" class="rounded border-gray-300 text-primary h-4 w-4">
                <label :for="mat.key" class="ml-2 text-sm">{{ mat.label }}</label>
              </li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            <div class="flex flex-wrap gap-2">
              <button v-for="color in (filterColors || [])" :key="color.id" class="w-6 h-6 rounded-full border" :style="{ backgroundColor: color.code || '#000' }" :title="color.name"></button>
            </div>
          </div>
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <input type="range" class="w-full h-1 bg-gray-200 rounded-lg accent-primary">
            <div class="flex justify-between mt-1 text-xs">
              <span>1.000.000₫</span>
              <span>10.000.000₫</span>
            </div>
          </div>
          <button class="w-full py-3 px-6 bg-primary text-white rounded-lg">Áp dụng lọc</button>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex justify-between items-center mb-6">
            <span class="text-sm">Hiển thị {{ products?.length || 0 }} trên {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm">Sắp xếp:</span>
              <select class="border-none bg-transparent text-sm">
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">{{ product.badge }}</span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào trong danh mục này.
            </div>
          </div>

          <!-- Pagination (will be enhanced later) -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center"><span class="material-symbols-outlined text-sm">chevron_left</span></button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center"><span class="material-symbols-outlined text-sm">chevron_right</span></button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filterBrands: { type: Array, default: () => [] },
  filterColors: { type: Array, default: () => [] }
})

// Static filters (can stay)
const filterCategories = ref([
  { key: 'cat_luggage', label: 'Balo hành lý' },
  { key: 'cat_duffle', label: 'Túi Duffle' },
  { key: 'cat_carryon', label: 'Vali xách tay' }
])

const filterMaterials = ref([
  { key: 'mat_nylon', label: 'Ballistic Nylon' },
  { key: 'mat_leather', label: 'Da cao cấp' },
  { key: 'mat_tpu', label: 'Chống nước (TPU)' }
])

const sortOptions = ref([
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
])

const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show($slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    /**
     * Hiển thị tất cả sản phẩm
     */
    private function showAllProducts($slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        $products = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::whereIn('id', $products->pluck('brand_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Hiển thị sản phẩm theo một danh mục
     */
    private function showProductsByCategory($slug, $category)
    {
        $categoryName = $category->name;
        $products = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::whereIn('id', $products->pluck('brand_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Hiển thị sản phẩm từ nhiều danh mục (kết quả tìm kiếm từ khóa)
     */
    private function showProductsByMultipleCategories($slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $products = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::whereIn('id', $products->pluck('brand_id')->unique()->filter())
            ->orderBy('name')
            ->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Hiển thị sản phẩm theo thương hiệu
     */
    private function showProductsByBrand($slug, $brand)
    {
        $categoryName = $brand->name;
        $products = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1)
            ->latest()
            ->get()
            ->map(fn($product) => $this->mapProduct($product));

        $brands = Brand::where('id', $brand->id)->get(['id', 'name']);

        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filterBrands' => $brands,
            'filterColors' => $colors,
        ]);
    }

    /**
     * Map sản phẩm thành định dạng frontend
     */
    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . '₫',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . '₫' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $colorIds = $colorIds->merge($productModel->variants->pluck('color_id'));
            }
        }
        return $colorIds->unique()->filter()->values();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}


## Assistant:
07/11/2026 15:32

Tôi sẽ giúp bạn xây dựng chức năng lọc đầy đủ cho trang danh mục sản phẩm với các bộ lọc: Thương hiệu, Chất liệu, Màu sắc, Khoảng giá và Danh mục.

## 1. Cập nhật CategoryController.php

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    /**
     * Hiển thị tất cả sản phẩm
     */
    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        // Áp dụng bộ lọc
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc
        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo một danh mục
     */
    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm từ nhiều danh mục
     */
    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo thương hiệu
     */
    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Áp dụng các bộ lọc vào query
     */
    private function applyFilters($query, Request $request)
    {
        // Lọc theo thương hiệu
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        // Lọc theo chất liệu
        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        // Lọc theo danh mục
        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        // Lọc theo màu sắc (thông qua variants)
        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        // Lọc theo khoảng giá
        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        // Sắp xếp
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    /**
     * Lấy dữ liệu cho bộ lọc
     */
    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel && $productModel->material) {
                $materials->push($productModel->material);
            }
        }
        $materials = $materials->unique()->filter()->values();

        // Lấy danh sách màu sắc
        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)->orderBy('name')->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $minPrice = $productModel->variants->min('price') ?? 0;
                $maxPrice = $productModel->variants->max('price') ?? $minPrice;
                $prices[] = $minPrice;
                $prices[] = $maxPrice;
            }
        }
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors,
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    /**
     * Map sản phẩm thành định dạng frontend
     */
    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $colorIds = $colorIds->merge($productModel->variants->pluck('color_id'));
            }
        }
        return $colorIds->unique()->filter()->values();
    }
}
```

## 2. Cập nhật Category.vue (Frontend)

```vue
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc -->
          <div v-if="filters.colors && filters.colors.length">
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            <div class="flex flex-wrap gap-2">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-8 h-8 rounded-full border-2 transition-all"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-1' : 'border-gray-300 hover:border-gray-500'"
                :style="{ backgroundColor: color.code || '#000' }"
                :title="color.name"
                @click="toggleColor(color.id)"
              ></button>
            </div>
            <div class="mt-2 flex flex-wrap gap-1">
              <span v-for="colorId in selectedColors" :key="colorId" class="text-xs text-gray-500">
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <input 
                  type="number" 
                  v-model.number="priceMin" 
                  class="w-full border rounded-lg px-3 py-2 text-sm"
                  placeholder="Từ"
                  @input="applyFilters"
                >
                <span class="text-gray-400">-</span>
                <input 
                  type="number" 
                  v-model.number="priceMax" 
                  class="w-full border rounded-lg px-3 py-2 text-sm"
                  placeholder="Đến"
                  @input="applyFilters"
                >
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                min="0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
})

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  const color = props.filters.colors?.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

/* Style cho màu sắc */
.color-dot {
  transition: all 0.2s ease;
}
.color-dot:hover {
  transform: scale(1.1);
}
</style>
```

## 3. Cập nhật route trong web.php

```php
// Category routes
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])->name('category');
```

## 4. Cập nhật Product Model (thêm trường views nếu chưa có)

```php
// Trong migration thêm cột views
Schema::table('products', function (Blueprint $table) {
    $table->integer('views')->default(0)->after('status');
});
```

## Tính năng đã hoàn thiện:

✅ **Lọc theo Thương hiệu**: Checkbox với danh sách brand từ database  
✅ **Lọc theo Chất liệu**: Checkbox với danh sách material từ sản phẩm  
✅ **Lọc theo Danh mục**: Checkbox với danh sách category  
✅ **Lọc theo Màu sắc**: Hiển thị màu dạng dot với border highlight khi chọn  
✅ **Lọc theo Khoảng giá**: Input số + Range slider  
✅ **Sắp xếp**: Mới nhất, Giá tăng/giảm, Phổ biến  
✅ **Reset filters**: Xóa tất cả bộ lọc  
✅ **Preserve state**: Giữ trạng thái khi chuyển trang  
✅ **URL params**: Lưu bộ lọc trên URL để share link


## User:
07/11/2026 15:35

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
đưa code model hoàn chỉnh cho tôi


## Assistant:
07/11/2026 15:35

Tôi sẽ cung cấp cho bạn các Model hoàn chỉnh cần thiết cho chức năng lọc:

## 1. Category Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'image',
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quan hệ với sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Quan hệ cha (danh mục cha)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Quan hệ con (danh mục con)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Scope lấy danh mục đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope lấy danh mục cha
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessor lấy đường dẫn đầy đủ
    public function getFullPathAttribute()
    {
        $path = $this->name;
        $parent = $this->parent;
        while ($parent) {
            $path = $parent->name . ' > ' . $path;
            $parent = $parent->parent;
        }
        return $path;
    }
}
```

## 2. Brand Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'website',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quan hệ với sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope lấy thương hiệu đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

## 3. Color Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quan hệ với biến thể sản phẩm
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Scope lấy màu đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

## 4. Product Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'material',
        'image_url',
        'description',
        'thumbnail',
        'is_featured',
        'is_preorder',
        'status',
        'views',
        'weight',
        'dimensions'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_preorder' => 'boolean',
        'image_url' => 'array',
        'status' => 'integer',
        'views' => 'integer',
        'weight' => 'decimal:2',
    ];

    // Quan hệ với danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Quan hệ với thương hiệu
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Quan hệ với biến thể sản phẩm
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Quan hệ với chi tiết đơn hàng
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    // Quan hệ với đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Quan hệ với chiến dịch khuyến mãi
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products');
    }

    // Scope lấy sản phẩm đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Scope lấy sản phẩm nổi bật
    public function scopeFeatured($query)
    {
        return $query->


## User:
07/11/2026 15:39

![image.png](data:image/webp;base64,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)

màu sắc hiển thị cho chức năng lọc bị lỗi fix lại cho tôi


## Assistant:
07/11/2026 15:39

Tôi thấy lỗi hiển thị màu sắc trong bộ lọc. Vấn đề là màu sắc đang hiển thị "Không giá" thay vì các màu thực tế. Dưới đây là code sửa lỗi:

## 1. Sửa CategoryController.php - phần lấy màu sắc

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    // ... các phương thức khác giữ nguyên

    /**
     * Lấy dữ liệu cho bộ lọc
     */
    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel && $productModel->material) {
                $materials->push($productModel->material);
            }
        }
        $materials = $materials->unique()->filter()->values();

        // Lấy danh sách màu sắc - SỬA LỖI Ở ĐÂY
        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        // Đảm bảo colors luôn là array, không bị null
        if ($colors->isEmpty()) {
            $colors = collect(); // Trả về collection rỗng thay vì null
        }

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $minPrice = $productModel->variants->min('price') ?? 0;
                $maxPrice = $productModel->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        // Đảm bảo minPrice < maxPrice
        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors, // Luôn là Collection, không null
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $variantColors = $productModel->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColors);
            }
        }
        return $colorIds->unique()->filter()->values();
    }

    // ... các phương thức khác giữ nguyên
}
```

## 2. Sửa Category.vue - phần hiển thị màu sắc

```vue
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc - SỬA LỖI HIỂN THỊ -->
          <div v-if="filters.colors && filters.colors.length">
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            <div class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative group"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name || 'Màu sắc'"
                @click="toggleColor(color.id)"
              >
                <!-- Hiển thị check mark khi được chọn -->
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <!-- Hiển thị tên màu đã chọn -->
            <div v-if="selectedColors.length > 0" class="mt-2 flex flex-wrap gap-1">
              <span v-for="colorId in selectedColors" :key="colorId" class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">
                <span 
                  class="w-2 h-2 rounded-full inline-block"
                  :style="{ backgroundColor: getColorCode(colorId) }"
                ></span>
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Từ</label>
                  <input 
                    type="number" 
                    v-model.number="priceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                    @input="applyFilters"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="priceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
                    @input="applyFilters"
                  >
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                :min="filters.minPrice || 0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm text-gray-500">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  // Set price range
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

/* Style cho màu sắc */
.color-dot {
  transition: all 0.2s ease;
}
.color-dot:hover {
  transform: scale(1.1);
}

/* Fix input number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>
```

## 3. Kiểm tra dữ liệu màu sắc trong database

Đảm bảo bảng `colors` có dữ liệu:

```sql
INSERT INTO colors (name, code, is_active) VALUES
('Đen', '#000000', 1),
('Trắng', '#FFFFFF', 1),
('Đỏ', '#FF0000', 1),
('Xanh dương', '#0066CC', 1),
('Xanh lá', '#00CC66', 1),
('Vàng', '#FFCC00', 1),
('Cam', '#FF6600', 1),
('Tím', '#9900CC', 1),
('Hồng', '#FF66B2', 1),
('Nâu', '#8B4513', 1),
('Xám', '#808080', 1),
('Bạc', '#C0C0C0', 1),
('Vàng gold', '#DAA520', 1);
```

## 4. Đảm bảo ProductVariant có color_id

Trong bảng `product_variants`, đảm bảo có cột `color_id` và liên kết với bảng `colors`:

```php
// Trong migration của product_variants
Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->foreignId('color_id')->nullable()->constrained()->onDelete('set null');
    $table->string('size_name')->nullable();
    $table->decimal('rating', 2, 1)->default(0);
    $table->integer('stock')->default(0);
    $table->decimal('price', 10, 0)->default(0);
    $table->timestamps();
});
```

Sau khi cập nhật các file trên, chức năng lọc màu sắc sẽ hiển thị đúng các màu có trong sản phẩm với các dot màu và tên màu tương ứng.


## User:
07/11/2026 15:45

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    /**
     * Hiển thị tất cả sản phẩm
     */
    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        // Áp dụng bộ lọc
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc
        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo một danh mục
     */
    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm từ nhiều danh mục
     */
    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo thương hiệu
     */
    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Áp dụng các bộ lọc vào query
     */
    private function applyFilters($query, Request $request)
    {
        // Lọc theo thương hiệu
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        // Lọc theo chất liệu
        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        // Lọc theo danh mục
        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        // Lọc theo màu sắc (thông qua variants)
        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        // Lọc theo khoảng giá
        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        // Sắp xếp
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    /**
     * Lấy dữ liệu cho bộ lọc
     */
    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel && $productModel->material) {
                $materials->push($productModel->material);
            }
        }
        $materials = $materials->unique()->filter()->values();

        // Lấy danh sách màu sắc - SỬA LỖI Ở ĐÂY
        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        // Đảm bảo colors luôn là array, không bị null
        if ($colors->isEmpty()) {
            $colors = collect(); // Trả về collection rỗng thay vì null
        }

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $minPrice = $productModel->variants->min('price') ?? 0;
                $maxPrice = $productModel->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        // Đảm bảo minPrice < maxPrice
        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors, // Luôn là Collection, không null
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $variantColors = $productModel->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColors);
            }
        }
        return $colorIds->unique()->filter()->values();
    }


    /**
     * Map sản phẩm thành định dạng frontend
     */
    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $colorIds = $colorIds->merge($productModel->variants->pluck('color_id'));
            }
        }
        return $colorIds->unique()->filter()->values();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'image',
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Quan hệ với sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Quan hệ cha (danh mục cha)
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Quan hệ con (danh mục con)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Scope lấy danh mục đang hoạt động
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope lấy danh mục cha
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessor lấy đường dẫn đầy đủ
    public function getFullPathAttribute()
    {
        $path = $this->name;
        $parent = $this->parent;
        while ($parent) {
            $path = $parent->name . ' > ' . $path;
            $parent = $parent->parent;
        }
        return $path;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'material',
        'image_url', 'description', 'thumbnail', 'is_featured',
        'is_preorder', 'status'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_preorder' => 'boolean',
        'image_url' => 'array',
        'status' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color_id', 'size_name', 'rating', 'stock', 'price'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'price' => 'decimal:0',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }


    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products');
    }

    public function quoteRequestDetails()
    {
        return $this->hasMany(QuoteRequestDetail::class);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    /**
     * Hiển thị tất cả sản phẩm
     */
    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        // Áp dụng bộ lọc
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc
        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo một danh mục
     */
    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm từ nhiều danh mục
     */
    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo thương hiệu
     */
    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Áp dụng các bộ lọc vào query
     */
    private function applyFilters($query, Request $request)
    {
        // Lọc theo thương hiệu
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        // Lọc theo chất liệu
        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        // Lọc theo danh mục
        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        // Lọc theo màu sắc (thông qua variants)
        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        // Lọc theo khoảng giá
        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        // Sắp xếp
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    /**
     * Lấy dữ liệu cho bộ lọc
     */
    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel && $productModel->material) {
                $materials->push($productModel->material);
            }
        }
        $materials = $materials->unique()->filter()->values();

        // Lấy danh sách màu sắc - SỬA LỖI Ở ĐÂY
        $colorIds = $this->getColorIdsFromProducts($products);
        $colors = Color::whereIn('id', $colorIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        // Đảm bảo colors luôn là array, không bị null
        if ($colors->isEmpty()) {
            $colors = collect(); // Trả về collection rỗng thay vì null
        }

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $minPrice = $productModel->variants->min('price') ?? 0;
                $maxPrice = $productModel->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        // Đảm bảo minPrice < maxPrice
        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors, // Luôn là Collection, không null
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $variantColors = $productModel->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColors);
            }
        }
        return $colorIds->unique()->filter()->values();
    }


    /**
     * Map sản phẩm thành định dạng frontend
     */
    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
        ];
    }

    /**
     * Lấy danh sách color_id từ các sản phẩm đã map
     */
    private function getColorIdsFromProducts($products)
    {
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $colorIds = $colorIds->merge($productModel->variants->pluck('color_id'));
            }
        }
        return $colorIds->unique()->filter()->values();
    }
}
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc - SỬA LỖI HIỂN THỊ -->
          <div v-if="filters.colors && filters.colors.length">
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            <div class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative group"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name || 'Màu sắc'"
                @click="toggleColor(color.id)"
              >
                <!-- Hiển thị check mark khi được chọn -->
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <!-- Hiển thị tên màu đã chọn -->
            <div v-if="selectedColors.length > 0" class="mt-2 flex flex-wrap gap-1">
              <span v-for="colorId in selectedColors" :key="colorId" class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">
                <span 
                  class="w-2 h-2 rounded-full inline-block"
                  :style="{ backgroundColor: getColorCode(colorId) }"
                ></span>
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Từ</label>
                  <input 
                    type="number" 
                    v-model.number="priceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                    @input="applyFilters"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="priceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
                    @input="applyFilters"
                  >
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                :min="filters.minPrice || 0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm text-gray-500">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  // Set price range
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

/* Style cho màu sắc */
.color-dot {
  transition: all 0.2s ease;
}
.color-dot:hover {
  transform: scale(1.1);
}

/* Fix input number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>


## Assistant:
07/11/2026 15:45

Tôi thấy bạn đã có đầy đủ code. Vấn đề màu sắc hiển thị "Không giá" là do dữ liệu màu sắc không được lấy đúng từ database. Dưới đây là các bước fix:

## 1. Kiểm tra và sửa lỗi trong CategoryController.php

Vấn đề chính là hàm `getColorIdsFromProducts` bị trùng lặp và không lấy đúng dữ liệu. Hãy sửa lại:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    /**
     * Hiển thị tất cả sản phẩm
     */
    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        // Áp dụng bộ lọc
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc
        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo một danh mục
     */
    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm từ nhiều danh mục
     */
    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Hiển thị sản phẩm theo thương hiệu
     */
    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    /**
     * Áp dụng các bộ lọc vào query
     */
    private function applyFilters($query, Request $request)
    {
        // Lọc theo thương hiệu
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        // Lọc theo chất liệu
        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        // Lọc theo danh mục
        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        // Lọc theo màu sắc (thông qua variants)
        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        // Lọc theo khoảng giá
        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        // Sắp xếp
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    /**
     * Lấy dữ liệu cho bộ lọc
     */
    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel && $productModel->material) {
                $materials->push($productModel->material);
            }
        }
        $materials = $materials->unique()->filter()->values();

        // Lấy danh sách màu sắc - QUAN TRỌNG: SỬA LỖI Ở ĐÂY
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                // Lấy tất cả color_id từ variants của sản phẩm
                $variantColorIds = $productModel->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColorIds);
            }
        }
        $colorIds = $colorIds->unique()->filter()->values();
        
        // Lấy thông tin màu sắc từ database
        $colors = Color::whereIn('id', $colorIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $minPrice = $productModel->variants->min('price') ?? 0;
                $maxPrice = $productModel->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        // Đảm bảo minPrice < maxPrice
        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors,
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    /**
     * Map sản phẩm thành định dạng frontend
     */
    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
        ];
    }
}
```

## 2. Kiểm tra dữ liệu trong database

Chạy các câu lệnh SQL sau để kiểm tra:

```sql
-- Kiểm tra bảng colors có dữ liệu không
SELECT * FROM colors;

-- Kiểm tra product_variants có color_id không
SELECT * FROM product_variants WHERE color_id IS NOT NULL;

-- Kiểm tra kết nối giữa variants và colors
SELECT 
    p.name as product_name,
    pv.id as variant_id,
    pv.color_id,
    c.name as color_name,
    c.code as color_code
FROM product_variants pv
LEFT JOIN products p ON p.id = pv.product_id
LEFT JOIN colors c ON c.id = pv.color_id
WHERE pv.color_id IS NOT NULL;
```

## 3. Thêm dữ liệu mẫu cho colors (nếu chưa có)

```sql
INSERT INTO colors (name, code, is_active, created_at, updated_at) VALUES
('Đen', '#000000', 1, NOW(), NOW()),
('Trắng', '#FFFFFF', 1, NOW(), NOW()),
('Đỏ', '#FF0000', 1, NOW(), NOW()),
('Xanh dương', '#0066CC', 1, NOW(), NOW()),
('Xanh lá', '#00CC66', 1, NOW(), NOW()),
('Vàng', '#FFCC00', 1, NOW(), NOW()),
('Cam', '#FF6600', 1, NOW(), NOW()),
('Tím', '#9900CC', 1, NOW(), NOW()),
('Hồng', '#FF66B2', 1, NOW(), NOW()),
('Nâu', '#8B4513', 1, NOW(), NOW()),
('Xám', '#808080', 1, NOW(), NOW()),
('Bạc', '#C0C0C0', 1, NOW(), NOW()),
('Vàng gold', '#DAA520', 1, NOW(), NOW());
```

## 4. Cập nhật ProductVariant Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color_id', 'size_name', 'rating', 'stock', 'price'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'price' => 'decimal:0',
        'stock' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    // Thêm scope để lấy variant có màu
    public function scopeHasColor($query)
    {
        return $query->whereNotNull('color_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products');
    }

    public function quoteRequestDetails()
    {
        return $this->hasMany(QuoteRequestDetail::class);
    }
}
```

## 5. Debug trong Vue component

Thêm đoạn debug để kiểm tra dữ liệu:

```vue
<script setup>
// ... code hiện tại

// Thêm vào onMounted để debug
onMounted(() => {
  console.log('🔍 Filters data:', props.filters)
  console.log('🎨 Colors:', props.filters.colors)
  console.log('📦 Products:', props.products)
  
  // Kiểm tra từng sản phẩm có color không
  props.products.forEach(p => {
    console.log(`Product ${p.id}:`, p)
  })
})
</script>
```

## 6. Kiểm tra lại

Sau khi cập nhật, hãy:

1. Clear cache: `php artisan cache:clear` và `php artisan view:clear`
2. Refresh trang danh mục
3. Kiểm tra console browser để xem dữ liệu màu sắc

Nếu vẫn không hiển thị, hãy kiểm tra:
- Bảng `colors` có dữ liệu không
- Bảng `product_variants` có `color_id` không
- Quan hệ `belongsTo(Color::class)` trong `ProductVariant` đã đúng chưa


## User:
07/11/2026 15:48

![image.png](data:image/webp;base64,UklGRopSAQBXRUJQVlA4IH5SAQDw7gGdASpJBNcCPlEoj0cjoiEhIDEIsHAKCWdu29z+Z/2/+vdF82/Lf6X/Y/8l/mv65///937yXU31o/U/7j/hP7h/Wv/5/qfLf2w/pv7b/of7d/o///z7eTf03+/f4n/H/2X9+PoF/HP5N/S/7x+yH9d////G8j39Z56nRP9R/bvYF/D/5P/Yf7j/lv85++n+882P9Z/tn98/P/4J/k/9K/qf92/Xb+nf///z/oB/FP5f/ZP7L/hv9H/cP///vPjr/yP65/oPVK9v/1P+p/z/uB/kH9I/xf9o/bT9//+R94P3v/Z/5f/Pf8D9///j8U/zD+/f7H/I/6D/o/v//8/wR/Hv5x/g/7h/jP+X+///t+5H/xe7v+0n/a9yz+Af1n/a/n58vMifxjJgdQfDH64f2v+r66DTjmb9wP6rpD7QOMp+K/zz8a/7T+0flAeIN05/kv41/QB/CP4n/HPw+/pP+J/tH/5+QDtG/2z+jfiB9AH8J/iH88/rH6Z/2L8//lB/Sfwz9wP1A/s34q/QB/Ef5F/RvyD/sX/5+EH8l9QL5A/kXuAfw7+Gf0j+p/sB/Xf+79S/2b+tf1X9kf65///dx+Xf1H+y/zf+6f37+Yf//7Bfw/+Of0/+v/3b/K/17/8f8D7gPYf/Yz/M/1X4Ev0L/rf9q/uv5//+/5nCTvDnUf6b8t+lu5k8ifFf+s+gD2m6s83vln/E/m9/ePoH/w/7H+WHyO/x36U+4B+m/9v/ln46/Gp6w/3E9QH8c/pv+Y/wH71/Kt/mf+j/rPcN+uX/V/xX/V+QD9Wfv/+X7/Hewv/Uv7l/5PcA/kv9F/6n5//F5/5/9b+//0N/1r/W/+P/U/v/9BP9C/v//f/Pr5AP3//9/uAf+P1ANZLXW/zbOAf1C3cPdr/Y+lZnNeYfYJ/Tr/VdebZWerj4anrL4uKp/T6JX3zpvvnTffOm++dN986b75033zpvvnTffOm2VTVclqtlgp5eDIPcvBtXtjRWtPLwbWnl4NrTy8G1p5eDa08vBtaeXg2tPLwbWnl4NrTy8G1p5eDa08vBtaeXgquT4Dd8Bu+A3fAbvgN3wG74Dd8Bu+A3fAbvgN3wG74Dd8Bu+A3fAbvgN3wG74Dd8Bu+A3fAbn6UVS17LLXssteyy17LLXssteyy17LLXssteyy17LLXssteyy17LLXssteyy17LLXssteyy17LLXssteyy1g6uS1XJarktVyWq5LVclquS1XJarktVyWq5LVclquS1XJarktVyWq5LVclquS1XJarktVyWq5LVclqq7QmlIViarktVyWq5LVclquS1XJarktVyWq5LVclquS1XJarktVyWq5LR6Um/s6eXg2tPLweaPgNtij0hBCCEEII1N4tYqZo1e0jtDp7yJKhAHsbehkdFVZ5eRPu1FAubOF2g4D/hA5vhh/L//YnPrqFf9orks0kbbPRooLhffABjma2jt8ByEBWsFYgYt61GCHwG74Czp25L8arktHch63C5WNFOu9vXe3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXe3rvb13t67sx6uXhkdY6584Ing2tPLwbWnluJgIOy0M4Q60wMX4fnsC/hpuF2jXvkWBFTuWWvZZa9kSVF1+TC7S1syz3sG8WiaQp9BxggGd0qB4OLuHJQ5D4Dd8Bu+A3VjikWOSjutsR+3y3rvb13t6729d7eu9vXdkAy/6YAE/gSWq5LVclqy1xj2mxiE4iovViXqxL1Yl6sS7/8FgYhNbcxbcW9d7eu9vXe3rvb13t67sGpJiE+m5nY6SovViXqxMTX2ZilKJhYav2P87/Efsf53+I/Y/zv8R+x/nf4j9hvTYk/Iz2hKW1qMt7N8CDMUpUEBJ7Woy3s3wIMxSlQPEXXJarOcjODIcwoACL1Yl6sS9cGc9Hb0NtmzhIT1oFFWfCzd3LiDgsFq4AzsGaUvbxj8+jrQGiYYDd8BGC5RvJdbyWq5LVclqy14ncS9RvqSYXegITrt/nOxL1QfTT+OnhGq5AlArH9tkIVI2/yduJeqD6fDpKi9WJdleu9vXe3rvb13tvKZ8pGbhVBh61tH894X8PWto/nvC/hq6OouzEVGFbFRdmIqMK2KjCtiowrYqLsxFRdmIqOZKhBB61tH894X7aFmZt5NH36PlIs736jmSoQQetbR/PeF+2gze6scXL0Uu1II7tSCO7Ugju1Gil2pBHdqNFLtSCO7Ugju1II7tSCO7UgmG+iZho713toIkxv0nDEAZVC5dGuS1XJarktVyWnYOPe19HHva+jj3tfRx72vo497Eelh4du6ufi3dfPx7uvn493Xz8e7r3BX0SovVh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)

chưa được đổi , chỉnh lại và đưa code hoàn chỉnh cho tôi


## Assistant:
07/11/2026 15:48

Tôi thấy lỗi là màu sắc không hiển thị. Vấn đề là trong database của bạn chưa có dữ liệu màu sắc hoặc chưa gán màu cho sản phẩm. Dưới đây là code hoàn chỉnh và cách fix:

## 1. Tạo Migration cho bảng colors (nếu chưa có)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Thêm color_id vào bảng product_variants nếu chưa có
        if (!Schema::hasColumn('product_variants', 'color_id')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->foreignId('color_id')->nullable()->constrained()->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('colors');
    }
};
```

## 2. Chạy seeder để thêm dữ liệu màu sắc

Tạo file `database/seeders/ColorSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Color;

class ColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            ['name' => 'Đen', 'code' => '#000000', 'slug' => 'den', 'is_active' => true],
            ['name' => 'Trắng', 'code' => '#FFFFFF', 'slug' => 'trang', 'is_active' => true],
            ['name' => 'Đỏ', 'code' => '#FF0000', 'slug' => 'do', 'is_active' => true],
            ['name' => 'Xanh dương', 'code' => '#0066CC', 'slug' => 'xanh-duong', 'is_active' => true],
            ['name' => 'Xanh lá', 'code' => '#00CC66', 'slug' => 'xanh-la', 'is_active' => true],
            ['name' => 'Vàng', 'code' => '#FFCC00', 'slug' => 'vang', 'is_active' => true],
            ['name' => 'Cam', 'code' => '#FF6600', 'slug' => 'cam', 'is_active' => true],
            ['name' => 'Tím', 'code' => '#9900CC', 'slug' => 'tim', 'is_active' => true],
            ['name' => 'Hồng', 'code' => '#FF66B2', 'slug' => 'hong', 'is_active' => true],
            ['name' => 'Nâu', 'code' => '#8B4513', 'slug' => 'nau', 'is_active' => true],
            ['name' => 'Xám', 'code' => '#808080', 'slug' => 'xam', 'is_active' => true],
            ['name' => 'Bạc', 'code' => '#C0C0C0', 'slug' => 'bac', 'is_active' => true],
            ['name' => 'Vàng gold', 'code' => '#DAA520', 'slug' => 'vang-gold', 'is_active' => true],
        ];

        foreach ($colors as $color) {
            Color::create($color);
        }
    }
}
```

Chạy seeder:
```bash
php artisan db:seed --class=ColorSeeder
```

## 3. CategoryController.php hoàn chỉnh

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get()->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $products,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel && $productModel->material) {
                $materials->push($productModel->material);
            }
        }
        $materials = $materials->unique()->filter()->values();

        // Lấy danh sách màu sắc - QUAN TRỌNG: LẤY TỪ DATABASE
        $colorIds = collect();
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $variantColors = $productModel->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColors);
            }
        }
        $colorIds = $colorIds->unique()->filter()->values();
        
        // Lấy thông tin màu sắc từ database
        $colors = collect();
        if ($colorIds->isNotEmpty()) {
            $colors = Color::whereIn('id', $colorIds)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        }

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            $productModel = Product::find($product['id']);
            if ($productModel) {
                $minPrice = $productModel->variants->min('price') ?? 0;
                $maxPrice = $productModel->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors,
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
        ];
    }
}
```

## 4. Category.vue hoàn chỉnh (phần hiển thị màu sắc)

```vue
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc - HIỂN THỊ MÀU SẮC -->
          <div>
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            
            <!-- Hiển thị nếu có màu -->
            <div v-if="filters.colors && filters.colors.length > 0" class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name || 'Màu sắc'"
                @click="toggleColor(color.id)"
              >
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <!-- Hiển thị nếu không có màu -->
            <div v-else class="text-gray-400 text-sm py-2">
              Không có màu sắc nào
            </div>
            
            <!-- Hiển thị tên màu đã chọn -->
            <div v-if="selectedColors.length > 0" class="mt-2 flex flex-wrap gap-1">
              <span v-for="colorId in selectedColors" :key="colorId" class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">
                <span 
                  class="w-2 h-2 rounded-full inline-block"
                  :style="{ backgroundColor: getColorCode(colorId) }"
                ></span>
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Từ</label>
                  <input 
                    type="number" 
                    v-model.number="priceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                    @input="applyFilters"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="priceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
                    @input="applyFilters"
                  >
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                :min="filters.minPrice || 0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm text-gray-500">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  console.log('🔍 Filters data:', props.filters)
  console.log('🎨 Colors:', props.filters.colors)
  
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})

// Watch để debug
watch(() => props.filters, (newVal) => {
  console.log('🔄 Filters updated:', newVal)
}, { deep: true })
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>
```

## 5. Chạy các lệnh sau:

```bash
# Tạo migration cho colors
php artisan make:migration create_colors_table

# Chạy migration
php artisan migrate

# Chạy seeder
php artisan db:seed --class=ColorSeeder

# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## 6. Gán màu cho sản phẩm hiện có

Nếu bạn đã có sản phẩm nhưng chưa có màu, hãy chạy lệnh SQL để gán màu mặc định:

```sql
-- Gán màu đen (id=1) cho tất cả variants chưa có màu
UPDATE product_variants 
SET color_id = 1 
WHERE color_id IS NULL;
```

Sau khi thực hiện các bước trên, màu sắc sẽ hiển thị đúng trong bộ lọc.


## User:
07/11/2026 15:53

[plugin:vite:vue] Single file component can contain only one <template> element
D:/LuanVanTotNghiep/resources/js/Pages/Web/Category.vue

?plugins=forms,container-queries:64 cdn.tailwindcss.com should not be used in production. To use Tailwind CSS in production, install it as a PostCSS plugin or use the Tailwind CLI: https://tailwindcss.com/docs/installation
(anonymous) @ ?plugins=forms,container-queries:64
Category.vue:1  Failed to load resource: the server responded with a status of 500 (Internal Server Error)
client:929 [vite] Failed to reload /resources/js/Pages/Web/Category.vue. This could be due to syntax errors or importing non-existent modules. (see errors above)

tôi sẽ đưa thêm code màu  sắc cho bạn nắm thông tin:

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::orderBy('id', 'desc')->get();
        return Inertia::render('Admin/Colors', [
            'colors' => $colors
        ]);
    }

    public function getColors()
    {
        try {
            $colors = Color::orderBy('id', 'desc')->get();
            return response()->json($colors);
        } catch (\Exception $e) {
            Log::error('Lỗi getColors: ' . $e->getMessage());
            return response()->json([
                'error' => 'Không thể tải danh sách màu sắc. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ], [
                'code.regex' => 'Mã màu phải là mã hex hợp lệ (ví dụ: #FF0000 hoặc #F00)',
                'code.max'   => 'Mã màu không được vượt quá 20 ký tự.',
                'name.max'   => 'Tên màu không được vượt quá 255 ký tự.'
            ]);

            // Xử lý logic nhập liệu
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $validated['code'] = $code;
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Mã màu này chưa có tên. Vui lòng nhập tên màu!'
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Chuẩn hóa tên (viết hoa chữ đầu)
            $validated['name'] = $this->capitalizeName($validated['name']);

            // Kiểm tra trùng tên
            if (Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã
            if (Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color = Color::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Thêm màu sắc thành công!',
                'data' => $color
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi store color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi thêm màu. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $validated = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/']
            ], [
                'code.regex' => 'Mã màu phải là mã hex hợp lệ (ví dụ: #FF0000 hoặc #F00)',
                'code.max'   => 'Mã màu không được vượt quá 20 ký tự.',
                'name.max'   => 'Tên màu không được vượt quá 255 ký tự.'
            ]);

            // Xử lý logic nhập liệu
            if (!empty($validated['name']) && !empty($validated['code'])) {
                $validated['code'] = $this->normalizeHexCode($validated['code']);
            } elseif (!empty($validated['name']) && empty($validated['code'])) {
                $validated['code'] = $this->getColorCodeFromName($validated['name']);
            } elseif (!empty($validated['code']) && empty($validated['name'])) {
                $code = $this->normalizeHexCode($validated['code']);
                $generatedName = $this->getColorNameFromCode($code);
                if ($generatedName && $generatedName !== 'Màu khác') {
                    $validated['name'] = $generatedName;
                } else {
                    $validated['name'] = $color->name; 
                }
                $validated['code'] = $code;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng nhập tên màu hoặc mã hex!'
                ], 422);
            }

            // Chuẩn hóa tên (viết hoa chữ đầu)
            $validated['name'] = $this->capitalizeName($validated['name']);

            // Kiểm tra trùng tên (không tính chính nó)
            if ($validated['name'] !== $color->name && Color::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên màu "' . $validated['name'] . '" đã tồn tại!'
                ], 422);
            }

            // Kiểm tra trùng mã (không tính chính nó)
            if ($validated['code'] !== $color->code && Color::where('code', $validated['code'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã màu "' . $validated['code'] . '" đã tồn tại!'
                ], 422);
            }

            $color->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật màu sắc thành công!',
                'data' => $color
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi update color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật màu. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            
            $variantCount = $color->productVariants()->count();
            
            if ($variantCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa màu này vì đang có ' . $variantCount . ' sản phẩm đang sử dụng!'
                ], 400);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa màu sắc thành công!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi delete color: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi xóa màu. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    // Hàm chuẩn hóa tên: viết hoa chữ cái đầu mỗi từ
    private function capitalizeName($name)
    {
        if (empty($name)) return '';
        $words = explode(' ', trim($name));
        $capitalized = array_map(function($word) {
            return mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        }, $words);
        return implode(' ', $capitalized);
    }

    private function normalizeHexCode($code)
    {
        if (empty($code)) return '#CCCCCC';
        $code = strtoupper(trim($code));
        if (preg_match('/^#([A-F0-9]{3})$/', $code, $matches)) {
            $r = $matches[1][0];
            $g = $matches[1][1];
            $b = $matches[1][2];
            return '#' . $r . $r . $g . $g . $b . $b;
        }
        if (preg_match('/^#([A-F0-9]{6})$/', $code)) {
            return $code;
        }
        return '#CCCCCC';
    }

    private function getColorCodeFromName($name)
    {
        if (empty($name)) return '#CCCCCC';
        $colorMap = [
            'đen' => '#000000', 'den' => '#000000', 'black' => '#000000',
            'trắng' => '#FFFFFF', 'trang' => '#FFFFFF', 'white' => '#FFFFFF',
            'xám' => '#808080', 'xam' => '#808080', 'gray' => '#808080',
            'đỏ' => '#FF0000', 'do' => '#FF0000', 'red' => '#FF0000',
            'hồng' => '#FFC0CB', 'hong' => '#FFC0CB', 'pink' => '#FFC0CB',
            'cam' => '#FFA500', 'orange' => '#FFA500',
            'vàng' => '#FFD700', 'vang' => '#FFD700', 'yellow' => '#FFD700',
            'xanh lá' => '#008000', 'xanhla' => '#008000', 'green' => '#008000',
            'xanh dương' => '#0000FF', 'xanhduong' => '#0000FF', 'blue' => '#0000FF',
            'xanh navy' => '#000080', 'xanhnavy' => '#000080', 'navy' => '#000080',
            'tím' => '#800080', 'tim' => '#800080', 'purple' => '#800080',
            'nâu' => '#8B4513', 'nau' => '#8B4513', 'brown' => '#8B4513',
            'be' => '#F5F5DC', 'beige' => '#F5F5DC',
            'bạc' => '#C0C0C0', 'bac' => '#C0C0C0', 'silver' => '#C0C0C0'
        ];
        $key = strtolower(trim($name));
        return $colorMap[$key] ?? '#CCCCCC';
    }

    private function getColorNameFromCode($code)
    {
        if (empty($code)) return 'Màu khác';
        $code = strtoupper(trim($code));
        $codeMap = [
            '#000000' => 'Đen',
            '#FFFFFF' => 'Trắng',
            '#808080' => 'Xám',
            '#FF0000' => 'Đỏ',
            '#FFC0CB' => 'Hồng',
            '#FFA500' => 'Cam',
            '#FFD700' => 'Vàng',
            '#008000' => 'Xanh lá',
            '#0000FF' => 'Xanh dương',
            '#000080' => 'Xanh navy',
            '#800080' => 'Tím',
            '#8B4513' => 'Nâu',
            '#F5F5DC' => 'Be',
            '#C0C0C0' => 'Bạc',
            '#CCCCCC' => 'Xám nhạt',
            '#6200EE' => 'Tím đậm',
            '#9C27B0' => 'Tím hồng',
            '#03DAC6' => 'Xanh ngọc',
            '#018786' => 'Xanh rêu',
            '#490C42' => 'Tím than',
            '#FF5733' => 'Cam đỏ',
            '#33FF57' => 'Xanh lá sáng',
            '#3357FF' => 'Xanh dương đậm',
            '#F333FF' => 'Hồng tím',
            '#FF33F3' => 'Hồng cánh sen',
            '#E91E63' => 'Hồng đậm',
            '#9C27B0' => 'Tím',
            '#673AB7' => 'Tím đậm',
            '#3F51B5' => 'Xanh dương',
            '#2196F3' => 'Xanh dương sáng',
            '#00BCD4' => 'Xanh cyan',
            '#009688' => 'Xanh lá cây',
            '#4CAF50' => 'Xanh lá',
            '#8BC34A' => 'Xanh lá nhạt',
            '#CDDC39' => 'Xanh vàng',
            '#FFEB3B' => 'Vàng chanh',
            '#FFC107' => 'Vàng cam',
            '#FF9800' => 'Cam',
            '#FF5722' => 'Cam đỏ',
            '#795548' => 'Nâu',
            '#9E9E9E' => 'Xám',
            '#607D8B' => 'Xám xanh'
        ];
        return $codeMap[$code] ?? 'Màu khác';
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ColorController extends Controller
{
    //
}
<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'color_id');
    }
}
<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

// State
const colors = ref(props.colors)
const search = ref('')
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

// Pagination - 5 items per page
const currentPage = ref(1)
const perPage = ref(5)

const form = ref({
    id: null,
    name: '',
    code: ''
})

// Mã hex hiển thị dưới picker
const displayCode = ref('#CCCCCC')

// Computed: lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})

// Sắp xếp theo ID giảm dần
const sortedColors = computed(() => {
    return [...filteredColors.value].sort((a, b) => b.id - a.id)
})

// Pagination
const paginatedColors = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return sortedColors.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(sortedColors.value.length / perPage.value)
})

// Hiển thị số trang (tối đa 5 trang)
const displayedPages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    const maxDisplay = 5
    
    if (total <= maxDisplay) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }
    
    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + maxDisplay - 1)
    
    if (end - start < maxDisplay - 1) {
        start = Math.max(1, end - maxDisplay + 1)
    }
    
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

// Reset về trang 1 khi tìm kiếm
watch(search, () => {
    currentPage.value = 1
})

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

// Cập nhật mã hiển thị
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        displayCode.value = '#CCCCCC'
    }
}

// Khi thay đổi picker
const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            await fetchColors()
            showDeleteModal.value = false
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    validationErrors.value = {}
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm màu sắc
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên màu hoặc mã hex..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <!-- Bảng -->
            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left p-4 font-semibold text-gray-700 w-16 whitespace-nowrap">STT</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Màu sắc</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Mã hex</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Ngày tạo</th>
                                <th class="text-center p-4 font-semibold text-gray-700 w-32 whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(color, index) in paginatedColors" 
                                :key="color.id" 
                                class="border-b border-gray-100 hover:bg-gray-50 transition"
                            >
                                <td class="p-4 text-gray-500 text-sm whitespace-nowrap">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div 
                                            class="w-8 h-8 rounded border border-gray-300 shadow-sm flex-shrink-0" 
                                            :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"
                                        ></div>
                                        <span class="font-medium text-gray-700">{{ color.name }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500 text-sm font-mono whitespace-nowrap">{{ color.code || '—' }}</td>
                                <td class="p-4 text-gray-500 text-sm whitespace-nowrap">{{ formatDate(color.created_at) }}</td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button 
                                            @click="openEditModal(color)" 
                                            class="px-3 py-1.5 text-xs text-green-600 hover:bg-green-100 rounded-lg transition-colors font-medium"
                                        >
                                            Sửa
                                        </button>
                                        <button 
                                            @click="confirmDelete(color)" 
                                            class="px-3 py-1.5 text-xs text-red-600 hover:bg-red-100 rounded-lg transition-colors font-medium"
                                        >
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedColors.length === 0 && !isLoading">
                                <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer với phân trang căn giữa -->
                <div class="p-4 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedColors.length }} / {{ sortedColors.length }} màu sắc
                    </div>
                    
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2">
                        <button
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ◄
                        </button>
                        
                        <div class="flex gap-1">
                            <button
                                v-for="page in displayedPages"
                                :key="page"
                                @click="currentPage = page"
                                class="px-3.5 py-1.5 text-sm rounded-lg transition-colors font-medium"
                                :class="currentPage === page ? 'bg-orange-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ►
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary transition"
                            >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isSaving">
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
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin { animation: spin 1s linear infinite; }
</style>


## Assistant:
07/11/2026 15:53

Lỗi của bạn là do file `Category.vue` có **2 thẻ `<template>`**. Trong Vue, mỗi file component chỉ được phép có **DUY NHẤT 1 thẻ `<template>`**.

Đây là code hoàn chỉnh đã sửa lỗi cho `Category.vue`:

```vue
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc -->
          <div>
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            
            <!-- Hiển thị nếu có màu -->
            <div v-if="filters.colors && filters.colors.length > 0" class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name || 'Màu sắc'"
                @click="toggleColor(color.id)"
              >
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <!-- Hiển thị nếu không có màu -->
            <div v-else class="text-gray-400 text-sm py-2">
              Không có màu sắc nào
            </div>
            
            <!-- Hiển thị tên màu đã chọn -->
            <div v-if="selectedColors.length > 0" class="mt-2 flex flex-wrap gap-1">
              <span v-for="colorId in selectedColors" :key="colorId" class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">
                <span 
                  class="w-2 h-2 rounded-full inline-block"
                  :style="{ backgroundColor: getColorCode(colorId) }"
                ></span>
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Từ</label>
                  <input 
                    type="number" 
                    v-model.number="priceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                    @input="applyFilters"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="priceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
                    @input="applyFilters"
                  >
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                :min="filters.minPrice || 0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm text-gray-500">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  console.log('🔍 Filters data:', props.filters)
  console.log('🎨 Colors:', props.filters.colors)
  
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})

// Watch để debug
watch(() => props.filters, (newVal) => {
  console.log('🔄 Filters updated:', newVal)
}, { deep: true })
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>
```

## 🔑 Điểm quan trọng:

1. **Chỉ có 1 thẻ `<template>`** - đây là lỗi chính gây ra 500 error
2. **Không có thẻ `<template>` thứ 2** - tôi đã gộp tất cả vào một template duy nhất
3. **Cấu trúc đúng**: `template` → `div` → nội dung
4. **Script setup** đúng chuẩn Vue 3 Composition API

## 📋 Các bước chạy lại:

```bash
# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Restart Vite (nếu đang chạy)
npm run dev
```

Sau khi sửa, trang sẽ hoạt động bình thường và hiển thị màu sắc trong bộ lọc.


## User:
07/11/2026 15:57

tôi thấy vẫn chưa hiển thị được màu sắc lên , nút tròn tôi muốn hiện màu sắc chứ khôg phải chỉ để tạm màu xám


## Assistant:
07/11/2026 15:57

Tôi thấy vấn đề là dữ liệu màu sắc không được truyền từ Controller sang View. Hãy cùng fix từng bước:

## 1. Kiểm tra và sửa CategoryController.php

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Các slug đặc biệt: hiển thị tất cả sản phẩm
        if (in_array($slug, ['san-pham', 'danh-muc', 'tat-ca', 'thuong-hieu'])) {
            return $this->showAllProducts($request, $slug);
        }

        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            return $this->showProductsByCategory($request, $slug, $category);
        }

        // Tìm kiếm danh mục theo từ khóa (fallback)
        $keywords = explode('-', $slug);
        $query = Category::query();
        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw) {
                $query->orWhere('slug', 'like', "%{$kw}%")
                      ->orWhere('name', 'like', "%{$kw}%");
            }
        }
        $matchingCategories = $query->get();
        if ($matchingCategories->isNotEmpty()) {
            return $this->showProductsByMultipleCategories($request, $slug, $matchingCategories);
        }

        // Tìm thương hiệu
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $this->showProductsByBrand($request, $slug, $brand);
        }

        abort(404, 'Không tìm thấy danh mục hoặc thương hiệu phù hợp');
    }

    private function showAllProducts(Request $request, $slug)
    {
        $categoryName = ($slug === 'san-pham') ? 'Sản phẩm' : 'Tất cả sản phẩm';
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        // Map products
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc - TRUYỀN TRỰC TIẾP products thay vì mappedProducts
        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByCategory(Request $request, $slug, $category)
    {
        $categoryName = $category->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByMultipleCategories(Request $request, $slug, $categories)
    {
        $keywords = explode('-', $slug);
        $displayName = collect($keywords)->map(fn($kw) => ucfirst($kw))->implode(' & ');
        $categoryIds = $categories->pluck('id')->toArray();

        $query = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $displayName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function showProductsByBrand(Request $request, $slug, $brand)
    {
        $categoryName = $brand->name;
        
        $query = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1);
        
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        $filterData = $this->getFilterData($products);

        return Inertia::render('Web/Category', [
            'slug' => $slug,
            'categoryName' => $categoryName,
            'products' => $mappedProducts,
            'filters' => $filterData,
            'selectedFilters' => $request->all(),
        ]);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->has('brands') && !empty($request->brands)) {
            $brandIds = explode(',', $request->brands);
            $query->whereIn('brand_id', $brandIds);
        }

        if ($request->has('materials') && !empty($request->materials)) {
            $materials = explode(',', $request->materials);
            $query->whereIn('material', $materials);
        }

        if ($request->has('categories') && !empty($request->categories)) {
            $categoryIds = explode(',', $request->categories);
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->has('colors') && !empty($request->colors)) {
            $colorIds = explode(',', $request->colors);
            $query->whereHas('variants', function($q) use ($colorIds) {
                $q->whereIn('color_id', $colorIds);
            });
        }

        if ($request->has('price_min') && $request->has('price_max')) {
            $minPrice = (int) $request->price_min;
            $maxPrice = (int) $request->price_max;
            
            $query->whereHas('variants', function($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_variants.product_id = products.id) ASC');
                    break;
                case 'price_desc':
                    $query->orderByRaw('(SELECT MAX(price) FROM product_variants WHERE product_variants.product_id = products.id) DESC');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('views', 'desc');
                    break;
                default:
                    $query->latest();
            }
        }

        return $query;
    }

    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = $products->pluck('material')->unique()->filter()->values();

        // Lấy danh sách màu sắc - CÁCH LẤY MỚI
        $colorIds = collect();
        foreach ($products as $product) {
            if ($product->variants) {
                $variantColorIds = $product->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColorIds);
            }
        }
        $colorIds = $colorIds->unique()->filter()->values();
        
        // Lấy thông tin màu sắc từ database
        $colors = collect();
        if ($colorIds->isNotEmpty()) {
            $colors = Color::whereIn('id', $colorIds)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        }

        // Lấy danh sách danh mục
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Lấy khoảng giá
        $prices = [];
        foreach ($products as $product) {
            if ($product->variants) {
                $minPrice = $product->variants->min('price') ?? 0;
                $maxPrice = $product->variants->max('price') ?? $minPrice;
                if ($minPrice > 0) {
                    $prices[] = $minPrice;
                }
                if ($maxPrice > 0) {
                    $prices[] = $maxPrice;
                }
            }
        }
        
        $minPrice = !empty($prices) ? min($prices) : 0;
        $maxPrice = !empty($prices) ? max($prices) : 10000000;

        if ($minPrice >= $maxPrice) {
            $maxPrice = $minPrice + 1000000;
        }

        return [
            'brands' => $brands,
            'materials' => $materials,
            'colors' => $colors,
            'categories' => $categories,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ];
    }

    private function mapProduct($product)
    {
        $minPrice = $product->variants->min('price') ?? 0;
        $maxPrice = $product->variants->max('price') ?? $minPrice;
        $originalPrice = $maxPrice > $minPrice ? $maxPrice : null;
        $discount = $originalPrice ? round((1 - $minPrice / $originalPrice) * 100) . '%' : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'image' => $product->thumbnail ?? 'https://picsum.photos/400/500',
            'price' => number_format($minPrice) . 'đ',
            'oldPrice' => $originalPrice ? number_format($originalPrice) . 'đ' : null,
            'badge' => $discount ? "-$discount" : ($product->is_preorder ? 'Pre-order' : null),
            'badgeClass' => $discount ? 'bg-primary text-white' : ($product->is_preorder ? 'bg-amber-600 text-white' : ''),
            'brandCategory' => $product->brand?->name ?? $product->category?->name ?? '',
            'brand_id' => $product->brand_id,
            'category_id' => $product->category_id,
        ];
    }
}
```

## 2. Sửa Category.vue - Debug và hiển thị màu

```vue
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc - HIỂN THỊ MÀU SẮC -->
          <div>
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            
            <!-- Debug: In ra số lượng màu -->
            <div class="text-xs text-gray-400 mb-2">
              Số màu: {{ filters.colors ? filters.colors.length : 0 }}
            </div>
            
            <!-- Hiển thị nếu có màu -->
            <div v-if="filters.colors && filters.colors.length > 0" class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name + ' (' + (color.code || '') + ')'"
                @click="toggleColor(color.id)"
              >
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <!-- Hiển thị nếu không có màu -->
            <div v-else class="text-gray-400 text-sm py-2 bg-gray-50 rounded-lg px-3">
              Không có màu sắc nào
            </div>
            
            <!-- Hiển thị tên màu đã chọn -->
            <div v-if="selectedColors.length > 0" class="mt-2 flex flex-wrap gap-1">
              <span v-for="colorId in selectedColors" :key="colorId" class="inline-flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded-full text-xs">
                <span 
                  class="w-2 h-2 rounded-full inline-block"
                  :style="{ backgroundColor: getColorCode(colorId) }"
                ></span>
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Từ</label>
                  <input 
                    type="number" 
                    v-model.number="priceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                    @input="applyFilters"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="priceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
                    @input="applyFilters"
                  >
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                :min="filters.minPrice || 0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm text-gray-500">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// Debug: In ra dữ liệu nhận được
console.log('📦 Props nhận được:', props)
console.log('🎨 Colors data:', props.filters?.colors)

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  console.log('🔍 Filters data:', props.filters)
  console.log('🎨 Colors:', props.filters.colors)
  
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})

// Watch để debug
watch(() => props.filters, (newVal) => {
  console.log('🔄 Filters updated:', newVal)
  console.log('🎨 Colors in filters:', newVal?.colors)
}, { deep: true })
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>
```

## 3. Kiểm tra dữ liệu trong database

Chạy các câu lệnh SQL để kiểm tra:

```sql
-- Kiểm tra bảng colors
SELECT * FROM colors;

-- Kiểm tra product_variants có color_id
SELECT p.name, pv.id, pv.color_id, c.name as color_name, c.code 
FROM product_variants pv
LEFT JOIN products p ON p.id = pv.product_id
LEFT JOIN colors c ON c.id = pv.color_id
WHERE pv.color_id IS NOT NULL;
```

## 4. Nếu chưa có dữ liệu, chạy seeder:

```bash
php artisan db:seed --class=ColorSeeder
```

## 5. Gán màu cho sản phẩm hiện có:

```sql
-- Gán màu đen (id=1) cho các variant chưa có màu
UPDATE product_variants SET color_id = 1 WHERE color_id IS NULL;

-- Hoặc gán màu ngẫu nhiên
UPDATE product_variants 
SET color_id = FLOOR(1 + RAND() * (SELECT COUNT(*) FROM colors)) 
WHERE color_id IS NULL;
```

Sau khi làm các bước trên, màu sắc sẽ hiển thị đúng trên giao diện. Kiểm tra console để xem dữ liệu màu sắc đã được truyền sang Vue chưa.


## User:
07/11/2026 16:02

![image.png](data:image/webp;base64,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)

![image.png](data:image/webp;base64,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)

đã hiện lên đâu
Tôi muốn hiện lên màu giống hình 2 kìa


## Assistant:
07/11/2026 16:02

Tôi hiểu! Bạn muốn hiển thị các màu sắc như trong hình 2: **Trắng, Cam, Xanh lá, Đỏ, Nâu** dưới dạng các nút tròn có màu sắc tương ứng, chứ không phải hiển thị tên màu.

Vấn đề là dữ liệu màu sắc đã có (8 màu) nhưng không hiển thị đúng. Đây là code đã sửa để hiển thị màu sắc đúng như hình 2:

## Category.vue - Phần hiển thị màu sắc (đã sửa)

```vue
<!-- Màu sắc - HIỂN THỊ MÀU SẮC DẠNG NÚT TRÒN -->
<div>
  <h3 class="font-semibold mb-4">Màu sắc</h3>
  
  <!-- Hiển thị số lượng màu -->
  <div class="text-xs text-gray-400 mb-2">
    {{ filters.colors ? filters.colors.length : 0 }} màu
  </div>
  
  <!-- Hiển thị các nút màu tròn -->
  <div v-if="filters.colors && filters.colors.length > 0" class="flex flex-wrap gap-3">
    <button 
      v-for="color in filters.colors" 
      :key="color.id"
      class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative shadow-sm"
      :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500 hover:scale-110'"
      :style="{ backgroundColor: color.code || '#CCCCCC' }"
      :title="color.name"
      @click="toggleColor(color.id)"
    >
      <!-- Hiển thị dấu check khi được chọn -->
      <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
        check
      </span>
    </button>
  </div>
  
  <!-- Hiển thị khi không có màu -->
  <div v-else class="text-gray-400 text-sm py-2">
    Không có màu sắc nào
  </div>
  
  <!-- Hiển thị tên màu đã chọn (dạng tag nhỏ) -->
  <div v-if="selectedColors.length > 0" class="mt-3 flex flex-wrap gap-1">
    <span 
      v-for="colorId in selectedColors" 
      :key="colorId" 
      class="inline-flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-full text-xs border border-gray-200"
    >
      <span 
        class="w-3 h-3 rounded-full inline-block border border-gray-300"
        :style="{ backgroundColor: getColorCode(colorId) }"
      ></span>
      {{ getColorName(colorId) }}
    </span>
  </div>
</div>
```

## Category.vue - Script setup (thêm các hàm xử lý)

```vue
<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  console.log('🔍 Filters data:', props.filters)
  console.log('🎨 Colors:', props.filters.colors)
  
  // Kiểm tra từng màu có code không
  if (props.filters.colors) {
    props.filters.colors.forEach(color => {
      console.log(`Color: ${color.name}, Code: ${color.code}`)
    })
  }
  
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})
</script>
```

## Toàn bộ file Category.vue hoàn chỉnh:

```vue
<template>
  <div>
    <Head :title="`${categoryName || 'Danh mục'} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName || 'Danh mục' }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName || 'Danh mục' }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName || 'này' }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0 space-y-6">
          <!-- Danh mục -->
          <div v-if="filters.categories && filters.categories.length">
            <h3 class="font-semibold mb-4">Danh mục</h3>
            <ul>
              <li v-for="cat in filters.categories" :key="cat.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'cat_' + cat.id" 
                  :value="cat.id"
                  v-model="selectedCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'cat_' + cat.id" class="ml-2 text-sm">{{ cat.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Thương hiệu -->
          <div v-if="filters.brands && filters.brands.length">
            <h3 class="font-semibold mb-4">Thương hiệu</h3>
            <ul>
              <li v-for="brand in filters.brands" :key="brand.id" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'brand_' + brand.id" 
                  :value="brand.id"
                  v-model="selectedBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'brand_' + brand.id" class="ml-2 text-sm">{{ brand.name }}</label>
              </li>
            </ul>
          </div>

          <!-- Chất liệu -->
          <div v-if="filters.materials && filters.materials.length">
            <h3 class="font-semibold mb-4">Chất liệu</h3>
            <ul>
              <li v-for="mat in filters.materials" :key="mat" class="flex items-center mb-2">
                <input 
                  type="checkbox" 
                  :id="'mat_' + mat" 
                  :value="mat"
                  v-model="selectedMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                  @change="applyFilters"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc - HIỂN THỊ MÀU SẮC DẠNG NÚT TRÒN -->
          <div>
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            
            <!-- Hiển thị số lượng màu -->
            <div class="text-xs text-gray-400 mb-2">
              {{ filters.colors ? filters.colors.length : 0 }} màu
            </div>
            
            <!-- Hiển thị các nút màu tròn -->
            <div v-if="filters.colors && filters.colors.length > 0" class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative shadow-sm"
                :class="selectedColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500 hover:scale-110'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name"
                @click="toggleColor(color.id)"
              >
                <!-- Hiển thị dấu check khi được chọn -->
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <!-- Hiển thị khi không có màu -->
            <div v-else class="text-gray-400 text-sm py-2 bg-gray-50 rounded-lg px-3 text-center">
              Không có màu sắc nào
            </div>
            
            <!-- Hiển thị tên màu đã chọn (dạng tag nhỏ) -->
            <div v-if="selectedColors.length > 0" class="mt-3 flex flex-wrap gap-1">
              <span 
                v-for="colorId in selectedColors" 
                :key="colorId" 
                class="inline-flex items-center gap-1.5 bg-gray-100 px-2.5 py-1 rounded-full text-xs border border-gray-200"
              >
                <span 
                  class="w-3 h-3 rounded-full inline-block border border-gray-300"
                  :style="{ backgroundColor: getColorCode(colorId) }"
                ></span>
                {{ getColorName(colorId) }}
              </span>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div>
            <h3 class="font-semibold mb-4">Khoảng giá</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Từ</label>
                  <input 
                    type="number" 
                    v-model.number="priceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                    @input="applyFilters"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="priceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
                    @input="applyFilters"
                  >
                </div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ formatPrice(filters.minPrice || 0) }}</span>
                <span>{{ formatPrice(filters.maxPrice || 10000000) }}</span>
              </div>
              <input 
                type="range" 
                :min="filters.minPrice || 0" 
                :max="filters.maxPrice || 10000000" 
                v-model="priceRange"
                @input="updatePriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

          <!-- Nút áp dụng và reset -->
          <div class="space-y-2">
            <button 
              @click="applyFilters" 
              class="w-full py-3 px-6 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
            >
              Áp dụng lọc
            </button>
            <button 
              @click="resetFilters" 
              class="w-full py-2 px-6 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition text-sm"
            >
              Xóa bộ lọc
            </button>
          </div>
        </aside>

        <!-- Product List -->
        <div class="flex-grow">
          <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <span class="text-sm text-gray-500">Hiển thị {{ products?.length || 0 }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-500">Sắp xếp:</span>
              <select 
                v-model="sortBy" 
                @change="applyFilters"
                class="border rounded-lg px-3 py-1.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template v-if="products && products.length">
              <div v-for="product in products" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { id: product.id })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                    <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                      {{ product.badge }}
                    </span>
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col flex-grow">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span class="font-bold text-primary">{{ product.price }}</span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Thêm vào giỏ hàng
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              Không có sản phẩm nào phù hợp với bộ lọc.
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-12 flex justify-center space-x-2">
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
            <button class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center">1</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">2</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">3</button>
            <span class="px-2 flex items-center">...</span>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">8</button>
            <button class="w-10 h-10 rounded border flex items-center justify-center hover:bg-gray-50 transition">
              <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) }
})

// State cho bộ lọc
const selectedBrands = ref([])
const selectedMaterials = ref([])
const selectedCategories = ref([])
const selectedColors = ref([])
const priceMin = ref(null)
const priceMax = ref(null)
const priceRange = ref(0)
const sortBy = ref('newest')

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Lấy tên màu từ ID
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

// Lấy mã màu từ ID
const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

// Toggle màu sắc
const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

// Cập nhật giá từ range slider
const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

// Format giá
const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedBrands.value.length) {
    params.append('brands', selectedBrands.value.join(','))
  }
  if (selectedMaterials.value.length) {
    params.append('materials', selectedMaterials.value.join(','))
  }
  if (selectedCategories.value.length) {
    params.append('categories', selectedCategories.value.join(','))
  }
  if (selectedColors.value.length) {
    params.append('colors', selectedColors.value.join(','))
  }
  if (priceMin.value && priceMin.value > 0) {
    params.append('price_min', priceMin.value)
  }
  if (priceMax.value && priceMax.value > 0) {
    params.append('price_max', priceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  selectedBrands.value = []
  selectedMaterials.value = []
  selectedCategories.value = []
  selectedColors.value = []
  priceMin.value = null
  priceMax.value = null
  priceRange.value = 0
  sortBy.value = 'newest'
  applyFilters()
}

// Thêm vào giỏ hàng
const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  console.log('🔍 Filters data:', props.filters)
  console.log('🎨 Colors:', props.filters.colors)
  
  // Kiểm tra từng màu có code không
  if (props.filters.colors) {
    props.filters.colors.forEach(color => {
      console.log(`Color: ${color.name}, Code: ${color.code}`)
    })
  }
  
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    selectedBrands.value = params.get('brands').split(',').map(Number).filter(Boolean)
  }
  if (params.has('materials')) {
    selectedMaterials.value = params.get('materials').split(',')
  }
  if (params.has('categories')) {
    selectedCategories.value = params.get('categories').split(',').map(Number).filter(Boolean)
  }
  if (params.has('colors')) {
    selectedColors.value = params.get('colors').split(',').map(Number).filter(Boolean)
  }
  if (params.has('price_min')) {
    priceMin.value = Number(params.get('price_min'))
  }
  if (params.has('price_max')) {
    priceMax.value = Number(params.get('price_max'))
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  if (props.filters.maxPrice) {
    priceRange.value = props.filters.maxPrice
  }
})
</script>

<style scoped>
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}

/* Hiệu ứng hover cho nút màu */
.color-btn {
  transition: all 0.2s ease;
}
.color-btn:hover {
  transform: scale(1.15);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.color-btn.selected {
  transform: scale(1.1);
}
</style>
```

## Kiểm tra dữ liệu màu sắc trong database:

```sql
-- Kiểm tra tất cả màu sắc
SELECT * FROM colors;

-- Kiểm tra màu sắc của sản phẩm
SELECT 
    p.id,
    p.name as product_name,
    c.id as color_id,
    c.name as color_name,
    c.code as color_code
FROM products p
JOIN product_variants pv ON pv.product_id = p.id
JOIN colors c ON c.id = pv.color_id
GROUP BY p.id, c.id, c.name, c.code;
```

Nếu bảng `colors` trống, hãy chạy lệnh để thêm dữ liệu:

```sql
INSERT INTO colors (name, code, created_at, updated_at) VALUES
('Trắng', '#FFFFFF', NOW(), NOW()),
('Đen', '#000000', NOW(), NOW()),
('Đỏ', '#FF0000', NOW(), NOW()),
('Xanh dương', '#0066CC', NOW(), NOW()),
('Xanh lá', '#00CC66', NOW(), NOW()),
('Vàng', '#FFCC00', NOW(), NOW()),
('Cam', '#FF6600', NOW(), NOW()),
('Tím', '#9900CC', NOW(), NOW()),
('Hồng', '#FF66B2', NOW(), NOW()),
('Nâu', '#8B4513', NOW(), NOW()),
('Xám', '#808080', NOW(), NOW());
```

Sau đó gán màu cho sản phẩm:

```sql
-- Gán màu ngẫu nhiên cho các variant
UPDATE product_variants 
SET color_id = FLOOR(1 + RAND() * (SELECT COUNT(*) FROM colors)) 
WHERE color_id IS NULL;
```

Sau khi làm các bước trên, màu sắc sẽ hiển thị đúng như hình 2 với các nút tròn có màu sắc tương ứng!



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
