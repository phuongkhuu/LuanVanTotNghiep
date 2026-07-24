<template>
  <div>
    <Head :title="`${pageTitle} - BigBag Premium Utility Carry Gear`" />
    
    <AppHeader />

    <main class="pt-8 pb-section-gap">
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto mb-8">
        <div class="py-6 border-b border-gray-200">
          <nav class="flex items-center text-gray-500 mb-4 space-x-2 text-sm">
            <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-gray-800 font-medium">{{ pageTitle }}</span>
          </nav>
          <h1 class="font-display-lg text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ pageTitle }}</h1>
          <p class="text-gray-500 max-w-2xl">{{ pageDescription }}</p>
        </div>
      </section>
      
      <section class="px-4 md:px-8 max-w-[1440px] mx-auto flex flex-col md:flex-row gap-6">
        <!-- Sidebar Filters - hiển thị trên mọi trang (kể cả tìm kiếm) nếu slug hợp lệ -->
        <aside v-if="isValidCategory" class="w-full md:w-64 flex-shrink-0 space-y-6">
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
        <div class="flex-1">
          <!-- Thông báo số lượng kết quả khi tìm kiếm -->
          <div v-if="search" class="mb-4 text-sm text-gray-500">
            Tìm thấy <span class="font-semibold text-gray-800">{{ totalItems }}</span> kết quả cho "{{ search }}"
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 items-start">
            <template v-if="productList && productList.length">
              <div v-for="product in productList" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
                <Link :href="route('product.detail', { slug: product.slug })" class="block">
                  <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                    <img 
                      :src="product.image || '/images/default-product.jpg'" 
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform" 
                      :alt="product.name"
                      @error="(e) => e.target.src = '/images/default-product.jpg'"
                    >
                    
                    <!-- Badge container -->
                    <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                      <span v-if="product.badge" class="px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                        {{ product.badge }}
                      </span>
                      <span v-if="product.is_preorder" class="px-3 py-1 text-xs rounded-full bg-purple-600 text-white font-bold">
                        Pre-Order
                      </span>
                    </div>
                    
                    <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                  </div>
                  <div class="p-4 flex flex-col">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                    <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                    <div class="flex items-baseline space-x-2 mt-auto">
                      <span v-if="product.is_on_sale" class="font-bold text-red-500">
                        {{ product.sale_price || product.price }}
                      </span>
                      <span v-else class="font-bold text-primary">
                        {{ product.price }}
                      </span>
                      <span v-if="product.oldPrice" class="text-sm line-through text-gray-400">{{ product.oldPrice }}</span>
                    </div>
                  </div>
                </Link>
                <div class="px-4 pb-4">
                  <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-bold text-sm">
                    Xem chi tiết
                  </button>
                </div>
              </div>
            </template>
            <div v-else class="col-span-full text-center py-12 text-gray-500">
              {{ search ? `Không tìm thấy sản phẩm nào cho "${search}"` : 'Không có sản phẩm nào phù hợp với bộ lọc.' }}
            </div>
          </div>

          <!-- Phân trang -->
          <div v-if="totalPages > 1" class="mt-6 flex justify-center">
            <nav class="flex items-center gap-1">
              <button
                @click="goToPage(null, currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ◄
              </button>
              <button
                v-for="page in displayedPages"
                :key="page"
                @click="goToPage(null, page)"
                class="px-3.5 py-1.5 text-sm rounded-lg transition-colors font-medium"
                :class="currentPage === page ? 'bg-primary text-white' : 'border border-gray-300 hover:bg-gray-50'"
              >
                {{ page }}
              </button>
              <button
                @click="goToPage(null, currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ►
              </button>
            </nav>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  slug: { type: String, default: '' },
  categoryName: { type: String, default: 'Danh mục' },
  products: { type: [Array, Object], default: () => [] },
  filters: { type: Object, default: () => ({
    brands: [],
    materials: [],
    colors: [],
    categories: [],
    minPrice: 0,
    maxPrice: 10000000
  }) },
  selectedFilters: { type: Object, default: () => ({}) },
  search: { type: String, default: '' }
})

// ---------- XÁC ĐỊNH TRẠNG THÁI HỢP LỆ ----------
const isValidCategory = computed(() => {
  // Cho phép hiển thị filter trên mọi trang (kể cả tìm kiếm) nếu có slug
  return props.slug && props.slug !== ''
})

// ---------- PAGE TITLE ----------
const pageTitle = computed(() => {
  if (props.search) return `Kết quả tìm kiếm "${props.search}"`
  // Sử dụng trực tiếp categoryName từ backend, không cần hardcode "Cap Tui"
  return props.categoryName || 'Danh mục'
})

