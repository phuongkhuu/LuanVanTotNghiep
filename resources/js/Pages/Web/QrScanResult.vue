<template>
  <div>
    <Head :title="`Đơn hàng #${order.display_code} - BigBag`" />
    <AppHeader />

    <main class="max-w-4xl mx-auto px-4 py-8">
      <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Chi tiết đơn hàng</h1>
            <p class="text-sm text-gray-500 mt-1">Mã đơn hàng: {{ order.display_code }}</p>
          </div>
          <div class="flex gap-3">
            <button 
              @click="printOrder"
              class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
            >
              <span class="material-symbols-outlined text-sm">print</span>
              In
            </button>
            <a 
              :href="route('orders.history')" 
              class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition"
            >
              <span class="material-symbols-outlined text-sm">arrow_back</span>
              Về lịch sử đơn hàng
            </a>
          </div>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <div v-else class="space-y-6">
        <!-- Status & Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <p class="text-xs text-gray-500 uppercase">Trạng thái</p>
              <span class="inline-block mt-1 px-3 py-1 text-sm font-bold rounded-full" :class="getStatusBadgeClass(order.order_status)">
                {{ getStatusLabel(order.order_status) }}
              </span>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Ngày đặt</p>
              <p class="font-medium text-gray-700 mt-1">{{ formatDate(order.created_at) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Tổng tiền</p>
              <p class="font-bold text-primary mt-1">{{ formatPrice(order.final_amount) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Loại đơn</p>
              <p class="font-medium text-gray-700 mt-1">{{ getOrderTypeLabel(order.order_code) }}</p>
            </div>
          </div>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">person</span>
              Thông tin người đặt
            </h3>
            <div class="space-y-2 text-sm">
              <p><span class="text-gray-500">Họ tên:</span> {{ order.customer_name }}</p>
              <p v-if="is_owner && order.customer_email"><span class="text-gray-500">Email:</span> {{ order.customer_email }}</p>
              <p><span class="text-gray-500">SĐT:</span> {{ order.customer_phone }}</p>
              <p v-if="!is_owner" class="text-xs text-gray-400 mt-2">
                <span class="material-symbols-outlined text-xs inline-block">lock</span>
                Thông tin liên hệ được ẩn
              </p>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">local_shipping</span>
              Thông tin giao hàng
            </h3>
            <div class="space-y-2 text-sm">
              <p><span class="text-gray-500">Người nhận:</span> {{ order.receiver_name }}</p>
              <p><span class="text-gray-500">SĐT:</span> {{ order.receiver_phone }}</p>
              <p><span class="text-gray-500">Địa chỉ:</span> {{ order.shipping_address }}</p>
              <p v-if="!is_owner" class="text-xs text-gray-400 mt-2">
                <span class="material-symbols-outlined text-xs inline-block">lock</span>
                Thông tin liên hệ được ẩn
              </p>
            </div>
          </div>
        </div>

        <!-- Sản phẩm -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-700 mb-4">Sản phẩm</h3>
          <div class="space-y-4">
            <div 
              v-for="(item, index) in order.details" 
              :key="index"
              class="flex items-center gap-4 py-3 border-b border-gray-100 last:border-0"
            >
              <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                <img 
                  :src="item.image || '/images/default-product.jpg'" 
                  :alt="item.product_name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="flex-1">
                <p class="font-medium text-gray-800">{{ item.product_name }}</p>
                <p class="text-sm text-gray-500">
                  <span v-if="item.color_name">Màu: {{ item.color_name }}</span>
                  <span v-if="item.color_name && item.size_name"> | </span>
                  <span v-if="item.size_name">Size: {{ item.size_name }}</span>
                </p>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="text-sm text-gray-500">x{{ item.quantity }}</p>
                <p class="font-semibold text-gray-800">{{ formatPrice(item.subtotal) }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="space-y-1 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Tạm tính</span>
                <span>{{ formatPrice(order.total_amount) }}</span>
              </div>
              <div v-if="is_owner && order.shipping_fee > 0" class="flex justify-between">
                <span class="text-gray-500">Phí vận chuyển</span>
                <span>{{ formatPrice(order.shipping_fee) }}</span>
              </div>
              <div v-if="is_owner && order.discount_amount > 0" class="flex justify-between">
                <span class="text-gray-500">Giảm giá</span>
                <span class="text-red-500">-{{ formatPrice(order.discount_amount) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                <span>Tổng cộng</span>
                <span class="text-primary">{{ formatPrice(order.final_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Thanh toán -->
        <div v-if="is_owner && order.payment" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">payment</span>
            Thông tin thanh toán
          </h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500">Phương thức</p>
              <p class="font-medium">{{ getPaymentLabel(order.payment.payment_method) }}</p>
            </div>
            <div>
              <p class="text-gray-500">Trạng thái</p>
              <span class="inline-block px-3 py-1 text-xs font-bold rounded-full" :class="getPaymentStatusBadge(order.payment.status)">
                {{ getPaymentStatusLabel(order.payment.status) }}
              </span>
            </div>
            <div v-if="order.payment.transaction_code" class="col-span-2">
              <p class="text-gray-500">Mã giao dịch</p>
              <p class="font-medium">{{ order.payment.transaction_code }}</p>
            </div>
          </div>
        </div>

        <!-- Ghi chú -->
        <div v-if="is_owner && order.note" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-700 mb-2">Ghi chú</h3>
          <p class="text-sm text-gray-600">{{ order.note }}</p>
        </div>

        <div class="text-center text-sm text-gray-500 border-t border-gray-200 pt-6">
          <p>Cảm ơn bạn đã mua hàng tại BigBag!</p>
          <p class="text-xs text-gray-400 mt-1">Hotline: 1900 1234 | Email: support@bigbag.vn</p>
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  is_owner: {
    type: Boolean,
    default: false
  },
  is_authenticated: {
    type: Boolean,
    default: false
  }
})

const loading = ref(false)

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

const getOrderTypeLabel = (type) => {
  const map = {
    retail: 'Bán lẻ',
    wholesale: 'Bán sỉ',
    preorder: 'Pre-order',
  }
  return map[type] || type || 'Không xác định'
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

const printOrder = () => {
  window.print()
}
</script>

<style scoped>
@media print {
  header, footer, .no-print {
    display: none !important;
  }
  body {
    background: white !important;
  }
  .bg-white {
    box-shadow: none !important;
    border: 1px solid #e5e7eb !important;
  }
}
</style>