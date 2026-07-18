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
            <Link :href="route('category', { slug: 'balo' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('balo') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
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
            <Link :href="route('category', { slug: 'cap-tui' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('cap-tui') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
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
            <Link :href="route('category', { slug: 'thuong-hieu' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('thuong-hieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
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
          <Link :href="route('wholesale')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('wholesale') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Mua sỉ
          </Link>
          <Link :href="route('promotion')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('promotion') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Khuyến mãi
          </Link>
          <Link :href="route('home') + '#gioi-thieu'" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveHash('gioi-thieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Giới thiệu
          </Link>
          <Link :href="route('category', { slug: 'new-arrivals' })" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveCategory('new-arrivals') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Sản phẩm mới
          </Link>
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
            class="absolute right-0 mt-2 w-52 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
          >
            <!-- Mục Dashboard cho admin -->
            <Link 
              v-if="user.role === 'admin'"
              :href="route('admin.dashboard')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">dashboard</span>
              Dashboard
            </Link>
            
            <!-- LỊCH SỬ ĐƠN HÀNG -->
            <Link 
              :href="route('orders.history')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">receipt_long</span>
              Lịch sử đơn hàng
            </Link>
            
            <!-- HỒ SƠ -->
            <Link 
              :href="route('profile.edit')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">person</span>
              Hồ sơ
            </Link>
            
            <!-- ĐĂNG XUẤT -->
            <button 
              @click="handleLogout" 
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100 mt-1 pt-2"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">logout</span>
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Giỏ hàng -->
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
            {{ cartCount }}
          </span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'
import { CartEvents } from '@/events/CartEvents'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

// Sử dụng useCart
const { cartCount, fetchCart, reloadCart, getUserId } = useCart()

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
    return '#'
  }
}

// Helper: kiểm tra active cho route name
const isActiveRoute = (routeName) => {
  return route().current(routeName)
}

// Helper: kiểm tra active cho category slug
const isActiveCategory = (slug) => {
  const currentRoute = route().current()
  const params = route().params
  
  if (currentRoute === 'category') {
    const currentSlug = params.slug
    return currentSlug === slug
  }
  return false
}

// Helper: kiểm tra active cho hash anchor (#gioi-thieu)
const isActiveHash = (hash) => {
  if (typeof window !== 'undefined') {
    return window.location.hash === `#${hash}`
  }
  return false
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
const handleLogout = async () => {
  try {
    const userId = getUserId()    
    closeDropdown()
    
    router.post(route('logout'), {}, {
      onSuccess: () => {
        window.user = null
        CartEvents.emitUserChanged('guest')
        setTimeout(() => {
          reloadCart()
        }, 100)
      }
    })
  } catch (error) {
    router.post(route('logout'))
  }
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

// Xử lý sự kiện cart-updated
const handleCartUpdated = (event) => {
  fetchCart()
}

// Xử lý sự kiện user-changed
const handleUserChanged = (event) => {
  reloadCart()
}

// Khi user thay đổi
watch(() => user.value, (newUser, oldUser) => {
  const newId = newUser?.id || 'guest'
  const oldId = oldUser?.id || 'guest'
  
  if (newId !== oldId) {
    window.user = newUser
    reloadCart()
    CartEvents.emitUserChanged(newId)
  }
}, { immediate: true })

// Lưu trữ các handler để cleanup
let cartUpdatedHandler = null
let userChangedHandler = null

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  // Fetch cart lần đầu
  fetchCart()
  
  // Lắng nghe sự kiện cart-updated
  cartUpdatedHandler = handleCartUpdated
  CartEvents.onUpdated(cartUpdatedHandler)
  
  // Lắng nghe sự kiện user-changed
  userChangedHandler = handleUserChanged
  CartEvents.onUserChanged(userChangedHandler)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (cartUpdatedHandler) {
    CartEvents.offUpdated(cartUpdatedHandler)
  }
  if (userChangedHandler) {
    CartEvents.offUserChanged(userChangedHandler)
  }
})
</script>

<style scoped>
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}

.dropdown-menu {
  animation: fadeDown 0.2s ease;
}

@keyframes fadeDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>