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
                <!-- Hiển thị phần trăm giảm giá -->
                <span 
                  v-if="product.discount_percent && product.discount_percent > 0" 
                  class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                >
                  -{{ product.discount_percent }}%
                </span>
                <span 
                  v-else-if="product.is_on_sale" 
                  class="absolute top-4 left-4 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                >
                  Hot
                </span>
                <!-- Badge loại khuyến mãi -->
                <span 
                  v-if="product.discount_type === 'preorder' || product.is_pre_order" 
                  class="absolute top-4 right-4 bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold"
                >
                  Pre-Order
                </span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-2">
                  <!-- Giá sale -->
                  <span v-if="product.is_on_sale" class="text-xl font-bold text-red-500">
                    {{ formatPrice(product.sale_price || product.price) }}
                  </span>
                  <span v-else class="text-xl font-bold text-primary">
                    {{ formatPrice(product.price) }}
                  </span>
                  <!-- Giá gốc (có gạch) -->
                  <span v-if="product.is_on_sale && product.original_price" class="text-sm text-gray-400 line-through">
                    {{ formatPrice(product.original_price) }}
                  </span>
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
                @click="handleBuyNow(product)" 
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
                <!-- Hiển thị phần trăm giảm giá cho trending -->
                <span 
                  v-if="product.discount_percent && product.discount_percent > 0" 
                  class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                >
                  -{{ product.discount_percent }}%
                </span>
                <!-- Badge Pre-Order -->
                <span 
                  v-if="product.discount_type === 'preorder' || product.is_pre_order" 
                  class="absolute top-4 right-4 bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold"
                >
                  Pre-Order
                </span>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4">
                  <span class="text-white text-sm font-semibold">🔥 Đã bán {{ product.sold || 0 }}</span>
                </div>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-4">
                  <span v-if="product.is_on_sale" class="text-xl font-bold text-red-500">
                    {{ formatPrice(product.sale_price || product.price) }}
                  </span>
                  <span v-else class="text-xl font-bold text-primary">
                    {{ formatPrice(product.price) }}
                  </span>
                  <span v-if="product.is_on_sale && product.original_price" class="text-sm text-gray-400 line-through">
                    {{ formatPrice(product.original_price) }}
                  </span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button 
                @click="handleBuyNow(product)" 
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
                <!-- Badge Mới -->
                <span class="absolute top-4 left-4 bg-emerald-600 text-white px-3 py-1 rounded-full text-sm uppercase font-bold">Mới</span>
                
                <!-- Hiển thị sale cho sản phẩm mới nếu có -->
                <span 
                  v-if="product.discount_percent && product.discount_percent > 0" 
                  class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                >
                  -{{ product.discount_percent }}%
                </span>
                
                <!-- Badge Pre-Order nếu có -->
                <span 
                  v-if="product.discount_type === 'preorder' || product.is_pre_order" 
                  class="absolute bottom-4 left-4 bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold"
                >
                  Pre-Order
                </span>
              </div>
              <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-1 line-clamp-1">{{ product.name }}</h3>
                <div class="flex items-baseline space-x-2 mb-4">
                  <!-- Hiển thị giá sale nếu có -->
                  <span v-if="product.is_on_sale" class="text-xl font-bold text-red-500">
                    {{ formatPrice(product.sale_price || product.price) }}
                  </span>
                  <span v-else class="text-xl font-bold text-primary">
                    {{ formatPrice(product.price) }}
                  </span>
                  <!-- Giá gốc có gạch ngang -->
                  <span v-if="product.is_on_sale && product.original_price" class="text-sm text-gray-400 line-through">
                    {{ formatPrice(product.original_price) }}
                  </span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button 
                @click="handleBuyNow(product)" 
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
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import { useCart } from '@/utils/useCart'
import axios from 'axios'

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

// ==================== COMPOSABLES ====================
const page = usePage()
const { addToCart: addToCartGlobal, fetchCart } = useCart()

// ==================== REACTIVE DATA ====================
const banners = ref(props.banners || [])
const hotSales = ref(props.hotSales || [])
const trending = ref(props.trending || [])
const newProducts = ref(props.newProducts || [])
const newsList = ref(props.newsList || [])
const loading = ref(false)
const isProcessing = ref(false)

