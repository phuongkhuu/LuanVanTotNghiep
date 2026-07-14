<template>
  <div>
    <Head title="Trang chủ - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <!-- Hero Carousel -->
    <section v-if="banners && banners.length > 0" class="relative group overflow-hidden">
      <div class="overflow-x-auto snap-x snap-mandatory flex hide-scrollbar" id="hero-carousel">
        <div 
          v-for="(banner, index) in banners" 
          :key="banner.id" 
          class="flex-none w-full snap-center relative"
        >
          <div class="h-[585px] w-full relative overflow-hidden">
            <a v-if="banner.link" :href="banner.link" target="_blank" rel="noopener noreferrer">
              <img 
                :src="banner.image || getDefaultImage()" 
                :alt="'Banner ' + (index + 1)" 
                class="w-full h-full object-cover" 
                loading="lazy"
                @error="handleImageError"
              />
            </a>
            <img 
              v-else 
              :src="banner.image || getDefaultImage()" 
              :alt="'Banner ' + (index + 1)" 
              class="w-full h-full object-cover" 
              loading="lazy"
              @error="handleImageError"
            />
          </div>
          <div v-if="banner.campaign" class="absolute bottom-24 left-1/2 -translate-x-1/2 bg-black/50 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-medium">
            {{ banner.campaign }}
          </div>
        </div>
      </div>

      <button 
        v-if="banners.length > 1"
        class="absolute left-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" 
        id="prev-hero"
        aria-label="Previous slide"
      >
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button 
        v-if="banners.length > 1"
        class="absolute right-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" 
        id="next-hero"
        aria-label="Next slide"
      >
        <span class="material-symbols-outlined">chevron_right</span>
      </button>

      <div v-if="banners.length > 1" class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2" id="carousel-indicators"></div>
    </section>

    <section v-else class="relative group overflow-hidden">
      <div class="relative h-[585px] w-full overflow-hidden bg-gray-200">
        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-r from-blue-500 to-purple-600">
          <div class="text-center text-white">
            <h2 class="text-4xl font-bold mb-4">BigBag Premium Utility Carry Gear</h2>
            <p class="text-xl">Trang bị hoàn hảo cho mọi hành trình</p>
          </div>
        </div>
      </div>
    </section>

    <!-- HOT SALE SECTION -->
    <section class="py-16 bg-gradient-to-br from-amber-50 to-white">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <div class="flex flex-col md:flex-row justify-center items-center gap-6 mb-4">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Sale Cực Sốc</h2>
            <div class="flex items-center gap-2 text-gray-700">
              <span class="font-medium">Kết thúc sau:</span>
              <div class="flex gap-1">
                <span class="bg-gray-800 text-white px-2 py-1 rounded text-sm font-bold">{{ countdown.hours }}</span>:
                <span class="bg-gray-800 text-white px-2 py-1 rounded text-sm font-bold">{{ countdown.minutes }}</span>:
                <span class="bg-gray-800 text-white px-2 py-1 rounded text-sm font-bold">{{ countdown.seconds }}</span>
              </div>
            </div>
          </div>
          <p class="text-gray-500">Ưu đãi giới hạn chỉ trong hôm nay</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="product in hotSales" 
            :key="product.id" 
            class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300"
          >
            <Link :href="getProductUrl(product)" class="block">
              <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                <img 
                  :src="getProductImage(product)" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                  :alt="product.name" 
                  loading="lazy"
                  @error="handleImageError"
                />
                <span 
                  v-if="product.discount" 
                  class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                >
                  -{{ product.discount }}%
                </span>
                <span 
                  v-else 
                  class="absolute top-4 left-4 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                >
                  Hot
                </span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-2">
                  <span class="text-xl font-bold text-primary">{{ formatPrice(product.salePrice || product.price) }}</span>
                  <span v-if="product.originalPrice" class="text-sm text-gray-400 line-through">{{ formatPrice(product.originalPrice) }}</span>
                </div>
                <div class="flex items-center gap-1 mb-4">
                  <div class="flex text-amber-400">
                    <span v-for="i in 5" :key="i" class="text-sm">{{ i <= (product.rating || 0) ? '★' : '☆' }}</span>
                  </div>
                  <span class="text-xs text-gray-400">({{ product.reviews || 0 }})</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button 
                @click="addToCart(product)" 
                class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors"
              >
                Mua Ngay
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TRENDING PRODUCTS -->
    <section class="py-16 bg-white">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Đang Được Săn Đón</h2>
          <p class="text-gray-500">Sản phẩm yêu thích nhất tháng này</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="product in trending" 
            :key="product.id" 
            class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300"
          >
            <Link :href="getProductUrl(product)" class="block">
              <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                <img 
                  :src="getProductImage(product)" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                  :alt="product.name" 
                  loading="lazy"
                  @error="handleImageError"
                />
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4">
                  <span class="text-white text-sm font-semibold">🔥 Đã bán {{ product.sold || 0 }}</span>
                </div>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-4">
                  <span class="text-xl font-bold text-primary">{{ formatPrice(product.price) }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button 
                @click="addToCart(product)" 
                class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors"
              >
                Mua Ngay
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- NEW ARRIVALS -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Sản Phẩm Mới Nhất</h2>
          <p class="text-gray-500">Đón đầu xu hướng cùng bộ sưu tập 2024</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="product in newProducts" 
            :key="product.id" 
            class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300"
          >
            <Link :href="getProductUrl(product)" class="block">
              <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                <img 
                  :src="getProductImage(product)" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                  :alt="product.name" 
                  loading="lazy"
                  @error="handleImageError"
                />
                <span class="absolute top-4 left-4 bg-emerald-600 text-white px-3 py-1 rounded-full text-sm uppercase font-bold">Mới</span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-4">
                  <span class="text-xl font-bold text-primary">{{ formatPrice(product.price) }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button 
                @click="addToCart(product)" 
                class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors"
              >
                Mua Ngay
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- NEWS & PROMOTIONS -->
    <section class="py-16 bg-white">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Tin Tức & Khuyến Mãi</h2>
          <p class="text-gray-500">Cập nhật những câu chuyện và ưu đãi mới nhất</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div 
            v-for="article in newsList" 
            :key="article.id" 
            class="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100"
          >
            <Link :href="route('promotion')" class="block">
              <div class="aspect-[1.5/1] overflow-hidden">
                <img 
                  :src="article.image || getDefaultImage()" 
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                  :alt="article.title" 
                  loading="lazy"
                  @error="handleImageError"
                />
              </div>
              <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                  <span class="text-xs text-primary bg-primary/10 px-2 py-1 rounded-full font-medium">{{ article.category }}</span>
                  <span class="text-xs text-gray-400">{{ article.date }}</span>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">{{ article.title }}</h3>
                <p class="text-gray-500 text-sm line-clamp-2">{{ article.excerpt }}</p>
                <div class="text-primary text-sm mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                  Đọc thêm →
                </div>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// ==================== PROPS ====================
const props = defineProps({
  banners: { 
    type: Array, 
    default: () => [] 
  },
  hotSales: { 
    type: Array, 
    default: () => [] 
  },
  trending: { 
    type: Array, 
    default: () => [] 
  },
  newProducts: { 
    type: Array, 
    default: () => [] 
  },
  newsList: { 
    type: Array, 
    default: () => [] 
  }
})

// ==================== REACTIVE DATA ====================
// Sử dụng computed để xử lý dữ liệu
const banners = ref(props.banners || [])
const hotSales = ref(props.hotSales || [])
const trending = ref(props.trending || [])
const newProducts = ref(props.newProducts || [])
const newsList = ref(props.newsList || [])

// Countdown
const countdown = ref({ hours: '23', minutes: '45', seconds: '12' })
let countdownInterval = null
let autoPlayInterval = null
let carouselInitialized = false

// ==================== DEFAULT IMAGE (BASE64) ====================
// Sử dụng base64 để không cần file ảnh vật lý
const DEFAULT_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23f3f4f6"/%3E%3Ctext x="200" y="195" font-family="Arial" font-size="20" text-anchor="middle" fill="%239ca3af"%3ENo Image%3C/text%3E%3Ctext x="200" y="225" font-family="Arial" font-size="14" text-anchor="middle" fill="%23d1d5db"%3EProduct%3C/text%3E%3C/svg%3E'

// ==================== METHODS ====================
/**
 * Lấy ảnh mặc định
 */
const getDefaultImage = () => {
  return DEFAULT_IMAGE
}

const getProductUrl = (product) => {
  if (product && product.slug) {
    return route('product.detail', { slug: product.slug })
  }
  // Fallback: return '#' or a generic page
  return '#'
}

/**
 * Lấy ảnh sản phẩm (có fallback)
 */
const getProductImage = (product) => {
  if (!product) return DEFAULT_IMAGE

  const image = product.image
  if (!image) return DEFAULT_IMAGE

  // Nếu là mảng, lấy phần tử đầu tiên
  if (Array.isArray(image)) {
    return image[0] || DEFAULT_IMAGE
  }

  // Nếu là chuỗi và không phải default
  if (typeof image === 'string' && image !== '/images/default-product.jpg') {
    return image
  }

  return DEFAULT_IMAGE
}

/**
 * Xử lý lỗi ảnh - FIX LỖI 404 VÀ VÒNG LẶP
 */
const handleImageError = (e) => {
  // Ngăn chặn vòng lặp vô tận
  if (e.target.src === DEFAULT_IMAGE) {
    e.target.style.display = 'none'
    return
  }
  
  // Set ảnh mặc định
  e.target.src = DEFAULT_IMAGE
  // Ngăn gọi lại event
  e.target.onerror = null
}

/**
 * Format giá tiền
 */
const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return Number(price).toLocaleString('vi-VN') + '₫'
}

/**
 * Thêm vào giỏ hàng
 */
const addToCart = (product) => {
  router.get(route('product.detail', { slug: product.slug }))
}

/**
 * Bắt đầu đếm ngược
 */
const startCountdown = () => {
  let hours = 23, minutes = 45, seconds = 12
  if (countdownInterval) clearInterval(countdownInterval)
  countdownInterval = setInterval(() => {
    seconds--
    if (seconds < 0) { 
      seconds = 59
      minutes-- 
    }
    if (minutes < 0) { 
      minutes = 59
      hours-- 
    }
    if (hours < 0) { 
      hours = 0
      minutes = 0
      seconds = 0
      clearInterval(countdownInterval) 
    }
    countdown.value = {
      hours: hours.toString().padStart(2, '0'),
      minutes: minutes.toString().padStart(2, '0'),
      seconds: seconds.toString().padStart(2, '0')
    }
  }, 1000)
}

/**
 * Khởi tạo carousel
 */
const initCarousel = () => {
  const carouselEl = document.getElementById('hero-carousel')
  if (!carouselEl || carouselInitialized || banners.value.length <= 1) return
  carouselInitialized = true

  const prevBtn = document.getElementById('prev-hero')
  const nextBtn = document.getElementById('next-hero')
  const indicatorsContainer = document.getElementById('carousel-indicators')

  const totalSlides = carouselEl.children.length
  let currentIndex = 0

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
    if (totalSlides > 1) {
      autoPlayInterval = setInterval(nextSlide, 5000)
    }
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => { 
      prevSlide()
      resetTimer()
    })
  }
  
  if (nextBtn) {
    nextBtn.addEventListener('click', () => { 
      nextSlide()
      resetTimer()
    })
  }

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
  updateCarousel()
  resetTimer()
}

// ==================== LIFECYCLE ====================
onMounted(() => {
  startCountdown()
  nextTick(() => {
    initCarousel()
  })
  console.log('Welcome.vue mounted. Banners:', banners.value, 'HotSales:', hotSales.value, 'Trending:', trending.value, 'NewProducts:', newProducts.value, 'NewsList:', newsList.value)
})

onUnmounted(() => {
  if (autoPlayInterval) clearInterval(autoPlayInterval)
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<style scoped>
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
.line-clamp-1 { 
  display: -webkit-box; 
  -webkit-line-clamp: 1; 
  -webkit-box-orient: vertical; 
  overflow: hidden; 
}
.line-clamp-2 { 
  display: -webkit-box; 
  -webkit-line-clamp: 2; 
  -webkit-box-orient: vertical; 
  overflow: hidden; 
}

/* Transition animations */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group-hover\:gap-2 {
  gap: 0.5rem;
}
</style>