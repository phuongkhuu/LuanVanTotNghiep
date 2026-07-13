<template>
  <div>
    <Head title="Giỏ hàng - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-12">
      <h1 class="font-headline-lg text-2xl md:text-3xl mb-6 border-l-4 pl-4 border-primary text-gray-900">Giỏ hàng của bạn</h1>
      
      <!-- Hiển thị khi giỏ hàng trống -->
      <div v-if="cartItems.length === 0 && !loading" class="text-center py-12">
        <div class="text-6xl mb-4">🛒</div>
        <p class="text-xl text-gray-600">Giỏ hàng trống</p>
        <Link :href="route('home')" class="inline-block mt-4 text-primary hover:underline">
          Tiếp tục mua sắm
        </Link>
      </div>

      <!-- Hiển thị khi đang tải -->
      <div v-else-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin text-4xl">⟳</div>
        <p class="text-gray-600 mt-4">Đang tải giỏ hàng...</p>
      </div>

      <!-- Hiển thị giỏ hàng có sản phẩm -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Cart Items -->
        <div class="lg:col-span-8 space-y-4">
          <div class="hidden md:grid grid-cols-12 gap-4 pb-4 border-b border-gray-200 text-gray-500 font-semibold text-xs uppercase tracking-wider">
            <div class="col-span-6">Sản phẩm</div>
            <div class="col-span-2 text-center">Giá</div>
            <div class="col-span-2 text-center">Số lượng</div>
            <div class="col-span-2 text-right">Tạm tính</div>
          </div>

          <div v-for="(item, idx) in cartItems" :key="item.id" class="grid grid-cols-1 md:grid-cols-12 gap-4 py-6 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="col-span-12 md:col-span-6 flex gap-4">
              <div class="w-24 h-32 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                <img class="w-full h-full object-cover" :src="item.image || '/images/default-product.jpg'" :alt="item.name">
              </div>
              <div>
                <Link :href="route('product.detail', { id: item.id })" class="font-semibold text-gray-800 hover:text-primary">
                  {{ item.name }}
                </Link>
                <p class="text-sm text-gray-500 mt-1">Màu: {{ item.color || 'Đen' }} | Size: {{ item.size || 'M' }}</p>
                <button @click="removeItem(idx)" class="text-primary text-sm flex items-center mt-2 hover:text-primary-dark">
                  <span class="material-symbols-outlined text-[18px] mr-1">delete</span> Xóa
                </button>
              </div>
            </div>
            <div class="col-span-4 md:col-span-2 text-left md:text-center">
              <span class="md:hidden font-semibold text-gray-500 block text-sm">Giá:</span>
              <span class="font-semibold text-gray-800">{{ formatPrice(item.price) }}</span>
            </div>
            <div class="col-span-4 md:col-span-2 flex justify-center">
              <div class="flex items-center border border-gray-200 rounded-full px-2 py-1">
                <button @click="updateQuantity(idx, -1)" class="w-8 h-8 flex items-center justify-center text-gray-600">
                  <span class="material-symbols-outlined text-[20px]">remove</span>
                </button>
                <span class="px-4 font-bold text-gray-800">{{ item.quantity }}</span>
                <button @click="updateQuantity(idx, 1)" class="w-8 h-8 flex items-center justify-center text-gray-600">
                  <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
              </div>
            </div>
            <div class="col-span-4 md:col-span-2 text-right">
              <span class="md:hidden font-semibold text-gray-500 block text-sm">Tạm tính:</span>
              <span class="font-semibold text-primary font-bold">{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>

          <div class="flex flex-col md:flex-row gap-4 mt-6 items-center">
            <div class="w-full md:w-auto flex-grow">
              <input v-model="couponCode" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-3 text-gray-700 focus:border-primary focus:ring-0" placeholder="Nhập mã giảm giá..." type="text">
            </div>
            <button @click="applyCoupon" class="w-full md:w-auto bg-primary text-white font-semibold py-3 px-8 rounded-lg hover:bg-primary-dark transition-colors">
              Áp dụng
            </button>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-4 sticky top-24">
          <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h2 class="font-semibold text-xl mb-6 border-b border-gray-200 pb-4 text-gray-800">Tóm tắt đơn hàng</h2>
            <div class="space-y-4">
              <div class="flex justify-between text-gray-600">
                <span>Tạm tính ({{ cartItems.length }} sản phẩm)</span>
                <span class="font-semibold text-gray-800">{{ formatPrice(subtotal) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Phí vận chuyển</span>
                <span v-if="displayShippingFee > 0" class="font-semibold text-gray-800">{{ formatPrice(displayShippingFee) }}</span>
                <span v-else class="font-semibold text-green-600">Miễn phí</span>
              </div>
              <div v-if="discountAmount > 0" class="flex justify-between text-gray-600">
                <span>Mã giảm giá</span>
                <span class="text-primary">- {{ formatPrice(discountAmount) }}</span>
              </div>
              <hr class="border-gray-200">
              <div class="flex justify-between items-center py-2">
                <span class="font-semibold text-gray-800">Tổng cộng</span>
                <span class="font-display-lg text-2xl text-primary font-bold">{{ formatPrice(total) }}</span>
              </div>
            </div>
            <Link :href="route('checkout')" class="w-full bg-primary text-white py-5 rounded-lg mt-6 hover:bg-primary-dark transition-colors uppercase font-bold text-center block">
              Tiến hành thanh toán
            </Link>
            <div class="mt-6 space-y-3">
              <div class="flex items-center gap-2 text-gray-500 text-sm">
                <span class="material-symbols-outlined text-green-600">verified_user</span> Thanh toán an toàn 100%
              </div>
              <div class="flex items-center gap-2 text-gray-500 text-sm">
                <span class="material-symbols-outlined text-green-600">local_shipping</span> Giao hàng nhanh toàn quốc (2-4 ngày)
              </div>
              <div class="flex items-center gap-2 text-gray-500 text-sm">
                <span class="material-symbols-outlined text-green-600">assignment_return</span> Đổi trả miễn phí trong 7 ngày
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Suggested Products -->
      <section v-if="cartItems.length > 0" class="mt-16">
        <div class="flex justify-between items-end mb-6">
          <div>
            <span class="text-primary font-semibold text-xs uppercase tracking-widest">Phối hợp hoàn hảo</span>
            <h2 class="font-headline-lg text-2xl font-bold text-gray-800">Có thể bạn sẽ thích</h2>
          </div>
          <Link :href="route('category', { slug: 'tat-ca' })" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm">
            Xem tất cả <span class="material-symbols-outlined text-sm">chevron_right</span>
          </Link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="product in suggestedProducts" :key="product.id" class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="aspect-[4/5] bg-gray-100 overflow-hidden relative">
                <img class="w-full h-full object-cover group-hover:scale-110 transition-transform" :src="product.image" :alt="product.name">
                <span v-if="product.badge" class="absolute top-4 left-4 bg-primary text-white px-3 py-1 rounded-full text-xs">{{ product.badge }}</span>
              </div>
              <div class="p-4">
                <h4 class="font-semibold text-base line-clamp-1 text-gray-800">{{ product.name }}</h4>
                <p class="text-gray-500 text-sm mb-2">{{ product.category }}</p>
                <div class="flex justify-between items-center">
                  <span class="font-bold text-primary text-lg">{{ formatPrice(product.price) }}</span>
                  <button @click="addToCartProduct(product)" class="bg-gray-100 p-2 rounded-full hover:bg-primary/10 transition-colors">
                    <span class="material-symbols-outlined text-gray-600">add_shopping_cart</span>
                  </button>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// State
const cartItems = ref([])
const loading = ref(false)
const couponCode = ref('')
const discountAmount = ref(0)
const shippingFee = 30000 // Phí vận chuyển cố định

// Computed
const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

// ✅ SỬA: Chỉ tính phí vận chuyển khi có sản phẩm
const total = computed(() => {
  const subtotalValue = subtotal.value
  // Nếu giỏ hàng trống hoặc subtotal = 0, không tính phí vận chuyển
  const shipping = (cartItems.value.length > 0 && subtotalValue > 0) ? shippingFee : 0
  return subtotalValue + shipping - discountAmount.value
})

// ✅ THÊM: Tính phí vận chuyển hiển thị
const displayShippingFee = computed(() => {
  return (cartItems.value.length > 0 && subtotal.value > 0) ? shippingFee : 0
})

const cartCount = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
})

// Methods
const loadCart = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/cart')
    console.log('📦 Cart data:', response.data)
    if (response.data.success) {
      cartItems.value = response.data.items || []
    }
  } catch (error) {
    console.error('❌ Lỗi tải giỏ hàng:', error)
    // Nếu lỗi 401 (chưa đăng nhập)
    if (error.response && error.response.status === 401) {
      router.get(route('login'))
    }
  } finally {
    loading.value = false
  }
}

