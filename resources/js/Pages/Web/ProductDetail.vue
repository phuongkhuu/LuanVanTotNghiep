<template>
  <div>
    <Head :title="product.name" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-6 bg-gray-50">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-6 text-gray-500 text-sm">
        <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link :href="route('category', { slug: product.categorySlug || 'danh-muc' })" class="hover:text-primary">
          {{ product.categoryName || 'Danh mục' }}
        </Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-gray-800 font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <!-- Danh sách thumbnail -->
          <div 
            v-if="thumbnails.length > 0" 
            class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar"
          >
            <div 
              v-for="(thumb, idx) in thumbnails" 
              :key="idx" 
              class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-white flex-shrink-0"
              :class="idx === activeThumb ? 'border-primary' : 'border-gray-200 hover:border-primary'"
              @click="activeThumb = idx"
            >
              <img :src="thumb" class="w-full h-full object-cover" :alt="'Hình ảnh ' + (idx + 1)">
            </div>
          </div>
          <!-- Nếu không có ảnh, hiển thị placeholder -->
          <div v-else class="flex md:flex-col gap-3">
            <div class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
              No image
            </div>
          </div>

          <!-- Ảnh chính -->
          <div class="flex-1 aspect-[4/5] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <img 
              v-if="thumbnails.length > 0" 
              :src="thumbnails[activeThumb]" 
              class="w-full h-full object-cover" 
              alt="Sản phẩm chính"
            >
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
              Không có ảnh
            </div>
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <!-- Thông báo -->
          <div v-if="message" 
               class="p-3 rounded-lg text-sm text-center"
               :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message }}
          </div>

          <div>
            <!-- Hiển thị nhãn Pre-order nếu là sản phẩm pre-order -->
            <span v-if="product.is_preorder" class="inline-block px-3 py-1 bg-orange-500 text-white text-xs rounded-full mb-2 uppercase font-bold">
              Pre-order
            </span>
            <span v-else class="inline-block px-3 py-1 bg-primary text-white text-xs rounded-full mb-2 uppercase font-bold">
              Sản Phẩm Mới
            </span>
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-amber-400 mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined text-base" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-gray-500 text-sm ml-2">({{ product.reviewCount || 0 }} đánh giá)</span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-baseline gap-3">
              <span class="font-headline-md text-3xl font-bold text-primary">{{ formatPrice(variantPrice) }}</span>
              <span v-if="product.oldPrice" class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
              <span v-if="product.discount" class="text-red-500 font-bold text-sm bg-red-50 px-2 py-0.5 rounded-full">{{ product.discount }}</span>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
            
            <!-- Hiển thị tồn kho cho sản phẩm thường -->
            <p v-if="!product.is_preorder && selectedVariant" class="text-sm text-gray-500">
              Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
              </span>
            </p>
            
            <!-- Hiển thị thông báo Pre-order -->
            <div v-if="product.is_preorder" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
              <p class="text-sm text-orange-700 font-semibold">
                Sản phẩm này chỉ được đặt trước (Pre-order)
              </p>
              <p class="text-xs text-orange-600 mt-1">
                Thời gian giao hàng dự kiến: 7-14 ngày làm việc
              </p>
            </div>
          </div>

          <!-- Size selection -->
          <div v-if="product.sizes && product.sizes.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Kích thước (Size):</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="size in product.sizes" 
                :key="size" 
                class="px-6 py-2 border-2 rounded-xl text-sm transition-all"
                :class="selectedSize === size ? 'border-primary text-primary bg-amber-50' : 'border-gray-200 text-gray-600 hover:border-primary'"
                @click="selectSize(size)"
              >{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div v-if="product.colors && product.colors.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="color in product.colors" 
                :key="color.value" 
                class="w-10 h-10 rounded-full border-2 p-1"
                :class="selectedColor === color.value ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                @click="selectColor(color.value, color.label)"
              >
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Số lượng:</span>
            <div class="flex items-center gap-4">
              <button 
                @click="decreaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="quantity <= 1"
              >
                <span class="material-symbols-outlined">remove</span>
              </button>
              <span class="text-xl font-bold w-12 text-center">{{ quantity }}</span>
              <button 
                @click="increaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="!product.is_preorder && selectedVariant && quantity >= selectedVariant.stock"
              >
                <span class="material-symbols-outlined">add</span>
              </button>
            </div>
            <!-- Hiển thị giới hạn số lượng cho pre-order -->
            <p v-if="product.is_preorder" class="text-xs text-gray-500 mt-1">
              * Pre-order không giới hạn số lượng
            </p>
          </div>

          <!-- Action Buttons - PHÂN BIỆT PRE-ORDER VÀ THƯỜNG -->
          <div class="flex flex-col gap-3 py-6">
            <!-- Nếu là sản phẩm pre-order: CHỈ CÓ NÚT ĐẶT TRƯỚC -->
            <template v-if="product.is_preorder">
              <!-- Nút Đặt trước ngay (full width) -->
              <button 
                @click="buyNow" 
                :disabled="loading || !selectedVariant"
                class="w-full h-14 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="material-symbols-outlined" v-if="!loading">bolt</span>
                <span v-if="loading" class="inline-block animate-spin">⟳</span>
                {{ loading ? 'Đang xử lý...' : 'Đặt trước ngay' }}
              </button>
              
              <!-- Nút Tùy chỉnh (full width) -->
              <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
              </Link>
            </template>

            <!-- Nếu là sản phẩm thường: 2 nút chia đôi + 1 nút full width -->
            <template v-else>
              <div class="grid grid-cols-2 gap-3">
                <button 
                  @click="addToCart" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
                  <span v-if="loading" class="inline-block animate-spin">⟳</span>
                  {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
                </button>
                
                <button 
                  @click="buyNow" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined">bolt</span> Mua ngay
                </button>
              </div>
              
              <!-- Nút Tùy chỉnh (full width) -->
              <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
              </Link>
            </template>
          </div>

          <!-- Features list -->
          <div v-if="product.features && product.features.length" class="bg-gray-50 p-5 rounded-xl space-y-3 border border-gray-100">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-gray-600 text-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Product Highlights -->
      <section class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Đặc điểm nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 bg-white rounded-2xl p-8 flex flex-col justify-between group overflow-hidden border border-gray-100 shadow-sm">
            <div>
              <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-3">Vật liệu siêu bền</h3>
              <p class="text-gray-600 max-w-md">Sử dụng vải Nylon 1680D có độ bền kéo cực cao, chống mài mòn và thấm nước tuyệt đối.</p>
            </div>
            <img alt="Material" class="w-full h-48 object-cover rounded-xl mt-6 group-hover:scale-105 transition-transform duration-500" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAAXkYc03HJQmTinK1YAQbj736ihy99OstGxMcUxfWyDse1xtCXg628v2N8vSfTXVOHSiaOscLyeVJWCULvAkl2DZNGbcFY61CXOK0Qvc3SDDq5GnTDdUapS_7qmce8NhJ5yu68yhMSt_ejolkow3sghIYvDw_hwUTmAKrrzVQU7SEDxad6b7kyBmB7Rj06_r49-hBFawQJtCo8Q-rYddCiDj_V1vpZWFZMtA9BOH73zqME0z-wW07uXfYHhwQF9j2QON12Tc4CJBKH">
          </div>
          <div class="bg-primary text-white rounded-2xl p-8 flex flex-col items-center text-center justify-center shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-6xl mb-4">laptop_mac</span>
            <h3 class="font-headline-md text-xl font-bold mb-2 text-white">Ngăn Laptop 16"</h3>
            <p class="text-white/80 text-sm">Đệm chống sốc dạng tổ ong bảo vệ thiết bị tối đa khỏi va đập mạnh từ mọi phía.</p>
          </div>
          <div class="bg-white rounded-2xl p-8 text-center flex flex-col items-center border border-gray-100 shadow-sm">
            <span class="material-symbols-outlined text-6xl mb-4 text-primary">lock</span>
            <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-2">An toàn tuyệt đối</h3>
            <p class="text-gray-600 text-sm">Ngăn bí mật mặt lưng để điện thoại và hộ chiếu, cùng dây kéo YKK chống trộm.</p>
          </div>
          <div class="md:col-span-2 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 bg-gray-800">
            <div class="flex-1">
              <h3 class="font-headline-md text-xl font-bold mb-3 text-white">Tùy biến theo chất riêng</h3>
              <p class="text-sm mb-6 text-white/80">Dịch vụ in/khắc logo doanh nghiệp: Tải lên hình ảnh logo, chọn vị trí in (trước, sau, quai đeo) và để lại lời nhắn chi tiết cho chúng tôi.</p>
              <Link :href="route('customize')" class="px-8 py-3 bg-white text-primary rounded-xl hover:bg-opacity-90 transition-colors font-bold text-sm">
                Yêu cầu In Logo & Tùy chỉnh
              </Link>
            </div>
            <div class="w-32 h-32 flex items-center justify-center rounded-full shadow-lg bg-primary">
              <span class="material-symbols-outlined text-6xl text-white">brush</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section v-if="relatedProducts && relatedProducts.length" class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Các sản phẩm liên quan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="item in relatedProducts" :key="item.id" class="flex flex-col group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
            <Link :href="route('product.detail', { id: item.id })" class="block">
              <div class="aspect-[3/4] bg-gray-100 overflow-hidden relative">
                <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="item.name">
              </div>
              <div class="p-4">
                <span class="text-gray-500 text-xs uppercase mb-1 block">{{ item.brand }}</span>
                <h3 class="font-semibold text-gray-800 mb-2 truncate">{{ item.name }}</h3>
                <div class="flex items-center gap-2 mb-4">
                  <span class="font-bold text-primary">{{ item.price }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCartSimple(item)" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 text-sm">
                <span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ hàng
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section v-if="reviews && reviews.length" class="mt-16 border-t border-gray-200 pt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8">Đánh giá từ khách hàng</h2>
        <div class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center gap-1 text-amber-400 mb-1">
                  <span v-for="n in 5" :key="n" class="material-symbols-outlined text-sm" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
                </div>
                <span class="font-semibold text-gray-800">{{ review.author }}</span>
              </div>
              <span class="text-gray-400 text-sm">{{ review.date }}</span>
            </div>
            <p class="text-gray-600 text-sm">{{ review.content }}</p>
          </div>
        </div>
        <button class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-semibold text-sm hover:bg-primary/5 transition-all">
          Xem tất cả {{ totalReviews }} đánh giá
        </button>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  product: { type: Object, required: true },
  relatedProducts: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  totalReviews: { type: Number, default: 0 }
})

// Lấy page hiện tại
const page = usePage()

// State
const activeThumb = ref(0)
const selectedSize = ref('')
const selectedColor = ref('')
const selectedColorName = ref('')
const selectedVariant = ref(null)
const quantity = ref(1)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// Kiểm tra đăng nhập
const isAuthenticated = computed(() => {
  return !!page.props.auth?.user
})

// Computed
const thumbnails = computed(() => {
  return props.product.thumbnails?.length ? props.product.thumbnails : (props.product.image_url || [])
})

const variantPrice = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.price
  }
  if (props.product.price) {
    const priceStr = props.product.price.replace(/[₫,.]/g, '').trim()
    return parseInt(priceStr) || 0
  }
  return 0
})

