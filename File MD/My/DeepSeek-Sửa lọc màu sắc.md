# Sửa lọc màu sắc

**Created:** 07/11/2026 16:07  
**Updated:** 07/11/2026 16:29  
**Exported:** 07/13/2026 03:05  
**Link:** [https://chat.deepseek.com/a/chat/s/f01691e5-b203-4520-8b02-afb863e3dc25](https://chat.deepseek.com/a/chat/s/f01691e5-b203-4520-8b02-afb863e3dc25)  

## User:
07/11/2026 16:14

> File: DeepSeek-Chỉnh giao diện pre-order.md

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

Chỉnh lại chức năng lọc cho tôi


## Assistant:
07/11/2026 16:14

Tôi thấy vấn đề là màu sắc không hiển thị vì `filters.colors` không có dữ liệu. Hãy kiểm tra và sửa theo các bước sau:

## 1. Kiểm tra dữ liệu trong database

Chạy các câu lệnh SQL này để kiểm tra:

```sql
-- Kiểm tra bảng colors có dữ liệu không
SELECT COUNT(*) FROM colors;

-- Xem danh sách màu
SELECT * FROM colors;

-- Kiểm tra product_variants có color_id không
SELECT COUNT(*) FROM product_variants WHERE color_id IS NOT NULL;
```

## 2. Nếu chưa có dữ liệu, thêm màu sắc

```sql
-- Thêm màu sắc
INSERT INTO colors (name, code, is_active, created_at, updated_at) VALUES
('Trắng', '#FFFFFF', 1, NOW(), NOW()),
('Đen', '#000000', 1, NOW(), NOW()),
('Đỏ', '#FF0000', 1, NOW(), NOW()),
('Xanh dương', '#0066CC', 1, NOW(), NOW()),
('Xanh lá', '#00CC66', 1, NOW(), NOW()),
('Vàng', '#FFCC00', 1, NOW(), NOW()),
('Cam', '#FF6600', 1, NOW(), NOW()),
('Tím', '#9900CC', 1, NOW(), NOW()),
('Hồng', '#FF66B2', 1, NOW(), NOW()),
('Nâu', '#8B4513', 1, NOW(), NOW()),
('Xám', '#808080', 1, NOW(), NOW());
```

## 3. Cập nhật CategoryController.php - sửa hàm getFilterData

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

    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = $products->pluck('material')->unique()->filter()->values();

        // Lấy danh sách màu sắc - QUAN TRỌNG
        $colorIds = collect();
        foreach ($products as $product) {
            if ($product->variants) {
                $variantColorIds = $product->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColorIds);
            }
        }
        $colorIds = $colorIds->unique()->filter()->values();
        
        // Log để debug
        \Log::info('Color IDs from products: ', $colorIds->toArray());
        
        // Lấy thông tin màu sắc từ database
        $colors = collect();
        if ($colorIds->isNotEmpty()) {
            $colors = Color::whereIn('id', $colorIds)
                ->where('is_active', 1)
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
}
```

## 4. Debug - Thêm log trong Controller

Thêm dòng này vào đầu file CategoryController.php để debug:

```php
use Illuminate\Support\Facades\Log;
```

Sau đó trong hàm `getFilterData` thêm:

```php
Log::info('Product count: ' . $products->count());
Log::info('Color IDs: ' . json_encode($colorIds->toArray()));
Log::info('Colors data: ' . json_encode($colors->toArray()));
```

## 5. Kiểm tra xem sản phẩm có variant và color không

```sql
SELECT 
    p.id,
    p.name,
    pv.id as variant_id,
    pv.color_id,
    c.name as color_name,
    c.code as color_code
FROM products p
JOIN product_variants pv ON pv.product_id = p.id
LEFT JOIN colors c ON c.id = pv.color_id
WHERE p.status = 1
LIMIT 10;
```

## 6. Nếu sản phẩm chưa có màu, gán màu cho sản phẩm

```sql
-- Gán màu ngẫu nhiên cho các variant chưa có màu
UPDATE product_variants 
SET color_id = FLOOR(1 + RAND() * (SELECT COUNT(*) FROM colors WHERE is_active = 1))
WHERE color_id IS NULL;
```

## 7. Chạy lại server

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

## 8. Category.vue - Thêm debug để kiểm tra dữ liệu

Thêm đoạn này vào `<script setup>`:

```javascript
// Debug dữ liệu
console.log('📦 Props nhận được:', props)
console.log('🎨 Colors data:', props.filters?.colors)