const updateQuantity = async (index, delta) => {
  const item = cartItems.value[index]
  if (!item) return
  
  const newQuantity = item.quantity + delta
  if (newQuantity < 1) {
    await removeItem(index)
    return
  }

  try {
    await axios.put('/api/cart/update', {
      variant_id: item.id,
      quantity: newQuantity
    })
    await loadCart()
  } catch (error) {
    alert(error.response?.data?.message || 'Cập nhật thất bại')
  }
}

const removeItem = async (index) => {
  const item = cartItems.value[index]
  if (!item) return
  
  if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return

  try {
    await axios.delete(`/api/cart/remove/${item.id}`)
    await loadCart()
  } catch (error) {
    alert('Xóa sản phẩm thất bại')
  }
}

const applyCoupon = () => {
  if (couponCode.value === 'SALE10') {
    discountAmount.value = subtotal.value * 0.1
  } else {
    discountAmount.value = 0
    alert('Mã giảm giá không hợp lệ')
  }
}

const formatPrice = (val) => {
  return (val || 0).toLocaleString('vi-VN') + '₫'
}

// Lifecycle
onMounted(() => {
  loadCart()
})

// Suggested products
const suggestedProducts = ref([])

// Thêm sản phẩm từ suggested
const addToCartProduct = (product) => {
  router.get(route('product.detail', { id: product.id }))
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>