<template>
  <div>
    <Head :title="product.name" />

    <!-- Header (giống các trang khác) -->
    <header class="w-full top-0 sticky z-[100] glass-nav border-b border-outline-variant shadow-sm bg-background">
      <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-margin-desktop py-4">
        <div class="flex items-center gap-8">
          <Link class="font-headline-lg text-headline-md tracking-tighter text-primary" href="/">BigBag.vn</Link>
          <div class="hidden md:flex items-center gap-6">
            <!-- Dropdown Balo -->
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
            <!-- Dropdown Cặp - Túi -->
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
            <!-- Dropdown Brands -->
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
          <Link :href="route('login')" class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">person</span>
          </Link>
          <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">2</span>
          </Link>
        </div>
      </nav>
    </header>

    <main class="max-w-container-max-width mx-auto px-margin-mobile md:px-margin-desktop py-stack-lg">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-stack-lg text-on-surface-variant font-label-md">
        <Link href="/" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link href="#" class="hover:text-primary">Business</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-on-surface font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <div class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar">
            <div v-for="(thumb, idx) in product.thumbnails" :key="idx" class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-surface-container" :class="idx === activeThumb ? 'border-primary' : 'border-outline-variant hover:border-primary'" @click="activeThumb = idx">
              <img :src="thumb" class="w-full h-full object-cover" alt="Thumbnail">
            </div>
          </div>
          <div class="flex-1 aspect-[4/5] bg-surface-container-high rounded-xl overflow-hidden shadow-sm border border-outline-variant">
            <img :src="product.thumbnails[activeThumb]" class="w-full h-full object-cover" alt="Main product">
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-stack-md">
          <div>
            <span class="inline-block px-3 py-1 bg-primary text-white text-label-sm rounded-full mb-2 uppercase font-bold">Sản Phẩm Mới</span>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-primary mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-on-surface-variant font-label-md ml-2">({{ product.reviewCount }} đánh giá)</span>
            </div>
          </div>
          <div class="flex flex-col gap-2">
            <div class="flex items-baseline gap-3">
              <span class="font-headline-md text-headline-md text-primary font-bold">{{ product.price }}</span>
              <span class="text-on-surface-variant/50 line-through font-body-sm">{{ product.oldPrice }}</span>
              <span class="text-error font-bold text-label-lg">{{ product.discount }}</span>
            </div>
            <p class="text-on-surface-variant font-body-md leading-relaxed">Thiết kế tối giản, chất liệu vải Ballistic Nylon chống nước tuyệt đối. Ngăn laptop 16 inch riêng biệt với đệm bảo vệ 360 độ. Lựa chọn hàng đầu cho các chuyến công tác chuyên nghiệp.</p>
          </div>

          <!-- Size selection -->
          <div class="py-4 border-t border-outline-variant">
            <span class="block font-label-lg text-on-surface mb-3 uppercase tracking-wider">Kích thước (Size):</span>
            <div class="flex gap-3">
              <button v-for="size in product.sizes" :key="size" class="px-6 py-2 border-2 rounded-xl font-label-md transition-all" :class="selectedSize === size ? 'border-primary text-primary bg-primary/5' : 'border-outline-variant text-on-surface-variant hover:border-primary'" @click="selectedSize = size">{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div class="py-4 border-t border-outline-variant">
            <span class="block font-label-lg text-on-surface mb-3 uppercase tracking-wider">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3">
              <button v-for="color in product.colors" :key="color.value" class="w-10 h-10 rounded-full border-2 p-1" :class="selectedColor === color.value ? 'border-primary' : 'border-outline-variant hover:border-primary'" @click="selectedColor = color.value; selectedColorName = color.label">
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col gap-3 py-6">
            <div class="grid grid-cols-2 gap-3">
              <button @click="addToCart" class="flex-1 h-14 bg-primary text-white font-headline-sm rounded-xl hover:bg-primary/90 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">shopping_cart</span> Thêm vào giỏ hàng
              </button>
              <button class="flex-1 h-14 border-2 border-primary text-primary font-headline-sm rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">event_repeat</span> Đặt hàng trước
              </button>
            </div>
            <Link :href="route('customize')" class="w-full h-14 text-white font-headline-sm rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-black hover:bg-gray-800">
              <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
            </Link>
          </div>

          <!-- Features list -->
          <div class="bg-surface-container-low p-5 rounded-xl space-y-3 border border-outline-variant">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-on-surface-variant font-body-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Features grid (Highlights) -->
      <section class="mt-section-gap">
        <h2 class="font-headline-lg text-headline-lg text-on-surface mb-stack-lg text-center">Đặc điểm nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-stack-lg">
          <div class="md:col-span-2 bg-surface-container rounded-2xl p-8 flex flex-col justify-between group overflow-hidden border border-outline-variant">
            <div><h3 class="font-headline-md text-headline-md mb-3">Vật liệu siêu bền</h3><p class="text-on-surface-variant font-body-md max-w-md">Sử dụng vải Nylon 1680D có độ bền kéo cực cao, chống mài mòn và thấm nước tuyệt đối.</p></div>
            <img alt="Material" class="w-full h-48 object-cover rounded-xl mt-6 group-hover:scale-105 transition-transform duration-500" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAAXkYc03HJQmTinK1YAQbj736ihy99OstGxMcUxfWyDse1xtCXg628v2N8vSfTXVOHSiaOscLyeVJWCULvAkl2DZNGbcFY61CXOK0Qvc3SDDq5GnTDdUapS_7qmce8NhJ5yu68yhMSt_ejolkow3sghIYvDw_hwUTmAKrrzVQU7SEDxad6b7kyBmB7Rj06_r49-hBFawQJtCo8Q-rYddCiDj_V1vpZWFZMtA9BOH73zqME0z-wW07uXfYHhwQF9j2QON12Tc4CJBKH">
          </div>
          <div class="bg-primary text-white rounded-2xl p-8 flex flex-col items-center text-center justify-center shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-6xl mb-4">laptop_mac</span>
            <h3 class="font-headline-md text-headline-md mb-2">Ngăn Laptop 16"</h3>
            <p class="text-white/80 font-body-md">Đệm chống sốc dạng tổ ong bảo vệ thiết bị tối đa khỏi va đập mạnh từ mọi phía.</p>
          </div>
          <div class="bg-surface-container rounded-2xl p-8 text-center flex flex-col items-center border border-outline-variant">
            <span class="material-symbols-outlined text-6xl mb-4 text-primary">lock</span>
            <h3 class="font-headline-md text-headline-md mb-2">An toàn tuyệt đối</h3>
            <p class="text-on-surface-variant font-body-md">Ngăn bí mật mặt lưng để điện thoại và hộ chiếu, cùng dây kéo YKK chống trộm.</p>
          </div>
          <div class="md:col-span-2 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 bg-black">
            <div class="flex-1"><h3 class="font-headline-md text-headline-md mb-3 text-white">Tùy biến theo chất riêng</h3><p class="font-body-md mb-6 text-white/90">Dịch vụ in/khắc logo doanh nghiệp: Tải lên hình ảnh logo, chọn vị trí in (trước, sau, quai đeo) và để lại lời nhắn chi tiết cho chúng tôi.</p><Link :href="route('customize')" class="px-8 py-3 bg-white text-primary rounded-xl hover:bg-opacity-90 transition-colors font-bold">Yêu cầu In Logo & Tùy chỉnh</Link></div>
            <div class="w-32 h-32 flex items-center justify-center rounded-full shadow-lg" style="background-color: rgb(230, 81, 0);"><span class="material-symbols-outlined text-6xl text-white">brush</span></div>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section class="mt-section-gap">
        <h2 class="font-headline-lg text-headline-lg text-on-surface mb-stack-lg text-center">Các sản phẩm liên quan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <div v-for="item in relatedProducts" :key="item.id" class="flex flex-col group">
            <div class="aspect-[3/4] bg-surface-container rounded-xl overflow-hidden mb-4 relative">
              <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="item.name">
            </div>
            <span class="text-on-surface-variant font-label-md uppercase mb-1">{{ item.brand }}</span>
            <h3 class="font-headline-sm text-on-surface mb-2 truncate">{{ item.name }}</h3>
            <div class="flex items-center gap-2 mb-4"><span class="font-bold text-primary">{{ item.price }}</span></div>
            <button @click="addToCartSimple(item)" class="w-full py-3 bg-primary text-white font-label-lg rounded-xl hover:bg-primary/90 transition-all flex items-center justify-center gap-2"><span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ hàng</button>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section class="mt-section-gap border-t border-outline-variant pt-section-gap">
        <h2 class="font-headline-lg text-headline-lg text-on-surface mb-stack-lg">Đánh giá từ khách hàng</h2>
        <div class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-surface-container-low rounded-xl border border-outline-variant">
            <div class="flex justify-between items-start mb-4">
              <div><div class="flex items-center gap-1 text-primary mb-1"><span v-for="n in 5" :key="n" class="material-symbols-outlined" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span></div><span class="font-label-lg text-on-surface font-bold">{{ review.author }}</span></div>
              <span class="text-on-surface-variant/60 font-label-md">{{ review.date }}</span>
            </div>
            <p class="text-on-surface-variant font-body-md">{{ review.content }}</p>
          </div>
        </div>
        <button class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-label-lg font-bold hover:bg-primary/5 transition-all">Xem tất cả {{ totalReviews }} đánh giá</button>
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

const props = defineProps({
  id: Number  // nhận từ route
})

// Dữ liệu sản phẩm mẫu (có thể thay bằng API call)
const product = ref({
  name: "Balo Doanh Nhân Apex v2",
  price: "3.450.000₫",
  oldPrice: "4.200.000₫",
  discount: "-18%",
  reviewCount: 128,
  thumbnails: [
    "https://lh3.googleusercontent.com/aida-public/AB6AXuCBNROb9o7-J001YqxpX3KeEkJlK1mk73Z6GdpA7KrkCp1f866BvULeVGKFQnAgYB88qmjmcgkStWTE5Njod1miJz2UaOPxvuLh8CvuqKbcxIC5n4Am70npbxTM3z52Z-Bs8UelUb7ADv3iNhYMQur8a6IFZVnr4N9AEKFBC6dRGl6n7Px0S_0m3jXLHghtMwGYOoMFHDKk2rcW_H1gpP-TP-61WGGoGNZRlCjCbj5UEGFm3mrDW1Wj5osbfytVqf1wgWkmkKbPV4QS",
    "https://lh3.googleusercontent.com/aida-public/AB6AXuBbS9o5gYfGLvmEFPlhsrMURgS2SHOyUykj0RVAYmzsu3iG7Uirt8fu571248-UFbHWMU3Q6TNOIEbM4PZ8ReR-0Wlkm3rZBdEFKMbpdi0BLTNY9_AeSwT-igbVjwMnX9MKCIwOtheIC3wM-pdyj3bcQIaN8tUc6y4Izm4NB2UlV7dQWcvKfl67KNLTY7FsZPh7Gq2Sxux0-xV1ff5CDnesjZ_N5O689gWjXYaiD-wbMYZ6IY0jop5a6eGIW1C9ivPX-DRaByKhiUjb",
    "https://lh3.googleusercontent.com/aida-public/AB6AXuCM-UF6EQSyOh9LXzIrh2Ic3FiZTWWqNgjlNw0-cBc8BDJMmO854hryJhRV6mK16UN6Rd3yuQX96rhjXheeTl0q7b7X-1bARu8PXynHlnfQJdPuEjQVcMNpYVCB7FS8KDx41zT1aL4e6FfBjwCxLIAdmBOrp7vxcBDVs027FGMUWW4Uc_0WwH84_81llMtl6uBHCtGTsXfXVunT1ymhhUwHrYe85vrICFu905zpMD7yX5O5fORwttdVW93N8dmRTKyNyz2QD6IMsEJF"
  ],
  sizes: ["Standard", "Large (Pro)"],
  colors: [
    { value: "#1A1A1A", label: "Midnight Charcoal" },
    { value: "#34495E", label: "Navy Blue" },
    { value: "#5D4037", label: "Brown" }
  ],
  features: [
    { icon: "verified", text: "Bảo hành trọn đời khóa kéo và đường chỉ" },
    { icon: "local_shipping", text: "Miễn phí vận chuyển toàn quốc" },
    { icon: "history", text: "Đổi trả trong vòng 30 ngày nếu không hài lòng" }
  ]
})

const activeThumb = ref(0)
const selectedSize = ref("Standard")
const selectedColor = ref("#1A1A1A")
const selectedColorName = ref("Midnight Charcoal")

// Related products mẫu
const relatedProducts = ref([
  { id: 1, name: "Balo Work-Travel Pro", brand: "BigBag Premium", price: "2.850.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDfgk46m0azvYWnVpwHQ7_pYNeVPefYWcMfopsF0ys5i8B0rInOqy_OevM6EkKfzwHyLqS-P5heqJh-beiPZAngg6HRfyhD-pQ6H1byonD5B-9CKZnF-CxSrG9KmZsgSVw3UET5ZAHKYM-rvCpB_BkTpRt9RWkPjHuDtVFhno9ajCaUzf2w5AtLVo2YcHcU9HBu3FN6heDPDQPGd_K9GWh_ZywonWYbVWzLge6JioetmVFezVB8A_3uyNS74S9GKNWLVFJmDJU5C6Y" },
  { id: 2, name: "Túi Messenger Elite", brand: "Apex Series", price: "1.950.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCUZ078bDuSIbR0KbQIH-_9b_DcUm4uQ2iI_J033VYvzVIXJIQe6zN2qyDl9839ozV82Enl2WbWfD3IqjjSRjFcs2U11f_fy9dd8MAFKejdeZiQnsJkkATfGdz4bK0PO_nXKJDxQxV5lZYMzgdfPF4_JeuF_VKNTXFJRJfsAOtoIpMsZVDA4eTGii98nOZdqync579qkfa7TjFdsT_f-wa7bogqC1j8EuaWH-pg1xltTyoRDR-xesTK-dMmll8Lvmri3MKLobAh-j0" },
  { id: 3, name: "Balo Daily Commuter", brand: "Urban Nomad", price: "1.450.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCmq_1micwrfrXEvnZ9MReosSfBcNsD699IW_s13z18_2v3c__1_-YGgTaBQz5HWdlUDsFabKTi0soOhH7QzgM3rWvInLsncrk970MLomgAxDBerSn76cgH-E5PnSrzD7Y6yyGvOYLemoRURhgrQPTHdXPZm6u5AvwhXE2Y6X10IQrPSiKfG5FAoO7j5_9lhq-r9iNiTvzHuaOVQ3qIBrYjOZlM-WD_BjpDo8pF18IDAq3YtqyBQAiS0LWBRivegaG4bUvxwW1lhU4" },
  { id: 4, name: "Vali Cabin Size Apex", brand: "BigBag Business", price: "4.200.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAKC3ZnxAUhZHOlhQzlWokjeL283mcUr_xRfTETreTxlJyfbXwR93lPHeymGXe-QSLW2OQ93__KVsY5lbMsHdLpN4KDOsbYd17-qi9-v3y8LG0KX0QhtEsgUOYDZaXJWATGAaUBynvLh3MjjhiUmzvfxolEYm9YKNPZB0IrwScgI2yIltFthDuApBD3rb2XhlvhJ4guWlYNXz32zbUxSsNRoFtVkkFEd7-0xbGn6wgoekUsT3xK7GnC8ozoXfDWhJJ2_Y2LHgmvV_E" }
])

const reviews = ref([
  { id: 1, author: "Nguyễn Văn A", rating: 5, date: "15/05/2024", content: "Sản phẩm rất chất lượng, vải dày dặn và chống nước tốt. Ngăn laptop rất an toàn cho máy 16 inch của mình." },
  { id: 2, author: "Trần Thị B", rating: 4, date: "10/05/2024", content: "Thiết kế đẹp, sang trọng. Tuy nhiên màu Midnight Charcoal ngoài đời hơi đậm hơn trong ảnh một chút." }
])
const totalReviews = ref(128)

const addToCart = () => {
  alert('Đã thêm sản phẩm vào giỏ hàng (demo)')
  // Có thể gọi router.post(route('cart.add'), { id: props.id })
}

const addToCartSimple = (item) => {
  alert(`Đã thêm ${item.name} vào giỏ hàng (demo)`)
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #ff6b00;
  border-radius: 10px;
}
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