// Theo dõi thay đổi của filters
watch(() => props.filters, (newVal, oldVal) => {
  console.log('🔄 Filters changed:', newVal)
  console.log('🔄 Colors changed:', newVal?.colors)
}, { deep: true })
```

## 9. Kiểm tra file web.php route

Đảm bảo route đúng:

```php
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])->name('category');
```

## 10. Chạy lại Vite

```bash
npm run dev
```

Sau khi làm các bước trên, bạn sẽ thấy màu sắc hiển thị. Nếu vẫn chưa được, hãy kiểm tra console của trình duyệt để xem lỗi và gửi lại cho tôi.


## User:
07/11/2026 16:18

đưa code vue và controller hoàn chỉnh lại cho tôi


## Assistant:
07/11/2026 16:18

Tôi sẽ đưa code hoàn chỉnh cho bạn:

## 1. CategoryController.php (Hoàn chỉnh)

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
use Illuminate\Support\Facades\Log;

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

    private function getFilterData($products)
    {
        // Lấy danh sách thương hiệu
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)->orderBy('name')->get(['id', 'name']);

        // Lấy danh sách chất liệu
        $materials = $products->pluck('material')->unique()->filter()->values();

        // Lấy danh sách màu sắc
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
                ->where('is_active', 1)
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

## 2. Category.vue (Hoàn chỉnh)

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
            
            <div class="text-xs text-gray-400 mb-2">
              {{ filters.colors ? filters.colors.length : 0 }} màu
            </div>
            
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
                <span v-if="selectedColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <div v-else class="text-gray-400 text-sm py-2 bg-gray-50 rounded-lg px-3 text-center">
              Không có màu sắc nào
            </div>
            
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

// State
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

// Helper functions
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

const toggleColor = (colorId) => {
  const index = selectedColors.value.indexOf(colorId)
  if (index > -1) {
    selectedColors.value.splice(index, 1)
  } else {
    selectedColors.value.push(colorId)
  }
  applyFilters()
}

const updatePriceFromRange = () => {
  priceMax.value = priceRange.value
  applyFilters()
}

const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

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

const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Lifecycle
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
</style>
```

## 3. Chạy các lệnh sau:

```bash
# Clear cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Restart Vite
npm run dev
```

## 4. Kiểm tra dữ liệu trong database:

```sql
-- Thêm màu sắc nếu chưa có
INSERT INTO colors (name, code, is_active, created_at, updated_at) VALUES
('Trắng', '#FFFFFF', 1, NOW(), NOW()),
('Đen', '#000000', 1, NOW(), NOW()),
('Đỏ', '#FF0000', 1, NOW(), NOW()),
('Xanh dương', '#0066CC', 1, NOW(), NOW()),
('Xanh lá', '#00CC66', 1, NOW(), NOW()),
('Cam', '#FF6600', 1, NOW(), NOW()),
('Tím', '#9900CC', 1, NOW(), NOW()),
('Hồng', '#FF66B2', 1, NOW(), NOW()),
('Nâu', '#8B4513', 1, NOW(), NOW()),
('Xám', '#808080', 1, NOW(), NOW());

-- Gán màu cho sản phẩm nếu chưa có
UPDATE product_variants 
SET color_id = FLOOR(1 + RAND() * (SELECT COUNT(*) FROM colors WHERE is_active = 1))
WHERE color_id IS NULL;
```

Sau khi làm các bước trên, màu sắc sẽ hiển thị đúng trên giao diện.


## User:
07/11/2026 16:22

Illuminate\Database\QueryException
vendor\laravel\framework\src\Illuminate\Database\Connection.php:841
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'is_active' in 'where clause' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: web_ba_lo, SQL: select `id`, `name`, `code` from `colors` where `id` in (1, 2, 3, 5, 8, 7, 4) and `is_active` = 1 order by `name` asc)

