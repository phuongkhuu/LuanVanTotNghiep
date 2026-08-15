<template>
  <div>
    <Head title="Tùy chỉnh sản phẩm - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-slate-50/50 min-h-[calc(100vh-80px)]">
      <!-- Tiêu đề trang -->
      <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Tùy chỉnh sản phẩm</h1>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Bên trái: Form Tùy Chỉnh (7 Cột) -->
        <section class="lg:col-span-7 bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm space-y-6">
          <form class="space-y-6" @submit.prevent="submitRequest">
            <!-- Thông báo trạng thái -->
            <transition name="fade">
              <div 
                v-if="message" 
                class="p-4 rounded-xl text-sm font-medium flex items-center gap-3"
                :class="messageType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
              >
                <span class="material-symbols-outlined text-xl">
                  {{ messageType === 'success' ? 'check_circle' : 'error' }}
                </span>
                <span>{{ message }}</span>
              </div>
            </transition>

            <!-- Khối 1: Thông tin liên hệ -->
            <div class="space-y-4">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">1. Thông tin liên hệ</h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Họ và tên <span class="text-rose-500">*</span></label>
                  <input 
                    v-model="form.fullName" 
                    type="text" 
                    required 
                    placeholder="Nguyễn Văn A" 
                    class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border"
                  >
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Email <span class="text-rose-500">*</span></label>
                  <input 
                    v-model="form.email" 
                    type="email" 
                    required 
                    placeholder="email@example.com" 
                    class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border"
                  >
                </div>
                <div class="sm:col-span-2">
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                  <input 
                    v-model="form.phone" 
                    type="tel" 
                    required 
                    placeholder="090 xxx xxxx" 
                    class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border"
                  >
                </div>
              </div>
            </div>

            <hr class="border-gray-100" />

            <!-- Khối 2: Tùy chỉnh vị trí & kích thước -->
            <div class="space-y-4">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">2. Thông số bản in</h2>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Vị trí in <span class="text-rose-500">*</span></label>
                  <select 
                    v-model="form.position" 
                    required 
                    class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border text-gray-700"
                  >
                    <option value="">-- Chọn --</option>
                    <option value="front">Mặt trước</option>
                    <option value="back">Mặt sau</option>
                    <option value="side">Bên hông</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Kích thước in <span class="text-rose-500">*</span></label>
                  <select 
                    v-model="form.size" 
                    required 
                    class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border text-gray-700"
                  >
                    <option value="">-- Chọn --</option>
                    <option value="small">Nhỏ (S)</option>
                    <option value="medium">Vừa (M)</option>
                    <option value="large">Lớn (L)</option>
                  </select>
                </div>
                <!-- ===== SỐ LƯỢNG ===== -->
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">Số lượng <span class="text-rose-500">*</span></label>
                  <input 
                    v-model.number="form.quantity" 
                    type="number" 
                    min="1" 
                    required 
                    class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border"
                  >
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Ghi chú thiết kế</label>
                <textarea 
                  v-model="form.note" 
                  rows="3" 
                  placeholder="Mô tả chi tiết mong muốn (màu sắc, vị trí chính xác...)" 
                  class="w-full text-sm border-gray-200 bg-gray-50/50 rounded-lg p-2.5 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none border resize-none"
                ></textarea>
              </div>
            </div>

            <hr class="border-gray-100" />

            <!-- Khối 3: Upload file -->
            <div class="space-y-2">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">3. Tải lên tệp thiết kế</h2>
              <div 
                class="border-2 border-dashed border-gray-200 rounded-xl p-5 hover:border-primary/50 hover:bg-slate-50/50 transition-all cursor-pointer text-center group"
                @click="triggerFileUpload"
              >
                <span class="material-symbols-outlined text-3xl text-gray-400 group-hover:text-primary transition-colors mb-1">cloud_upload</span>
                <p class="text-sm font-semibold text-gray-700">Tải tệp logo/hình ảnh</p>
                <p class="text-xs text-gray-400 mt-0.5">PNG, JPG, AI, PDF (Tối đa 10MB)</p>
                <input ref="fileInput" type="file" class="hidden" accept=".png,.jpg,.jpeg,.ai,.pdf" @change="handleFileUpload">
              </div>
              <div v-if="uploadedFileName" class="flex items-center gap-2 text-xs text-emerald-600 bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100">
                <span class="material-symbols-outlined text-base">description</span>
                <span class="font-medium truncate">{{ uploadedFileName }}</span>
              </div>
              <p v-if="uploadError" class="text-xs text-rose-500 mt-1">{{ uploadError }}</p>
            </div>

            <!-- Nút hành động -->
            <div class="pt-2 flex flex-col sm:flex-row gap-3">
              <button 
                type="submit" 
                :disabled="isSubmitting"
                class="flex-1 bg-gray-900 text-white font-semibold py-3 px-6 rounded-xl hover:bg-primary transition-colors flex items-center justify-center gap-2 text-sm shadow-sm disabled:opacity-50"
              >
                <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span v-else class="material-symbols-outlined text-lg">shopping_bag</span>
                {{ isSubmitting ? 'Đang xử lý...' : 'Tiến hành thanh toán' }}
              </button>
            </div>
          </form>
        </section>

        <!-- Bên phải: Thông tin sản phẩm & Bảng giá (5 Cột) -->
        <aside class="lg:col-span-5 space-y-6">
          <!-- Thông tin sản phẩm -->
          <div v-if="product" class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex gap-4 items-center">
              <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl bg-gray-100 flex-shrink-0 overflow-hidden border border-gray-100">
                <img 
                  :src="product.image || '/images/default-product.jpg'" 
                  :alt="product.name || 'Sản phẩm'" 
                  class="w-full h-full object-cover object-center"
                  @error="handleImageError"
                >
              </div>
              <div class="flex-1 min-w-0 space-y-1">
                <span v-if="product.brand" class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ product.brand }}</span>
                <h3 class="font-bold text-gray-900 text-base line-clamp-1" :title="product.name">{{ product.name || 'Sản phẩm' }}</h3>
                <p v-if="product.category" class="text-xs text-gray-500">{{ product.category }}</p>
                <div class="text-sm font-semibold text-gray-900 pt-1">
                  Giá gốc: {{ formatPrice(product.price) }}
                </div>
                <!-- ===== HIỂN THỊ GIÁ SALE NẾU CÓ ===== -->
                <div v-if="selectedVariant && selectedVariant.is_on_sale" class="text-xs text-red-500 font-semibold">
                  Giá bán: {{ formatPrice(selectedVariant.sale_price) }}
                </div>
              </div>
            </div>

            <!-- Tính toán giá in ước tính -->
            <div v-if="calculatedPrintFee > 0" class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center bg-slate-50 p-3 rounded-xl">
              <span class="text-xs font-semibold text-gray-600">Phí in ước tính:</span>
              <span class="text-sm font-bold text-primary">+ {{ formatPrice(calculatedPrintFee) }}</span>
            </div>
            <!-- Hiển thị số lượng và tổng -->
            <div v-if="form.quantity > 0 && product.price > 0" class="mt-3 pt-2 border-t border-gray-100 flex justify-between items-center">
              <span class="text-xs font-semibold text-gray-600">Số lượng:</span>
              <span class="text-sm font-bold text-gray-800">{{ form.quantity }}</span>
            </div>
            <div v-if="form.quantity > 0 && product.price > 0" class="flex justify-between items-center">
              <span class="text-xs font-semibold text-gray-600">Tổng tiền dự kiến:</span>
              <span class="text-sm font-bold text-primary">{{ formatPrice((Number(finalProductPrice) + Number(calculatedPrintFee)) * Number(form.quantity)) }}</span>
            </div>
          </div>

          <!-- Chưa chọn sản phẩm -->
          <div v-else class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm text-center text-gray-400">
            <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">work_off</span>
            <p class="text-sm font-medium">Chưa có sản phẩm được chọn</p>
            <Link :href="route('home')" class="mt-2 text-xs text-primary font-semibold hover:underline inline-block">Về danh mục sản phẩm</Link>
          </div>

          <!-- Bảng giá tham khảo dịch vụ in -->
          <div v-if="product && product.price > 0" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm space-y-3">
            <h3 class="font-bold text-sm text-gray-900">Bảng giá in theo yêu cầu</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-xs text-left">
                <thead>
                  <tr class="text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-medium">Vị trí</th>
                    <th class="pb-2 font-medium">S (Nhỏ)</th>
                    <th class="pb-2 font-medium">M (Vừa)</th>
                    <th class="pb-2 font-medium">L (Lớn)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-600">
                  <tr>
                    <td class="py-2.5 font-medium text-gray-800">Mặt trước</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.1) }}</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.15) }}</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.2) }}</td>
                  </tr>
                  <tr>
                    <td class="py-2.5 font-medium text-gray-800">Mặt sau</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.12) }}</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.18) }}</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.25) }}</td>
                  </tr>
                  <tr>
                    <td class="py-2.5 font-medium text-gray-800">Bên hông</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.08) }}</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.12) }}</td>
                    <td class="py-2.5">{{ formatPrice(product.price * 0.18) }}</td>
                  </tr>
                </tbody>
              </table>
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
import { ref, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const page = usePage()
const props = defineProps({
  selectedProduct: {
    type: Object,
    default: null
  }
})

const product = computed(() => {
  return props.selectedProduct || page.props.selectedProduct || null
})

// Lấy variant đầu tiên (hoặc cho phép người dùng chọn nếu có)
const selectedVariant = computed(() => {
  if (!product.value || !product.value.variants) return null
  return product.value.variants[0] || null
})

// Giá sau giảm giá (ưu tiên sale_price)
const finalProductPrice = computed(() => {
  const variant = selectedVariant.value
  if (!variant) return 0
  if (variant.is_on_sale && variant.sale_price) {
    return variant.sale_price
  }
  return variant.price || product.value.price || 0
})

const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return new Intl.NumberFormat('vi-VN').format(Math.round(price)) + '₫'
}

