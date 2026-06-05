<template>
  <div>
    <Head title="Trang chủ - BigBag Premium Utility Carry Gear" />

    <!-- Header (giữ nguyên) -->
    <header class="w-full top-0 sticky z-[100] glass-nav border-b border-outline-variant shadow-sm bg-background">
      <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-margin-desktop py-4">
        <div class="flex items-center gap-8">
          <Link class="font-headline-lg text-headline-md tracking-tighter text-primary" href="/">BigBag.vn</Link>
          <div class="hidden md:flex items-center gap-6">
            <!-- Dropdown Balo -->
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Balo</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link v-for="cat in laptopCategories" :key="cat.id" :href="route('category', { slug: cat.slug })" class="text-body-sm text-on-surface-variant hover:text-primary">
                    {{ cat.name }}
                  </Link>
                </div>
              </div>
            </div>
            <!-- Dropdown Cặp - Túi -->
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Cặp - Túi</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link v-for="cat in bagCategories" :key="cat.id" :href="route('category', { slug: cat.slug })" class="text-body-sm text-on-surface-variant hover:text-primary">
                    {{ cat.name }}
                  </Link>
                </div>
              </div>
            </div>
            <!-- Dropdown Brands -->
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Brands</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link v-for="brand in brands" :key="brand.id" :href="route('category', { slug: brand.slug || brand.name.toLowerCase() })" class="text-body-sm text-on-surface-variant hover:text-primary">
                    {{ brand.name }}
                  </Link>
                </div>
              </div>
            </div>
            <!-- Các link đơn -->
            <Link :href="route('wholesale')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Mua sỉ</Link>
            <Link :href="route('promotion')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Khuyến mãi</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">Giới thiệu</Link>
            <Link class="font-label-md text-label-md text-primary border-b-2 border-primary pb-1" href="#">New Arrivals</Link>
          </div>
        </div>
        <!-- Search -->
        <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
          <div class="relative w-full">
            <input 
              v-model="searchKeyword"
              @keyup.enter="handleSearch"
              class="w-full bg-surface-container border border-outline-variant rounded-full py-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" 
              placeholder="Tìm kiếm sản phẩm..." 
              type="text">
            <button @click="handleSearch" class="absolute right-4 top-1/2 -translate-y-1/2">
              <span class="material-symbols-outlined text-on-surface-variant">search</span>
            </button>
          </div>
        </div>
        <!-- User & Cart -->
        <div class="flex items-center gap-2">
          <Link v-if="!user" :href="route('register')" class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">person</span>
          </Link>
          <Link v-else :href="route('profile.edit')" class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">account_circle</span>
          </Link>
          <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartCount }}</span>
          </Link>
        </div>
      </nav>
    </header>

    <!-- Hero Carousel (giữ nguyên) -->
    <section class="relative group overflow-hidden">
      <div class="overflow-x-auto snap-x snap-mandatory flex hide-scrollbar" id="hero-carousel">
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full relative overflow-hidden">
            <img alt="Sale Banner 1" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDxx0m6cgeB_wFfg7s6Gg9fUlG74LJAjQX52e76-kLKbboHcvdGuP8wLvolaZ2nn44uSU4mSzGcMnWRrxegCgrBQPS_CJCrqTw_lR9qipVD13hl9T_DV9Vwt4PmieoYHWvSuOgDjr4TLs2YpCS6eO_P1Ya4-_gUurI8xgCqtWZq3EvAe9WrB0_PXR8pDs-UdKo5u7vHbg-s3eYwYc1YpaZsyCDVrp1oAxlY5NkvxU8DCvx9sj5PwWBzawIL86tZy9He4cl9TZdngHc">
          </div>
        </div>
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full relative overflow-hidden">
            <img alt="Sale Banner 2" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCp5eQ5SZCwA43e9ZQV6q5AsixqVrngZDfmTBxJnnZZnN9FJ-UksaoW1_6ST0Oc6LoiJEgpvMf4K1zxMWMDQMiSsoVTBNGkDP_gHl8zHBONErOgONG9qdZ1Uj2M143jhRomrMwOr7m_k66Z1qw8Dg6V-3CBkzDQGEdnu4uUQFh56yuIQox-XTGWy1stgcNRm_9bBcHtgvXHSzjDoLZxarh8vh22_7wpoMLjWSTigP2X-laqEhuIKyvDhR7HHBaSrePhkDvbOjOKw9c">
          </div>
        </div>
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full bg-surface-container-low flex items-center">
            <div class="max-w-[1440px] mx-auto px-margin-desktop grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
              <div class="z-10">
                <div class="inline-flex items-center px-4 py-2 bg-tertiary-fixed rounded-full text-primary mb-6">
                  <span class="material-symbols-outlined text-[18px] mr-2" style="font-variation-settings: 'FILL' 1;">eco</span>
                  <span class="font-label-sm text-label-sm uppercase tracking-wider">BỀN BỈ & THÂN THIỆN MÔI TRƯỜNG</span>
                </div>
                <h1 class="font-display-lg text-display-lg text-on-background mb-6 leading-tight">Tự Do <span class='text-primary italic'>Khám Phá</span>,<br>Trải Nghiệm Đỉnh Cao</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-lg">Đồng hành cùng bạn trên mọi nẻo đường. BigBag mang đến bộ sưu tập Balo dã ngoại cao cấp, tối ưu cho mọi chuyến đi.</p>
                <div class="flex flex-wrap gap-4">
                  <button class="bg-primary text-on-primary px-8 py-4 rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">Săn Ngay Deal Mới</button>
                </div>
              </div>
              <div class="relative">
                <img alt="Adventure" class="rounded-xl shadow-2xl h-[450px] w-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC2oIX2ZXuMRF61KyS0HPGQ4C1jpXMcV244LQH5GGO8TuK6Wg_bXXEqchbz9CShLLk4HDYcJP5IUTiqVvFBtnI-IyrZPGtlmb663en8YlXThpEIWlBcMnF3fl4RpfRjFiesjVp2C5MNplsVssGWIw0A5bVIirI3zrVH0chvYxvqS9H7XzCPs3wmS8um8qoctJiWPWOPnxNkv86moCcvFY5htC21WfmAqMrgHrW19sO51qVt3MsgjyXq7DJCMC7XvmMxqQalDsfYru8">
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="absolute left-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" id="prev-hero">
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button class="absolute right-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" id="next-hero">
        <span class="material-symbols-outlined">chevron_right</span>
      </button>
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2" id="carousel-indicators"></div>
    </section>

    <!-- Loading State -->
    <div v-if="loading" class="fixed inset-0 bg-white/80 z-50 flex items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
    </div>

    <!-- HOT SALE SECTION - Lấy từ DB -->
    <section class="py-section-gap bg-[#FFF5F2]">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <div class="flex flex-col md:flex-row justify-center items-center gap-6 mb-4">
            <h2 class="font-headline-lg text-headline-lg">Sale Cực Sốc</h2>
            <div class="flex items-center gap-2 text-on-surface">
              <span class="font-label-md">Kết thúc sau:</span>
              <div class="flex gap-1">
                <span class="bg-on-surface text-white px-2 py-1 rounded text-label-md font-bold">{{ countdown.hours }}</span>:
                <span class="bg-on-surface text-white px-2 py-1 rounded text-label-md font-bold">{{ countdown.minutes }}</span>:
                <span class="bg-on-surface text-white px-2 py-1 rounded text-label-md font-bold">{{ countdown.seconds }}</span>
              </div>
            </div>
          </div>
          <p class="font-body-md text-body-md text-on-surface-variant">Ưu đãi giới hạn chỉ trong hôm nay</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <div v-for="product in hotSales" :key="product.id" class="block product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden transition-all duration-300">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden">
                <img :src="product.image_url || product.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                <span class="absolute top-4 left-4 bg-error text-white px-3 py-1 rounded-full font-label-sm text-label-sm">
                  -{{ getDiscountPercent(product) }}%
                </span>
                <span v-if="product.is_preorder" class="absolute top-4 right-4 bg-warning text-white px-3 py-1 rounded-full font-label-sm text-label-sm">
                  Pre-order
                </span>
              </div>
              <div class="p-4 flex flex-col">
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">{{ product.name }}</p>
                <div class="flex items-baseline space-x-2 mb-2">
                  <span class="font-headline-md text-headline-md text-primary">{{ formatPrice(getFinalPrice(product)) }}</span>
                  <span v-if="getOriginalPrice(product) > getFinalPrice(product)" class="font-body-sm text-body-sm text-on-surface-variant/60 line-through">{{ formatPrice(getOriginalPrice(product)) }}</span>
                </div>
                <div class="flex items-center gap-1 mb-4">
                  <div class="flex text-yellow-400">
                    <span v-for="i in 5" :key="i" class="text-sm">
                      {{ i <= Math.floor(product.rating || 0) ? '★' : '☆' }}
                    </span>
                  </div>
                  <span class="text-xs text-on-surface-variant">({{ product.reviews_count || 0 }})</span>
                </div>
                <button class="w-full py-3 text-white rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity bg-primary font-bold">Mua Ngay!</button>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- TRENDING PRODUCTS - Lấy từ DB -->
    <section class="py-section-gap">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <h2 class="font-headline-lg text-headline-lg mb-2">Đang Được Săn Đón</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Sản phẩm yêu thích nhất tháng này</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <div v-for="product in trending" :key="product.id" class="block product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden transition-all duration-300">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden">
                <img :src="product.image_url || product.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4">
                  <span class="text-white text-sm font-semibold">🔥 Đã bán {{ product.sold_count || 0 }}</span>
                </div>
              </div>
              <div class="p-4 flex flex-col">
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">{{ product.name }}</p>
                <div class="flex items-baseline space-x-2 mb-4">
                  <span class="font-headline-md text-headline-md text-primary">{{ formatPrice(getFinalPrice(product)) }}</span>
                </div>
                <button class="w-full py-3 text-white rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity bg-primary font-bold">Mua Ngay!</button>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- NEW ARRIVALS - Lấy từ DB -->
    <section class="py-section-gap">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <h2 class="font-headline-lg text-headline-lg mb-2">Sản Phẩm Mới Nhất</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Đón đầu xu hướng cùng bộ sưu tập 2024</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <div v-for="product in newProducts" :key="product.id" class="block product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden transition-all duration-300">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden">
                <img :src="product.image_url || product.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                <span class="absolute top-4 left-4 bg-on-surface text-white px-3 py-1 rounded-full font-label-sm text-label-sm uppercase">Mới</span>
              </div>
              <div class="p-4 flex flex-col">
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">{{ product.name }}</p>
                <div class="flex items-baseline space-x-2 mb-4">
                  <span class="font-headline-md text-headline-md text-primary">{{ formatPrice(getFinalPrice(product)) }}</span>
                </div>
                <button class="w-full py-3 text-white rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity bg-primary font-bold">Mua Ngay!</button>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- NEWS & PROMOTIONS - Lấy từ DB -->
    <section class="py-section-gap">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <h2 class="font-headline-lg text-headline-lg mb-2">Tin Tức & Khuyến Mãi</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Cập nhật những câu chuyện và ưu đãi mới nhất</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
          <div v-for="article in newsList" :key="article.id" class="flex flex-col">
            <Link :href="route('news.detail', { id: article.id })" class="aspect-[1.5/1] overflow-hidden mb-6 group rounded-xl block">
              <img :src="article.image_url || article.thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="article.title">
            </Link>
            <Link :href="route('news.detail', { id: article.id })" class="font-label-md text-label-md text-on-surface mb-2 leading-snug hover:text-primary transition-colors uppercase">
              {{ article.title }}
            </Link>
            <p class="text-on-surface-variant/60 text-label-sm mb-4">{{ formatDate(article.created_at) }}</p>
            <p v-if="article.excerpt" class="text-on-surface-variant text-body-sm line-clamp-2">{{ article.excerpt }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Chatbot (giữ nguyên) -->
    <div class="fixed bottom-8 right-8 z-50">
      <button class="w-16 h-16 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center relative group bg-primary text-white hover:bg-[#e66000]">
        <span class="material-symbols-outlined text-3xl">chat</span>
        <div class="absolute bottom-full right-0 mb-4 bg-white p-4 rounded-xl shadow-xl w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all pointer-events-none">
          <p class="font-label-lg text-label-lg text-on-surface mb-1">Xin chào!</p>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Chúng tôi có thể giúp gì cho bạn hôm nay?</p>
          <div class="absolute bottom-[-8px] right-6 w-4 h-4 bg-white rotate-45"></div>
        </div>
      </button>
    </div>

    <!-- Footer (giữ nguyên) -->
    <footer class="w-full border-t border-outline font-['Montserrat'] py-16 text-[#333333]" style="background-color: #F5F5F5;">
      <div class="grid grid-cols-1 md:grid-cols-4 px-margin-desktop max-w-[1440px] mx-auto gap-12 items-start">
        <div class="space-y-4">
          <div class="font-headline-sm text-headline-sm font-bold text-[#333333]">BigBag.vn</div>
          <p class="font-body-sm leading-relaxed text-[#333333]">© 2025 BigBag. Premium Utility Carry Gear.</p>
        </div>
        <div class="flex flex-col gap-2">
          <h4 class="font-label-lg uppercase mb-2 text-[#333333]">VỀ BIGBAG</h4>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Về chúng tôi</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Phát triển bền vững</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Liên hệ</Link>
        </div>
        <div class="flex flex-col gap-2">
          <h4 class="font-label-lg uppercase mb-2 text-[#333333]">HỖ TRỢ KHÁCH HÀNG</h4>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Giao hàng & Trả hàng</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo hành</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo mật</Link>
        </div>
        <div class="flex flex-col gap-4">
          <h4 class="font-label-lg uppercase mb-2 text-[#333333]">ĐĂNG KÝ NHẬN TIN</h4>
          <div class="flex gap-2">
            <input v-model="subscribeEmail" class="bg-white border border-[#333333] px-3 py-2 text-body-sm w-full focus:border-primary outline-none placeholder:text-[#333333]/50 rounded-none" placeholder="Email của bạn" type="email">
            <button @click="handleSubscribe" class="bg-transparent border border-[#333333] text-[#333333] px-4 py-2 flex items-center justify-center hover:bg-primary/10 rounded-none transition-colors">
              <span class="material-symbols-outlined">arrow_forward</span>
            </button>
          </div>
          <div class="flex gap-4">
            <span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors">public</span>
            <span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors">share</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const user = computed(() => page.props.auth.user)

// State
const loading = ref(true)
const searchKeyword = ref('')
const subscribeEmail = ref('')
const cartCount = ref(0)

// Data from DB
const categories = ref([])
const brands = ref([])
const allProducts = ref([])
const discounts = ref([])
const newsList = ref([])

// Filtered products
const hotSales = ref([])
const trending = ref([])
const newProducts = ref([])

// Countdown timer
const countdown = ref({
  hours: '00',
  minutes: '00',
  seconds: '00'
})

let countdownInterval = null

// Helper functions
const formatPrice = (price) => {
  if (!price) return '0₫'
  return new Intl.NumberFormat('vi-VN').format(price) + '₫'
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN')
}

const getDiscountPercent = (product) => {
  if (!product.discount_percent) return 0
  return product.discount_percent
}

const getOriginalPrice = (product) => {
  // Get the minimum variant price
  if (product.variants && product.variants.length > 0) {
    return Math.min(...product.variants.map(v => v.price))
  }
  return product.price || 0
}

const getFinalPrice = (product) => {
  const originalPrice = getOriginalPrice(product)
  const discountPercent = product.discount_percent || 0
  if (discountPercent > 0) {
    return originalPrice * (1 - discountPercent / 100)
  }
  return originalPrice
}

const handleSearch = () => {
  if (searchKeyword.value.trim()) {
    window.location.href = route('category', { search: searchKeyword.value })
  }
}

const handleSubscribe = async () => {
  if (!subscribeEmail.value) {
    alert('Vui lòng nhập email')
    return
  }
  
  try {
    await axios.post('/api/subscribe', { email: subscribeEmail.value })
    alert('Đăng ký nhận tin thành công!')
    subscribeEmail.value = ''
  } catch (error) {
    console.error('Subscribe error:', error)
    alert('Có lỗi xảy ra, vui lòng thử lại sau')
  }
}

// Start countdown
const startCountdown = (endTime) => {
  if (countdownInterval) clearInterval(countdownInterval)
  
  const updateCountdown = () => {
    const now = new Date().getTime()
    const distance = endTime - now
    
    if (distance < 0) {
      clearInterval(countdownInterval)
      countdown.value = { hours: '00', minutes: '00', seconds: '00' }
      return
    }
    
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))
    const seconds = Math.floor((distance % (1000 * 60)) / 1000)
    
    countdown.value = {
      hours: hours.toString().padStart(2, '0'),
      minutes: minutes.toString().padStart(2, '0'),
      seconds: seconds.toString().padStart(2, '0')
    }
  }
  
  updateCountdown()
  countdownInterval = setInterval(updateCountdown, 1000)
}

