<template>
  <div>
    <Head title="Tùy chỉnh sản phẩm - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-12 bg-gray-50">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Side: Customization Form -->
        <section class="flex-1 space-y-6">
          <div class="space-y-2">
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900">Tùy chỉnh sản phẩm</h1>
            <p class="text-gray-600 text-sm">Cá nhân hóa chiếc balo của bạn với logo hoặc hình ảnh riêng biệt. Đội ngũ thiết kế của chúng tôi sẽ xem xét và phản hồi trong vòng 24h.</p>
          </div>

          <form class="space-y-4 bg-white p-6 rounded-xl border border-gray-100 shadow-sm" @submit.prevent="submitRequest">
            <div v-if="message" class="p-3 rounded-lg text-sm text-center" :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
              {{ message }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Họ và tên <span class="text-red-500">*</span></label>
                <input class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="Nhập tên của bạn" type="text" v-model="form.fullName" required>
              </div>
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Email <span class="text-red-500">*</span></label>
                <input class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="email@example.com" type="email" v-model="form.email" required>
              </div>
              <div class="md:col-span-2">
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Số điện thoại <span class="text-red-500">*</span></label>
                <input class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="090 xxx xxxx" type="tel" v-model="form.phone" required>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Vị trí in <span class="text-red-500">*</span></label>
                <select class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50 text-gray-700" v-model="form.position" required>
                  <option value="">Chọn vị trí</option>
                  <option value="front">Mặt trước</option>
                  <option value="back">Mặt sau</option>
                  <option value="side">Bên hông</option>
                </select>
              </div>
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Kích thước in <span class="text-red-500">*</span></label>
                <select class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50 text-gray-700" v-model="form.size" required>
                  <option value="">Chọn kích thước</option>
                  <option value="small">Nhỏ (S)</option>
                  <option value="medium">Vừa (M)</option>
                  <option value="large">Lớn (L)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Ghi chú</label>
              <textarea class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="Nhập ghi chú hoặc yêu cầu chi tiết của bạn tại đây..." rows="4" v-model="form.note"></textarea>
            </div>

            <div>
              <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Tải lên Logo/Hình in</label>
              <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer group hover:border-primary transition-colors" @click="triggerFileUpload">
                <span class="material-symbols-outlined text-4xl text-gray-400 group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                <p class="font-semibold text-sm text-gray-700">Kéo và thả hoặc nhấp để tải lên</p>
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, AI, PDF (Tối đa 10MB)</p>
                <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" accept=".png,.jpg,.jpeg,.ai,.pdf">
              </div>
              <p v-if="uploadedFileName" class="text-xs text-green-600 mt-2">Đã tải lên: {{ uploadedFileName }}</p>
              <p v-if="uploadError" class="text-xs text-red-600 mt-2">{{ uploadError }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
              <button class="flex-1 bg-primary text-white py-4 px-8 rounded-lg hover:bg-primary-dark transition-colors flex items-center justify-center gap-2" type="submit" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span v-else class="material-symbols-outlined">add_shopping_cart</span>
                {{ isSubmitting ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
              </button>
              <button class="flex-1 border border-gray-300 text-gray-700 py-4 px-8 rounded-lg hover:bg-gray-100 transition-all flex items-center justify-center gap-2" type="button" @click="saveDesign">
                <span class="material-symbols-outlined">save</span> Lưu thiết kế
              </button>
            </div>
            <p class="text-xs text-gray-400 text-center">Sản phẩm sẽ được thêm vào giỏ hàng với phí in logo.</p>
          </form>
        </section>

        <!-- Right Side: Product Info & Price Table -->
        <aside class="flex-1 flex flex-col gap-4">
          <!-- Product Image & Info -->
          <div v-if="product" class="bg-white rounded-xl overflow-hidden border border-gray-100 shadow-sm">
            <div class="aspect-square bg-gray-100 relative">
              <img 
                :src="product.image || '/images/default-product.jpg'" 
                :alt="product.name || 'Sản phẩm'" 
                class="w-full h-full object-cover"
                @error="handleImageError"
              >
            </div>
            <div class="p-4">
              <div class="flex items-start justify-between">
                <div>
                  <p v-if="product.brand" class="text-xs text-gray-500 uppercase">{{ product.brand }}</p>
                  <h3 class="font-bold text-lg text-gray-800">{{ product.name || 'Sản phẩm' }}</h3>
                  <p v-if="product.category" class="text-xs text-gray-400">{{ product.category }}</p>
                </div>
              </div>
              <p v-if="product.description" class="text-sm text-gray-600 mt-2 line-clamp-2">{{ product.description }}</p>
            </div>
          </div>

          <!-- No product selected -->
          <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center text-gray-400">
            <span class="material-symbols-outlined text-6xl block mb-2">shopping_bag</span>
            <p class="font-medium">Chưa có sản phẩm được chọn</p>
            <p class="text-sm">Vui lòng chọn sản phẩm từ trang chi tiết</p>
            <Link :href="route('home')" class="inline-block mt-3 text-primary hover:underline text-sm">Về trang chủ</Link>
          </div>

          <!-- Price Table (tính toán dựa trên giá sản phẩm) -->
          <div v-if="product && product.price > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <h3 class="font-bold text-lg text-gray-800 mb-3">Bảng giá tham khảo</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left py-2 px-3 font-semibold text-gray-600">Vị trí in</th>
                    <th class="text-left py-2 px-3 font-semibold text-gray-600">Nhỏ (S)</th>
                    <th class="text-left py-2 px-3 font-semibold text-gray-600">Vừa (M)</th>
                    <th class="text-left py-2 px-3 font-semibold text-gray-600">Lớn (L)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-700">Mặt trước</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.1) }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.15) }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.2) }}</td>
                  </tr>
                  <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-700">Mặt sau</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.12) }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.18) }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.25) }}</td>
                  </tr>
                  <tr>
                    <td class="py-2 px-3 font-medium text-gray-700">Bên hông</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.08) }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.12) }}</td>
                    <td class="py-2 px-3 text-gray-600">{{ formatPrice(product.price * 0.18) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200">
              <p class="text-xs text-amber-700">
                * Giá in chưa bao gồm VAT và phí vận chuyển. Giá thực tế sẽ được cập nhật khi thêm vào giỏ hàng.
              </p>
              <p class="text-xs text-amber-700 mt-1">
                * Số lượng càng lớn, chiết khấu càng cao. Liên hệ để được báo giá tốt nhất.
              </p>
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
import { Head, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import { useCart } from '@/utils/useCart'

// Lấy dữ liệu từ page props
const page = usePage()
const props = defineProps({
  selectedProduct: {
    type: Object,
    default: null
  }
})

const { addToCart } = useCart()

// Ưu tiên dùng props, nếu không thì dùng page.props
const product = computed(() => {
  return props.selectedProduct || page.props.selectedProduct || null
})

// Format price
const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return new Intl.NumberFormat('vi-VN').format(Math.round(price)) + '₫'
}

// Form state
const form = ref({
  fullName: '',
  email: '',
  phone: '',
  position: '',
  size: '',
  note: ''
})

const uploadedFileName = ref('')
const uploadError = ref('')
const fileInput = ref(null)
const isSubmitting = ref(false)
const message = ref('')
const messageType = ref('success')

// Trigger file upload
const triggerFileUpload = () => {
  fileInput.value.click()
}

// Handle file upload
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  uploadError.value = ''
  if (!file) return

  // Validate file size (10MB)
  if (file.size > 10 * 1024 * 1024) {
    uploadError.value = 'File vượt quá 10MB. Vui lòng chọn file nhỏ hơn.'
    event.target.value = ''
    return
  }

  // Validate file type
  const allowedTypes = ['image/png', 'image/jpeg', 'application/pdf', 'application/postscript', 'image/ai']
  if (!allowedTypes.includes(file.type) && !file.name.match(/\.(png|jpg|jpeg|ai|pdf)$/i)) {
    uploadError.value = 'Định dạng file không hỗ trợ. Chấp nhận: PNG, JPG, AI, PDF.'
    event.target.value = ''
    return
  }

  uploadedFileName.value = file.name
}

// Handle image error
const handleImageError = (e) => {
  e.target.src = '/images/default-product.jpg'
}

// Submit request: thêm vào giỏ hàng với meta
const submitRequest = async () => {
  // Reset message
  message.value = ''
  messageType.value = 'success'

  // Validation
  if (!form.value.fullName || !form.value.email || !form.value.phone) {
    message.value = 'Vui lòng điền đầy đủ thông tin bắt buộc (Họ tên, Email, Số điện thoại)'
    messageType.value = 'error'
    return
  }

  if (!form.value.position) {
    message.value = 'Vui lòng chọn vị trí in'
    messageType.value = 'error'
    return
  }

  if (!form.value.size) {
    message.value = 'Vui lòng chọn kích thước in'
    messageType.value = 'error'
    return
  }

  if (!product.value) {
    message.value = 'Không tìm thấy sản phẩm. Vui lòng quay lại trang chi tiết sản phẩm.'
    messageType.value = 'error'
    return
  }

  // Lấy variant đầu tiên (có thể cải thiện: cho phép chọn màu/size)
  const variant = product.value.variants?.[0]
  if (!variant) {
    message.value = 'Sản phẩm không có biến thể'
    messageType.value = 'error'
    return
  }

  isSubmitting.value = true

  try {
    let logoPath = ''
    // Upload logo nếu có file
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
        message.value = 'Không thể tải lên file logo'
        messageType.value = 'error'
        isSubmitting.value = false
        return
      }
    }

    // Tạo meta
    const meta = {
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

    // Thêm vào giỏ hàng
    await addToCart(variant.id, 1, meta)

    message.value = '✅ Đã thêm sản phẩm tùy chỉnh vào giỏ hàng!'
    messageType.value = 'success'
    
    // Reset form
    form.value = { fullName: '', email: '', phone: '', position: '', size: '', note: '' }
    uploadedFileName.value = ''
    fileInput.value.value = ''

    // Chuyển đến giỏ hàng sau 1.5s
    setTimeout(() => {
      window.location.href = route('cart')
    }, 1500)

  } catch (error) {
    console.error('Error:', error)
    message.value = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.'
    messageType.value = 'error'
  } finally {
    isSubmitting.value = false
  }
}

// Save design (demo)
const saveDesign = () => {
  alert('Tính năng lưu thiết kế đang được phát triển. Vui lòng gửi yêu cầu để được hỗ trợ.')
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
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>