// State Form
const form = ref({
  fullName: '',
  email: '',
  phone: '',
  position: '',
  size: '',
  quantity: 1,
  note: ''
})

const uploadedFileName = ref('')
const uploadError = ref('')
const fileInput = ref(null)
const isSubmitting = ref(false)
const message = ref('')
const messageType = ref('success')

// Tính toán chi phí in tạm tính (trên 1 sản phẩm)
const calculatedPrintFee = computed(() => {
  if (!product.value || !finalProductPrice.value || !form.value.position || !form.value.size) return 0
  
  const basePrice = finalProductPrice.value
  const rateMap = {
    front: { small: 0.1, medium: 0.15, large: 0.2 },
    back: { small: 0.12, medium: 0.18, large: 0.25 },
    side: { small: 0.08, medium: 0.12, large: 0.18 }
  }

  const rate = rateMap[form.value.position]?.[form.value.size] || 0
  return basePrice * rate
})

const triggerFileUpload = () => {
  fileInput.value.click()
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  uploadError.value = ''
  if (!file) return

  if (file.size > 10 * 1024 * 1024) {
    uploadError.value = 'File vượt quá 10MB. Vui lòng chọn file nhỏ hơn.'
    event.target.value = ''
    return
  }

  const allowedTypes = ['image/png', 'image/jpeg', 'application/pdf', 'application/postscript', 'image/ai']
  if (!allowedTypes.includes(file.type) && !file.name.match(/\.(png|jpg|jpeg|ai|pdf)$/i)) {
    uploadError.value = 'Định dạng file không hỗ trợ. Chấp nhận: PNG, JPG, AI, PDF.'
    event.target.value = ''
    return
  }

  uploadedFileName.value = file.name
}