const pageDescription = computed(() => {
  if (props.search) {
    const count = totalItems.value
    return `Tìm thấy ${count} sản phẩm phù hợp với từ khóa "${props.search}"`
  }
  return `Khám phá bộ sưu tập ${props.categoryName || 'này'} cao cấp, được thiết kế cho những chuyến đi xa với độ bền vượt trội và tính năng thông minh.`
})

// ---------- PHÂN TRANG ----------
const currentPage = ref(1)
const perPage = ref(6)

const isServerPaginated = computed(() => {
  return props.products && typeof props.products === 'object' && 
         'data' in props.products && 'links' in props.products
})

const totalItems = computed(() => {
  if (props.products?.meta?.total !== undefined) {
    return props.products.meta.total
  }
  if (Array.isArray(props.products)) {
    return props.products.length
  }
  if (props.products?.data && Array.isArray(props.products.data)) {
    return props.products.data.length
  }
  return 0
})

const totalPages = computed(() => {
  if (isServerPaginated.value) {
    return props.products.meta?.last_page || 1
  }
  return Math.ceil(totalItems.value / perPage.value) || 1
})

const productList = computed(() => {
  if (isServerPaginated.value) {
    return props.products.data || []
  }
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return (Array.isArray(props.products) ? props.products : []).slice(start, end)
})

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

const goToPage = (url, page) => {
  // Nếu đang tìm kiếm, chuyển trang server-side để giữ filter
  if (props.search) {
    if (page && page >= 1 && page <= totalPages.value) {
      const params = new URLSearchParams(window.location.search)
      params.set('page', page)
      if (!params.has('q')) params.set('q', props.search)
      const newUrl = route('search') + '?' + params.toString()
      router.get(newUrl, {}, { preserveState: true, preserveScroll: true })
    }
    return
  }

  if (isServerPaginated.value) {
    if (url) {
      router.get(url, {}, { preserveState: true, preserveScroll: true })
    }
  } else {
    if (page && page >= 1 && page <= totalPages.value) {
      currentPage.value = page
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
}

watch(() => props.products, (newVal) => {
  if (!isServerPaginated.value && Array.isArray(newVal)) {
    currentPage.value = 1
  }
}, { deep: false })

watch(() => props.search, () => {
  currentPage.value = 1
})

// ---------- BỘ LỌC ----------
const tempBrands = ref([])
const tempMaterials = ref([])
const tempCategories = ref([])
const tempColors = ref([])
const tempPriceMin = ref(null)
const tempPriceMax = ref(null)
const tempPriceRange = ref(0)
const sortBy = ref('newest')

const appliedBrands = ref([])
const appliedMaterials = ref([])
const appliedCategories = ref([])
const appliedColors = ref([])
const appliedPriceMin = ref(null)
const appliedPriceMax = ref(null)

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

const applyFilters = () => {
  if (!isValidCategory.value) {
    console.warn('Không thể áp dụng bộ lọc khi slug không hợp lệ')
    return
  }

  const params = new URLSearchParams()
  
  appliedBrands.value = [...tempBrands.value]
  appliedMaterials.value = [...tempMaterials.value]
  appliedCategories.value = [...tempCategories.value]
  appliedColors.value = [...tempColors.value]
  appliedPriceMin.value = tempPriceMin.value
  appliedPriceMax.value = tempPriceMax.value
  
  params.set('page', '1')
  
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

  // Nếu đang tìm kiếm, thêm tham số q
  if (props.search) {
    params.set('q', props.search)
  }
  
  let url
  if (props.search) {
    url = route('search') + '?' + params.toString()
  } else {
    url = route('category', { slug: props.slug }) + '?' + params.toString()
  }
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const resetFilters = () => {
  if (!isValidCategory.value) {
    console.warn('⚠️ Không thể reset bộ lọc khi slug không hợp lệ')
    return
  }

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
  
  const params = new URLSearchParams()
  params.set('page', '1')
  if (props.search) {
    params.set('q', props.search)
  }
  
  let url
  if (props.search) {
    url = route('search') + '?' + params.toString()
  } else {
    url = route('category', { slug: props.slug }) + '?' + params.toString()
  }
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const addToCart = (product) => {
  router.get(route('product.detail', { slug: product.slug }))
}

onMounted(() => {
  // Chỉ khôi phục filter nếu slug hợp lệ
  if (!isValidCategory.value) return

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