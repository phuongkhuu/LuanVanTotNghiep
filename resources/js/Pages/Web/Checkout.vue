<template>
  <div>
    <Head title="Thanh toán - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="mt-6 mb-16 px-4 md:px-8 max-w-[1440px] mx-auto">
      <div class="mb-6">
        <h1 class="font-headline-lg text-2xl md:text-3xl border-l-4 pl-4 border-primary text-gray-900">Thanh toán</h1>
        <p class="text-gray-500 text-sm mt-2 ml-5">Vui lòng kiểm tra lại thông tin đặt hàng và nhận hàng.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Shipping & Payment -->
        <section class="lg:col-span-7 space-y-6">
          <!-- Thông tin người đặt -->
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-4">
              <span class="material-symbols-outlined text-primary">person</span>
              <h2 class="font-semibold text-lg uppercase tracking-wider text-gray-800">Thông tin người đặt</h2>
            </div>
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Họ và tên <span class="text-red-500">*</span></label>
                  <input 
                    v-model="customerInfo.name" 
                    class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                    placeholder="Nguyễn Văn A" 
                    type="text" 
                    required
                  >
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Email <span class="text-red-500">*</span></label>
                  <input 
                    v-model="customerInfo.email" 
                    class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                    placeholder="example@bigbag.vn" 
                    type="email" 
                    required
                  >
                </div>
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-600">Số điện thoại <span class="text-red-500">*</span></label>
                <input 
                  v-model="customerInfo.phone" 
                  class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                  placeholder="090 1234 567" 
                  type="tel" 
                  required
                >
              </div>
            </div>
          </div>

          <!-- Thông tin người nhận -->
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-4">
              <span class="material-symbols-outlined text-primary">local_shipping</span>
              <h2 class="font-semibold text-lg uppercase tracking-wider text-gray-800">Thông tin người nhận</h2>
            </div>
            <div class="space-y-4">
              <label class="flex items-center gap-2 cursor-pointer">
                <input 
                  v-model="sameAsCustomer" 
                  type="checkbox" 
                  class="w-4 h-4 accent-primary"
                >
                <span class="text-sm text-gray-700">Người nhận giống người đặt</span>
              </label>

              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-600">Họ và tên người nhận <span class="text-red-500">*</span></label>
                    <input 
                      v-model="receiverInfo.name" 
                      class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                      placeholder="Trần Thị B" 
                      type="text" 
                      :disabled="sameAsCustomer"
                      required
                    >
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-600">SĐT người nhận <span class="text-red-500">*</span></label>
                    <input 
                      v-model="receiverInfo.phone" 
                      class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                      placeholder="091 2345 678" 
                      type="tel" 
                      :disabled="sameAsCustomer"
                      required
                    >
                  </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-600">Tỉnh / Thành</label>
                    <select v-model="receiverInfo.city" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0 text-gray-700">
                      <option value="">Chọn tỉnh/thành</option>
                      <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                      <option value="Hà Nội">Hà Nội</option>
                      <option value="Đà Nẵng">Đà Nẵng</option>
                      <option value="Hải Phòng">Hải Phòng</option>
                      <option value="Cần Thơ">Cần Thơ</option>
                    </select>
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-600">Quận / Huyện</label>
                    <input 
                      v-model="receiverInfo.district" 
                      class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                      placeholder="Quận 1" 
                      type="text"
                    >
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-gray-600">Phường / Xã</label>
                    <input 
                      v-model="receiverInfo.ward" 
                      class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                      placeholder="Phường Bến Nghé" 
                      type="text"
                    >
                  </div>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
                  <input 
                    v-model="receiverInfo.address" 
                    class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                    placeholder="Số nhà, tên đường..." 
                    type="text" 
                    required
                  >
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Ghi chú giao hàng</label>
                  <textarea 
                    v-model="receiverInfo.note" 
                    class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" 
                    placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..." 
                    rows="3"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Phương thức thanh toán -->
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-4">
              <span class="material-symbols-outlined text-primary">payments</span>
              <h2 class="font-semibold text-lg uppercase tracking-wider text-gray-800">Phương thức thanh toán</h2>
            </div>
            <div class="space-y-4">
              <label 
                class="flex items-center p-4 rounded-lg cursor-pointer transition-all duration-200"
                :class="paymentMethod === 'cod' 
                  ? 'border-2 border-primary bg-amber-50 shadow-sm' 
                  : 'border border-gray-200 bg-white hover:border-primary/50 hover:bg-amber-50/30'"
              >
                <div class="flex items-center gap-4 w-full">
                  <input 
                    v-model="paymentMethod" 
                    value="cod" 
                    type="radio" 
                    class="w-5 h-5 text-primary border-gray-300 focus:ring-0 accent-primary"
                  >
                  <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl" :class="paymentMethod === 'cod' ? 'text-primary' : 'text-gray-500'">local_atm</span>
                    <div>
                      <span class="font-semibold text-gray-800 block">Thanh toán khi nhận hàng (COD)</span>
                      <span class="text-xs text-gray-500">Trả tiền mặt khi nhận hàng</span>
                    </div>
                  </div>
                </div>
              </label>
              <!-- <label 
                class="flex items-center p-4 rounded-lg cursor-pointer transition-all duration-200"
                :class="paymentMethod === 'ewallet' 
                  ? 'border-2 border-primary bg-amber-50 shadow-sm' 
                  : 'border border-gray-200 bg-white hover:border-primary/50 hover:bg-amber-50/30'"
              >
                <div class="flex items-center gap-4 w-full">
                  <input 
                    v-model="paymentMethod" 
                    value="ewallet" 
                    type="radio" 
                    class="w-5 h-5 text-primary border-gray-300 focus:ring-0 accent-primary"
                  >
                  <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl" :class="paymentMethod === 'ewallet' ? 'text-primary' : 'text-gray-500'">account_balance_wallet</span>
                    <div>
                      <span class="font-semibold text-gray-800 block">Ví điện tử (Momo, ZaloPay)</span>
                      <span class="text-xs text-gray-500">Thanh toán qua ví điện tử</span>
                    </div>
                  </div>
                </div>
              </label>
              <label 
                class="flex items-center p-4 rounded-lg cursor-pointer transition-all duration-200"
                :class="paymentMethod === 'bank_transfer' 
                  ? 'border-2 border-primary bg-amber-50 shadow-sm' 
                  : 'border border-gray-200 bg-white hover:border-primary/50 hover:bg-amber-50/30'"
              >
                <div class="flex items-center gap-4 w-full">
                  <input 
                    v-model="paymentMethod" 
                    value="bank_transfer" 
                    type="radio" 
                    class="w-5 h-5 text-primary border-gray-300 focus:ring-0 accent-primary"
                  >
                  <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl" :class="paymentMethod === 'bank_transfer' ? 'text-primary' : 'text-gray-500'">account_balance</span>
                    <div>
                      <span class="font-semibold text-gray-800 block">Chuyển khoản ngân hàng</span>
                      <span class="text-xs text-gray-500">Thanh toán qua chuyển khoản</span>
                    </div>
                  </div>
                </div>
              </label> -->
            </div>
          </div>
        </section>

        <!-- Right Column: Order Summary -->
        <aside class="lg:col-span-5">
          <div class="sticky top-28 space-y-6">
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
              <h2 class="font-semibold text-xl mb-6 border-b border-gray-200 pb-4 text-gray-800">Đơn hàng của bạn</h2>
              <div class="space-y-4 mb-6 max-h-80 overflow-y-auto">
                <div v-for="item in cartItems" :key="item.id" class="flex gap-4 items-center">
                  <div class="relative w-20 h-20 bg-gray-100 rounded-lg border border-gray-100 overflow-hidden flex-shrink-0">
                    <img :src="item.image" class="w-full h-full object-cover" :alt="item.name">
                    <span class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-6 h-6 flex items-center justify-center rounded-full font-bold">{{ item.quantity }}</span>
                  </div>
                  <div class="flex-grow">
                    <p class="font-semibold text-sm leading-tight text-gray-800">{{ item.name }}</p>
                    <p class="text-xs text-gray-500">Màu: {{ item.color }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-sm font-bold text-gray-800">{{ formatPrice(item.price) }}</p>
                  </div>
                </div>
              </div>
              <div class="space-y-4 border-t border-gray-200 pt-4 mb-4">
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Tạm tính ({{ cartItems.length }} sản phẩm)</span>
                  <span class="text-gray-800 font-semibold">{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Phí vận chuyển</span>
                  <span class="text-green-600 font-semibold">Miễn phí</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Mã giảm giá</span>
                  <span class="text-primary">- 0₫</span>
                </div>
                <hr class="border-gray-200">
                <div class="flex justify-between items-center py-2">
                  <span class="font-semibold text-gray-800">Tổng cộng</span>
                  <span class="font-display-lg text-2xl text-primary font-bold">{{ formatPrice(total) }}</span>
                </div>
              </div>
              <button @click="placeOrder" :disabled="loading" class="w-full bg-primary text-white font-semibold py-5 rounded-lg shadow-sm hover:bg-primary-dark transition-all font-bold uppercase tracking-wide disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="!loading">Đặt hàng ngay</span>
                <span v-else>Đang xử lý...</span>
              </button>
              <p class="text-center text-xs text-gray-500 mt-4">
                Bằng cách đặt hàng, bạn đồng ý với <a href="#" class="underline text-primary hover:text-primary-dark">Điều khoản dịch vụ</a>.
              </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white p-4 rounded-xl border border-gray-100 flex flex-col items-center text-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
                <span class="font-semibold text-xs text-gray-700">Bảo hành trọn đời</span>
              </div>
              <div class="bg-white p-4 rounded-xl border border-gray-100 flex flex-col items-center text-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-primary text-3xl">published_with_changes</span>
                <span class="font-semibold text-xs text-gray-700">90 ngày đổi trả</span>
              </div>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// --- Props nhận từ controller ---
const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  products: {
    type: Array,
    default: () => []
  },
  subtotal: {
    type: Number,
    default: 0
  },
  shipping_fee: {
    type: Number,
    default: 0
  },
  discount: {
    type: Number,
    default: 0
  },
  final_total: {
    type: Number,
    default: 0
  }
})