// Fetch all data from API
const fetchData = async () => {
  loading.value = true
  
  try {
    // Fetch categories
    const categoriesRes = await axios.get('/api/categories')
    categories.value = categoriesRes.data
    
    // Fetch brands
    const brandsRes = await axios.get('/api/brands')
    brands.value = brandsRes.data
    
    // Fetch products with variants and discounts
    const productsRes = await axios.get('/api/products?with=variants,discounts,reviews')
    allProducts.value = productsRes.data
    
    // Fetch news
    const newsRes = await axios.get('/api/news')
    newsList.value = newsRes.data
    
    // Fetch active discounts
    const discountsRes = await axios.get('/api/discounts/active')
    discounts.value = discountsRes.data
    
    // Fetch cart count
    const cartRes = await axios.get('/api/cart/count')
    cartCount.value = cartRes.data.count
    
    // Process products with discounts
    const productsWithDiscounts = allProducts.value.map(product => {
      // Find active discount for this product
      const activeDiscount = discounts.value.find(d => 
        d.product_id === product.id && 
        new Date(d.start_date) <= new Date() && 
        new Date(d.end_date) >= new Date()
      )
      
      if (activeDiscount) {
        product.discount_percent = activeDiscount.discount_percent
      }
      
      return product
    })
    
    // Filter hot sales (products with discount > 0)
    hotSales.value = productsWithDiscounts
      .filter(p => p.discount_percent && p.discount_percent > 0)
      .sort((a, b) => b.discount_percent - a.discount_percent)
      .slice(0, 4)
    
    // Filter trending (products with highest rating or featured)
    trending.value = productsWithDiscounts
      .filter(p => p.is_featured)
      .sort((a, b) => (b.rating || 0) - (a.rating || 0))
      .slice(0, 4)
    
    // Filter new arrivals (latest products)
    newProducts.value = productsWithDiscounts
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 4)
    
    // Set countdown to end of day
    const endOfDay = new Date()
    endOfDay.setHours(23, 59, 59, 999)
    startCountdown(endOfDay.getTime())
    
  } catch (error) {
    console.error('Error fetching data:', error)
    // Fallback to seed data if API fails
    useFallbackData()
  } finally {
    loading.value = false
  }
}

