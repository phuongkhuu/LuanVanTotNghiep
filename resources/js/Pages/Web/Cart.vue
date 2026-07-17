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
                <Link :href="route('product.detail', { slug: item.slug || '#' })" class="font-semibold text-gray-800 hover:text-primary">
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
              <span v-if="item.is_on_sale" class="font-semibold text-red-500">{{ formatPrice(item.price) }}</span>
              <span v-else class="font-semibold text-gray-800">{{ formatPrice(item.price) }}</span>
              <span v-if="item.is_on_sale" class="text-xs text-gray-400 line-through block">{{ formatPrice(item.original_price) }}</span>
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

          <!-- Coupon Section -->
          <div class="flex flex-col md:flex-row gap-4 mt-6 items-center">
            <div class="w-full md:w-auto flex-grow">
              <input 
                v-model="couponCode" 
                class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-3 text-gray-700 focus:border-primary focus:ring-0" 
                placeholder="Nhập mã giảm giá..." 
                type="text"
                :disabled="appliedCoupon !== null"
              >
            </div>
            <button 
              v-if="appliedCoupon === null"
              @click="handleApplyCoupon" 
              class="w-full md:w-auto bg-primary text-white font-semibold py-3 px-8 rounded-lg hover:bg-primary-dark transition-colors"
            >
              Áp dụng
            </button>
            <button 
              v-else
              @click="handleRemoveCoupon" 
              class="w-full md:w-auto bg-red-500 text-white font-semibold py-3 px-8 rounded-lg hover:bg-red-600 transition-colors"
            >
              Xóa mã
            </button>
          </div>
          
          <!-- Coupon message -->
          <div v-if="appliedCoupon" class="mt-2 text-sm text-green-600 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            Đã áp dụng mã: <strong>{{ appliedCoupon.code }}</strong> (giảm {{ formatPrice(discountAmount) }})
          </div>
          <div v-else-if="couponError" class="mt-2 text-sm text-red-500">
            {{ couponError }}
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
                <span class="font-semibold text-green-600">Miễn phí</span>
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
            
            <!-- SỬA: Link thanh toán gửi cart qua URL -->
            <Link 
              :href="route('checkout', { cart: JSON.stringify(cartDataForCheckout) })" 
              class="w-full bg-primary text-white py-5 rounded-lg mt-6 hover:bg-primary-dark transition-colors uppercase font-bold text-center block"
            >
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
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed, onBeforeUnmount } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import { useCart } from '@/utils/useCart'
import { CartEvents } from '@/events/CartEvents'

const { 
  cartItems, 
  fetchCart, 
  updateCart, 
  removeFromCart,
  subtotal,
  total,
  couponCode,
  discountAmount,
  appliedCoupon,
  couponError,
  applyCoupon,
  removeCoupon,
  loading,
  cartCount,
  setVoucherFromSession,
  restoreVoucher,
  clearVoucherStorage
} = useCart()

const page = usePage()

// Tạo dữ liệu cart để gửi qua URL
const cartDataForCheckout = computed(() => {
  const data = {}
  cartItems.value.forEach(item => {
    data[item.id] = {
      quantity: item.quantity,
      price: item.price
    }
  })
  return data
})

// ============ LẮNG NGHE SỰ KIỆN XÓA VOUCHER TỪ CHECKOUT ============
const handleVoucherCleared = () => {
  // Xóa voucher khỏi state
  discountAmount.value = 0
  appliedCoupon.value = null
  couponCode.value = ''
  couponError.value = ''
  
  // Xóa localStorage
  clearVoucherStorage()
 
}

// Watch cartItems
watch(cartItems, (newItems, oldItems) => {
  const newCount = newItems.reduce((sum, item) => sum + item.quantity, 0)
  const oldCount = oldItems ? oldItems.reduce((sum, item) => sum + item.quantity, 0) : 0
  
  if (newCount !== oldCount) {
    CartEvents.emitUpdated(newCount)
  }
}, { deep: true })

// Methods
const loadCart = async () => {
  await fetchCart()
}

const loadVoucherFromSession = () => {
  const voucherCode = page.props.voucher_code || null
  const voucherDiscount = page.props.voucher_discount || 0
  
  if (voucherCode && voucherDiscount > 0) {
    setVoucherFromSession(voucherCode, voucherDiscount)
  } else {
    restoreVoucher()
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
    await updateCart(item.id, newQuantity)
  } catch (error) {
    alert(error.response?.data?.message || 'Cập nhật thất bại')
  }
}

const removeItem = async (index) => {
  const item = cartItems.value[index]
  if (!item) return
  
  if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return

  try {
    await removeFromCart(item.id)
  } catch (error) {
    alert('Xóa sản phẩm thất bại')
  }
}

const handleApplyCoupon = async () => {
  if (!couponCode.value.trim()) {
    return
  }
  try {
    await applyCoupon(couponCode.value.trim())
  } catch (error) {
    // Error handled in composable
  }
}

const handleRemoveCoupon = async () => {
  try {
    await removeCoupon()
  } catch (error) {
    console.error('❌ Failed to remove voucher:', error)
  }
}

const formatPrice = (val) => {
  if (!val && val !== 0) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

// Lifecycle
onMounted(() => {
  loadCart()
  loadVoucherFromSession()
  
  // Lắng nghe sự kiện xóa voucher từ Checkout
  window.addEventListener('voucher:cleared', handleVoucherCleared)
})

onBeforeUnmount(() => {
  window.removeEventListener('voucher:cleared', handleVoucherCleared)
})

const suggestedProducts = ref([])
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>