Category.vue?t=1783760634480:139 Color: Cam, Code: null
Category.vue?t=1783760634480:139 Color: Đen, Code: null
Category.vue?t=1783760634480:139 Color: Đỏ, Code: null
Category.vue?t=1783760634480:139 Color: Nâu, Code: null
Category.vue?t=1783760634480:139 Color: Trắng, Code: null
Category.vue?t=1783760634480:139 Color: Xám, Code: null
Category.vue?t=1783760634480:139 Color: Xanh lá, Code: null
Category.vue?t=1783760634480:139 Color: Xanh Navy, Code: null
Category.vue?t=1783760634480:133 🔍 Filters data: Proxy(Object)
Category.vue?t=1783760634480:134 🎨 Colors: Proxy(Array)
Category.vue?t=1783760634480:139 Color: Đen, Code: null
Category.vue?t=1783760634480:139 Color: Nâu, Code: null
Category.vue?t=1783760634480:139 Color: Xanh lá, Code: null
Category.vue?t=1783760634480:133 🔍 Filters data: Proxy(Object)
Category.vue?t=1783760634480:134 🎨 Colors: Proxy(Array)
Category.vue?t=1783760634480:139 Color: Cam, Code: null
Category.vue?t=1783760634480:139 Color: Đen, Code: null
Category.vue?t=1783760634480:139 Color: Đỏ, Code: null
Category.vue?t=1783760634480:139 Color: Nâu, Code: null
Category.vue?t=1783760634480:139 Color: Trắng, Code: null
Category.vue?t=1783760634480:139 Color: Xám, Code: null
Category.vue?t=1783760634480:139 Color: Xanh Navy, Code: null
Category.vue?t=1783760634480:133 🔍 Filters data: Proxy(Object)
Category.vue?t=1783760634480:134 🎨 Colors: Proxy(Array)
Category.vue?t=1783760634480:139 Color: Cam, Code: null
Category.vue?t=1783760634480:139 Color: Đen, Code: null
Category.vue?t=1783760634480:139 Color: Đỏ, Code: null
Category.vue?t=1783760634480:139 Color: Nâu, Code: null
Category.vue?t=1783760634480:139 Color: Trắng, Code: null
Category.vue?t=1783760634480:139 Color: Xám, Code: null
Category.vue?t=1783760634480:139 Color: Xanh Navy, Code: null
Checkout.vue:460 📦 Checkout props: Proxy(Object)
Checkout.vue:460 📦 Checkout props: Proxy(Object)
Checkout.vue:460 📦 Checkout props: Proxy(Object)
:8000/danh-muc/cap-tui:1  Failed to load resource: the server responded with a status of 500 (Internal Server Error)
:8000/danh-muc/balo:1  Failed to load resource: the server responded with a status of 500 (Internal Server Error)
about:srcdoc:4957 Access to script at 'http://[::1]:5173/@vite/client' from origin 'null' has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present on the requested resource.
client:1  Failed to load resource: net::ERR_FAILED


## Assistant:
07/11/2026 16:22

Lỗi là do bảng `colors` không có cột `is_active`. Hãy sửa lại code:

## 1. Sửa CategoryController.php - Bỏ cột `is_active`

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

        // Lấy danh sách màu sắc - BỎ is_active
        $colorIds = collect();
        foreach ($products as $product) {
            if ($product->variants) {
                $variantColorIds = $product->variants->pluck('color_id')->filter();
                $colorIds = $colorIds->merge($variantColorIds);
            }
        }
        $colorIds = $colorIds->unique()->filter()->values();
        
        // Lấy thông tin màu sắc từ database - KHÔNG dùng is_active
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

## 2. Sửa Color Model - Bỏ is_active nếu chưa có

```php
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
```

## 3. Cập nhật bảng colors - Thêm cột is_active nếu chưa có

```sql
-- Kiểm tra xem cột is_active đã tồn tại chưa
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'colors' AND COLUMN_NAME = 'is_active';

-- Nếu chưa có, thêm cột is_active
ALTER TABLE colors ADD COLUMN is_active TINYINT(1) DEFAULT 1;

-- Cập nhật lại dữ liệu
UPDATE colors SET is_active = 1;
```

