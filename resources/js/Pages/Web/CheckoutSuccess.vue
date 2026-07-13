<template>
  <div>
    <Head title="Đặt hàng thành công - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-4xl mx-auto px-4 py-12" id="print-area">
      <!-- Success Banner -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
          <span class="material-symbols-outlined text-green-600 text-4xl">check_circle</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h1>
        <p class="text-gray-500 text-lg">Cảm ơn bạn đã mua hàng tại BigBag</p>
        
        <!-- Order Code -->
        <div class="mt-4 inline-block bg-gray-50 px-6 py-3 rounded-xl border border-gray-200">
          <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
          <p class="text-2xl font-bold text-primary">{{ orderDisplayCode }}</p>
        </div>
      </div>

      <!-- Order Details Card -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 px-6 py-4 border-b border-gray-100">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
              <h2 class="text-xl font-semibold text-gray-800">Chi tiết đơn hàng</h2>
            </div>
            <div class="flex items-center gap-2">
              <span 
                v-if="order?.order_code === 'preorder'"
                class="inline-block px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full"
              >
                Pre-order
              </span>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
          <!-- Customer Information - 2 columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                Thông tin người đặt
              </h3>
              <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Họ tên:</span> <span class="font-medium">{{ order?.customer_name || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Email:</span> <span class="font-medium">{{ customerEmail }}</span></p>
                <p><span class="text-gray-500">Số điện thoại:</span> <span class="font-medium">{{ order?.customer_phone || 'N/A' }}</span></p>
              </div>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                Thông tin người nhận
              </h3>
              <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Họ tên:</span> <span class="font-medium">{{ order?.receiver_name || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Số điện thoại:</span> <span class="font-medium">{{ order?.receiver_phone || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Địa chỉ:</span> <span class="font-medium">{{ order?.shipping_address || 'N/A' }}</span></p>
              </div>
            </div>
          </div>

          <!-- Note -->
          <div v-if="order?.note" class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500 flex items-start gap-2">
              <span><span class="font-medium text-gray-600">Ghi chú:</span> {{ order.note }}</span>
            </p>
          </div>

          <!-- Products List -->
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
              Sản phẩm đã đặt
            </h3>
            <div class="border border-gray-100 rounded-xl overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Sản phẩm</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium">Số lượng</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Đơn giá</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="item in orderDetails" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                          <img 
                            :src="item.image || '/images/default-product.jpg'" 
                            :alt="item.name"
                            @error="(e) => { e.target.src = '/images/default-product.jpg' }"
                            class="w-full h-full object-cover"
                          />
                        </div>
                        <div>
                          <p class="font-medium text-gray-800">{{ item.name }}</p>
                          <p v-if="item.color || item.size" class="text-xs text-gray-500">
                            {{ item.color ? `Màu: ${item.color}` : '' }}
                            {{ item.color && item.size ? ' | ' : '' }}
                            {{ item.size ? `Size: ${item.size}` : '' }}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td class="text-center px-4 py-3 font-medium">{{ item.quantity }}</td>
                    <td class="text-right px-4 py-3">{{ formatPrice(item.unit_price) }}</td>
                    <td class="text-right px-4 py-3 font-medium text-primary">{{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Tạm tính</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(orderSummary.subtotal) }}</td>
                  </tr>
                  <tr v-if="orderSummary.shipping_fee > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Phí vận chuyển</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(orderSummary.shipping_fee) }}</td>
                  </tr>
                  <tr v-if="orderSummary.discount_amount > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Giảm giá</td>
                    <td class="text-right px-4 py-3 font-medium text-red-500">-{{ formatPrice(orderSummary.discount_amount) }}</td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-3 font-bold text-gray-800">Tổng cộng</td>
                    <td class="text-right px-4 py-3 font-bold text-2xl text-primary">{{ formatPrice(orderSummary.final_amount) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-xl p-4">
            <div>
              <p class="text-sm text-gray-500">Phương thức thanh toán</p>
              <p class="font-medium text-gray-800 flex items-center gap-2 mt-1">
                <span 
                  class="w-3 h-3 rounded-full inline-block"
                  :class="getPaymentColor(order?.payment_method)"
                ></span>
                {{ getPaymentLabel(order?.payment_method) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Trạng thái thanh toán</p>
              <p class="font-medium flex items-center gap-2 mt-1">
                <span 
                  class="inline-block px-2 py-0.5 text-xs font-bold rounded-full"
                  :class="getPaymentStatusBadge(order?.payment_status || 'pending')"
                >
                  {{ getPaymentStatusLabel(order?.payment_status || 'pending') }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center no-print">
        <a 
          :href="route('home')" 
          class="inline-flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 rounded-xl hover:bg-primary-dark transition-all font-semibold shadow-sm hover:shadow-md"
        >
          Tiếp tục mua sắm
        </a>
        <a 
          :href="route('orders.history')" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-50 transition-all font-semibold"
        >
          Xem lịch sử đơn hàng
        </a>
        <button 
          v-if="order?.id"
          @click="printOrder" 
          class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-200 transition-all font-semibold"
        >
          In đơn hàng
        </button>
      </div>

      <!-- Support Info -->
      <div class="mt-8 text-center text-sm text-gray-500 no-print">
        <p>Cần hỗ trợ? Liên hệ hotline: <a href="tel:19001234" class="text-primary font-medium">1900 1234</a></p>
        <p class="mt-1">Email: <a href="mailto:support@bigbag.vn" class="text-primary font-medium">support@bigbag.vn</a></p>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  order: {
    type: Object,
    default: null
  },
  order_display_code: {
    type: String,
    default: ''
  }
})

// Debug: Log toàn bộ props để kiểm tra
onMounted(() => {
  console.log('📦 Full props:', props)
  console.log('📦 Order object:', props.order)
  console.log('📦 Order display code from props.order_display_code:', props.order_display_code)
  console.log('📦 Order display code from order.order_display_code:', props.order?.order_display_code)
  console.log('📦 Order display_code from order.display_code:', props.order?.display_code)
})

// QUAN TRỌNG: Lấy mã đơn hàng hiển thị
const orderDisplayCode = computed(() => {
  // Ưu tiên 1: Lấy từ props.order_display_code (được truyền từ backend)
  if (props.order_display_code) {
    console.log('✅ Using order_display_code from props:', props.order_display_code)
    return props.order_display_code
  }
  
  // Ưu tiên 2: Lấy từ order.order_display_code
  if (props.order?.order_display_code) {
    console.log('✅ Using order.order_display_code:', props.order.order_display_code)
    return props.order.order_display_code
  }
  
  // Ưu tiên 3: Lấy từ order.display_code (giống OrderHistory)
  if (props.order?.display_code) {
    console.log('✅ Using order.display_code:', props.order.display_code)
    return props.order.display_code
  }
  
  // Nếu không có, hiển thị N/A
  console.warn('⚠️ No display code found!')
  return 'N/A'
})

// Lấy email từ nhiều nguồn khác nhau
const customerEmail = computed(() => {
  if (props.order?.customer_email && props.order.customer_email !== 'N/A') {
    return props.order.customer_email
  }
  if (props.order?.user?.email) {
    return props.order.user.email
  }
  return 'N/A'
})

// Compute order details from order
const orderDetails = computed(() => {
  if (props.order?.details) {
    return props.order.details.map(detail => ({
      ...detail,
      name: detail.productVariant?.product?.name || detail.name || 'Sản phẩm không xác định',
      image: detail.image || detail.productVariant?.product?.image_url?.[0] || '/images/default-product.jpg',
      color: detail.color || detail.productVariant?.color?.name || '',
      size: detail.size || detail.productVariant?.size_name || '',
    }))
  }
  return []
})

// Compute order summary
const orderSummary = computed(() => {
  if (props.order) {
    return {
      subtotal: props.order.total_amount || 0,
      shipping_fee: props.order.shipping_fee || 0,
      discount_amount: props.order.discount_amount || 0,
      final_amount: props.order.final_amount || props.order.total_amount || 0,
    }
  }
  return {
    subtotal: 0,
    shipping_fee: 0,
    discount_amount: 0,
    final_amount: 0
  }
})

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

const getPaymentColor = (method) => {
  const map = {
    cod: 'bg-green-500',
    bank_transfer: 'bg-blue-500',
    ewallet: 'bg-purple-500',
    vnpay: 'bg-red-500',
    momo: 'bg-pink-500',
  }
  return map[method] || 'bg-gray-400'
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

// Hàm in đơn hàng
const printOrder = () => {
  if (!props.order) return
  
  const order = props.order
  const details = orderDetails.value
  const displayCode = orderDisplayCode.value
  
  const printWindow = window.open('', '_blank')
  if (!printWindow) {
    alert('Vui lòng cho phép popup để in đơn hàng')
    return
  }
  
  const detailsHtml = details.map(item => `
    <tr>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">${item.name}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: center;">${item.quantity}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.unit_price)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.subtotal)}</td>
    </tr>
  `).join('')

  const content = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Đơn hàng #${displayCode}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: auto; }
        h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-box h3 { margin: 0 0 10px 0; color: #6b7280; font-size: 14px; text-transform: uppercase; }
        .info-box p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; border: 1px solid #ddd; }
        td { padding: 8px 12px; border: 1px solid #ddd; }
        .total { font-size: 20px; font-weight: bold; color: #1a56db; text-align: right; }
        .footer { margin-top: 40px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
      </style>
    </head>
    <body>
      <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
      <p><strong>Mã đơn hàng:</strong> ${displayCode}</p>
      <p><strong>Ngày đặt:</strong> ${formatDate(order.created_at)}</p>
      
      <div class="info-grid">
        <div class="info-box">
          <h3>Thông tin người đặt</h3>
          <p><strong>Họ tên:</strong> ${order.customer_name}</p>
          <p><strong>Email:</strong> ${customerEmail.value}</p>
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
        <p><strong>Phương thức thanh toán:</strong> ${getPaymentLabel(order.payment_method)}</p>
        <p><strong>Trạng thái thanh toán:</strong> ${getPaymentStatusLabel(order.payment_status)}</p>
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
  
  printWindow.document.write(content)
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
}
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  
  header, footer, .chatbot, .chatbot-toggle {
    display: none !important;
  }
  
  body {
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  #print-area {
    max-width: 100% !important;
    padding: 20px !important;
    margin: 0 !important;
  }
  
  .bg-white {
    background: white !important;
    box-shadow: none !important;
    border: 1px solid #e5e7eb !important;
  }
  
  .shadow-lg, .shadow-sm {
    box-shadow: none !important;
  }
  
  .rounded-2xl, .rounded-xl {
    border-radius: 8px !important;
  }
  
  .text-primary {
    color: #1a56db !important;
  }
  
  .bg-primary {
    background-color: #1a56db !important;
  }
  
  .bg-primary\/5 {
    background-color: #f0f4ff !important;
  }
  
  .bg-primary\/10 {
    background-color: #e8edf8 !important;
  }
  
  .bg-gray-50 {
    background-color: #f9fafb !important;
  }
  
  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }
  
  th, td {
    border: 1px solid #e5e7eb !important;
    padding: 8px 12px !important;
  }
  
  thead {
    background-color: #f9fafb !important;
  }
  
  @page {
    margin: 20mm;
  }
}
</style>