const handleImageError = (e) => {
  e.target.src = '/images/default-product.jpg'
}

const submitRequest = async () => {
  message.value = ''
  messageType.value = 'success'

  if (!form.value.fullName || !form.value.email || !form.value.phone) {
    message.value = 'Vui lòng điền đầy đủ thông tin liên hệ.'
    messageType.value = 'error'
    return
  }

  if (!form.value.position || !form.value.size) {
    message.value = 'Vui lòng chọn vị trí và kích thước in.'
    messageType.value = 'error'
    return
  }

  if (!form.value.quantity || form.value.quantity < 1) {
    message.value = 'Vui lòng nhập số lượng hợp lệ (tối thiểu 1).'
    messageType.value = 'error'
    return
  }

  if (!product.value) {
    message.value = 'Không tìm thấy sản phẩm.'
    messageType.value = 'error'
    return
  }

  const variant = selectedVariant.value
  if (!variant) {
    message.value = 'Sản phẩm không có biến thể khả dụng.'
    messageType.value = 'error'
    return
  }

  isSubmitting.value = true

  try {
    let logoPath = ''
    if (fileInput.value && fileInput.value.files[0]) {
      const file = fileInput.value.files[0]
      const uploadForm = new FormData()
      uploadForm.append('logo_file', file)
      const uploadRes = await axios.post('/api/upload-logo', uploadForm, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      if (uploadRes.data.success) {
        logoPath = uploadRes.data.path
      } else {
        message.value = 'Không thể tải lên file logo.'
        messageType.value = 'error'
        isSubmitting.value = false
        return
      }
    }

    // Tính giá cuối cùng cho sản phẩm (sau giảm giá + phí in)
    const basePrice = finalProductPrice.value
    const printFee = calculatedPrintFee.value
    const finalPrice = basePrice + printFee

    // Tạo dữ liệu cart để truyền qua URL
    const cartData = {
      [variant.id]: {
        quantity: form.value.quantity,
        price: finalPrice,
        meta: {
          logo: {
            position: form.value.position,
            size: form.value.size,
            note: form.value.note || '',
            file: logoPath,
            fullName: form.value.fullName,
            email: form.value.email,
            phone: form.value.phone
          }
        }
      }
    }

    // Chuẩn bị query params
    const params = new URLSearchParams({
      cart: JSON.stringify(cartData),
      name: form.value.fullName,
      email: form.value.email,
      phone: form.value.phone
    })

    // Chuyển thẳng đến checkout
    window.location.href = route('checkout') + '?' + params.toString()

  } catch (error) {
    console.error('Error:', error)
    message.value = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.'
    messageType.value = 'error'
    isSubmitting.value = false
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>