## 4. Hoặc đơn giản hơn, bỏ điều kiện is_active trong query

Sửa dòng này trong `getFilterData`:

```php
// Từ:
$colors = Color::whereIn('id', $colorIds)->where('is_active', 1)->orderBy('name')->get(['id', 'name', 'code']);

// Thành:
$colors = Color::whereIn('id', $colorIds)->orderBy('name')->get(['id', 'name', 'code']);
```

## 5. Thêm cột is_active vào bảng colors (Migration)

Tạo migration mới:

```bash
php artisan make:migration add_is_active_to_colors_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('colors', function (Blueprint $table) {
            if (!Schema::hasColumn('colors', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('code');
            }
        });
    }

    public function down()
    {
        Schema::table('colors', function (Blueprint $table) {
            if (Schema::hasColumn('colors', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
```

Chạy migration:

```bash
php artisan migrate
```

## 6. Sau đó chạy lại:

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
npm run dev
```

Màu sắc sẽ hiển thị đúng với code đã có.


## User:
07/11/2026 16:27

![image.png](data:image/webp;base64,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)

màu sắc vẫn chưa hiển thị được , quan trọng là fix lại cho tôi chức năng lọc chỉ lọc khi nhấn nút áp dụng lọc chứ không phải đang chọn là lọc , quan trọng vẫn bị màu sắc chưa hiển thị nảy giờ , kiểm tra và chỉnh lại đưa code hoàn chỉnh cho tôi


## Assistant:
07/11/2026 16:27

Tôi thấy vấn đề là màu sắc không hiển thị vì cột `code` trong bảng `colors` đang bị `null`. Hãy cùng fix:

## 1. Cập nhật dữ liệu màu sắc trong database

```sql
-- Cập nhật mã màu cho các màu hiện có
UPDATE colors SET code = '#000000' WHERE name = 'Đen';
UPDATE colors SET code = '#FFFFFF' WHERE name = 'Trắng';
UPDATE colors SET code = '#FF0000' WHERE name = 'Đỏ';
UPDATE colors SET code = '#0066CC' WHERE name = 'Xanh dương';
UPDATE colors SET code = '#00CC66' WHERE name = 'Xanh lá';
UPDATE colors SET code = '#FF6600' WHERE name = 'Cam';
UPDATE colors SET code = '#9900CC' WHERE name = 'Tím';
UPDATE colors SET code = '#FF66B2' WHERE name = 'Hồng';
UPDATE colors SET code = '#8B4513' WHERE name = 'Nâu';
UPDATE colors SET code = '#808080' WHERE name = 'Xám';
UPDATE colors SET code = '#C0C0C0' WHERE name = 'Bạc';
UPDATE colors SET code = '#DAA520' WHERE name = 'Vàng gold';
UPDATE colors SET code = '#000080' WHERE name = 'Xanh Navy';
```

## 2. CategoryController.php - Sửa để lấy màu và chỉ lọc khi nhấn nút

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
        
        // Áp dụng bộ lọc từ URL
        $query = $this->applyFilters($query, $request);
        
        $products = $query->latest()->get();
        
        $mappedProducts = $products->map(fn($product) => $this->mapProduct($product));

        // Lấy dữ liệu cho bộ lọc (luôn lấy từ tất cả sản phẩm, không bị ảnh hưởng bởi filter)
        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

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

        // Lấy dữ liệu cho bộ lọc từ tất cả sản phẩm trong danh mục
        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

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

        // Lấy dữ liệu cho bộ lọc
        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->whereIn('category_id', $categoryIds)
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

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

        // Lấy dữ liệu cho bộ lọc
        $allProducts = Product::with(['category', 'brand', 'variants.color'])
            ->where('brand_id', $brand->id)
            ->where('status', 1)
            ->get();
        $filterData = $this->getFilterData($allProducts);

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

        // Lấy danh sách màu sắc - QUAN TRỌNG
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

## 3. Category.vue - Chỉ lọc khi nhấn nút "Áp dụng lọc"

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
                  v-model="tempCategories"
                  class="rounded border-gray-300 text-primary h-4 w-4"
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
                  v-model="tempBrands"
                  class="rounded border-gray-300 text-primary h-4 w-4"
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
                  v-model="tempMaterials"
                  class="rounded border-gray-300 text-primary h-4 w-4"
                >
                <label :for="'mat_' + mat" class="ml-2 text-sm">{{ mat }}</label>
              </li>
            </ul>
          </div>

          <!-- Màu sắc -->
          <div>
            <h3 class="font-semibold mb-4">Màu sắc</h3>
            
            <div class="text-xs text-gray-400 mb-2">
              {{ filters.colors ? filters.colors.length : 0 }} màu
            </div>
            
            <div v-if="filters.colors && filters.colors.length > 0" class="flex flex-wrap gap-3">
              <button 
                v-for="color in filters.colors" 
                :key="color.id"
                class="w-10 h-10 rounded-full border-2 transition-all flex items-center justify-center relative shadow-sm"
                :class="tempColors.includes(color.id) ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-gray-300 hover:border-gray-500 hover:scale-110'"
                :style="{ backgroundColor: color.code || '#CCCCCC' }"
                :title="color.name"
                @click="toggleTempColor(color.id)"
              >
                <span v-if="tempColors.includes(color.id)" class="material-symbols-outlined text-white text-sm" style="text-shadow: 0 0 4px rgba(0,0,0,0.5);">
                  check
                </span>
              </button>
            </div>
            
            <div v-else class="text-gray-400 text-sm py-2 bg-gray-50 rounded-lg px-3 text-center">
              Không có màu sắc nào
            </div>
            
            <div v-if="tempColors.length > 0" class="mt-3 flex flex-wrap gap-1">
              <span 
                v-for="colorId in tempColors" 
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
                    v-model.number="tempPriceMin" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="0"
                  >
                </div>
                <div class="flex-1">
                  <label class="text-xs text-gray-500">Đến</label>
                  <input 
                    type="number" 
                    v-model.number="tempPriceMax" 
                    class="w-full border rounded-lg px-3 py-2 text-sm"
                    placeholder="10.000.000"
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
                v-model="tempPriceRange"
                @input="updateTempPriceFromRange"
                class="w-full h-1 bg-gray-200 rounded-lg accent-primary"
              >
            </div>
          </div>

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

// Temporary filter state (chỉ lưu khi chưa áp dụng)
const tempBrands = ref([])
const tempMaterials = ref([])
const tempCategories = ref([])
const tempColors = ref([])
const tempPriceMin = ref(null)
const tempPriceMax = ref(null)
const tempPriceRange = ref(0)
const sortBy = ref('newest')

// Actual applied filters (từ URL)
const appliedBrands = ref([])
const appliedMaterials = ref([])
const appliedCategories = ref([])
const appliedColors = ref([])
const appliedPriceMin = ref(null)
const appliedPriceMax = ref(null)

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

// Helper functions
const getColorName = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return ''
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.name : ''
}

const getColorCode = (colorId) => {
  if (!props.filters.colors || props.filters.colors.length === 0) return '#CCCCCC'
  const color = props.filters.colors.find(c => c.id === colorId)
  return color ? color.code : '#CCCCCC'
}

const toggleTempColor = (colorId) => {
  const index = tempColors.value.indexOf(colorId)
  if (index > -1) {
    tempColors.value.splice(index, 1)
  } else {
    tempColors.value.push(colorId)
  }
}

const updateTempPriceFromRange = () => {
  tempPriceMax.value = tempPriceRange.value
}

const formatPrice = (price) => {
  if (!price || price === 0) return '0đ'
  return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
}

// Áp dụng bộ lọc - CHỈ GỌI KHI NHẤN NÚT
const applyFilters = () => {
  const params = new URLSearchParams()
  
  // Lưu các giá trị đã chọn vào applied
  appliedBrands.value = [...tempBrands.value]
  appliedMaterials.value = [...tempMaterials.value]
  appliedCategories.value = [...tempCategories.value]
  appliedColors.value = [...tempColors.value]
  appliedPriceMin.value = tempPriceMin.value
  appliedPriceMax.value = tempPriceMax.value
  
  if (tempBrands.value.length) {
    params.append('brands', tempBrands.value.join(','))
  }
  if (tempMaterials.value.length) {
    params.append('materials', tempMaterials.value.join(','))
  }
  if (tempCategories.value.length) {
    params.append('categories', tempCategories.value.join(','))
  }
  if (tempColors.value.length) {
    params.append('colors', tempColors.value.join(','))
  }
  if (tempPriceMin.value && tempPriceMin.value > 0) {
    params.append('price_min', tempPriceMin.value)
  }
  if (tempPriceMax.value && tempPriceMax.value > 0) {
    params.append('price_max', tempPriceMax.value)
  }
  if (sortBy.value) {
    params.append('sort', sortBy.value)
  }
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

// Reset bộ lọc
const resetFilters = () => {
  tempBrands.value = []
  tempMaterials.value = []
  tempCategories.value = []
  tempColors.value = []
  tempPriceMin.value = null
  tempPriceMax.value = null
  tempPriceRange.value = 0
  sortBy.value = 'newest'
  
  appliedBrands.value = []
  appliedMaterials.value = []
  appliedCategories.value = []
  appliedColors.value = []
  appliedPriceMin.value = null
  appliedPriceMax.value = null
  
  // Reload page without filters
  const url = route('category', { slug: props.slug })
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

// Khởi tạo bộ lọc từ URL
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  
  if (params.has('brands')) {
    const brands = params.get('brands').split(',').map(Number).filter(Boolean)
    tempBrands.value = brands
    appliedBrands.value = brands
  }
  if (params.has('materials')) {
    const materials = params.get('materials').split(',')
    tempMaterials.value = materials
    appliedMaterials.value = materials
  }
  if (params.has('categories')) {
    const categories = params.get('categories').split(',').map(Number).filter(Boolean)
    tempCategories.value = categories
    appliedCategories.value = categories
  }
  if (params.has('colors')) {
    const colors = params.get('colors').split(',').map(Number).filter(Boolean)
    tempColors.value = colors
    appliedColors.value = colors
  }
  if (params.has('price_min')) {
    const val = Number(params.get('price_min'))
    tempPriceMin.value = val
    appliedPriceMin.value = val
  }
  if (params.has('price_max')) {
    const val = Number(params.get('price_max'))
    tempPriceMax.value = val
    appliedPriceMax.value = val
  }
  if (params.has('sort')) {
    sortBy.value = params.get('sort')
  }
  
  if (props.filters.maxPrice) {
    tempPriceRange.value = props.filters.maxPrice
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
</style>
```

