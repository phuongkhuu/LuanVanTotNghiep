<template>
  <div>
    <Head title="Thanh toán - BigBag Premium Utility Carry Gear" />

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
          <Link :href="route('login')" class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">person</span>
          </Link>
          <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartItems.length }}</span>
          </Link>
        </div>
      </nav>
    </header>

    <main class="mt-stack-lg mb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto">
      <div class="mb-stack-lg">
        <h1 class="font-headline-lg text-headline-lg border-l-4 pl-4 border-primary">Thanh toán</h1>
        <p class="text-secondary font-body-md mt-2 ml-5">Vui lòng kiểm tra lại thông tin nhận hàng và phương thức thanh toán.</p>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter items-start">
        <!-- Left Column: Shipping & Payment -->
        <section class="lg:col-span-7 space-y-stack-lg">
          <div class="bg-surface-container-lowest p-stack-lg rounded-xl shadow-sm border border-gray-warm">
            <div class="flex items-center gap-2 mb-stack-lg border-b border-gray-warm pb-4">
              <span class="material-symbols-outlined text-primary">local_shipping</span>
              <h2 class="font-headline-sm text-headline-sm uppercase tracking-wider">Thông tin nhận hàng</h2>
            </div>
            <form class="space-y-stack-md" @submit.prevent="placeOrder">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
                <div class="flex flex-col gap-1">
                  <label class="text-label-md font-label-md text-secondary">Họ và tên</label>
                  <input v-model="shippingInfo.fullName" class="border border-outline-variant bg-white p-3 rounded-lg w-full" placeholder="Nguyễn Văn A" type="text">
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-label-md font-label-md text-secondary">Email</label>
                  <input v-model="shippingInfo.email" class="border border-outline-variant bg-white p-3 rounded-lg w-full" placeholder="example@bigbag.vn" type="email">
                </div>
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-label-md font-label-md text-secondary">Số điện thoại</label>
                <input v-model="shippingInfo.phone" class="border border-outline-variant bg-white p-3 rounded-lg w-full" placeholder="090 1234 567" type="tel">
              </div>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-stack-md">
                <div class="flex flex-col gap-1">
                  <label class="text-label-md font-label-md text-secondary">Tỉnh / Thành</label>
                  <select v-model="shippingInfo.city" class="border border-outline-variant bg-white p-3 rounded-lg w-full">
                    <option>TP. Hồ Chí Minh</option>
                    <option>Hà Nội</option>
                    <option>Đà Nẵng</option>
                  </select>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-label-md font-label-md text-secondary">Quận / Huyện</label>
                  <select v-model="shippingInfo.district" class="border border-outline-variant bg-white p-3 rounded-lg w-full">
                    <option>Quận 1</option>
                    <option>Quận 3</option>
                    <option>Quận 7</option>
                  </select>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-label-md font-label-md text-secondary">Phường / Xã</label>
                  <select v-model="shippingInfo.ward" class="border border-outline-variant bg-white p-3 rounded-lg w-full">
                    <option>Phường Bến Nghé</option>
                    <option>Phường Đa Kao</option>
                  </select>
                </div>
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-label-md font-label-md text-secondary">Địa chỉ chi tiết</label>
                <input v-model="shippingInfo.address" class="border border-outline-variant bg-white p-3 rounded-lg w-full" placeholder="Số nhà, tên đường..." type="text">
              </div>
              <div class="flex flex-col gap-1">
                <label class="text-label-md font-label-md text-secondary">Ghi chú đơn hàng (Tùy chọn)</label>
                <textarea v-model="shippingInfo.note" class="border border-outline-variant bg-white p-3 rounded-lg w-full" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..." rows="3"></textarea>
              </div>
            </form>
          </div>
          <div class="bg-surface-container-lowest p-stack-lg rounded-xl shadow-sm border border-gray-warm">
            <div class="flex items-center gap-2 mb-stack-lg border-b border-gray-warm pb-4">
              <span class="material-symbols-outlined text-primary">payments</span>
              <h2 class="font-headline-sm text-headline-sm uppercase tracking-wider">Phương thức thanh toán</h2>
            </div>
            <div class="space-y-stack-md">
              <label class="flex items-center justify-between p-4 border border-primary bg-orange-50 rounded-lg cursor-pointer">
                <div class="flex items-center gap-4">
                  <input v-model="paymentMethod" value="cod" type="radio" class="w-5 h-5 text-primary border-outline-variant focus:ring-0">
                  <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">local_atm</span>
                    <span class="font-body-md font-semibold">Thanh toán khi nhận hàng (COD)</span>
                  </div>
                </div>
              </label>
              <label class="flex items-center justify-between p-4 border border-outline-variant rounded-lg cursor-pointer hover:bg-orange-50 transition-colors">
                <div class="flex items-center gap-4">
                  <input v-model="paymentMethod" value="ewallet" type="radio" class="w-5 h-5 text-primary border-outline-variant focus:ring-0">
                  <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-secondary">account_balance_wallet</span>
                    <span class="font-body-md font-semibold">Ví điện tử (Momo, ZaloPay)</span>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </section>

        <!-- Right Column: Order Summary -->
        <aside class="lg:col-span-5">
          <div class="sticky top-28 space-y-stack-lg">
            <div class="bg-surface-container rounded-xl p-stack-lg border border-outline-variant shadow-lg">
              <h2 class="font-headline-md text-headline-md mb-stack-lg border-b border-outline-variant pb-stack-sm">Đơn hàng của bạn</h2>
              <div class="space-y-stack-md mb-stack-lg">
                <div v-for="item in cartItems" :key="item.id" class="flex gap-4 items-center">
                  <div class="relative w-20 h-20 bg-white rounded-lg border border-gray-warm overflow-hidden flex-shrink-0">
                    <img :src="item.image" class="w-full h-full object-cover" :alt="item.name">
                    <span class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-6 h-6 flex items-center justify-center rounded-full font-bold">{{ item.quantity }}</span>
                  </div>
                  <div class="flex-grow">
                    <p class="font-body-md font-semibold leading-tight">{{ item.name }}</p>
                    <p class="text-body-sm text-secondary">Màu: {{ item.color }}</p>
                  </div>
                  <div class="text-right"><p class="font-headline-sm text-[16px] font-bold">{{ formatPrice(item.price) }}</p></div>
                </div>
              </div>
              <div class="space-y-4 border-t border-outline-variant pt-stack-md mb-stack-md">
                <div class="flex justify-between font-body-md text-secondary">
                  <span>Tạm tính ({{ cartItems.length }} sản phẩm)</span>
                  <span class="text-on-surface font-semibold">{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between font-body-md text-secondary">
                  <span>Phí vận chuyển</span>
                  <span class="text-success-green font-semibold">Miễn phí</span>
                </div>
                <div class="flex justify-between font-body-md text-secondary">
                  <span>Mã giảm giá</span>
                  <span class="text-primary">- 0₫</span>
                </div>
                <hr class="border-outline-variant">
                <div class="flex justify-between items-center py-2">
                  <span class="font-headline-sm text-headline-sm">Tổng cộng</span>
                  <span class="font-display-lg text-[28px] text-primary font-bold">{{ formatPrice(total) }}</span>
                </div>
              </div>
              <button @click="placeOrder" class="w-full bg-primary text-white font-headline-sm text-headline-sm py-5 rounded-lg mt-stack-md shadow-sm hover:brightness-110 transition-all active:scale-100 font-bold uppercase tracking-wide">Đặt hàng ngay</button>
              <p class="text-center text-body-sm text-secondary mt-4">Bằng cách đặt hàng, bạn đồng ý với <a class="underline" href="#">Điều khoản dịch vụ</a>.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-surface-container-lowest p-4 rounded-xl border border-gray-warm flex flex-col items-center text-center gap-2">
                <span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
                <span class="font-label-lg text-label-lg">Bảo hành trọn đời</span>
              </div>
              <div class="bg-surface-container-lowest p-4 rounded-xl border border-gray-warm flex flex-col items-center text-center gap-2">
                <span class="material-symbols-outlined text-primary text-3xl">published_with_changes</span>
                <span class="font-label-lg text-label-lg">90 ngày đổi trả</span>
              </div>
            </div>
          </div>
        </aside>
      </div>
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

// Dữ liệu giỏ hàng (thay thế bằng dữ liệu thực từ store hoặc props)
const cartItems = ref([
  { id: 1, name: "Balo Vanguard Pro Series", color: "Carbon Black", price: 2450000, quantity: 1, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB7p7et_MXVQ2VfOJhXvEZCneowWYJKVeB4ZCx0ZMtPvsRsOJz7YYExd_RHLFNAXtqLC3rynIITO1RpjU58P3QjgxKtPTdWCvOD_xFGYo-RjHugOAtLdmKgwvuxnYsBOtDnfQdUYvGDz03-t6UBOMt22LZOe591oYR_IXtXPdWU2b4HAsD7vbv4ZGiP932rtwGXFZqJb3xCBPbHxtF3kOZ2ENUpufXsmb4w89-79vGHY6rPF9EgDxRxI7uKYTV_scaoIO-1Q-Q9XZGD" },
  { id: 2, name: "Túi đựng phụ kiện Tech-Pouch", color: "Slate Grey", price: 450000, quantity: 1, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBvxwP-CST0f4D1efno3EcTOE_sv9WJoyXiOc95-iajaLl8QbB5N3y8Irv-f9ewS1r4PqoWCF7qmP8CaPojqHvTxPe-BM9pxN1WCg80ZALYHy2oCoTH8iIPf2S-yiQjnIpNcJdn_XDnE709J1rOuY5qt-5EgnyjsjkT0s89_9UsY-4-1MiljppHg4V-TZWmznAvkY5-mQJmg3L15E2HWAmW_QmnhTmVWAzBOCBBYvksixa4Ci009877AvhnoWblHP7TwqCwXP8p_Mq_" }
])

const subtotal = computed(() => cartItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0))
const total = computed(() => subtotal.value)

// Thông tin nhận hàng
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

// Helper format giá
const formatPrice = (val) => {
  return val.toLocaleString('vi-VN') + '₫'
}

// Xử lý đặt hàng
const placeOrder = () => {
  // TODO: Gửi dữ liệu đơn hàng lên server qua Inertia post
  alert('Đặt hàng thành công (demo)')
  // router.post(route('order.store'), {
  //   shipping: shippingInfo.value,
  //   payment_method: paymentMethod.value,
  //   items: cartItems.value,
  //   total: total.value
  // })
}
</script>

<style scoped>
/* Các style cần thiết (kế thừa từ global) */
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}
.glass-nav {
  backdrop-filter: blur(12px);
  background-color: rgba(251, 249, 245, 0.85);
}
</style>