// Fallback data from ProductSeeder
const useFallbackData = () => {
  hotSales.value = [
    { id: 1, name: "Balo Laptop BigBag Pro 15.6 inch", price: 1450000, discount_percent: 20, image_url: "https://via.placeholder.com/400x500", rating: 4.8, reviews_count: 12 },
    { id: 2, name: "Solo Adventure 40L", price: 2100000, discount_percent: 15, image_url: "https://via.placeholder.com/400x500", rating: 4.9, reviews_count: 8 },
    { id: 3, name: "KingBag Crossbody Mini", price: 450000, discount_percent: 30, image_url: "https://via.placeholder.com/400x500", rating: 4.5, reviews_count: 15 },
  ]
  
  trending.value = [
    { id: 1, name: "Balo Laptop BigBag Pro", price: 1450000, image_url: "https://via.placeholder.com/400x500", rating: 4.8, sold_count: 156 },
    { id: 2, name: "Solo Adventure", price: 2100000, image_url: "https://via.placeholder.com/400x500", rating: 4.9, sold_count: 89 },
  ]
  
  newProducts.value = [
    { id: 3, name: "KingBag Crossbody", price: 450000, image_url: "https://via.placeholder.com/400x500", created_at: new Date() },
  ]
}

// Computed categories for dropdowns
const laptopCategories = computed(() => {
  return categories.value.filter(c => c.name.toLowerCase().includes('balo') || c.slug.includes('balo'))
})

