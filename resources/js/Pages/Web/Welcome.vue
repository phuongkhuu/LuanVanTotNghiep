<template>
  <div>
    <Head title="Trang chủ - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <!-- Hero Carousel - Hiển thị banner từ database -->
    <section v-if="banners.length" class="relative group overflow-hidden">
      <div class="overflow-x-auto snap-x snap-mandatory flex hide-scrollbar" id="hero-carousel">
        <div 
          v-for="(banner, index) in banners" 
          :key="banner.id" 
          class="flex-none w-full snap-center relative"
        >
          <div class="h-[585px] w-full relative overflow-hidden">
            <a v-if="banner.link" :href="banner.link" target="_blank">
              <img :src="banner.image" :alt="'Banner ' + (index + 1)" class="w-full h-full object-cover" />
            </a>
            <img v-else :src="banner.image" :alt="'Banner ' + (index + 1)" class="w-full h-full object-cover" />
          </div>
        </div>
      </div>

      <!-- Nút điều hướng -->
      <button 
        class="absolute left-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" 
        id="prev-hero"
      >
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button 
        class="absolute right-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" 
        id="next-hero"
      >
        <span class="material-symbols-outlined">chevron_right</span>
      </button>

      <!-- Indicators -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2" id="carousel-indicators"></div>
    </section>

    <!-- Fallback nếu không có banner (hiển thị ảnh mặc định) -->
    <section v-else class="relative group overflow-hidden">
      <div class="overflow-x-auto snap-x snap-mandatory flex hide-scrollbar" id="hero-carousel">
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full relative overflow-hidden">
            <img alt="Default Banner" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDxx0m6cgeB_wFfg7s6Gg9fUlG74LJAjQX52e76-kLKbboHcvdGuP8wLvolaZ2nn44uSU4mSzGcMnWRrxegCgrBQPS_CJCrqTw_lR9qipVD13hl9T_DV9Vwt4PmieoYHWvSuOgDjr4TLs2YpCS6eO_P1Ya4-_gUurI8xgCqtWZq3EvAe9WrB0_PXR8pDs-UdKo5u7vHbg-s3eYwYc1YpaZsyCDVrp1oAxlY5NkvxU8DCvx9sj5PwWBzawIL86tZy9He4cl9TZdngHc">
          </div>
        </div>
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full relative overflow-hidden">
            <img alt="Default Banner 2" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCp5eQ5SZCwA43e9ZQV6q5AsixqVrngZDfmTBxJnnZZnN9FJ-UksaoW1_6ST0Oc6LoiJEgpvMf4K1zxMWMDQMiSsoVTBNGkDP_gHl8zHBONErOgONG9qdZ1Uj2M143jhRomrMwOr7m_k66Z1qw8Dg6V-3CBkzDQGEdnu4uUQFh56yuIQox-XTGWy1stgcNRm_9bBcHtgvXHSzjDoLZxarh8vh22_7wpoMLjWSTigP2X-laqEhuIKyvDhR7HHBaSrePhkDvbOjOKw9c">
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
          <div v-for="product in hotSales" :key="product.id" class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                <span class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm">
                  -{{ product.discount }}%
                </span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-2">
                  <span class="text-xl font-bold text-primary">{{ formatPrice(product.salePrice) }}</span>
                  <span class="text-sm text-gray-400 line-through">{{ formatPrice(product.originalPrice) }}</span>
                </div>
                <div class="flex items-center gap-1 mb-4">
                  <div class="flex text-amber-400">
                    <span v-for="i in 5" :key="i" class="text-sm">{{ i <= product.rating ? '★' : '☆' }}</span>
                  </div>
                  <span class="text-xs text-gray-400">({{ product.reviews }})</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors">
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
          <div v-for="product in trending" :key="product.id" class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4">
                  <span class="text-white text-sm font-semibold">🔥 Đã bán {{ product.sold }}</span>
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
              <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors">
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
          <div v-for="product in newProducts" :key="product.id" class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden">
                <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
                <span class="absolute top-4 left-4 bg-emerald-600 text-white px-3 py-1 rounded-full text-sm uppercase">Mới</span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-4">
                  <span class="text-xl font-bold text-primary">{{ formatPrice(product.price) }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCart(product)" class="w-full py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors">
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
          <div v-for="article in newsList" :key="article.id" class="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
            <Link :href="route('promotion')" class="block">
              <div class="aspect-[1.5/1] overflow-hidden">
                <img :src="article.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="article.title">
              </div>
              <div class="p-5">
                <div class="flex items-center gap-2 mb-3">
                  <span class="text-xs text-primary bg-primary/10 px-2 py-1 rounded-full">{{ article.category }}</span>
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
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// Nhận dữ liệu từ HomeController
const props = defineProps({
  banners: { type: Array, default: () => [] },
  hotSales: { type: Array, default: () => [] },
  trending: { type: Array, default: () => [] },
  newProducts: { type: Array, default: () => [] },
  newsList: { type: Array, default: () => [] }
})

// Gán dữ liệu vào ref
const banners = ref(props.banners)
const hotSales = ref(props.hotSales)
const trending = ref(props.trending)
const newProducts = ref(props.newProducts)
const newsList = ref(props.newsList.length ? props.newsList : [
  { id: 1, title: 'BigBag ra mắt bộ sưu tập Xuân Hè 2024', excerpt: 'Những thiết kế mới nhất với chất liệu thân thiện môi trường, phong cách thời trang công sở hiện đại.', image: 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop', category: 'Sự kiện', date: '15/03/2024' },
  { id: 2, title: 'Ưu đãi đặc biệt dịp 30/4 - Giảm đến 40%', excerpt: 'Nhân dịp lễ lớn, BigBag dành tặng ưu đãi cực sốc cho tất cả sản phẩm balo và túi xách.', image: 'https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?w=800&h=500&fit=crop', category: 'Khuyến mãi', date: '10/04/2024' },
  { id: 3, title: 'Bí quyết chọn balo phù hợp với vóc dáng', excerpt: 'Khám phá những bí quyết chọn balo giúp bạn tôn lên vóc dáng và phong cách riêng.', image: 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800&h=500&fit=crop', category: 'Mẹo hay', date: '05/04/2024' }
])

const formatPrice = (price) => price.toLocaleString('vi-VN') + '₫'

const addToCart = (product) => {
  router.get(route('product.detail', { id: product.id }))
}

const countdown = ref({ hours: '23', minutes: '45', seconds: '12' })
let countdownInterval = null
let autoPlayInterval = null

const startCountdown = () => {
  let hours = 23, minutes = 45, seconds = 12
  if (countdownInterval) clearInterval(countdownInterval)
  countdownInterval = setInterval(() => {
    seconds--
    if (seconds < 0) { seconds = 59; minutes-- }
    if (minutes < 0) { minutes = 59; hours-- }
    if (hours < 0) { hours = 0; minutes = 0; seconds = 0; clearInterval(countdownInterval) }
    countdown.value = {
      hours: hours.toString().padStart(2, '0'),
      minutes: minutes.toString().padStart(2, '0'),
      seconds: seconds.toString().padStart(2, '0')
    }
  }, 1000)
}

// Carousel logic với dynamic banners
let carouselInitialized = false

const initCarousel = () => {
  const carouselEl = document.getElementById('hero-carousel')
  if (!carouselEl || carouselInitialized) return
  carouselInitialized = true

  const prevBtn = document.getElementById('prev-hero')
  const nextBtn = document.getElementById('next-hero')
  const indicatorsContainer = document.getElementById('carousel-indicators')

  // Nếu không có banner, lấy số slide từ DOM
  const totalSlides = carouselEl.children.length
  let currentIndex = 0

  function renderIndicators() {
    if (!indicatorsContainer) return
    indicatorsContainer.innerHTML = ''
    for (let i = 0; i < totalSlides; i++) {
      const dot = document.createElement('div')
      dot.className = `w-2 h-2 rounded-full cursor-pointer transition-all duration-300 ${i === currentIndex ? 'bg-white scale-125' : 'bg-white/40'}`
      dot.dataset.index = i
      dot.addEventListener('click', () => { currentIndex = i; updateCarousel(); resetTimer() })
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

  function nextSlide() { currentIndex = (currentIndex + 1) % totalSlides; updateCarousel() }
  function prevSlide() { currentIndex = (currentIndex - 1 + totalSlides) % totalSlides; updateCarousel() }
  function resetTimer() { if (autoPlayInterval) clearInterval(autoPlayInterval); autoPlayInterval = setInterval(nextSlide, 5000) }

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
  updateCarousel()
  resetTimer()
}

// Khởi tạo carousel khi component mount
onMounted(() => {
  startCountdown()
  nextTick(() => {
    initCarousel()
  })
})

// Nếu banners thay đổi, cập nhật carousel
watch(() => props.banners, (newVal) => {
  // Reset flag để khởi tạo lại
  carouselInitialized = false
  // Đợi DOM cập nhật
  nextTick(() => {
    initCarousel()
  })
})

onUnmounted(() => {
  if (autoPlayInterval) clearInterval(autoPlayInterval)
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
#hero-carousel { scroll-behavior: smooth; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>