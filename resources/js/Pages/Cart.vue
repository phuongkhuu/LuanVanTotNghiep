<template>
  <div>
    <Head title="Giỏ hàng - BigBag Premium Utility Carry Gear" />

    <!-- Header (giống các trang khác) -->
    <header class="w-full top-0 sticky z-[100] glass-nav border-b border-outline-variant shadow-sm bg-background">
      <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-margin-desktop py-4">
        <div class="flex items-center gap-8">
          <Link class="font-headline-lg text-headline-md tracking-tighter text-primary" href="/">BigBag.vn</Link>
          <div class="hidden md:flex items-center gap-6">
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Balo</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Laptop</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Du Lịch</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Thời Trang</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Cao Cấp</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Thể Thao</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Đa Năng</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Công Nghệ</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Chống Trộm</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Đi Học</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Giáo Viên</Link>
                </div>
              </div>
            </div>
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Cặp - Túi</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp Xách</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Du Lịch</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Đeo Chéo</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp Túi Macbook</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi iPad</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Gym</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Thể Thao</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp Giáo Viên</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Cặp</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Túi Sinh Viên</Link>
                </div>
              </div>
            </div>
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Brands</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Tomtoc</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Mark Ryden</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Rokin</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Sakos</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Balo Mikkor</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Hier</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Bopai</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Bange</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Kmore</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Kingbag</Link>
                  <Link class="text-body-sm text-on-surface-variant hover:text-primary" href="#">Simple Carry</Link>
                </div>
              </div>
            </div>
            <Link :href="route('wholesale')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Mua sỉ</Link>
            <Link :href="route('promotion')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Khuyến mãi</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">Giới thiệu</Link>
            <Link class="font-label-md text-label-md text-primary border-b-2 border-primary pb-1" href="#">New Arrivals</Link>
          </div>
        </div>
        <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
          <div class="relative w-full">
            <input class="w-full bg-surface-container border border-outline-variant rounded-full py-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" placeholder="Tìm kiếm sản phẩm..." type="text">
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">person</span>
          </button>
          <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartItems.length }}</span>
          </Link>
        </div>
      </nav>
    </header>

    <main class="max-w-container-max-width mx-auto px-margin-desktop py-section-gap">
      <h1 class="font-headline-lg text-headline-lg mb-stack-lg border-l-4 pl-4 border-primary">Giỏ hàng của bạn</h1>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
        <div class="lg:col-span-8 space-y-stack-md">
          <div class="hidden md:grid grid-cols-12 gap-4 pb-stack-sm border-b border-outline-variant text-secondary font-label-lg uppercase tracking-wider">
            <div class="col-span-6">Sản phẩm</div>
            <div class="col-span-2 text-center">Giá</div>
            <div class="col-span-2 text-center">Số lượng</div>
            <div class="col-span-2 text-right">Tạm tính</div>
          </div>
          <div v-for="(item, idx) in cartItems" :key="item.id" class="grid grid-cols-1 md:grid-cols-12 gap-4 py-stack-lg items-center bg-surface-container-lowest p-stack-md rounded-lg shadow-sm border border-gray-warm">
            <div class="col-span-12 md:col-span-6 flex gap-stack-md">
              <div class="w-24 h-32 flex-shrink-0 bg-surface-container rounded-md overflow-hidden">
                <img class="w-full h-full object-cover" :src="item.image" :alt="item.name">
              </div>
              <div>
                <h3 class="font-headline-sm text-headline-sm">{{ item.name }}</h3>
                <p class="font-body-sm text-secondary mt-1">Màu: {{ item.color }} | Size: {{ item.size }}</p>
                <button @click="removeItem(idx)" class="text-primary font-label-md flex items-center mt-2">
                  <span class="material-symbols-outlined text-[18px] mr-1">delete</span> Xóa
                </button>
              </div>
            </div>
            <div class="col-span-4 md:col-span-2 text-left md:text-center">
              <span class="md:hidden font-label-md text-secondary block">Giá:</span>
              <span class="font-headline-sm">{{ formatPrice(item.price) }}</span>
            </div>
            <div class="col-span-4 md:col-span-2 flex justify-center">
              <div class="flex items-center border border-outline rounded-full px-2 py-1">
                <button @click="updateQuantity(idx, -1)" class="w-8 h-8 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">remove</span></button>
                <span class="px-4 font-body-md font-bold">{{ item.quantity }}</span>
                <button @click="updateQuantity(idx, 1)" class="w-8 h-8 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">add</span></button>
              </div>
            </div>
            <div class="col-span-4 md:col-span-2 text-right">
              <span class="md:hidden font-label-md text-secondary block">Tạm tính:</span>
              <span class="font-headline-sm text-primary font-bold">{{ formatPrice(item.price * item.quantity) }}</span>
            </div>
          </div>
          <div class="flex flex-col md:flex-row gap-4 mt-stack-lg items-center">
            <div class="w-full md:w-auto flex-grow">
              <input v-model="couponCode" class="w-full border border-outline-variant bg-surface-container-low rounded-lg px-4 py-3" placeholder="Nhập mã giảm giá..." type="text">
            </div>
            <button @click="applyCoupon" class="w-full md:w-auto bg-primary text-white font-label-lg py-3 px-8 rounded-lg uppercase">Áp dụng</button>
          </div>
        </div>
        <div class="lg:col-span-4 sticky top-24">
          <div class="bg-surface-container rounded-xl p-stack-lg border border-outline-variant shadow-lg">
            <h2 class="font-headline-md text-headline-md mb-stack-lg border-b border-outline-variant pb-stack-sm">Tóm tắt đơn hàng</h2>
            <div class="space-y-4">
              <div class="flex justify-between"><span>Tạm tính ({{ cartItems.length }} sản phẩm)</span><span class="font-semibold">{{ formatPrice(subtotal) }}</span></div>
              <div class="flex justify-between"><span>Phí vận chuyển</span><span class="font-semibold">Miễn phí</span></div>
              <div v-if="discountAmount > 0" class="flex justify-between"><span>Mã giảm giá</span><span class="text-primary">- {{ formatPrice(discountAmount) }}</span></div>
              <hr>
              <div class="flex justify-between items-center py-2"><span class="font-headline-sm">Tổng cộng</span><span class="font-display-lg text-[28px] text-primary font-bold">{{ formatPrice(total) }}</span></div>
            </div>
            <button class="w-full bg-primary text-white py-5 rounded-lg mt-stack-lg hover:scale-[1.02] transition-transform uppercase font-bold" @click="$inertia.visit(route('checkout'))">Tiến hành thanh toán</button>
            <div class="mt-stack-lg space-y-3">
              <div class="flex items-center gap-2 text-secondary"><span class="material-symbols-outlined text-success-green">verified_user</span> Thanh toán an toàn 100%</div>
              <div class="flex items-center gap-2 text-secondary"><span class="material-symbols-outlined text-success-green">local_shipping</span> Giao hàng nhanh toàn quốc (2-4 ngày)</div>
              <div class="flex items-center gap-2 text-secondary"><span class="material-symbols-outlined text-success-green">assignment_return</span> Đổi trả miễn phí trong 7 ngày</div>
            </div>
          </div>
        </div>
      </div>
      <section class="mt-section-gap">
        <div class="flex justify-between items-end mb-stack-lg">
          <div><span class="text-primary font-label-lg uppercase tracking-widest">Phối hợp hoàn hảo</span><h2 class="font-headline-lg">Có thể bạn sẽ thích</h2></div>
          <a class="text-secondary hover:text-primary flex items-center gap-1 font-label-lg" href="#">Xem tất cả <span class="material-symbols-outlined">chevron_right</span></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <div v-for="product in suggestedProducts" :key="product.id" class="group bg-surface-container-lowest border border-gray-warm rounded-lg overflow-hidden">
            <div class="aspect-[4/5] bg-surface-container overflow-hidden relative">
              <img class="w-full h-full object-cover group-hover:scale-110 transition-transform" :src="product.image" :alt="product.name">
              <span v-if="product.badge" class="absolute top-4 left-4 bg-primary text-white px-3 py-1 rounded-full text-xs">{{ product.badge }}</span>
            </div>
            <div class="p-stack-md">
              <h4 class="font-headline-sm text-[16px] line-clamp-1">{{ product.name }}</h4>
              <p class="text-secondary text-sm mb-2">{{ product.category }}</p>
              <div class="flex justify-between items-center">
                <span class="font-headline-md text-primary text-[18px]">{{ formatPrice(product.price) }}</span>
                <button @click="addToCart(product)" class="bg-surface-container-highest p-2 rounded-full hover:bg-primary/10"><span class="material-symbols-outlined">add_shopping_cart</span></button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Chatbot Button -->
    <div class="fixed bottom-8 right-8 z-50">
      <button class="w-16 h-16 rounded-full shadow-lg hover:scale-110 transition-transform flex items-center justify-center relative group bg-primary text-white hover:bg-[#e66000]">
        <span class="material-symbols-outlined text-3xl">chat</span>
        <div class="absolute bottom-full right-0 mb-4 bg-white p-4 rounded-xl shadow-xl w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all pointer-events-none">
          <p class="font-label-lg text-label-lg text-on-surface mb-1">Xin chào!</p>
          <p class="font-body-sm text-body-sm text-on-surface-variant">Chúng tôi có thể giúp gì cho bạn hôm nay?</p>
          <div class="absolute bottom-[-8px] right-6 w-4 h-4 bg-white rotate-45"></div>
        </div>
      </button>
    </div>

    <!-- Footer -->
    <footer class="w-full border-t border-outline font-['Montserrat'] py-16 text-[#333333]" style="background-color: #F5F5F5;">
      <div class="grid grid-cols-1 md:grid-cols-4 px-margin-desktop max-w-[1440px] mx-auto gap-12 items-start">
        <div class="space-y-4"><div class="font-headline-sm text-headline-sm font-bold text-[#333333]">BigBag.vn</div><p class="font-body-sm leading-relaxed text-[#333333]">© 2025 BigBag. Premium Utility Carry Gear.</p></div>
        <div class="flex flex-col gap-2"><h4 class="font-label-lg uppercase mb-2 text-[#333333]">VỀ BIGBAG</h4><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Về chúng tôi</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Phát triển bền vững</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Liên hệ</Link></div>
        <div class="flex flex-col gap-2"><h4 class="font-label-lg uppercase mb-2 text-[#333333]">HỖ TRỢ KHÁCH HÀNG</h4><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Giao hàng & Trả hàng</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo hành</Link><Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo mật</Link></div>
        <div class="flex flex-col gap-4"><h4 class="font-label-lg uppercase mb-2 text-[#333333]">ĐĂNG KÝ NHẬN TIN</h4><div class="flex gap-2"><input class="bg-white border border-[#333333] px-3 py-2 text-body-sm w-full focus:border-primary outline-none placeholder:text-[#333333]/50 rounded-none" placeholder="Email của bạn" type="email"><button class="bg-transparent border border-[#333333] text-[#333333] px-4 py-2 flex items-center justify-center hover:bg-primary/10 rounded-none transition-colors"><span class="material-symbols-outlined">arrow_forward</span></button></div><div class="flex gap-4"><span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors">public</span><span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors">share</span></div></div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