// Countdown
const countdown = ref({ hours: '23', minutes: '45', seconds: '12' })
let countdownInterval = null
let autoPlayInterval = null
let carouselInitialized = false

// ==================== DEFAULT IMAGE (BASE64) ====================
const DEFAULT_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23f3f4f6"/%3E%3Ctext x="200" y="195" font-family="Arial" font-size="20" text-anchor="middle" fill="%239ca3af"%3ENo Image%3C/text%3E%3Ctext x="200" y="225" font-family="Arial" font-size="14" text-anchor="middle" fill="%23d1d5db"%3EProduct%3C/text%3E%3C/svg%3E'

// ==================== COMPUTED ====================
const isAuthenticated = computed(() => {
  return !!page.props.auth?.user
})

// ==================== METHODS ====================
const getDefaultImage = () => DEFAULT_IMAGE

const getProductUrl = (product) => {
  if (product && product.slug) {
    return route('product.detail', { slug: product.slug })
  }
  return '#'
}

const getProductImage = (product) => {
  if (!product) return DEFAULT_IMAGE

  const image = product.image
  if (!image) return DEFAULT_IMAGE

  if (Array.isArray(image)) {
    return image[0] || DEFAULT_IMAGE
  }

  if (typeof image === 'string' && image !== '/images/default-product.jpg') {
    return image
  }

  return DEFAULT_IMAGE
}

const handleImageError = (e) => {
  if (e.target.src === DEFAULT_IMAGE) {
    e.target.style.display = 'none'
    return
  }
  e.target.src = DEFAULT_IMAGE
  e.target.onerror = null
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return Number(price).toLocaleString('vi-VN') + '₫'
}

// ==================== HÀM LƯU VÀO LOCALSTORAGE ====================
const saveToLocalStorage = (variantId, product, quantity = 1, isPreOrder = false) => {
  try {
    // Lấy giỏ hàng hiện tại
    let cartData = {}
    const existingCart = localStorage.getItem('cart')
    if (existingCart) {
      try {
        cartData = JSON.parse(existingCart)
      } catch (e) {
        console.warn('⚠️ Parse cart error, using empty cart')
        cartData = {}
      }
    }
    
    // Lấy giá sản phẩm
    const price = product.sale_price || product.price || 0
    
    // Thêm hoặc cập nhật sản phẩm
    cartData[variantId] = {
      quantity: quantity,
      price: price,
      product_id: product.id,
      name: product.name,
      image: getProductImage(product),
      is_pre_order: isPreOrder ? 1 : 0
    }
    
    localStorage.setItem('cart', JSON.stringify(cartData))
    console.log('✅ Đã lưu vào localStorage:', cartData)
    
    // Tính tổng số lượng
    const totalCount = Object.values(cartData).reduce((sum, item) => sum + (item.quantity || 0), 0)
    console.log('📦 Tổng số lượng trong giỏ:', totalCount)
    
    return { success: true, cartData, totalCount }
  } catch (error) {
    console.error('❌ Lỗi lưu localStorage:', error)
    return { success: false, error: error.message }
  }
}

// ==================== HÀM GỌI API THÊM VÀO GIỎ ====================
const callAddToCartAPI = async (variantId, quantity = 1) => {
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await axios.post('/api/cart/add', {
      variant_id: variantId,
      quantity: quantity
    }, {
      headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })
    
    return { success: true, data: response.data }
  } catch (error) {
    console.error('❌ API add to cart error:', error)
    return { 
      success: false, 
      message: error.response?.data?.message || 'Không thể thêm vào giỏ hàng',
      status: error.response?.status
    }
  }
}

