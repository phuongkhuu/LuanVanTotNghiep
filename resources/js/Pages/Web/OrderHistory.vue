<template>
  <div>
    <Head title="Lịch sử đơn hàng - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-6xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
          Lịch sử đơn hàng
        </h1>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <span class="material-symbols-outlined text-red-500 text-5xl block mb-3">error_outline</span>
        <p class="text-red-600">{{ error }}</p>
        <button @click="fetchOrders" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
          Thử lại
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="orders.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <span class="material-symbols-outlined text-gray-300 text-7xl block mb-4">shopping_bag</span>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Chưa có đơn hàng nào</h3>
        <p class="text-gray-500 mb-6">Bạn chưa đặt bất kỳ đơn hàng nào tại BigBag</p>
        <a :href="route('home')" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl hover:bg-primary-dark transition font-semibold">
          <span class="material-symbols-outlined text-sm">shopping_cart</span>
          Mua sắm ngay
        </a>
      </div>

      <!-- Orders List -->
      <div v-else class="space-y-6">
        <!-- Filter / Search -->
        <div class="flex flex-wrap gap-4 items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-100">
          <div class="flex flex-wrap gap-3">
            <button 
              v-for="tab in tabs" 
              :key="tab.value"
              @click="activeTab = tab.value"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
              :class="activeTab === tab.value 
                ? 'bg-primary text-white shadow-sm' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            >
              {{ tab.label }}
              <span class="ml-1 text-xs" :class="activeTab === tab.value ? 'text-white/70' : 'text-gray-400'">
                ({{ getOrderCountByStatus(tab.value) }})
              </span>
            </button>
          </div>
          
          <div class="flex items-center gap-3">
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
              <input 
                v-model="searchQuery"
                type="text"
                placeholder="Tìm kiếm đơn hàng..."
                class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:border-primary focus:ring-0 text-sm w-48 md:w-64"
              />
            </div>
            <button @click="fetchOrders" class="p-2 text-gray-500 hover:text-primary transition">
              <span class="material-symbols-outlined">refresh</span>
            </button>
          </div>
        </div>

        <!-- Order Cards -->
        <div 
          v-for="order in filteredOrders" 
          :key="order.id"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
        >
          <!-- Order Header -->
          <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100/50 border-b border-gray-100">
            <div class="flex items-center gap-4 flex-wrap">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
                <p class="font-bold text-gray-800">{{ order.display_code || order.order_code || 'N/A' }}</p>
              </div>
              <div class="hidden sm:block w-px h-8 bg-gray-300"></div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Ngày đặt</p>
                <p class="font-medium text-gray-700">{{ formatDate(order.created_at) }}</p>
              </div>
              <div class="hidden sm:block w-px h-8 bg-gray-300"></div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Tổng tiền</p>
                <p class="font-bold text-primary">{{ formatPrice(order.final_amount || order.total_amount) }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <!-- Badge Pre-order -->
              <span 
                v-if="order.order_code === 'preorder'"
                class="inline-block px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full"
              >
                Pre-order
              </span>
              
              <!-- Status Badge -->
              <span 
                class="inline-block px-4 py-1.5 text-xs font-bold rounded-full"
                :class="getStatusBadgeClass(order.order_status)"
              >
                {{ getStatusLabel(order.order_status) }}
              </span>
            </div>
          </div>

          <!-- Order Body -->
          <div class="px-6 py-4">
            <!-- Products -->
            <div class="space-y-3">
              <div 
                v-for="(item, index) in order.details" 
                :key="index"
                class="flex items-center gap-4 py-2 border-b border-gray-50 last:border-0"
              >
                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                  <img 
                    :src="getProductImage(item)" 
                    :alt="getProductName(item)"
                    @error="(e) => { e.target.src = '/images/default-product.jpg' }"
                    class="w-full h-full object-cover"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-gray-800 truncate">{{ getProductName(item) }}</p>
                  <p class="text-sm text-gray-500">
                    <span v-if="getProductColor(item)">Màu: {{ getProductColor(item) }}</span>
                    <span v-if="getProductColor(item) && getProductSize(item)"> | </span>
                    <span v-if="getProductSize(item)">Size: {{ getProductSize(item) }}</span>
                  </p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm text-gray-500">x{{ item.quantity }}</p>
                  <p class="font-semibold text-gray-800">{{ formatPrice(item.subtotal || item.unit_price * item.quantity) }}</p>
                </div>
              </div>
            </div>

            <!-- Order Footer -->
            <div class="flex flex-wrap items-center justify-between gap-4 mt-4 pt-4 border-t border-gray-100">
              <div class="text-sm text-gray-500">
                <span class="font-medium text-gray-700">Phương thức thanh toán:</span>
                {{ getPaymentLabel(order.payment?.payment_method) }}
              </div>
              
              <div class="flex gap-3">
                <button 
                  @click="viewOrderDetail(order.id)"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition"
                >
                  Xem chi tiết
                </button>
                <button 
                  @click="printOrder(order)"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                >
                  In
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
          <button 
            v-for="page in pagination.last_page" 
            :key="page"
            @click="goToPage(page)"
            class="px-4 py-2 rounded-lg text-sm font-medium transition"
            :class="page === pagination.current_page 
              ? 'bg-primary text-white shadow-sm' 
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </main>

    <!-- Order Detail Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModal">
      <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto modal-scroll">
        <div class="sticky top-0 bg-white z-10 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800">Chi tiết đơn hàng</h3>
          <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div v-if="selectedOrder" class="p-6">
          <!-- Order Info -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div>
              <p class="text-xs text-gray-500 uppercase">Mã đơn hàng</p>
              <p class="font-bold text-gray-800">{{ selectedOrder.display_code }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Ngày đặt</p>
              <p class="font-medium text-gray-700">{{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Trạng thái</p>
              <span class="inline-block px-3 py-1 text-xs font-bold rounded-full" :class="getStatusBadgeClass(selectedOrder.order_status)">
                {{ getStatusLabel(selectedOrder.order_status) }}
              </span>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Tổng tiền</p>
              <p class="font-bold text-primary">{{ formatPrice(selectedOrder.final_amount) }}</p>
            </div>
          </div>

          <!-- Customer Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person</span>
                Thông tin người đặt
              </h4>
              <p class="text-sm"><span class="text-gray-500">Họ tên:</span> {{ selectedOrder.customer_name }}</p>
              <p class="text-sm"><span class="text-gray-500">Email:</span> {{ selectedOrder.customer_email || 'N/A' }}</p>
              <p class="text-sm"><span class="text-gray-500">SĐT:</span> {{ selectedOrder.customer_phone }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">local_shipping</span>
                Thông tin người nhận
              </h4>
              <p class="text-sm"><span class="text-gray-500">Họ tên:</span> {{ selectedOrder.receiver_name }}</p>
              <p class="text-sm"><span class="text-gray-500">SĐT:</span> {{ selectedOrder.receiver_phone }}</p>
              <p class="text-sm"><span class="text-gray-500">Địa chỉ:</span> {{ selectedOrder.shipping_address }}</p>
            </div>
          </div>

          <!-- Products -->
          <div class="mb-6">
            <h4 class="font-semibold text-gray-700 mb-3">Sản phẩm</h4>
            <div class="border border-gray-100 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Sản phẩm</th>
                    <th class="text-center px-4 py-2 text-gray-500 font-medium">SL</th>
                    <th class="text-right px-4 py-2 text-gray-500 font-medium">Đơn giá</th>
                    <th class="text-right px-4 py-2 text-gray-500 font-medium">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="item in selectedOrder.details" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                          <img :src="getProductImage(item)" class="w-full h-full object-cover" :alt="getProductName(item)" />
                        </div>
                        <span class="font-medium">{{ getProductName(item) }}</span>
                      </div>
                    </td>
                    <td class="text-center px-4 py-3">{{ item.quantity }}</td>
                    <td class="text-right px-4 py-3">{{ formatPrice(item.unit_price) }}</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="3" class="text-right px-4 py-2 text-gray-600">Tạm tính</td>
                    <td class="text-right px-4 py-2 font-medium">{{ formatPrice(selectedOrder.total_amount) }}</td>
                  </tr>
                  <tr v-if="selectedOrder.shipping_fee > 0">
                    <td colspan="3" class="text-right px-4 py-2 text-gray-600">Phí vận chuyển</td>
                    <td class="text-right px-4 py-2 font-medium">{{ formatPrice(selectedOrder.shipping_fee) }}</td>
                  </tr>
                  <tr v-if="selectedOrder.discount_amount > 0">
                    <td colspan="3" class="text-right px-4 py-2 text-gray-600">Giảm giá</td>
                    <td class="text-right px-4 py-2 font-medium text-red-500">-{{ formatPrice(selectedOrder.discount_amount) }}</td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-2 font-bold text-gray-800">Tổng cộng</td>
                    <td class="text-right px-4 py-2 font-bold text-primary">{{ formatPrice(selectedOrder.final_amount) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Payment -->
          <div class="grid grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4">
            <div>
              <p class="text-xs text-gray-500 uppercase">Phương thức thanh toán</p>
              <p class="font-medium">{{ getPaymentLabel(selectedOrder.payment?.payment_method) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Trạng thái thanh toán</p>
              <span class="inline-block px-3 py-1 text-xs font-bold rounded-full" :class="getPaymentStatusBadge(selectedOrder.payment?.status)">
                {{ getPaymentStatusLabel(selectedOrder.payment?.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// Lấy user từ page props
const page = usePage()
const userEmail = computed(() => page.props.auth?.user?.email || '')

// State
const orders = ref([])
const loading = ref(true)
const error = ref(null)
const activeTab = ref('all')
const searchQuery = ref('')
const pagination = ref(null)
const showModal = ref(false)
const selectedOrder = ref(null)

// Tabs filter
const tabs = [
  { value: 'all', label: 'Tất cả' },
  { value: 'pending', label: 'Chờ xử lý' },
  { value: 'processing', label: 'Đang xử lý' },
  { value: 'shipping', label: 'Đang giao' },
  { value: 'completed', label: 'Hoàn thành' },
  { value: 'cancelled', label: 'Đã hủy' },
]

// Fetch orders
const fetchOrders = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch('/lich-su-don-hang/data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin'
    })
    
    if (response.status === 401) {
      window.location.href = '/login'
      return
    }
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      throw new Error(errorData.message || 'Không thể tải dữ liệu đơn hàng')
    }
    
    const data = await response.json()
    console.log('📦 Orders data:', data)
    
    if (data.success) {
      // Thay thế email nếu bị N/A bằng email user
      orders.value = (data.orders || []).map(order => ({
        ...order,
        customer_email: order.customer_email && order.customer_email !== 'N/A' 
          ? order.customer_email 
          : userEmail.value || 'N/A'
      }))
      pagination.value = data.pagination || null
      console.log('✅ Loaded orders:', orders.value.length)
    } else {
      throw new Error(data.message || 'Có lỗi xảy ra')
    }
  } catch (err) {
    console.error('❌ Error fetching orders:', err)
    error.value = err.message || 'Có lỗi xảy ra khi tải đơn hàng'
  } finally {
    loading.value = false
  }
}

// Filtered orders
const filteredOrders = computed(() => {
  let filtered = orders.value
  
  if (activeTab.value !== 'all') {
    const statusMap = {
      pending: 0,
      processing: 1,
      shipping: 2,
      completed: 3,
      cancelled: 4
    }
    const statusValue = statusMap[activeTab.value]
    if (statusValue !== undefined) {
      filtered = filtered.filter(order => order.order_status === statusValue)
    }
  }
  
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.trim().toLowerCase()
    filtered = filtered.filter(order => 
      (order.display_code || '').toLowerCase().includes(query) ||
      (order.order_code || '').toLowerCase().includes(query) ||
      (order.customer_name || '').toLowerCase().includes(query) ||
      (order.customer_phone || '').includes(query)
    )
  }
  
  return filtered
})

const getOrderCountByStatus = (status) => {
  if (status === 'all') return orders.value.length
  
  const statusMap = {
    pending: 0,
    processing: 1,
    shipping: 2,
    completed: 3,
    cancelled: 4
  }
  const statusValue = statusMap[status]
  if (statusValue === undefined) return 0
  
  return orders.value.filter(order => order.order_status === statusValue).length
}

// Helper functions
const formatPrice = (val) => {
  if (!val && val !== 0) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  const d = new Date(date)
  return d.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBadgeClass = (status) => {
  const map = {
    0: 'bg-yellow-100 text-yellow-800',
    1: 'bg-blue-100 text-blue-800',
    2: 'bg-purple-100 text-purple-800',
    3: 'bg-green-100 text-green-800',
    4: 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const map = {
    0: 'Chờ xử lý',
    1: 'Đang xử lý',
    2: 'Đang giao',
    3: 'Hoàn thành',
    4: 'Đã hủy',
  }
  return map[status] || 'Không xác định'
}

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
  }
  return map[method] || method || 'Chưa xác định'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

const getProductName = (item) => {
  return item.product?.name || item.product_name || 'Sản phẩm không xác định'
}

const getProductImage = (item) => {
  if (item.product?.image_url) {
    if (Array.isArray(item.product.image_url) && item.product.image_url.length > 0) {
      return item.product.image_url[0]
    }
    if (typeof item.product.image_url === 'string') {
      return item.product.image_url
    }
  }
  if (item.product?.thumbnail) {
    return item.product.thumbnail
  }
  if (item.image) {
    return item.image
  }
  return '/images/default-product.jpg'
}

const getProductColor = (item) => {
  return item.color?.name || item.color_name || ''
}

const getProductSize = (item) => {
  return item.size || item.size_name || ''
}

// View order detail
const viewOrderDetail = (orderId) => {
  const order = orders.value.find(o => o.id === orderId)
  if (order) {
    // Đảm bảo email được hiển thị
    const orderWithEmail = {
      ...order,
      customer_email: order.customer_email && order.customer_email !== 'N/A' 
        ? order.customer_email 
        : userEmail.value || 'N/A'
    }
    selectedOrder.value = orderWithEmail
    showModal.value = true
  }
}

const closeModal = () => {
  showModal.value = false
  selectedOrder.value = null
}

// Print order
const printOrder = (order) => {
  const printWindow = window.open('', '_blank')
  if (!printWindow) {
    alert('Vui lòng cho phép popup để in đơn hàng')
    return
  }
  
  const content = generatePrintContent(order)
  printWindow.document.write(content)
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
}

// Generate print content
const generatePrintContent = (order) => {
  // Lấy email từ order hoặc từ user
  const email = order.customer_email && order.customer_email !== 'N/A' 
    ? order.customer_email 
    : userEmail.value || 'N/A'

  const detailsHtml = order.details.map(item => `
    <tr>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">${getProductName(item)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: center;">${item.quantity}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.unit_price)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.subtotal)}</td>
    </tr>
  `).join('')

  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Đơn hàng #${order.display_code || order.order_code}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: auto; }
        h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .info { margin: 20px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-box h3 { margin: 0 0 10px 0; color: #6b7280; font-size: 14px; text-transform: uppercase; }
        .info-box p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; border: 1px solid #ddd; }
        td { padding: 8px 12px; border: 1px solid #ddd; }
        .total { font-size: 20px; font-weight: bold; color: #1a56db; text-align: right; }
        .footer { margin-top: 40px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-shipping { background: #f3e8ff; color: #6b21a8; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
      </style>
    </head>
    <body>
      <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
      <p><strong>Mã đơn hàng:</strong> ${order.display_code || order.order_code}</p>
      <p><strong>Ngày đặt:</strong> ${formatDate(order.created_at)}</p>
      
      <div class="info-grid">
        <div class="info-box">
          <h3>Thông tin người đặt</h3>
          <p><strong>Họ tên:</strong> ${order.customer_name}</p>
          <p><strong>Email:</strong> ${email}</p>
          <p><strong>SĐT:</strong> ${order.customer_phone}</p>
        </div>
        <div class="info-box">
          <h3>Thông tin người nhận</h3>
          <p><strong>Họ tên:</strong> ${order.receiver_name}</p>
          <p><strong>SĐT:</strong> ${order.receiver_phone}</p>
          <p><strong>Địa chỉ:</strong> ${order.shipping_address}</p>
        </div>
      </div>
      
      <h3>Danh sách sản phẩm</h3>
      <table>
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th style="text-align: center;">Số lượng</th>
            <th style="text-align: right;">Đơn giá</th>
            <th style="text-align: right;">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          ${detailsHtml}
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">Tạm tính</td>
            <td style="text-align: right;">${formatPrice(order.total_amount)}</td>
          </tr>
          ${order.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Phí vận chuyển</td>
              <td style="text-align: right;">${formatPrice(order.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${order.discount_amount > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Giảm giá</td>
              <td style="text-align: right; color: red;">-${formatPrice(order.discount_amount)}</td>
            </tr>
          ` : ''}
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db;">${formatPrice(order.final_amount)}</td>
          </tr>
        </tfoot>
      </table>
      
      <div style="margin-top: 20px;">
        <p><strong>Trạng thái:</strong> <span class="badge badge-${['pending','processing','shipping','completed','cancelled'][order.order_status] || 'pending'}">${getStatusLabel(order.order_status)}</span></p>
        <p><strong>Phương thức thanh toán:</strong> ${getPaymentLabel(order.payment?.payment_method)}</p>
        <p><strong>Trạng thái thanh toán:</strong> ${getPaymentStatusLabel(order.payment?.status)}</p>
      </div>
      
      ${order.note ? `<p><strong>Ghi chú:</strong> ${order.note}</p>` : ''}
      
      <div class="footer">
        <p>Cảm ơn bạn đã mua hàng tại BigBag!</p>
        <p>Hotline: 1900 1234 | Email: support@bigbag.vn</p>
        <p style="font-size: 10px; color: #9ca3af;">Hóa đơn được tạo tự động</p>
      </div>
    </body>
    </html>
  `
}

// Pagination
const goToPage = (page) => {
  // Implement pagination logic
}

// Lifecycle
onMounted(() => {
  fetchOrders()
})
</script>

<style scoped>
/* Custom scrollbar cho modal */
.modal-scroll::-webkit-scrollbar {
  width: 6px;
}

.modal-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 8px;
}

.modal-scroll::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 8px;
}

.modal-scroll::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>