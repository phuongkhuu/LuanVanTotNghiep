<template>
  <div>
    <Head title="Mua sỉ - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main>
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 py-12 md:py-16" id="contact">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
          <!-- KHUNG CAM -->
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

          <!-- NỘI DUNG 2 CỘT -->
          <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- CỘT TRÁI: Thông tin sản phẩm -->
            <div class="p-6 md:p-8 border-r border-gray-100">
              <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                Thông tin đặt hàng
              </h3>
              
              <!-- Sản phẩm chọn -->
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

              <!-- Bộ lọc: Số lượng, Màu, Kích thước -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Số lượng <span class="text-red-500">*</span></label>
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
                      v-model.number="orderQuantity" 
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
                  <p v-if="orderQuantity < 50" class="text-xs text-red-500 mt-1">* Số lượng tối thiểu là 50</p>
                  <p v-else class="text-xs text-green-500 mt-1">✓ Đủ số lượng tối thiểu</p>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Màu sắc <span class="text-red-500">*</span></label>
                  <select v-model="selectedColor" class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-orange-400 bg-white text-gray-700">
                    <option v-for="color in colorOptions" :key="color" :value="color">{{ color }}</option>
                  </select>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Kích thước <span class="text-red-500">*</span></label>
                  <select v-model="selectedSize" class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-orange-400 bg-white text-gray-700">
                    <option v-for="size in sizeOptions" :key="size" :value="size">{{ size }}</option>
                  </select>
                </div>
              </div>

              <!-- Nút gửi yêu cầu -->
              <button 
                @click="submitQuoteRequest"
                :disabled="loading || orderQuantity < 50"
                class="w-full mt-4 bg-orange-600 text-white py-4 rounded-xl font-semibold hover:bg-orange-700 transition-all flex items-center justify-center gap-2 uppercase tracking-wide text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="material-symbols-outlined">send</span>
                {{ loading ? 'Đang xử lý...' : 'GỬI YÊU CẦU MUA SỈ' }}
              </button>
              <p v-if="orderQuantity < 50" class="text-center text-sm text-red-500 mt-2">Vui lòng nhập số lượng tối thiểu 50 để gửi yêu cầu.</p>
            </div>

            <!-- CỘT PHẢI: Thông tin doanh nghiệp -->
            <div class="p-6 md:p-8 bg-gray-50">
              <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                Thông tin doanh nghiệp
              </h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Tên công ty <span class="text-red-500">*</span></label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Nhập tên doanh nghiệp của bạn" type="text" v-model="form.company" required>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Email <span class="text-red-500">*</span></label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="email@congty.com" type="email" v-model="form.email" required>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Số điện thoại <span class="text-red-500">*</span></label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="09xx xxx xxx" type="tel" v-model="form.phone" required>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Mã số thuế</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Mã số thuế công ty" type="text" v-model="form.tax_code">
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Ngày cần nhận hàng</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" type="date" v-model="form.delivery_date" :min="today">
                </div>

                <!-- Địa chỉ chi tiết -->
                <div class="space-y-3 border-t border-gray-200 pt-3">
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-sm font-medium mb-1 text-gray-600">Tỉnh / Thành</label>
                      <select v-model="form.city" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                        <option value="">Chọn tỉnh / thành</option>
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
                        <option value="">Chọn quận / huyện</option>
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
                      <option value="">Chọn phường / xã</option>
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
                    <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Số nhà, tên đường..." type="text" v-model="form.address" required>
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
                <p class="text-center text-xs text-gray-400 mt-2 italic">* Thông tin bắt buộc. Sau khi gửi, chúng tôi sẽ liên hệ để báo giá và xác nhận.</p>
              </div>
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
import { Head } from '@inertiajs/vue3'
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

