<template>
  <div>
    <Head title="Giỏ hàng - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-12">
      <h1 class="font-headline-lg text-2xl md:text-3xl mb-6 border-l-4 pl-4 border-primary text-gray-900">Giỏ hàng của bạn</h1>
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Cart Items -->
        <div class="lg:col-span-8 space-y-4">
          <div class="hidden md:grid grid-cols-12 gap-4 pb-4 border-b border-gray-200 text-gray-500 font-semibold text-xs uppercase tracking-wider">
            <div class="col-span-6">Sản phẩm</div>
            <div class="col-span-2 text-center">Giá</div>
            <div class="col-span-2 text-center">Số lượng</div>
            <div class="col-span-2 text-right">Tạm tính</div>
          </div>

          <div v-for="(item, idx) in cartItems" :key="item.id" class="grid grid-cols-1 md:grid-cols-12 gap-4 py-6 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="col-span-12 md:col-span-6 flex gap-4">
              <div class="w-24 h-32 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
                <img class="w-full h-full object-cover" :src="item.image" :alt="item.name">
              </div>
              <div>
                <Link :href="route('product.detail', { id: item.id })" class="font-semibold text-gray-800 hover:text-primary">
                  {{ item.name }}
                </Link>
                <p class="text-sm text-gray-500 mt-1">Màu: {{ item.color }} | Size: {{ item.size }}</p>
                <button @click="removeItem(idx)" class="text-primary text-sm flex items-center mt-2 hover:text-primary-dark">
                  <span class="material-symbols-outlined text-[18px] mr-1">delete</span> Xóa
                </button>
              </div>
            </div>
            <div class="col-span-4 md:col-span-2 text-left md:text-center">
              <span class="md:hidden font-semibold text-gray-500 block text-sm">Giá:</span>
              <span class="font-semibold text-gray-800">{{ formatPrice(item.price) }}</span>
            </div>
            <div class="col-span-4 md:col-span-2 flex justify-center">
              <div class="flex items-center border border-gray-200 rounded-full px-2 py-1">
                <button @click="updateQuantity(idx, -1)" class="w-8 h-8 flex items-center justify-center text-gray-600">
                  <span class="material-symbols-outlined text-[20px]">remove</span>
                </button>
                <span class="px-4 font-bold text-gray-800">{{ item.quantity }}</span>
                <button @click="updateQuantity(idx, 1)" class="w-8 h-8 flex items-center justify-center text-gray-600">
                  <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
              </div>
            </div>
            <div class="col-span-4 md:col-span-2 text-right">
              <span class="md:hidden font-semibold text-gray-500 block text-sm">Tạm tính:</span>
              <span class="font-semibold text-primary font-bold">{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>

          <div class="flex flex-col md:flex-row gap-4 mt-6 items-center">
            <div class="w-full md:w-auto flex-grow">
              <input v-model="couponCode" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-3 text-gray-700 focus:border-primary focus:ring-0" placeholder="Nhập mã giảm giá..." type="text">
            </div>
            <button @click="applyCoupon" class="w-full md:w-auto bg-primary text-white font-semibold py-3 px-8 rounded-lg hover:bg-primary-dark transition-colors">
              Áp dụng
            </button>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-4 sticky top-24">
          <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h2 class="font-semibold text-xl mb-6 border-b border-gray-200 pb-4 text-gray-800">Tóm tắt đơn hàng</h2>
            <div class="space-y-4">
              <div class="flex justify-between text-gray-600">
                <span>Tạm tính ({{ cartItems.length }} sản phẩm)</span>
                <span class="font-semibold text-gray-800">{{ formatPrice(subtotal) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Phí vận chuyển</span>
                <span class="font-semibold text-green-600">Miễn phí</span>
              </div>
              <div v-if="discountAmount > 0" class="flex justify-between text-gray-600">
                <span>Mã giảm giá</span>
                <span class="text-primary">- {{ formatPrice(discountAmount) }}</span>
              </div>
              <hr class="border-gray-200">
              <div class="flex justify-between items-center py-2">
                <span class="font-semibold text-gray-800">Tổng cộng</span>
                <span class="font-display-lg text-2xl text-primary font-bold">{{ formatPrice(total) }}</span>
              </div>
            </div>
            <Link :href="route('checkout')" class="w-full bg-primary text-white py-5 rounded-lg mt-6 hover:bg-primary-dark transition-colors uppercase font-bold text-center block">
              Tiến hành thanh toán
            </Link>
            <div class="mt-6 space-y-3">
              <div class="flex items-center gap-2 text-gray-500 text-sm">
                <span class="material-symbols-outlined text-green-600">verified_user</span> Thanh toán an toàn 100%
              </div>
              <div class="flex items-center gap-2 text-gray-500 text-sm">
                <span class="material-symbols-outlined text-green-600">local_shipping</span> Giao hàng nhanh toàn quốc (2-4 ngày)
              </div>
              <div class="flex items-center gap-2 text-gray-500 text-sm">
                <span class="material-symbols-outlined text-green-600">assignment_return</span> Đổi trả miễn phí trong 7 ngày
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Suggested Products -->
      <section class="mt-16">
        <div class="flex justify-between items-end mb-6">
          <div>
            <span class="text-primary font-semibold text-xs uppercase tracking-widest">Phối hợp hoàn hảo</span>
            <h2 class="font-headline-lg text-2xl font-bold text-gray-800">Có thể bạn sẽ thích</h2>
          </div>
          <Link :href="route('category', { slug: 'tat-ca' })" class="text-gray-500 hover:text-primary flex items-center gap-1 text-sm">
            Xem tất cả <span class="material-symbols-outlined text-sm">chevron_right</span>
          </Link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="product in suggestedProducts" :key="product.id" class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all">
            <Link :href="route('product.detail', { id: product.id })" class="block">
              <div class="aspect-[4/5] bg-gray-100 overflow-hidden relative">
                <img class="w-full h-full object-cover group-hover:scale-110 transition-transform" :src="product.image" :alt="product.name">
                <span v-if="product.badge" class="absolute top-4 left-4 bg-primary text-white px-3 py-1 rounded-full text-xs">{{ product.badge }}</span>
              </div>
              <div class="p-4">
                <h4 class="font-semibold text-base line-clamp-1 text-gray-800">{{ product.name }}</h4>
                <p class="text-gray-500 text-sm mb-2">{{ product.category }}</p>
                <div class="flex justify-between items-center">
                  <span class="font-bold text-primary text-lg">{{ formatPrice(product.price) }}</span>
                  <button @click="addToCartProduct(product)" class="bg-gray-100 p-2 rounded-full hover:bg-primary/10 transition-colors">
                    <span class="material-symbols-outlined text-gray-600">add_shopping_cart</span>
                  </button>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </section>
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
  { id: 1, name: "Balo Công Sở BigBag Pro v2", color: "Xám Than", size: "L", price: 1450000, quantity: 1, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAD6L9OcEUTVWCJgZLA27C1yucsUoMiG4JGAl6IwgTUvjQyMj9AKQh2W8VicuT_uDzKJw3PZXo0Y3Ej4mQib4HwdRnY0LAWt3KEmjUzXKZ_c7IdWXpRd07xN9HDQuIw49fPe_3DxamU26Em0QaXLhYXssey5TKc_EkPC5KoCS8GREZTb6AfvlciZwjEG02UZubecIY-h3l9LqK2n4-Rk1GN74fOlm3fB9B8R5krVUxMvyXDqWERB5A79__Ktq98waxauoM5G7xd3Eub" },
  { id: 2, name: "Túi Messenger Da Thật Elite", color: "Nâu Chocolate", size: "M", price: 2200000, quantity: 1, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAMDbCZL7hrYPadl_jDsmyQjwUWuf84hu8upz_vP1SyN0rxjf8UlS_nJGEhXGW8yAH87Sfu5bfKZiyv7QMZEFW4CBLCOSj8PwiAIclS9CGuMPP2y2BEEtM-0i5ySZ6sZ9R9EmvyIdoXD54Oa8hb_skbIf7FqUVzy1FGY7zJvb0GK3_zqgoglhu09ylrIx5FL-eqvM0KKA6NY4UM8rea8od2PtWRwefZvE4NwRcK-ZwHJdPN0QSLzUajutM1uLOq3UQil-NwstVrg6El" }
])

const couponCode = ref('')
const discountAmount = ref(0)

const subtotal = computed(() => cartItems.value.reduce((s, i) => s + i.price * i.quantity, 0))
const total = computed(() => subtotal.value - discountAmount.value)

const updateQuantity = (idx, delta) => {
  const newQty = cartItems.value[idx].quantity + delta
  if (newQty >= 1) cartItems.value[idx].quantity = newQty
}
const removeItem = (idx) => cartItems.value.splice(idx, 1)
const applyCoupon = () => {
  if (couponCode.value === 'SALE10') discountAmount.value = subtotal.value * 0.1
  else discountAmount.value = 0
}
const formatPrice = (val) => val.toLocaleString('vi-VN') + '₫'

const suggestedProducts = ref([
  { id: 3, name: "Túi Tote Canvas Modern", category: "Phụ kiện công sở", price: 850000, badge: "-10%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB9nuQnQvGBE7sY3P0hoxV7XX9aEeWWlXeLxZXHaO200msr2mNJAMFhVgdQrBRxbCs4h8XLGhofTI6iR3rsOx0WzYYKX3GWPqKBwMSt1GTVW_9pdYM_o9z-4WvRDy8ucUHgBnYXx07ihkJPtoqB5j3suWiXTvp1jqNcHXrbedVcQ4g8KNkbDOoHo8QuDqi2g7WFQlTH_RxyjBdWjuc_4RS85X9GeKHS-OdqHMYGk4vr1Y5g8GjgHnUl4rg3_va_LOU48mDVZ05jYz-F" },
  { id: 4, name: "Balo Commuter X-Shield", category: "Chống nước tuyệt đối", price: 1950000, badge: "New", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuApv4oUo-7Jbdz4aTiDcyN2XaEh1-g-bSJlqGDxMHnup3OxqE_gl-mCYLV8Wa8PLdRf1AfOMRQ5gwGCdN4OtWaqcYuI0iV5Yyw11nd4n9t-0Ww_U3mbLCnD8ShDTxHX7CNj1zAVPIyqy6aJxUxEajVOqBGLONC8VuV6obJzvo1fBzgRUq8DiSbrkmgThldHB8rmz4_keWqZiGS-JJHD1hUghpUo_g_1Qn_xg-38YT8-ywdQUH1FBsD4lEqV0keOOB9UDkoBfzD4z6iC" },
  { id: 5, name: "Ví Da Mini Slim Fit", category: "Da bò thật 100%", price: 420000, badge: null, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuADaTT9wHiB4RkN8NP_Oq4u3j5gpAW167KPKTVl5AKSsVyKyZgxy-aGOAaDDHugAYPzs3N0pZqI9n_N_WNsaTYHN7wzyGrxVsHvl9bRTEsOa64RN8Kf70DgevQ5o4pZe1_0R8FebpLajvuKs4GwTtKh5JOBKz_8pWTtuvmWrQZ5Aht1cVqbBbyQNYvsM5gmD6Te8zkQfHW8djCsopztkw6Vp2fI_Tf5sH0Z3gEuR1o_KfuoB-PkfWUiKQke7RDDfZ6teX5beUQmYlhi" },
  { id: 6, name: "Túi Phụ Kiện Travel Pro", category: "Tiện ích du lịch", price: 350000, badge: null, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDVFTuGwrRVt-bcWXV7oDBhXAOoG5mKZbgzkMCreWded8E9ZvnD3nP8PCSIl4-bBe8Hx54K7MjZeXhEVo9bEtHXwnWtiBSfb33QO8W1WBJE-5-tv047NQzZf7kH6hN1fRol5A9gwh2DO5Mcy64mK8_8cJWrsABtI2JZdQkz9m3a0eBmebOR8VIJsHFrOiRnUQH1oHqf0FUFP2S4_EZgXMXbmArZLiPCP9Y7QSMrT6FSp-mmQMWjYg37iiwSSuVjU-ih_AqF8DWdrOkx" }
])

const addToCartProduct = (product) => {
  router.get(route('product.detail', { id: product.id }))
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>