## 4. Cập nhật dữ liệu màu sắc

Chạy SQL để cập nhật mã màu:

```sql
-- Cập nhật code cho tất cả màu
UPDATE colors SET code = 
  CASE name
    WHEN 'Đen' THEN '#000000'
    WHEN 'Trắng' THEN '#FFFFFF'
    WHEN 'Đỏ' THEN '#FF0000'
    WHEN 'Xanh dương' THEN '#0066CC'
    WHEN 'Xanh lá' THEN '#00CC66'
    WHEN 'Cam' THEN '#FF6600'
    WHEN 'Tím' THEN '#9900CC'
    WHEN 'Hồng' THEN '#FF66B2'
    WHEN 'Nâu' THEN '#8B4513'
    WHEN 'Xám' THEN '#808080'
    WHEN 'Bạc' THEN '#C0C0C0'
    WHEN 'Vàng gold' THEN '#DAA520'
    WHEN 'Xanh Navy' THEN '#000080'
    ELSE code
  END
WHERE code IS NULL OR code = '';
```

## 5. Chạy lại:

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
npm run dev
```

Sau khi làm các bước trên:
1. Màu sắc sẽ hiển thị đúng với các nút tròn có màu
2. Bộ lọc chỉ áp dụng khi nhấn nút "Áp dụng lọc"
3. Khi chọn checkbox hay màu sắc, danh sách sản phẩm không thay đ



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