// ==================== FORM B2B ====================
const form = ref({
  company: '',
  email: '',
  phone: '',
  tax_code: '',
  delivery_date: '',
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

// Format tiền
const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return new Intl.NumberFormat('vi-VN').format(price) + '₫'
}

// ==================== NGÀY HIỆN TẠI CHO INPUT DATE ====================
const today = new Date().toISOString().split('T')[0]

// ==================== METHODS ====================
const increaseQuantity = () => {
  orderQuantity.value++
}

const decreaseQuantity = () => {
  if (orderQuantity.value > 1) {
    orderQuantity.value--
  }
}

// ===== GỬI YÊU CẦU MUA SỈ =====
const submitQuoteRequest = async () => {
  // Kiểm tra số lượng tối thiểu
  if (orderQuantity.value < 50) {
    alert('Số lượng đặt tối thiểu là 50 sản phẩm.')
    return
  }

  // Kiểm tra thông tin bắt buộc
  if (!form.value.company) {
    alert('Vui lòng nhập tên công ty.')
    return
  }
  if (!form.value.email) {
    alert('Vui lòng nhập email.')
    return
  }
  if (!form.value.phone) {
    alert('Vui lòng nhập số điện thoại.')
    return
  }
  // Kiểm tra số điện thoại đúng 10 chữ số
  if (!/^\d{10}$/.test(form.value.phone)) {
    alert('Số điện thoại phải gồm đúng 10 chữ số.')
    return
  }
  if (!form.value.address) {
    alert('Vui lòng nhập địa chỉ chi tiết.')
    return
  }

  // Kiểm tra ngày cần nhận (nếu có)
  if (form.value.delivery_date) {
    const todayDate = new Date()
    todayDate.setHours(0,0,0,0)
    const deliveryDate = new Date(form.value.delivery_date)
    deliveryDate.setHours(0,0,0,0)
    if (deliveryDate < todayDate) {
      alert('Ngày cần nhận không được là quá khứ.')
      return
    }
  }

  if (!selectedProduct.value) {
    alert('Vui lòng chọn sản phẩm.')
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
    alert('Vui lòng chọn màu sắc và kích thước hợp lệ.')
    return
  }

  loading.value = true

  try {
    const response = await axios.post(route('wholesale.submit-request'), {
      company: form.value.company,
      email: form.value.email,
      phone: form.value.phone,
      tax_code: form.value.tax_code,
      delivery_date: form.value.delivery_date,
      city: form.value.city,
      district: form.value.district,
      ward: form.value.ward,
      address: form.value.address,
      note: form.value.note,
      requirements: form.value.requirements,
      variant_id: selectedVariant.id,
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
      alert(response.data.message || 'Gửi yêu cầu thành công! Chúng tôi sẽ liên hệ trong 30 phút.')
      setTimeout(() => {
        window.location.href = '/'
      }, 1500)
    } else {
      alert(response.data.message || 'Có lỗi xảy ra, vui lòng thử lại.')
    }
  } catch (error) {
    console.error('Error submitting quote request:', error)
    let msg = 'Không thể gửi yêu cầu. Vui lòng thử lại!'
    if (error.response?.data?.message) {
      msg = error.response.data.message
    } else if (error.response?.status === 401) {
      msg = 'Vui lòng đăng nhập để gửi yêu cầu mua sỉ.'
    }
    alert(msg)
  } finally {
    loading.value = false
  }
}

// ==================== WATCH ====================
watch(() => props.selectedProduct, (newVal) => {
  if (newVal) {
    selectedProduct.value = newVal
    if (newVal.colors && newVal.colors.length > 0) {
      selectedColor.value = newVal.colors[0].name
    }
    if (newVal.sizes && newVal.sizes.length > 0) {
      selectedSize.value = newVal.sizes[0]
    }
  }
}, { immediate: true })

onMounted(() => {
  if (props.defaultColor) {
    selectedColor.value = props.defaultColor
  }
  if (props.defaultSize) {
    selectedSize.value = props.defaultSize
  }
})
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>