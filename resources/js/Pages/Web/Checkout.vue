<template>
  <div>
    <Head title="Thanh toán - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="mt-6 mb-16 px-4 md:px-8 max-w-[1440px] mx-auto">
      <div class="mb-6">
        <h1 class="font-headline-lg text-2xl md:text-3xl border-l-4 pl-4 border-primary text-gray-900">Thanh toán</h1>
        <p class="text-gray-500 text-sm mt-2 ml-5">Vui lòng kiểm tra lại thông tin nhận hàng và phương thức thanh toán.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Shipping & Payment -->
        <section class="lg:col-span-7 space-y-6">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-4">
              <span class="material-symbols-outlined text-primary">local_shipping</span>
              <h2 class="font-semibold text-lg uppercase tracking-wider text-gray-800">Thông tin nhận hàng</h2>
            </div>
            <form class="space-y-4" @submit.prevent="placeOrder">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Họ và tên</label>
                  <input v-model="shippingInfo.fullName" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" placeholder="Nguyễn Văn A" type="text">
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Email</label>
                  <input v-model="shippingInfo.email" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" placeholder="example@bigbag.vn" type="email">
                </div>
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-600">Số điện thoại</label>
                <input v-model="shippingInfo.phone" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" placeholder="090 1234 567" type="tel">
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Tỉnh / Thành</label>
                  <select v-model="shippingInfo.city" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0 text-gray-700">
                    <option>TP. Hồ Chí Minh</option>
                    <option>Hà Nội</option>
                    <option>Đà Nẵng</option>
                  </select>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Quận / Huyện</label>
                  <select v-model="shippingInfo.district" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0 text-gray-700">
                    <option>Quận 1</option>
                    <option>Quận 3</option>
                    <option>Quận 7</option>
                  </select>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-gray-600">Phường / Xã</label>
                  <select v-model="shippingInfo.ward" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0 text-gray-700">
                    <option>Phường Bến Nghé</option>
                    <option>Phường Đa Kao</option>
                  </select>
                </div>
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-600">Địa chỉ chi tiết</label>
                <input v-model="shippingInfo.address" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" placeholder="Số nhà, tên đường..." type="text">
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-gray-600">Ghi chú đơn hàng (Tùy chọn)</label>
                <textarea v-model="shippingInfo.note" class="border border-gray-200 bg-gray-50 p-3 rounded-lg w-full focus:border-primary focus:ring-0" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..." rows="3"></textarea>
              </div>
            </form>
          </div>

          <!-- Phương thức thanh toán - Gọn gàng không hướng dẫn -->
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-4">
              <span class="material-symbols-outlined text-primary">payments</span>
              <h2 class="font-semibold text-lg uppercase tracking-wider text-gray-800">Phương thức thanh toán</h2>
            </div>
            <div class="space-y-4">
              <!-- COD Option -->
              <label 
                class="flex items-center p-4 rounded-lg cursor-pointer transition-all duration-200"
                :class="paymentMethod === 'cod' 
                  ? 'border-2 border-primary bg-amber-50 shadow-sm' 
                  : 'border border-gray-200 bg-white hover:border-primary/50 hover:bg-amber-50/30'"
              >
                <div class="flex items-center gap-4 w-full">
                  <input 
                    v-model="paymentMethod" 
                    value="cod" 
                    type="radio" 
                    class="w-5 h-5 text-primary border-gray-300 focus:ring-0 focus:ring-offset-0 accent-primary"
                  >
                  <div class="flex items-center gap-3">
                    <span 
                      class="material-symbols-outlined text-2xl"
                      :class="paymentMethod === 'cod' ? 'text-primary' : 'text-gray-500'"
                    >local_atm</span>
                    <div>
                      <span class="font-semibold text-gray-800 block">Thanh toán khi nhận hàng (COD)</span>
                      <span class="text-xs text-gray-500">Trả tiền mặt khi nhận hàng</span>
                    </div>
                  </div>
                </div>
              </label>

              <!-- E-Wallet Option -->
              <label 
                class="flex items-center p-4 rounded-lg cursor-pointer transition-all duration-200"
                :class="paymentMethod === 'ewallet' 
                  ? 'border-2 border-primary bg-amber-50 shadow-sm' 
                  : 'border border-gray-200 bg-white hover:border-primary/50 hover:bg-amber-50/30'"
              >
                <div class="flex items-center gap-4 w-full">
                  <input 
                    v-model="paymentMethod" 
                    value="ewallet" 
                    type="radio" 
                    class="w-5 h-5 text-primary border-gray-300 focus:ring-0 focus:ring-offset-0 accent-primary"
                  >
                  <div class="flex items-center gap-3">
                    <span 
                      class="material-symbols-outlined text-2xl"
                      :class="paymentMethod === 'ewallet' ? 'text-primary' : 'text-gray-500'"
                    >account_balance_wallet</span>
                    <div>
                      <span class="font-semibold text-gray-800 block">Ví điện tử (Momo, ZaloPay)</span>
                      <span class="text-xs text-gray-500">Thanh toán qua ví điện tử</span>
                    </div>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </section>

        <!-- Right Column: Order Summary -->
        <aside class="lg:col-span-5">
          <div class="sticky top-28 space-y-6">
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
              <h2 class="font-semibold text-xl mb-6 border-b border-gray-200 pb-4 text-gray-800">Đơn hàng của bạn</h2>
              <div class="space-y-4 mb-6">
                <div v-for="item in cartItems" :key="item.id" class="flex gap-4 items-center">
                  <div class="relative w-20 h-20 bg-gray-100 rounded-lg border border-gray-100 overflow-hidden flex-shrink-0">
                    <img :src="item.image" class="w-full h-full object-cover" :alt="item.name">
                    <span class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-6 h-6 flex items-center justify-center rounded-full font-bold">{{ item.quantity }}</span>
                  </div>
                  <div class="flex-grow">
                    <p class="font-semibold text-sm leading-tight text-gray-800">{{ item.name }}</p>
                    <p class="text-xs text-gray-500">Màu: {{ item.color }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-sm font-bold text-gray-800">{{ formatPrice(item.price) }}</p>
                  </div>
                </div>
              </div>
              <div class="space-y-4 border-t border-gray-200 pt-4 mb-4">
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Tạm tính ({{ cartItems.length }} sản phẩm)</span>
                  <span class="text-gray-800 font-semibold">{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Phí vận chuyển</span>
                  <span class="text-green-600 font-semibold">Miễn phí</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Mã giảm giá</span>
                  <span class="text-primary">- 0₫</span>
                </div>
                <hr class="border-gray-200">
                <div class="flex justify-between items-center py-2">
                  <span class="font-semibold text-gray-800">Tổng cộng</span>
                  <span class="font-display-lg text-2xl text-primary font-bold">{{ formatPrice(total) }}</span>
                </div>
              </div>
              <button @click="placeOrder" class="w-full bg-primary text-white font-semibold py-5 rounded-lg shadow-sm hover:bg-primary-dark transition-all font-bold uppercase tracking-wide">
                Đặt hàng ngay
              </button>
              <p class="text-center text-xs text-gray-500 mt-4">
                Bằng cách đặt hàng, bạn đồng ý với <Link :href="route('home') + '#terms'" class="underline text-primary hover:text-primary-dark">Điều khoản dịch vụ</Link>.
              </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white p-4 rounded-xl border border-gray-100 flex flex-col items-center text-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
                <span class="font-semibold text-xs text-gray-700">Bảo hành trọn đời</span>
              </div>
              <div class="bg-white p-4 rounded-xl border border-gray-100 flex flex-col items-center text-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-primary text-3xl">published_with_changes</span>
                <span class="font-semibold text-xs text-gray-700">90 ngày đổi trả</span>
              </div>
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
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const cartItems = ref([
  { id: 1, name: "Balo Vanguard Pro Series", color: "Carbon Black", price: 2450000, quantity: 1, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB7p7et_MXVQ2VfOJhXvEZCneowWYJKVeB4ZCx0ZMtPvsRsOJz7YYExd_RHLFNAXtqLC3rynIITO1RpjU58P3QjgxKtPTdWCvOD_xFGYo-RjHugOAtLdmKgwvuxnYsBOtDnfQdUYvGDz03-t6UBOMt22LZOe591oYR_IXtXPdWU2b4HAsD7vbv4ZGiP932rtwGXFZqJb3xCBPbHxtF3kOZ2ENUpufXsmb4w89-79vGHY6rPF9EgDxRxI7uKYTV_scaoIO-1Q-Q9XZGD" },
  { id: 2, name: "Túi đựng phụ kiện Tech-Pouch", color: "Slate Grey", price: 450000, quantity: 1, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBvxwP-CST0f4D1efno3EcTOE_sv9WJoyXiOc95-iajaLl8QbB5N3y8Irv-f9ewS1r4PqoWCF7qmP8CaPojqHvTxPe-BM9pxN1WCg80ZALYHy2oCoTH8iIPf2S-yiQjnIpNcJdn_XDnE709J1rOuY5qt-5EgnyjsjkT0s89_9UsY-4-1MiljppHg4V-TZWmznAvkY5-mQJmg3L15E2HWAmW_QmnhTmVWAzBOCBBYvksixa4Ci009877AvhnoWblHP7TwqCwXP8p_Mq_" }
])

const subtotal = computed(() => cartItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0))
const total = computed(() => subtotal.value)

const shippingInfo = ref({
  fullName: 'Nguyễn Văn A',
  email: 'example@bigbag.vn',
  phone: '090 1234 567',
  city: 'TP. Hồ Chí Minh',
  district: 'Quận 1',
  ward: 'Phường Bến Nghé',
  address: '',
  note: ''
})

const paymentMethod = ref('cod')

const formatPrice = (val) => val.toLocaleString('vi-VN') + '₫'

const placeOrder = () => {
  let methodName = ''
  if (paymentMethod.value === 'cod') methodName = 'Thanh toán khi nhận hàng (COD)'
  else methodName = 'Ví điện tử (Momo, ZaloPay)'
  
  alert(`Đặt hàng thành công!\nPhương thức thanh toán: ${methodName}`)
  router.get(route('home'))
}
</script>