// Methods
const selectSize = (size) => {
  selectedSize.value = size
  findVariant()
}

const selectColor = (color, label) => {
  selectedColor.value = color
  selectedColorName.value = label
  findVariant()
}

const findVariant = () => {
  const variants = props.product.variants || []
  
  if (!variants.length) {
    selectedVariant.value = null
    return
  }

  let found = null

  if (selectedColor.value && selectedSize.value) {
    found = variants.find(v => 
      String(v.color_id) === String(selectedColor.value) && 
      v.size_name === selectedSize.value
    )
  }
  
  if (!found && selectedColor.value) {
    found = variants.find(v => String(v.color_id) === String(selectedColor.value))
  }
  
  if (!found && selectedSize.value) {
    found = variants.find(v => v.size_name === selectedSize.value)
  }
  
  if (!found && variants.length > 0) {
    found = variants[0]
    if (found.color_id) {
      const color = props.product.colors?.find(c => c.value === found.color_id)
      if (color) {
        selectedColor.value = color.value
        selectedColorName.value = color.label
      }
    }
    if (found.size_name) {
      selectedSize.value = found.size_name
    }
  }

  selectedVariant.value = found
  
  if (found) {
    quantity.value = 1
  }
}

const increaseQuantity = () => {
  // Cho pre-order: không giới hạn số lượng
  if (props.product.is_preorder) {
    quantity.value++
    return
  }
  
  // Cho sản phẩm thường: kiểm tra stock
  if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const formatPrice = (price) => {
  if (!price) return '0đ'
  if (typeof price === 'number') {
    return new Intl.NumberFormat('vi-VN').format(price) + 'đ'
  }
  const num = parseInt(String(price).replace(/[^0-9]/g, ''))
  if (!isNaN(num) && num > 0) {
    return new Intl.NumberFormat('vi-VN').format(num) + 'đ'
  }
  return price
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => { 
    message.value = '' 
  }, 3000)
}

