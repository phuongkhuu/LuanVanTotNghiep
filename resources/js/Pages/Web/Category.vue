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
        <!-- Phần Product List trong Category.vue -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <template v-if="productList && productList.length">
            <div v-for="product in productList" :key="product.id" class="product-card-hover group bg-white border border-gray-100 rounded-lg overflow-hidden flex flex-col">
              <Link :href="route('product.detail', { slug: product.slug })" class="block">
                <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                  <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform" :alt="product.name">
                  
                  <!-- Badge giảm giá -->
                  <span v-if="product.badge" class="absolute top-4 left-4 px-3 py-1 text-xs rounded-full" :class="product.badgeClass">
                    {{ product.badge }}
                  </span>
                  
                  <!-- Badge Pre-order -->
                  <span v-if="product.is_preorder" class="absolute top-4 right-4 bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">
                    Pre-Order
                  </span>
                  
                  <button class="absolute top-4 right-4 p-2 bg-white/80 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="material-symbols-outlined text-sm">favorite</span>
                  </button>
                </div>
                <div class="p-4 flex flex-col flex-grow">
                  <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">{{ product.brandCategory }}</p>
                  <h3 class="font-semibold text-base mb-1 line-clamp-1">{{ product.name }}</h3>
                  <div class="flex items-baseline space-x-2 mt-auto">
                    <!-- Hiển thị giá sale nếu có -->
                    <span v-if="product.is_on_sale" class="font-bold text-red-500">
                      {{ product.sale_price || product.price }}
                    </span>
                    <span v-else class="font-bold text-primary">
                      {{ product.price }}
                    </span>
                    <!-- Giá gốc có gạch ngang -->
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
            Không có sản phẩm nào phù hợp với bộ lọc.
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
  selectedFilters: { type: Object, default: () => ({}) }
})

// ---------- PHÂN TRANG ----------
const currentPage = ref(1)
const perPage = ref(5)

const isServerPaginated = computed(() => {
  return props.products && typeof props.products === 'object' && 
         'data' in props.products && 'links' in props.products
})

const totalItems = computed(() => {
  if (isServerPaginated.value) {
    return props.products.meta?.total || 0
  }
  return Array.isArray(props.products) ? props.products.length : 0
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

const paginationData = computed(() => {
  if (isServerPaginated.value) {
    return props.products
  }
  const links = []
  links.push({
    url: currentPage.value > 1 ? '#' : null,
    label: '&laquo;',
    active: false,
    page: currentPage.value > 1 ? currentPage.value - 1 : null
  })
  for (let i = 1; i <= totalPages.value; i++) {
    links.push({
      url: '#',
      label: String(i),
      active: i === currentPage.value,
      page: i
    })
  }
  links.push({
    url: currentPage.value < totalPages.value ? '#' : null,
    label: '&raquo;',
    active: false,
    page: currentPage.value < totalPages.value ? currentPage.value + 1 : null
  })
  return {
    links,
    meta: {
      total: totalItems.value,
      last_page: totalPages.value
    }
  }
})

const goToPage = (url, page) => {
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

const sortOptions = [
  { value: 'newest', label: 'Mới nhất' },
  { value: 'price_asc', label: 'Giá: Thấp đến Cao' },
  { value: 'price_desc', label: 'Giá: Cao đến Thấp' },
  { value: 'popular', label: 'Phổ biến nhất' }
]

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
  
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

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
  
  const params = new URLSearchParams()
  params.set('page', '1')
  const url = route('category', { slug: props.slug }) + '?' + params.toString()
  router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const addToCart = (product) => {
  router.get(route('product.detail', { slug: product.slug }))
}

onMounted(() => {
  console.log("Xem tên danh mục",prop.categoryName);
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