// Cart data
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

// Suggested products
const suggestedProducts = ref([
  { id: 3, name: "Túi Tote Canvas Modern", category: "Phụ kiện công sở", price: 850000, badge: "-10%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB9nuQnQvGBE7sY3P0hoxV7XX9aEeWWlXeLxZXHaO200msr2mNJAMFhVgdQrBRxbCs4h8XLGhofTI6iR3rsOx0WzYYKX3GWPqKBwMSt1GTVW_9pdYM_o9z-4WvRDy8ucUHgBnYXx07ihkJPtoqB5j3suWiXTvp1jqNcHXrbedVcQ4g8KNkbDOoHo8QuDqi2g7WFQlTH_RxyjBdWjuc_4RS85X9GeKHS-OdqHMYGk4vr1Y5g8GjgHnUl4rg3_va_LOU48mDVZ05jYz-F" },
  { id: 4, name: "Balo Commuter X-Shield", category: "Chống nước tuyệt đối", price: 1950000, badge: "New", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuApv4oUo-7Jbdz4aTiDcyN2XaEh1-g-bSJlqGDxMHnup3OxqE_gl-mCYLV8Wa8PLdRf1AfOMRQ5gwGCdN4OtWaqcYuI0iV5Yyw11nd4n9t-0Ww_U3mbLCnD8ShDTxHX7CNj1zAVPIyqy6aJxUxEajVOqBGLONC8VuV6obJzvo1fBzgRUq8DiSbrkmgThldHB8rmz4_keWqZiGS-JJHD1hUghpUo_g_1Qn_xg-38YT8-ywdQUH1FBsD4lEqV0keOOB9UDkoBfzD4z6iC" },
  { id: 5, name: "Ví Da Mini Slim Fit", category: "Da bò thật 100%", price: 420000, badge: null, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuADaTT9wHiB4RkN8NP_Oq4u3j5gpAW167KPKTVl5AKSsVyKyZgxy-aGOAaDDHugAYPzs3N0pZqI9n_N_WNsaTYHN7wzyGrxVsHvl9bRTEsOa64RN8Kf70DgevQ5o4pZe1_0R8FebpLajvuKs4GwTtKh5JOBKz_8pWTtuvmWrQZ5Aht1cVqbBbyQNYvsM5gmD6Te8zkQfHW8djCsopztkw6Vp2fI_Tf5sH0Z3gEuR1o_KfuoB-PkfWUiKQke7RDDfZ6teX5beUQmYlhi" },
  { id: 6, name: "Túi Phụ Kiện Travel Pro", category: "Tiện ích du lịch", price: 350000, badge: null, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDVFTuGwrRVt-bcWXV7oDBhXAOoG5mKZbgzkMCreWded8E9ZvnD3nP8PCSIl4-bBe8Hx54K7MjZeXhEVo9bEtHXwnWtiBSfb33QO8W1WBJE-5-tv047NQzZf7kH6hN1fRol5A9gwh2DO5Mcy64mK8_8cJWrsABtI2JZdQkz9m3a0eBmebOR8VIJsHFrOiRnUQH1oHqf0FUFP2S4_EZgXMXbmArZLiPCP9Y7QSMrT6FSp-mmQMWjYg37iiwSSuVjU-ih_AqF8DWdrOkx" }
])

const addToCart = (product) => {
  const existing = cartItems.value.find(i => i.name === product.name)
  if (existing) existing.quantity++
  else cartItems.value.push({ ...product, quantity: 1, color: "Mặc định", size: "M", price: product.price })
}
</script>

<style scoped>
/* Các style cần thiết */
.glass-nav {
  backdrop-filter: blur(12px);
  background-color: rgba(251, 249, 245, 0.85);
}
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}
</style>