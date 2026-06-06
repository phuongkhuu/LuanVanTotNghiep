<template>
  <div>
    <Head title="Danh mục sản phẩm - BigBag Premium Utility Carry Gear" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <!-- Breadcrumb -->
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ categoryName }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ categoryName }}</h1>
          <p class="text-gray-500 max-w-2xl">Khám phá bộ sưu tập {{ categoryName }} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.</p>
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
              <li v-for="brand in filterBrands" :key="brand.key" class="flex items-center mb-2">
                <input type="checkbox" :id="brand.key" class="rounded border-gray-300 text-primary h-4 w-4">
                <label :for="brand.key" class="ml-2 text-sm">{{ brand.label }}</label>
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
              <button v-for="color in filterColors" :key="color.code" class="w-6 h-6 rounded-full border" :style="{ backgroundColor: color.code }" :title="color.label"></button>
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
            <span class="text-sm">Hiển thị {{ products.length }} trên {{ products.length }} sản phẩm</span>
            <div class="flex items-center gap-2">
              <span class="text-sm">Sắp xếp:</span>
              <select class="border-none bg-transparent text-sm">
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
          </div>

          <!-- Pagination -->
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
  slug: String
})

const categoryName = ref('Dòng Sản Phẩm Du Lịch')

const filterCategories = ref([
  { key: 'cat_luggage', label: 'Balo hành lý' },
  { key: 'cat_duffle', label: 'Túi Duffle' },
  { key: 'cat_carryon', label: 'Vali xách tay' }
])

const filterBrands = ref([
  { key: 'brand_apex', label: 'Apex' },
  { key: 'brand_nomad', label: 'Nomad' },
  { key: 'brand_orbit', label: 'Orbit' }
])

const filterMaterials = ref([
  { key: 'mat_nylon', label: 'Ballistic Nylon' },
  { key: 'mat_leather', label: 'Da cao cấp' },
  { key: 'mat_tpu', label: 'Chống nước (TPU)' }
])

const filterColors = ref([
  { code: '#000000', label: 'Đen' },
  { code: '#1e3a8a', label: 'Xanh navy' },
  { code: '#9ca3af', label: 'Xám' },
  { code: '#78350f', label: 'Nâu' }
])

const sortOptions = ref([
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
])

const products = ref([
  { id: 1, name: "Balo Du Lịch Apex 45L", brandCategory: "APEX SERIES", price: "2.450.000₫", oldPrice: "3.200.000₫", badge: "-25%", badgeClass: "bg-primary text-white", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDKCfoSzLMJrOxoHCA3HhoGLKcBD41mK1GiHo2rLvQqPfSDlu-iahA2iuCYG3FJDmGBl32Eq8xRBwW7WTKPl2BigNWLHyLH6uHEAc6FLqY8knmx_85YLpOOguaESmWmXVZywiadplzMKqeL-5xgxQhmUg4yYFqLQa8H_AACoZb3an6NGiQFheINurt9rEw70lX-05QMAGEsKFccFhoh_Sh-T9L3FkeWvgsiA_ut4yvwWd64HTv_b1aRrTL1g6KiNN4Nnn4XSDefVm0" },
  { id: 2, name: "Túi Duffle Chống Nước", brandCategory: "NOMAD", price: "1.850.000₫", oldPrice: "2.500.000₫", badge: "-26%", badgeClass: "bg-primary text-white", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCvEDV1gsTxEIibIDu4S3QzNtSVp4GBZsBBkh9DtJNFMqQVKZarzzovagKQ2-rpY-P0m8JCygvQGs6IUjAW0YNq28zBNwJJ2_Yw6HM5gfw456itxkJq2lZHwsYJc6lf9uy0jUiVdmzSCU0SAxkI_poJReAYvx2ZwLcYWITiv4DWBljbpj4nPAST83-RoMjniZOfvQ36HL6oJ_hT-mKR2F1kHURPsCZs2Fz7rUTiq7XVhnlBjETiiMHTKH0BMT5nbKWNodrzdwA5B8U" },
  { id: 3, name: "Vali Xách Tay Orbit", brandCategory: "ORBIT", price: "3.200.000₫", oldPrice: null, badge: "Mới", badgeClass: "bg-emerald-600 text-white", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAKC3ZnxAUhZHOlhQzlWokjeL283mcUr_xRfTETreTxlJyfbXwR93lPHeymGXe-QSLW2OQ93__KVsY5lbMsHdLpN4KDOsbYd17-qi9-v3y8LG0KX0QhtEsgUOYDZaXJWATGAaUBynvLh3MjjhiUmzvfxolEYm9YKNPZB0IrwScgI2yIltFthDuApBD3rb2XhlvhJ4guWlYNXz32zbUxSsNRoFtVkkFEd7-0xbGn6wgoekUsT3xK7GnC8ozoXfDWhJJ2_Y2LHgmvV_E" },
  { id: 4, name: "Balo Laptop Pro 16 inch", brandCategory: "APEX SERIES", price: "1.950.000₫", oldPrice: "2.400.000₫", badge: "-19%", badgeClass: "bg-primary text-white", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB2moOzz6ydHoJfraJitRWBuhER7pbb6LXfkQIb0qAqGtaP1XIqseW0VZsekc10A4VzG9yfRQf5H5WgSBahClC4oNshtyBBXXpRYCsIeE1ZPwLAoGM13Iu_NvqijHnL8uQ7nCIPsrkKmid7qIGL7BrC3D9BHPlwY9e5xz4Z0fGThAjp_IqneXLLXvtV0V2p9zmMtsDhpE6FMBTy8cyLTr1zQ4EEPRNGEP4rg3DcwDUM5X0eoUpCEKa5LrE3dTEMJ0JaEboY82dQDGER" },
  { id: 5, name: "Túi Đeo Chéo Urban", brandCategory: "STREET", price: "650.000₫", oldPrice: "890.000₫", badge: "-27%", badgeClass: "bg-primary text-white", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBvxwP-CST0f4D1efno3EcTOE_sv9WJoyXiOc95-iajaLl8QbB5N3y8Irv-f9ewS1r4PqoWCF7qmP8CaPojqHvTxPe-BM9pxN1WCg80ZALYHy2oCoTH8iIPf2S-yiQjnIpNcJdn_XDnE709J1rOuY5qt-5EgnyjsjkT0s89_9UsY-4-1MiljppHg4V-TZWmznAvkY5-mQJmg3L15E2HWAmW_QmnhTmVWAzBOCBBYvksixa4Ci009877AvhnoWblHP7TwqCwXP8p_Mq_" },
  { id: 6, name: "Balo Du Lịch 30L", brandCategory: "NOMAD", price: "1.250.000₫", oldPrice: null, badge: null, badgeClass: "", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCIIGnYF1bk7O3WW8_rRR7HTixjVlOIm21_O5gIkElDeZKANejYSlLRzVv5TN0HmbeZMxv3fNQRTnn3ZxTSxg8La_G5F7wO_ayA1wY2xCmzXvdbZTigUlL8df-JW68zWYcu-uzT80fNo6XbssHyv7NGYTnMB746ubigSN24VA5d0UsNiPxx1WBvb46BdfVHMrkzKArcyePMpSKLEwZvRAbO14_OiDbLI6nHNDzpPTr_zctRXqBVLityHCxYlIz67RxCmKsAB9biQGtd" }
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