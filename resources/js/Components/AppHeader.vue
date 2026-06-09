<template>
  <header class="w-full top-0 sticky z-[100] bg-white border-b border-gray-200 shadow-sm">
    <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-4 md:px-8 py-4">
      <!-- Logo -->
      <div class="flex items-center gap-8">
        <Link :href="route('home')" class="font-headline-lg text-xl md:text-2xl font-bold hover:opacity-80 transition-opacity">
          <span class="text-primary">BigBag</span><span class="text-gray-800">.vn</span>
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
                <template v-for="cat in laptopCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
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
                <template v-for="cat in bagCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
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
                <template v-for="brand in brands" :key="brand.id">
                  <Link 
                    v-if="brand.slug"
                    :href="getCategoryUrl(brand.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ brand.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ brand.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Các link đơn -->
          <Link :href="route('wholesale')" class="font-label-md text-sm text-gray-700 hover:text-primary">Mua sỉ</Link>
          <Link :href="route('promotion')" class="font-label-md text-sm text-gray-700 hover:text-primary">Khuyến mãi</Link>
          <Link :href="route('home') + '#gioi-thieu'" class="font-label-md text-sm text-gray-700 hover:text-primary">Giới thiệu</Link>
          <Link :href="route('category', { slug: 'new-arrivals' })" class="font-label-md text-sm text-primary border-b-2 border-primary pb-1">Sản phẩm mới</Link>
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
        <!-- Nếu chưa đăng nhập -->
        <Link v-if="!user" :href="route('login')" class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">person</span>
        </Link>

        <!-- Nếu đã đăng nhập: Dropdown -->
        <div v-else class="relative" ref="userDropdownRef">
          <button 
            @click="toggleDropdown" 
            class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary focus:outline-none"
          >
            <span class="material-symbols-outlined">account_circle</span>
          </button>
          <div 
            v-if="dropdownOpen" 
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
          >
            <Link 
              :href="route('profile.edit')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">person</span>
              Hồ sơ
            </Link>
            <button 
              @click="handleLogout" 
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">logout</span>
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Giỏ hàng -->
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartCount }}</span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const cartCount = ref(3)

// Dropdown state
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

// Lọc danh mục Balo
const laptopCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('balo') || c.name?.toLowerCase().includes('balo')) && c.slug
  ).slice(0, 8)
})

// Lọc danh mục Cặp - Túi
const bagCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('tui') || c.slug?.includes('cap') ||
     c.name?.toLowerCase().includes('túi') || c.name?.toLowerCase().includes('cặp')) && c.slug
  ).slice(0, 8)
})

// Hàm tạo URL an toàn
const getCategoryUrl = (slug) => {
  if (!slug) return '#'
  try {
    return route('category', { slug })
  } catch (e) {
    console.warn(`Invalid slug: ${slug}`, e)
    return '#'
  }
}

// Toggle dropdown
const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

// Đóng dropdown
const closeDropdown = () => {
  dropdownOpen.value = false
}

// Xử lý logout
const handleLogout = () => {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      window.location.href = route('home')
    }
  })
}

// Xử lý tìm kiếm
const handleSearch = () => {
  if (searchKeyword.value.trim()) {
    router.get(route('category', { slug: 'tim-kiem' }), { q: searchKeyword.value })
  }
}

// Đóng dropdown khi click bên ngoài
const handleClickOutside = (event) => {
  if (userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
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