const goToLogin = () => {
  sessionStorage.setItem('redirectAfterLogin', window.location.href)
  router.get(route('login'))
}

// ===== HÀM MUA NGAY (XỬ LÝ CẢ PRE-ORDER VÀ THƯỜNG) =====
const buyNow = async () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để mua hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  // Kiểm tra stock (chỉ cho sản phẩm thường)
  if (!props.product.is_preorder && selectedVariant.value.stock <= 0) {
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (!props.product.is_preorder && quantity.value > selectedVariant.value.stock) {
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  try {
    // ✅ PRE-ORDER: Lưu vào session và chuyển thẳng đến checkout
    if (props.product.is_preorder) {
      // Lưu thông tin pre-order vào session
      await axios.post('/api/pre-order/session', {
        variant_id: selectedVariant.value.id,
        quantity: quantity.value
      }, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        withCredentials: true
      })
      
      loading.value = false
      // Chuyển đến trang thanh toán với flag pre-order
      router.get(route('checkout'))
      return
    }

    // ✅ SẢN PHẨM THƯỜNG: Thêm vào giỏ hàng rồi chuyển đến checkout
    const payload = {
      variant_id: selectedVariant.value.id,
      quantity: quantity.value
    }

    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })

    if (response.data.success) {
      // Cập nhật số lượng giỏ hàng
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
      loading.value = false
      // CHUYỂN ĐẾN TRANG THANH TOÁN
      router.get(route('checkout'))
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
      loading.value = false
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
    loading.value = false
  }
}

