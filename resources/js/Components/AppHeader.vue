<template>
  <header class="w-full top-0 sticky z-[100] bg-white border-b border-gray-200 shadow-sm">
    <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-4 md:px-8 py-4">
      <!-- Logo -->
      <div class="flex items-center gap-8">
        <Link :href="route('home')" class="font-headline-lg text-xl md:text-2xl tracking-tighter text-primary font-bold">
          BigBag.vn
        </Link>

        <!-- Main Menu Desktop -->
        <div class="hidden md:flex items-center gap-6">
          <!-- Dropdown Balo -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'balo' })" class="font-label-md text-sm text-gray-700 hover:text-primary transition-colors py-4 block">
              Balo
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <Link 
                  v-for="cat in laptopCategories" 
                  :key="cat.id" 
                  :href="route('category', { slug: cat.slug })" 
                  class="text-sm text-gray-600 hover:text-primary"
                >
                  {{ cat.name }}
                </Link>
              </div>
            </div>
          </div>

          <!-- Dropdown Cặp - Túi -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'cap-tui' })" class="font-label-md text-sm text-gray-700 hover:text-primary transition-colors py-4 block">
              Cặp - Túi
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <Link 
                  v-for="cat in bagCategories" 
                  :key="cat.id" 
                  :href="route('category', { slug: cat.slug })" 
                  class="text-sm text-gray-600 hover:text-primary"
                >
                  {{ cat.name }}
                </Link>
              </div>
            </div>
          </div>

          <!-- Dropdown Thương hiệu -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'thuong-hieu' })" class="font-label-md text-sm text-gray-700 hover:text-primary transition-colors py-4 block">
              Thương hiệu
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <Link 
                  v-for="brand in brands" 
                  :key="brand.id" 
                  :href="route('category', { slug: brand.slug })" 
                  class="text-sm text-gray-600 hover:text-primary"
                >
                  {{ brand.name }}
                </Link>
              </div>
            </div>
          </div>

          <!-- Các link đơn -->
          <Link :href="route('wholesale')" class="font-label-md text-sm text-gray-700 hover:text-primary">Mua sỉ</Link>
          <Link :href="route('promotion')" class="font-label-md text-sm text-gray-700 hover:text-primary">Khuyến mãi</Link>
          <Link :href="route('home') + '#gioi-thieu'" class="font-label-md text-sm text-gray-700 hover:text-primary">Giới thiệu</Link>
          <Link :href="route('category', { slug: 'new-arrivals' })" class="font-label-md text-sm text-primary border-b-2 border-primary pb-1">New Arrivals</Link>
        </div>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
        <div class="relative w-full">
          <input 
            v-model="searchKeyword"
            @keyup.enter="handleSearch"
            class="w-full bg-gray-50 border border-gray-200 rounded-full py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" 
            placeholder="Tìm kiếm sản phẩm..." 
            type="text">
          <button @click="handleSearch" class="absolute right-4 top-1/2 -translate-y-1/2">
            <span class="material-symbols-outlined text-gray-400 text-xl">search</span>
          </button>
        </div>
      </div>

      <!-- User & Cart -->
      <div class="flex items-center gap-2">
        <Link v-if="!user" :href="route('login')" class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">person</span>
        </Link>
        <Link v-else :href="route('profile.edit')" class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">account_circle</span>
        </Link>
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartCount }}</span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)

const searchKeyword = ref('')
const cartCount = ref(0)
const categories = ref([])
const brands = ref([])

const laptopCategories = computed(() => {
  return categories.value.filter(c => 
    c.name?.toLowerCase().includes('balo') || 
    c.slug?.includes('balo')
  ).slice(0, 8)
})

const bagCategories = computed(() => {
  return categories.value.filter(c => 
    c.name?.toLowerCase().includes('túi') || 
    c.slug?.includes('tui')
  ).slice(0, 8)
})

const handleSearch = () => {
  if (searchKeyword.value.trim()) {
    router.get(route('category', { search: searchKeyword.value }))
  }
}

const fetchData = async () => {
  try {
    const [categoriesRes, brandsRes, cartRes] = await Promise.all([
      axios.get('/api/categories').catch(() => ({ data: [] })),
      axios.get('/api/brands').catch(() => ({ data: [] })),
      axios.get('/api/cart/count').catch(() => ({ data: { count: 0 } }))
    ])
    categories.value = categoriesRes.data
    brands.value = brandsRes.data
    cartCount.value = cartRes.data.count
  } catch (error) {
    console.error('Error fetching header data:', error)
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}
</style>