const bagCategories = computed(() => {
  return categories.value.filter(c => c.name.toLowerCase().includes('túi') || c.slug.includes('tui'))
})

// Carousel logic (giữ nguyên)
let autoPlayInterval = null

onMounted(() => {
  fetchData()
  
  const carouselEl = document.getElementById('hero-carousel')
  const prevBtn = document.getElementById('prev-hero')
  const nextBtn = document.getElementById('next-hero')
  const indicatorsContainer = document.getElementById('carousel-indicators')
  if (!carouselEl) return

  const totalSlides = carouselEl.children.length
  let currentIndex = 2

  function renderIndicators() {
    if (!indicatorsContainer) return
    indicatorsContainer.innerHTML = ''
    for (let i = 0; i < totalSlides; i++) {
      const dot = document.createElement('div')
      dot.className = `w-2 h-2 rounded-full cursor-pointer transition-all duration-300 ${i === currentIndex ? 'bg-white scale-125' : 'bg-white/40'}`
      dot.dataset.index = i
      dot.addEventListener('click', () => {
        currentIndex = i
        updateCarousel()
        resetTimer()
      })
      indicatorsContainer.appendChild(dot)
    }
  }

  function updateCarousel() {
    const slideWidth = carouselEl.clientWidth
    carouselEl.scrollTo({ left: slideWidth * currentIndex, behavior: 'smooth' })
    const dots = document.querySelectorAll('#carousel-indicators div')
    dots.forEach((dot, idx) => {
      if (idx === currentIndex) {
        dot.classList.add('bg-white', 'scale-125')
        dot.classList.remove('bg-white/40')
      } else {
        dot.classList.remove('bg-white', 'scale-125')
        dot.classList.add('bg-white/40')
      }
    })
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides
    updateCarousel()
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides
    updateCarousel()
  }

  function resetTimer() {
    if (autoPlayInterval) clearInterval(autoPlayInterval)
    autoPlayInterval = setInterval(nextSlide, 5000)
  }

  if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetTimer() })
  if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetTimer() })

  carouselEl.addEventListener('scroll', () => {
    const slideWidth = carouselEl.clientWidth
    const newIndex = Math.round(carouselEl.scrollLeft / slideWidth)
    if (newIndex !== currentIndex && newIndex >= 0 && newIndex < totalSlides) {
      currentIndex = newIndex
      const dots = document.querySelectorAll('#carousel-indicators div')
      dots.forEach((dot, idx) => {
        if (idx === currentIndex) {
          dot.classList.add('bg-white', 'scale-125')
          dot.classList.remove('bg-white/40')
        } else {
          dot.classList.remove('bg-white', 'scale-125')
          dot.classList.add('bg-white/40')
        }
      })
    }
  })

  renderIndicators()
  window.addEventListener('resize', () => updateCarousel())
  updateCarousel()
  resetTimer()
})