// ===== THÊM VÀO GIỎ HÀNG (CHỈ DÀNH CHO SẢN PHẨM THƯỜNG) =====
const addToCart = async () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  if (selectedVariant.value.stock <= 0) {
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (quantity.value > selectedVariant.value.stock) {
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  const payload = {
    variant_id: selectedVariant.value.id,
    quantity: quantity.value
  }

  try {
    const response = await axios.post('/api/cart/add', payload, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })

    if (response.data.success) {
      showMessage('✅ Đã thêm vào giỏ hàng thành công!', 'success')
      
      window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: response.data.cart_count || 0 }
      }))
      
    } else {
      showMessage(response.data.message || 'Thêm vào giỏ hàng thất bại', 'error')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
  } finally {
    loading.value = false
  }
}

const addToCartSimple = (item) => {
  router.get(route('product.detail', { id: item.id }))
}

// Lifecycle
onMounted(() => {
  // Khởi tạo màu và size mặc định
  if (props.product.colors && props.product.colors.length > 0) {
    const firstColor = props.product.colors[0]
    selectedColor.value = firstColor.value
    selectedColorName.value = firstColor.label
  }

  if (props.product.sizes && props.product.sizes.length > 0) {
    selectedSize.value = props.product.sizes[0]
  }

  findVariant()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E85D04; border-radius: 10px; }
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>