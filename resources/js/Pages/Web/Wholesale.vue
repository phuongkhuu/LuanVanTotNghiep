<template>
  <div>
    <Head title="Mua sỉ - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main>
      
      <!-- CTA Section -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 py-12 md:py-16" id="contact">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
          <!-- KHUNG CAM "BẮT ĐẦU DỰ ÁN DOANH NGHIỆP" -->
          <div class="bg-orange-600 p-6 md:p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mt-24"></div>
            <div class="z-10">
              <h2 class="text-xl md:text-2xl font-bold text-white mb-2">Bắt Đầu Dự Án Doanh Nghiệp</h2>
              <p class="text-orange-100 text-sm max-w-xl">Đội ngũ chuyên viên tư vấn của BigBag sẵn sàng hỗ trợ bạn thiết kế và báo giá chi tiết trong vòng 30 phút.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 md:gap-8 z-10 text-white text-sm flex-shrink-0">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined bg-white/20 p-2 rounded-full">call</span>
                <div>
                  <p class="text-orange-200 text-xs">Hotline</p>
                  <p class="font-semibold">1900 1234</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined bg-white/20 p-2 rounded-full">mail</span>
                <div>
                  <p class="text-orange-200 text-xs">Email</p>
                  <p class="font-semibold">b2b@bigbag.vn</p>
                </div>
              </div>
            </div>
          </div>

          <!-- PHẦN NỘI DUNG BÊN DƯỚI: 2 CỘT -->
          <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- CỘT TRÁI: Thông tin sản phẩm + Bộ lọc -->
            <div class="p-6 md:p-8 border-r border-gray-100">
              <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                Thông tin đặt hàng
              </h3>
              
              <!-- Sản phẩm đang chọn -->
              <div v-if="selectedProduct" class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="w-full sm:w-[120px] flex-shrink-0">
                  <div class="aspect-[4/5] bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
                    <img 
                      :src="selectedProduct.image" 
                      :alt="selectedProduct.name"
                      class="w-full h-full object-cover"
                    />
                  </div>
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-800 text-sm">{{ selectedProduct.name }}</h4>
                  <p v-if="selectedProduct.description" class="text-gray-500 text-xs mb-2">{{ selectedProduct.description }}</p>
                  <div class="flex items-baseline gap-2">
                    <span class="text-lg font-bold text-red-600">{{ formatPrice(selectedProduct.sale_price || selectedProduct.base_price) }}</span>
                    <span v-if="selectedProduct.original_price && selectedProduct.original_price > selectedProduct.sale_price" class="text-gray-400 line-through text-xs">{{ formatPrice(selectedProduct.original_price) }}</span>
                    <span v-if="selectedProduct.discount_percent > 0" class="text-red-500 text-xs bg-red-50 px-2 py-0.5 rounded-full">-{{ selectedProduct.discount_percent }}%</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Tồn kho: <span class="font-semibold text-green-600">{{ selectedProduct.stock }} sản phẩm</span></p>
                </div>
              </div>

              <!-- BỘ LỌC: Số lượng, Màu sắc, Kích thước -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Số lượng -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Số lượng</label>
                  <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button   
                      @click="decreaseQuantity" 
                      class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                      :disabled="orderQuantity <= 1"
                      >
                      <span class="material-symbols-outlined text-lg">remove</span>
                    </button>
                    <input 
                      type="number" 
                      v-model="orderQuantity" 
                      min="1"
                      class="w-full h-10 text-center outline-none text-sm font-semibold bg-white"
                    />
                    <button 
                      @click="increaseQuantity"
                      class="w-10 h-10 flex items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors text-gray-600 border-l border-gray-200"
                    >
                      <span class="material-symbols-outlined text-lg">add</span>
                    </button>
                  </div>
                </div>

                <!-- Màu sắc -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Màu sắc</label>
                  <select v-model="selectedColor" class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-orange-400 bg-white text-gray-700">
                    <option v-for="color in colorOptions" :key="color" :value="color">{{ color }}</option>
                  </select>
                </div>

                <!-- Kích thước -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Kích thước</label>
                  <select v-model="selectedSize" class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-orange-400 bg-white text-gray-700">
                    <option v-for="size in sizeOptions" :key="size" :value="size">{{ size }}</option>
                  </select>
                </div>
              </div>

              <!-- THÔNG TIN ĐƠN HÀNG -->
              <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tạm tính</span>
                    <span class="font-semibold text-gray-800">{{ formatPrice(subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Phí vận chuyển</span>
                    <span class="font-semibold text-green-600">Miễn phí</span>
                  </div>
                  <div v-if="currentDiscountPercent > 0" class="flex justify-between text-sm">
                    <span class="text-gray-500">Chiết khấu ({{ currentDiscountPercent }}%)</span>
                    <span class="font-semibold text-red-500">- {{ formatPrice(discountAmount) }}</span>
                  </div>
                  <div v-else class="flex justify-between text-sm">
                    <span class="text-gray-500">Chiết khấu</span>
                    <span class="font-semibold text-gray-400">0₫</span>
                  </div>
                  <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between">
                    <span class="font-bold text-gray-800">Tổng cộng</span>
                    <span class="text-xl font-bold text-orange-600">{{ formatPrice(totalPrice) }}</span>
                  </div>
                </div>
              </div>

              <!-- ===== PHƯƠNG THỨC THANH TOÁN - CHỈ PAYOS ===== -->
              <div class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wider">
                  PHƯƠNG THỨC THANH TOÁN
                </h4>
                
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                  <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-blue-600">payments</span>
                    <div>
                      <p class="font-medium text-gray-800 text-sm">Thanh toán qua PayOS</p>
                      <p class="text-xs text-gray-600 mt-0.5">Thanh toán an toàn qua cổng PayOS, hỗ trợ nhiều ngân hàng</p>
                      <div class="flex flex-wrap gap-2 mt-2">
                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">✓ Bảo mật SSL</span>
                        <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">✓ Hỗ trợ 40+ ngân hàng</span>
                        <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">✓ QR Code</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
                  <p class="text-xs text-amber-700 flex items-start">
                    <span class="material-symbols-outlined text-sm mr-2">info</span>
                    <span>Sau khi xác nhận đơn hàng, bạn sẽ được chuyển đến cổng thanh toán PayOS để hoàn tất giao dịch.</span>
                  </p>
                </div>
              </div>

              <!-- Nút đặt hàng -->
              <button 
                @click="placeOrder"
                :disabled="loading"
                class="w-full mt-4 bg-orange-600 text-white py-4 rounded-xl font-semibold hover:bg-orange-700 transition-all flex items-center justify-center gap-2 uppercase tracking-wide text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="material-symbols-outlined">shopping_cart</span>
                {{ loading ? 'Đang xử lý...' : 'TIẾN HÀNH ĐẶT HÀNG' }}
              </button>

              <!-- Badge bảo mật -->
              <div class="mt-4 flex flex-wrap justify-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-green-500 text-sm">verified</span>
                  Thanh toán an toàn 100%
                </span>
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-green-500 text-sm">local_shipping</span>
                  Giao hàng nhanh toàn quốc (2-4 ngày)
                </span>
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-green-500 text-sm">autorenew</span>
                  Đổi trả miễn phí trong 7 ngày
                </span>
              </div>
            </div>

            <!-- CỘT PHẢI: Form báo giá (giữ nguyên) -->
            <div class="p-6 md:p-8 bg-gray-50">
              <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                Yêu Cầu Báo Giá (B2B)
              </h3>
              <form class="space-y-4" @submit.prevent="submitRequest">
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Tên công ty</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Nhập tên doanh nghiệp của bạn" type="text" v-model="form.company">
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Email</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="email@congty.com" type="email" v-model="form.email">
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Số điện thoại</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="09xx xxx xxx" type="tel" v-model="form.phone">
                </div>
                
                <!-- ĐỊA CHỈ CHI TIẾT -->
                <div class="space-y-3 border-t border-gray-200 pt-3">
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-sm font-medium mb-1 text-gray-600">Tỉnh / Thành</label>
                      <select v-model="form.city" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                        <option value="">Chọn tỉnh / thành </option>
                        <option value="Hà Nội">Hà Nội</option>
                        <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                        <option value="Đà Nẵng">Đà Nẵng</option>
                        <option value="Hải Phòng">Hải Phòng</option>
                        <option value="Cần Thơ">Cần Thơ</option>
                        <option value="Bình Dương">Bình Dương</option>
                        <option value="Đồng Nai">Đồng Nai</option>
                        <option value="Khác">Khác</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium mb-1 text-gray-600">Quận / Huyện</label>
                      <select v-model="form.district" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                        <option value="">Chọn quận / huyện </option>
                        <option value="Quận 1">Quận 1</option>
                        <option value="Quận 2">Quận 2</option>
                        <option value="Quận 3">Quận 3</option>
                        <option value="Quận 4">Quận 4</option>
                        <option value="Quận 5">Quận 5</option>
                        <option value="Quận 6">Quận 6</option>
                        <option value="Quận 7">Quận 7</option>
                        <option value="Quận 8">Quận 8</option>
                        <option value="Quận 9">Quận 9</option>
                        <option value="Quận 10">Quận 10</option>
                        <option value="Quận 11">Quận 11</option>
                        <option value="Quận 12">Quận 12</option>
                        <option value="Bình Thạnh">Bình Thạnh</option>
                        <option value="Gò Vấp">Gò Vấp</option>
                        <option value="Tân Bình">Tân Bình</option>
                        <option value="Tân Phú">Tân Phú</option>
                        <option value="Phú Nhuận">Phú Nhuận</option>
                        <option value="Khác">Khác</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Phường / Xã</label>
                    <select v-model="form.ward" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                      <option value="">Chọn phường / xã </option>
                      <option value="Phường Bến Nghé">Phường Bến Nghé</option>
                      <option value="Phường Bến Thành">Phường Bến Thành</option>
                      <option value="Phường Cầu Kho">Phường Cầu Kho</option>
                      <option value="Phường Cầu Ông Lãnh">Phường Cầu Ông Lãnh</option>
                      <option value="Phường Cô Giang">Phường Cô Giang</option>
                      <option value="Phường Đa Kao">Phường Đa Kao</option>
                      <option value="Phường Nguyễn Thái Bình">Phường Nguyễn Thái Bình</option>
                      <option value="Khác">Khác</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
                    <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Số nhà, tên đường..." type="text" v-model="form.address">
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Ghi chú giao hàng</label>
                    <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..." type="text" v-model="form.note">
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Yêu cầu đặc biệt</label>
                    <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Ví dụ: In logo, bao bì thương hiệu..." type="text" v-model="form.requirements">
                  </div>
                </div>
                <p class="text-center text-xs text-gray-400 mt-2 italic">Chúng tôi sẽ phản hồi trong vòng 30 phút</p>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import axios from 'axios'

// ==================== PROPS ====================
const props = defineProps({
  selectedProduct: {
    type: Object,
    default: null
  },
  suggestedProducts: {
    type: Array,
    default: () => []
  },
  defaultQuantity: {
    type: Number,
    default: 1
  },
  defaultColor: {
    type: String,
    default: ''
  },
  defaultSize: {
    type: String,
    default: ''
  },
  discounts: {
    type: Array,
    default: () => []
  }
})

// ==================== REACTIVE DATA ====================
const selectedProduct = ref(props.selectedProduct)
const suggestedProducts = ref(props.suggestedProducts)

const orderQuantity = ref(props.defaultQuantity || 1)
const selectedColor = ref('')
const selectedSize = ref('')
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// ==================== FORM B2B ====================
const form = ref({
  company: '',
  email: '',
  phone: '',
  city: '',
  district: '',
  ward: '',
  address: '',
  note: '',
  requirements: ''
})

// ==================== COMPUTED ====================
const colorOptions = computed(() => {
  if (!selectedProduct.value) return []
  return selectedProduct.value.colors?.map(c => c.name) || []
})

const sizeOptions = computed(() => {
  if (!selectedProduct.value) return []
  return selectedProduct.value.sizes || []
})

const basePrice = computed(() => {
  return selectedProduct.value?.base_price || 0
})

const salePrice = computed(() => {
  return selectedProduct.value?.sale_price || basePrice.value
})

// ===== TÍNH CHIẾT KHẤU DỰA TRÊN DISCOUNTS =====
const currentDiscountPercent = computed(() => {
  const qty = orderQuantity.value
  if (!props.discounts || props.discounts.length === 0) return 0
  // Lọc discounts có min_quantity <= qty
  const applicable = props.discounts.filter(d => d.min_quantity <= qty)
  if (applicable.length === 0) return 0
  // Lấy discount có min_quantity lớn nhất
  const maxDiscount = applicable.reduce((a, b) => a.min_quantity > b.min_quantity ? a : b)
  return maxDiscount.discount_percent || 0
})

// Tổng tiền trước chiết khấu
const subtotal = computed(() => {
  return salePrice.value * orderQuantity.value
})

// Số tiền chiết khấu
const discountAmount = computed(() => {
  return (subtotal.value * currentDiscountPercent.value) / 100
})

// Tổng tiền sau chiết khấu
const totalPrice = computed(() => {
  return subtotal.value - discountAmount.value
})

// Format tiền
const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return new Intl.NumberFormat('vi-VN').format(price) + '₫'
}

// ==================== METHODS ====================
const increaseQuantity = () => {
  orderQuantity.value++
}

const decreaseQuantity = () => {
  if (orderQuantity.value > 1) {
    orderQuantity.value--
  }
}

// ===== SUBMIT REQUEST =====
const submitRequest = async () => {
  if (!form.value.address) {
    alert('Vui lòng nhập địa chỉ chi tiết!')
    return
  }

  loading.value = true

  try {
    const response = await axios.post(route('wholesale.submit'), {
      company: form.value.company,
      email: form.value.email,
      phone: form.value.phone,
      city: form.value.city,
      district: form.value.district,
      ward: form.value.ward,
      address: form.value.address,
      note: form.value.note,
      requirements: form.value.requirements,
      product_id: selectedProduct.value?.id,
      variant_id: selectedProduct.value?.variant_id,
      quantity: orderQuantity.value,
      color: selectedColor.value,
      size: selectedSize.value,
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      alert(response.data.message)
      // Reset form
      form.value = {
        company: '',
        email: '',
        phone: '',
        city: '',
        district: '',
        ward: '',
        address: '',
        note: '',
        requirements: ''
      }
    } else {
      alert(response.data.message || 'Có lỗi xảy ra, vui lòng thử lại!')
    }
  } catch (error) {
    console.error('Error submitting quote:', error)
    alert(error.response?.data?.message || 'Không thể gửi yêu cầu. Vui lòng thử lại!')
  } finally {
    loading.value = false
  }
}

// ===== PLACE ORDER (Chỉ PayOS) =====
const placeOrder = async () => {
  if (!selectedProduct.value) {
    alert('Vui lòng chọn sản phẩm!')
    return
  }

  if (orderQuantity.value < 1) {
    alert('Số lượng phải lớn hơn 0')
    return
  }

  // Tìm variant dựa trên màu và size
  const variants = selectedProduct.value.variants || []
  let selectedVariant = null

  if (selectedColor.value && selectedSize.value) {
    selectedVariant = variants.find(v => v.color_name === selectedColor.value && v.size_name === selectedSize.value)
  } else if (selectedColor.value) {
    selectedVariant = variants.find(v => v.color_name === selectedColor.value)
  } else if (selectedSize.value) {
    selectedVariant = variants.find(v => v.size_name === selectedSize.value)
  }

  if (!selectedVariant) {
    alert('Vui lòng chọn màu sắc và kích thước hợp lệ!')
    return
  }

  loading.value = true

  try {
    const response = await axios.post(route('wholesale.order'), {
      variant_id: selectedVariant.id,
      quantity: orderQuantity.value,
      customer_name: form.value.company || 'Khách hàng',
      customer_phone: form.value.phone || '0900000000',
      customer_email: form.value.email || 'khachhang@example.com',
      receiver_name: form.value.company || 'Khách hàng',
      receiver_phone: form.value.phone || '0900000000',
      shipping_address: form.value.address || 'Chưa nhập địa chỉ',
      note: form.value.note || '',
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    // Kiểm tra response
    if (response.data.redirect_url) {
      // Chuyển hướng đến PayOS
      window.location.href = response.data.redirect_url
    } else if (response.data.order_id) {
      // Chuyển đến trang tạo payment
      router.get(route('payment.create', { order_id: response.data.order_id }))
    } else {
      alert('Đặt hàng thành công! Chúng tôi sẽ liên hệ xác nhận trong 30 phút.')
    }
  } catch (error) {
    console.error('Error placing order:', error)
    const msg = error.response?.data?.message || 'Không thể đặt hàng. Vui lòng thử lại!'
    alert(msg)
  } finally {
    loading.value = false
  }
}

// ==================== WATCH ====================
watch(() => props.selectedProduct, (newVal) => {
  if (newVal) {
    selectedProduct.value = newVal
    // Set default color và size nếu có
    if (newVal.colors && newVal.colors.length > 0) {
      selectedColor.value = newVal.colors[0].name
    }
    if (newVal.sizes && newVal.sizes.length > 0) {
      selectedSize.value = newVal.sizes[0]
    }
  }
}, { immediate: true })

onMounted(() => {
  // Set default từ props
  if (props.defaultColor) {
    selectedColor.value = props.defaultColor
  }
  if (props.defaultSize) {
    selectedSize.value = props.defaultSize
  }
})
</script>

<style scoped>
/* Ẩn mũi tên lên xuống của input number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>