onUnmounted(() => {
  if (autoPlayInterval) clearInterval(autoPlayInterval)
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<style>
/* Giữ nguyên toàn bộ style từ file gốc của bạn */
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

.glass-nav {
  backdrop-filter: blur(12px);
  background-color: rgba(251, 249, 245, 0.85);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

#hero-carousel {
  scroll-behavior: smooth;
}

.product-card-hover {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
}

.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}

/* Các style bổ sung */
.shadow-elevation-1 {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.03);
}
.shadow-elevation-2 {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04), 0 2px 4px rgba(0, 0, 0, 0.02);
}
.shadow-elevation-3 {
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.04);
}
.btn-primary {
  background-color: #ff6b00;
  color: #ffffff;
  padding: 0.75rem 1.5rem;
  border-radius: 9999px;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.05em;
  transition: all 0.2s cubic-bezier(0.34, 1.2, 0.64, 1);
}
.btn-primary:hover {
  transform: scale(1.02);
  box-shadow: 0 12px 24px rgba(255, 107, 0, 0.2);
}
.btn-primary:active {
  transform: scale(0.98);
}
.font-headline-xl {
  font-size: 3rem;
  line-height: 1.2;
  letter-spacing: -0.02em;
  font-weight: 700;
}
.font-display-lg {
  font-size: 3rem;
  line-height: 1.2;
  letter-spacing: -0.02em;
  font-weight: 700;
}
@media (min-width: 768px) {
  .font-headline-xl,
  .font-display-lg {
    font-size: 4rem;
  }
}
.glass-card {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}
::-webkit-scrollbar-thumb {
  background: #ff6b00;
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: #e66000;
}
</style>