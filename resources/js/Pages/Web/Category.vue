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