// ==================== HÀM MUA NGAY ====================
const handleBuyNow = async (product) => {
  // Chặn click liên tục
  if (isProcessing.value) {
    console.log('⏳ Đang xử lý, vui lòng chờ...')
    return
  }

  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    sessionStorage.setItem('redirectAfterLogin', window.location.href)
    router.get(route('login'))
    return
  }

  // Kiểm tra xem có phải pre-order không
  const isPreOrder = product.discount_type === 'preorder' || product.is_pre_order || false

  // Lấy variant_id từ sản phẩm
  let variantId = product.default_variant_id || product.variants?.[0]?.id

  isProcessing.value = true
  loading.value = true

  try {
    // ✅ TRƯỜNG HỢP 1: PRE-ORDER - Lưu trực tiếp vào localStorage
    if (isPreOrder) {
      console.log('🟣 Pre-order product, saving directly to localStorage...')
      
      // Nếu không có variant, tạo variant ảo
      const finalVariantId = variantId || `product_${product.id}`
      
      // Lưu vào localStorage
      const result = saveToLocalStorage(finalVariantId, product, 1, true)
      
      if (result.success) {
        console.log('✅ Pre-order saved to localStorage, redirecting to checkout...')
        loading.value = false
        isProcessing.value = false
        
        // 👉 CHUYỂN ĐẾN CHECKOUT
        router.get(route('checkout'))
        return
      } else {
        alert('Không thể lưu thông tin đặt hàng. Vui lòng thử lại!')
        loading.value = false
        isProcessing.value = false
        return
      }
    }

    // ✅ TRƯỜNG HỢP 2: SẢN PHẨM THƯỜNG - Gọi API thêm vào giỏ
    console.log('🔵 Normal product, calling API to add to cart...')
    
    // Nếu không có variant, tạo variant ảo và lưu thẳng
    if (!variantId) {
      console.warn('⚠️ No variant found, using product_id as fallback')
      const fakeVariantId = `product_${product.id}`
      const result = saveToLocalStorage(fakeVariantId, product, 1, false)
      
      if (result.success) {
        loading.value = false
        isProcessing.value = false
        router.get(route('checkout'))
        return
      }
    }

    // Gọi API thêm vào giỏ hàng
    const apiResult = await callAddToCartAPI(variantId, 1)
    
    if (apiResult.success) {
      console.log('✅ Added to cart via API successfully')
      
      // 👉 QUAN TRỌNG: KHÔNG CẦN ĐỢI fetchCart, CHỈ CẦN LƯU LOCALSTORAGE LÀ ĐỦ
      // Vì trang checkout sẽ tự đọc từ localStorage
      
      loading.value = false
      isProcessing.value = false
      
      // 👉 CHUYỂN ĐẾN CHECKOUT NGAY LẬP TỨC
      router.get(route('checkout'))
      return
    } else {
      // API thất bại
      console.warn('❌ API add to cart failed:', apiResult.message)
      
      // Nếu lỗi là hết hàng
      if (apiResult.message?.toLowerCase().includes('hết hàng') || 
          apiResult.message?.toLowerCase().includes('stock')) {
        alert(apiResult.message)
        loading.value = false
        isProcessing.value = false
        return
      }
      
      // FALLBACK: Lưu trực tiếp vào localStorage
      console.log('🔄 Fallback: Saving directly to localStorage...')
      const finalVariantId = variantId || `product_${product.id}`
      const result = saveToLocalStorage(finalVariantId, product, 1, false)
      
      if (result.success) {
        loading.value = false
        isProcessing.value = false
        router.get(route('checkout'))
        return
      } else {
        alert('Không thể thêm vào giỏ hàng. Vui lòng thử lại!')
        loading.value = false
        isProcessing.value = false
        return
      }
    }
    
  } catch (error) {
    console.error('❌ Buy now error:', error)
    
    // Fallback cuối cùng: lưu trực tiếp vào localStorage
    try {
      const finalVariantId = variantId || `product_${product.id}`
      const result = saveToLocalStorage(finalVariantId, product, 1, isPreOrder)
      
      if (result.success) {
        loading.value = false
        isProcessing.value = false
        router.get(route('checkout'))
        return
      }
    } catch (fallbackError) {
      console.error('❌ Fallback also failed:', fallbackError)
    }
    
    alert('Có lỗi xảy ra. Vui lòng thử lại!')
    loading.value = false
    isProcessing.value = false
  }
}

// ==================== COUNTDOWN ====================
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

// ==================== CAROUSEL ====================
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
  
  console.log('=== WELCOME.VUE DEBUG ===')
  console.log('Banners:', banners.value)
  console.log('HotSales:', hotSales.value)
  console.log('Trending:', trending.value)
  console.log('New Products:', newProducts.value)
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
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}
.group-hover\:gap-2 {
  gap: 0.5rem;
}
</style>