// --- Sử dụng dữ liệu từ props ---
const cartItems = ref(props.products || [])
const loading = ref(false)
const isAuthenticated = ref(false)

// Thông tin người đặt
const customerInfo = ref({
  name: props.user?.name || '',
  email: props.user?.email || '',
  phone: props.user?.phone || '',
})

// Thông tin người nhận
const receiverInfo = ref({
  name: '',
  phone: '',
  city: '',
  district: '',
  ward: '',
  address: '',
  note: '',
})

// Checkbox "Nhận hàng cùng người đặt"
const sameAsCustomer = ref(false)

// Phương thức thanh toán
const paymentMethod = ref('cod')

// Tính toán lại subtotal và total
const subtotal = computed(() => props.subtotal || 0)
const total = computed(() => props.final_total || subtotal.value)

// Watch để copy thông tin
watch(sameAsCustomer, (val) => {
  if (val) {
    receiverInfo.value.name = customerInfo.value.name
    receiverInfo.value.phone = customerInfo.value.phone
  } else {
    receiverInfo.value.name = ''
    receiverInfo.value.phone = ''
    receiverInfo.value.city = ''
    receiverInfo.value.district = ''
    receiverInfo.value.ward = ''
    receiverInfo.value.address = ''
    receiverInfo.value.note = ''
  }
})

