<template>
  <div>
    <Head title="Đặt hàng thành công - BigBag" />
    <AppHeader />

    <main class="max-w-3xl mx-auto px-4 py-16 text-center">
      <div class="bg-green-100 text-green-700 p-4 rounded-full w-20 h-20 mx-auto flex items-center justify-center text-4xl">✓</div>
      <h1 class="text-3xl font-bold text-gray-800 mt-4">Đặt hàng thành công!</h1>
      <p class="text-gray-600 mt-2">Cảm ơn bạn đã mua hàng. Chúng tôi sẽ xử lý đơn hàng sớm nhất.</p>
      
      <!-- Hiển thị mã đơn hàng -->
      <div v-if="orderDisplayCode" class="mt-6 bg-gray-50 p-6 rounded-xl border border-gray-200">
        <p class="text-sm text-gray-500">Mã đơn hàng của bạn</p>
        <p class="text-2xl font-bold text-primary">{{ orderDisplayCode }}</p>
      </div>
      
      <!-- Thông tin đơn hàng tóm tắt -->
      <div v-if="order" class="mt-6 text-left bg-white p-6 rounded-xl border border-gray-200 max-w-md mx-auto">
        <h3 class="font-semibold text-gray-800 mb-4">Thông tin đơn hàng</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Khách hàng:</span>
            <span class="font-medium">{{ order.customer_name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Số điện thoại:</span>
            <span class="font-medium">{{ order.customer_phone }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Địa chỉ:</span>
            <span class="font-medium">{{ order.shipping_address }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Tổng tiền:</span>
            <span class="font-medium text-primary">{{ formatPrice(order.final_amount) }}</span>
          </div>
        </div>
      </div>

      <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
        <a :href="route('home')" class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition">
          Tiếp tục mua sắm
        </a>
        <a :href="route('home') + '#orders'" class="inline-block border border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-50 transition">
          Xem đơn hàng
        </a>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  order: Object,
  order_display_code: String,
})

const formatPrice = (val) => (val || 0).toLocaleString('vi-VN') + '₫'
</script>