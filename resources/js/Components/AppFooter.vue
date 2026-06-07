<template>
  <footer class="w-full border-t border-gray-200 font-['Montserrat'] py-12 text-gray-700" style="background-color: #F5F5F5;">
    <div class="grid grid-cols-1 md:grid-cols-4 px-4 md:px-8 max-w-[1440px] mx-auto gap-8">
      
      <!-- Cột 1: Logo & Bản quyền -->
      <div class="space-y-3">
        <Link :href="route('home')" class="font-headline-sm text-xl font-bold block hover:opacity-80 transition-opacity">
          <span class="text-primary">BigBag</span><span class="text-gray-800">.vn</span>
        </Link>
        <p class="font-body-sm text-sm leading-relaxed text-gray-600">Premium Utility Carry Gear - Đồng hành cùng bạn trên mọi nẻo đường.</p>
        <p class="font-body-sm text-xs text-gray-500">© 2025 BigBag. All rights reserved.</p>
      </div>

      <!-- Cột 2: LIÊN KẾT NHANH -->
      <div class="flex flex-col gap-2">
        <h4 class="font-label-lg font-semibold uppercase text-sm mb-2 text-gray-800">LIÊN KẾT NHANH</h4>
        <Link :href="route('category', { slug: 'san-pham' })" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Sản phẩm
        </Link>
        <Link :href="route('wholesale')" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Mua sỉ B2B
        </Link>
        <Link :href="route('promotion')" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Khuyến mãi
        </Link>
        <Link :href="route('customize')" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Tùy chỉnh sản phẩm
        </Link>
      </div>

      <!-- Cột 3: CHÍNH SÁCH -->
      <div class="flex flex-col gap-2">
        <h4 class="font-label-lg font-semibold uppercase text-sm mb-2 text-gray-800">CHÍNH SÁCH</h4>
        <Link :href="route('home') + '#giao-hang'" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Giao hàng & Trả hàng
        </Link>
        <Link :href="route('home') + '#bao-hanh'" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Chính sách bảo hành
        </Link>
        <Link :href="route('home') + '#bao-mat'" class="hover:text-primary transition-all font-body-sm text-sm text-gray-600 hover:underline">
          Chính sách bảo mật
        </Link>
      </div>

      <!-- Cột 4: ĐĂNG KÝ NHẬN TIN -->
      <div class="flex flex-col gap-3">
        <h4 class="font-label-lg font-semibold uppercase text-sm mb-2 text-gray-800">ĐĂNG KÝ NHẬN TIN</h4>
        <div class="flex gap-2">
          <input 
            v-model="subscribeEmail" 
            class="bg-white border border-gray-300 px-3 py-2 text-sm w-full focus:border-primary outline-none rounded-md placeholder:text-gray-400" 
            placeholder="Email của bạn" 
            type="email"
          >
          <button 
            @click="handleSubscribe" 
            class="bg-gray-800 text-white px-4 py-2 flex items-center justify-center hover:bg-gray-700 transition-colors rounded-md"
          >
            <span class="material-symbols-outlined text-base">arrow_forward</span>
          </button>
        </div>
        <p class="text-xs text-gray-500">Nhận ưu đãi và sản phẩm mới nhất</p>
        
        <!-- Icon mạng xã hội -->
        <div class="flex gap-4 mt-2">
          <span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors text-gray-500 text-xl">
            public
          </span>
          <span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors text-gray-500 text-xl">
            share
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const subscribeEmail = ref('')

const handleSubscribe = async () => {
  if (!subscribeEmail.value) {
    alert('Vui lòng nhập email')
    return
  }
  try {
    await axios.post('/api/subscribe', { email: subscribeEmail.value })
    alert('Đăng ký nhận tin thành công!')
    subscribeEmail.value = ''
  } catch (error) {
    console.error('Subscribe error:', error)
    alert('Có lỗi xảy ra, vui lòng thử lại sau')
  }
}
</script>