// Format tiền
const formatPrice = (val) => {
  if (!val) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

// --- Đặt hàng - KIỂM TRA ĐĂNG NHẬP ---
const placeOrder = () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value || !props.user) {
    alert('Vui lòng đăng nhập để thanh toán')
    sessionStorage.setItem('redirectAfterLogin', window.location.href)
    router.get(route('login'))
    return
  }

  // Validate
  if (!customerInfo.value.name || !customerInfo.value.email || !customerInfo.value.phone) {
    alert('Vui lòng điền đầy đủ thông tin người đặt')
    return
  }

  if (!receiverInfo.value.name || !receiverInfo.value.phone || !receiverInfo.value.address) {
    alert('Vui lòng điền đầy đủ thông tin người nhận')
    return
  }

  // Nếu checkbox được tick, đồng bộ lại
  if (sameAsCustomer.value) {
    receiverInfo.value.name = customerInfo.value.name
    receiverInfo.value.phone = customerInfo.value.phone
  }

  // Tạo địa chỉ đầy đủ
  const fullAddress = [
    receiverInfo.value.address,
    receiverInfo.value.ward,
    receiverInfo.value.district,
    receiverInfo.value.city,
  ].filter(Boolean).join(', ')

  // Dữ liệu gửi lên server
  const orderData = {
    customer_name: customerInfo.value.name,
    customer_phone: customerInfo.value.phone,
    customer_email: customerInfo.value.email,
    receiver_name: receiverInfo.value.name,
    receiver_phone: receiverInfo.value.phone,
    shipping_address: fullAddress,
    note: receiverInfo.value.note,
    payment_method: paymentMethod.value,
    items: cartItems.value.map(item => ({
      id: item.id,
      quantity: item.quantity,
      price: item.price,
    })),
    total_amount: total.value,
  }

  console.log('📦 Order data:', orderData)

  loading.value = true

  // Gửi dữ liệu bằng Inertia
  router.post(route('checkout.store'), orderData, {
    onSuccess: () => {
      loading.value = false
    },
    onError: (errors) => {
      loading.value = false
      console.error('❌ Lỗi đặt hàng:', errors)
      const errorMsg = errors.error || 'Có lỗi xảy ra, vui lòng thử lại.'
      alert(errorMsg)
    },
  })
}

// Lifecycle
onMounted(() => {
  console.log('📦 Checkout props:', props)
  
  // Kiểm tra đăng nhập
  if (props.user) {
    isAuthenticated.value = true
  } else {
    // Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
    sessionStorage.setItem('redirectAfterLogin', window.location.href)
    router.get(route('login'))
  }
})
</script>