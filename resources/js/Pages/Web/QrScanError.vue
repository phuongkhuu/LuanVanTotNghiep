<template>
  <div>
    <Head title="Lỗi - BigBag" />
    <AppHeader />

    <main class="max-w-2xl mx-auto px-4 py-20">
      <div class="text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-6">
          <span class="material-symbols-outlined text-red-600 text-4xl">error_outline</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-3">Không thể tải thông tin đơn hàng</h1>
        <p class="text-gray-600 mb-8">{{ error || 'Dữ liệu QR không hợp lệ hoặc đã hết hạn' }}</p>
        <div class="flex flex-wrap justify-center gap-4">
          <a 
            :href="route('home')" 
            class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary-dark transition font-semibold"
          >
            <span class="material-symbols-outlined text-sm">home</span>
            Về trang chủ
          </a>
          <a 
            :href="route('orders.history')" 
            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-semibold"
          >
            <span class="material-symbols-outlined text-sm">history</span>
            Lịch sử đơn hàng
          </a>
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

defineProps({
  error: {
    type: String,
    default: 'Có lỗi xảy ra khi xử lý dữ liệu'
  }
})
</script>