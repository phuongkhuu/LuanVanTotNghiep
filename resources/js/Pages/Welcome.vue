<template>
  <div>
    <Head title="Trang chủ - BigBag Premium Utility Carry Gear" />

    <!-- Header -->
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
                  <Link :href="route('category', { slug: 'balo-laptop' })" class="text-body-sm text-on-surface-variant hover:text-primary">Balo Laptop</Link>
                  <Link :href="route('category', { slug: 'balo-du-lich' })" class="text-body-sm text-on-surface-variant hover:text-primary">Balo Du Lịch</Link>
                  <Link :href="route('category', { slug: 'balo-thoi-trang' })" class="text-body-sm text-on-surface-variant hover:text-primary">Balo Thời Trang</Link>
                  <Link :href="route('category', { slug: 'balo-cao-cap' })" class="text-body-sm text-on-surface-variant hover:text-primary">Balo Cao Cấp</Link>
                  <Link :href="route('category', { slug: 'balo-the-thao' })" class="text-body-sm text-on-surface-variant hover:text-primary">Balo Thể Thao</Link>
                  <Link :href="route('category', { slug: 'balo-da-nang' })" class="text-body-sm text-on-surface-variant hover:text-primary">Balo Đa Năng</Link>
                </div>
              </div>
            </div>
            <!-- Dropdown Cặp - Túi -->
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Cặp - Túi</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link :href="route('category', { slug: 'cap-xach' })" class="text-body-sm text-on-surface-variant hover:text-primary">Cặp Xách</Link>
                  <Link :href="route('category', { slug: 'tui-du-lich' })" class="text-body-sm text-on-surface-variant hover:text-primary">Túi Du Lịch</Link>
                  <Link :href="route('category', { slug: 'tui-deo-cheo' })" class="text-body-sm text-on-surface-variant hover:text-primary">Túi Đeo Chéo</Link>
                  <Link :href="route('category', { slug: 'cap-tui-macbook' })" class="text-body-sm text-on-surface-variant hover:text-primary">Cặp Túi Macbook</Link>
                </div>
              </div>
            </div>
            <!-- Dropdown Brands -->
            <div class="relative dropdown-group">
              <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-4 block" href="#">Brands</Link>
              <div class="dropdown-menu absolute top-full left-0 bg-white border border-outline-variant shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
                <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                  <Link :href="route('category', { slug: 'tomtoc' })" class="text-body-sm text-on-surface-variant hover:text-primary">Tomtoc</Link>
                  <Link :href="route('category', { slug: 'mark-ryden' })" class="text-body-sm text-on-surface-variant hover:text-primary">Mark Ryden</Link>
                  <Link :href="route('category', { slug: 'rokin' })" class="text-body-sm text-on-surface-variant hover:text-primary">Rokin</Link>
                  <Link :href="route('category', { slug: 'sakos' })" class="text-body-sm text-on-surface-variant hover:text-primary">Sakos</Link>
                </div>
              </div>
            </div>
            <!-- Các link đơn -->
            <Link :href="route('wholesale')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Mua sỉ</Link>
            <Link :href="route('promotion')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Khuyến mãi</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">Giới thiệu</Link>
            <Link class="font-label-md text-label-md text-primary border-b-2 border-primary pb-1" href="#">New Arrivals</Link>
          </div>
        </div>
        <!-- Search -->
        <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
          <div class="relative w-full">
            <input class="w-full bg-surface-container border border-outline-variant rounded-full py-2 text-body-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" placeholder="Tìm kiếm sản phẩm..." type="text">
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
          </div>
        </div>
        <!-- User & Cart -->
        <div class="flex items-center gap-2">
          <Link v-if="!user" :href="route('login')" class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">person</span>
          </Link>
          <Link v-else :href="route('profile.edit')" class="p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">account_circle</span>
          </Link>
          <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-primary">
            <span class="material-symbols-outlined">shopping_bag</span>
            <span class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">2</span>
          </Link>
        </div>
      </nav>
    </header>

    <!-- Hero Carousel -->
    <section class="relative group overflow-hidden">
      <div class="overflow-x-auto snap-x snap-mandatory flex hide-scrollbar" id="hero-carousel">
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full relative overflow-hidden">
            <img alt="Sale Banner 1" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDxx0m6cgeB_wFfg7s6Gg9fUlG74LJAjQX52e76-kLKbboHcvdGuP8wLvolaZ2nn44uSU4mSzGcMnWRrxegCgrBQPS_CJCrqTw_lR9qipVD13hl9T_DV9Vwt4PmieoYHWvSuOgDjr4TLs2YpCS6eO_P1Ya4-_gUurI8xgCqtWZq3EvAe9WrB0_PXR8pDs-UdKo5u7vHbg-s3eYwYc1YpaZsyCDVrp1oAxlY5NkvxU8DCvx9sj5PwWBzawIL86tZy9He4cl9TZdngHc">
          </div>
        </div>
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full relative overflow-hidden">
            <img alt="Sale Banner 2" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCp5eQ5SZCwA43e9ZQV6q5AsixqVrngZDfmTBxJnnZZnN9FJ-UksaoW1_6ST0Oc6LoiJEgpvMf4K1zxMWMDQMiSsoVTBNGkDP_gHl8zHBONErOgONG9qdZ1Uj2M143jhRomrMwOr7m_k66Z1qw8Dg6V-3CBkzDQGEdnu4uUQFh56yuIQox-XTGWy1stgcNRm_9bBcHtgvXHSzjDoLZxarh8vh22_7wpoMLjWSTigP2X-laqEhuIKyvDhR7HHBaSrePhkDvbOjOKw9c">
          </div>
        </div>
        <!-- slide 3: nội dung chính -->
        <div class="flex-none w-full snap-center relative">
          <div class="h-[585px] w-full bg-surface-container-low flex items-center">
            <div class="max-w-[1440px] mx-auto px-margin-desktop grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
              <div class="z-10">
                <div class="inline-flex items-center px-4 py-2 bg-tertiary-fixed rounded-full text-primary mb-6">
                  <span class="material-symbols-outlined text-[18px] mr-2" style="font-variation-settings: 'FILL' 1;">eco</span>
                  <span class="font-label-sm text-label-sm uppercase tracking-wider">BỀN BỈ & THÂN THIỆN MÔI TRƯỜNG</span>
                </div>
                <h1 class="font-display-lg text-display-lg text-on-background mb-6 leading-tight">Tự Do <span class='text-primary italic'>Khám Phá</span>,<br>Trải Nghiệm Đỉnh Cao</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant mb-10 max-w-lg">Đồng hành cùng bạn trên mọi nẻo đường. BigBag mang đến bộ sưu tập Balo dã ngoại cao cấp, tối ưu cho mọi chuyến đi.</p>
                <div class="flex flex-wrap gap-4">
                  <button class="bg-primary text-on-primary px-8 py-4 rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity shadow-lg shadow-primary/20">Săn Ngay Deal Mới</button>
                </div>
              </div>
              <div class="relative">
                <img alt="Adventure" class="rounded-xl shadow-2xl h-[450px] w-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC2oIX2ZXuMRF61KyS0HPGQ4C1jpXMcV244LQH5GGO8TuK6Wg_bXXEqchbz9CShLLk4HDYcJP5IUTiqVvFBtnI-IyrZPGtlmb663en8YlXThpEIWlBcMnF3fl4RpfRjFiesjVp2C5MNplsVssGWIw0A5bVIirI3zrVH0chvYxvqS9H7XzCPs3wmS8um8qoctJiWPWOPnxNkv86moCcvFY5htC21WfmAqMrgHrW19sO51qVt3MsgjyXq7DJCMC7XvmMxqQalDsfYru8">
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="absolute left-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" id="prev-hero">
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button class="absolute right-8 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md hover:bg-white/50 text-white p-3 rounded-full transition-all opacity-0 group-hover:opacity-100 hidden md:block" id="next-hero">
        <span class="material-symbols-outlined">chevron_right</span>
      </button>
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2" id="carousel-indicators"></div>
    </section>

    <!-- HOT SALE SECTION -->
    <section class="py-section-gap bg-[#FFF5F2]">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <div class="flex flex-col md:flex-row justify-center items-center gap-6 mb-4">
            <h2 class="font-headline-lg text-headline-lg">Sale Cực Sốc</h2>
            <div class="flex items-center gap-2 text-on-surface">
              <span class="font-label-md">Kết thúc sau:</span>
              <div class="flex gap-1">
                <span class="bg-on-surface text-white px-2 py-1 rounded text-label-md font-bold">12</span>:
                <span class="bg-on-surface text-white px-2 py-1 rounded text-label-md font-bold">45</span>:
                <span class="bg-on-surface text-white px-2 py-1 rounded text-label-md font-bold">08</span>
              </div>
            </div>
          </div>
          <p class="font-body-md text-body-md text-on-surface-variant">Ưu đãi giới hạn chỉ trong hôm nay</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <Link v-for="product in hotSales" :key="product.id" :href="route('product.detail', { id: product.id })" class="block product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden transition-all duration-300">
            <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden">
              <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
              <span class="absolute top-4 left-4 bg-error text-white px-3 py-1 rounded-full font-label-sm text-label-sm">{{ product.discountText }}</span>
            </div>
            <div class="p-4 flex flex-col">
              <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">{{ product.name }}</p>
              <div class="flex items-baseline space-x-2 mb-4">
                <span class="font-headline-md text-headline-md text-primary">{{ product.price }}</span>
                <span v-if="product.oldPrice" class="font-body-sm text-body-sm text-on-surface-variant/60 line-through">{{ product.oldPrice }}</span>
              </div>
              <button class="w-full py-3 text-white rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity bg-primary font-bold">Mua Ngay!</button>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- TRENDING PRODUCTS -->
    <section class="py-section-gap">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <h2 class="font-headline-lg text-headline-lg mb-2">Đang Được Săn Đón</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Sản phẩm yêu thích nhất tháng này</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <Link v-for="product in trending" :key="product.id" :href="route('product.detail', { id: product.id })" class="block product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden transition-all duration-300">
            <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden">
              <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
            </div>
            <div class="p-4 flex flex-col">
              <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">{{ product.name }}</p>
              <div class="flex items-baseline space-x-2 mb-4">
                <span class="font-headline-md text-headline-md text-primary">{{ product.price }}</span>
              </div>
              <button class="w-full py-3 text-white rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity bg-primary font-bold">Mua Ngay!</button>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- NEW ARRIVALS -->
    <section class="py-section-gap">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <h2 class="font-headline-lg text-headline-lg mb-2">Sản Phẩm Mới Nhất</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Đón đầu xu hướng cùng bộ sưu tập 2024</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
          <Link v-for="product in newProducts" :key="product.id" :href="route('product.detail', { id: product.id })" class="block product-card-hover group bg-white border border-gray-warm rounded-lg overflow-hidden transition-all duration-300">
            <div class="relative aspect-[4/5] bg-surface-container-low overflow-hidden">
              <img :src="product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="product.name">
              <span class="absolute top-4 left-4 bg-on-surface text-white px-3 py-1 rounded-full font-label-sm text-label-sm uppercase">Mới</span>
            </div>
            <div class="p-4 flex flex-col">
              <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">{{ product.name }}</p>
              <div class="flex items-baseline space-x-2 mb-4">
                <span class="font-headline-md text-headline-md text-primary">{{ product.price }}</span>
              </div>
              <button class="w-full py-3 text-white rounded-xl font-label-lg text-label-lg hover:opacity-90 transition-opacity bg-primary font-bold">Mua Ngay!</button>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- NEWS & PROMOTIONS -->
    <section class="py-section-gap">
      <div class="max-w-[1440px] mx-auto px-margin-desktop">
        <div class="text-center mb-12">
          <h2 class="font-headline-lg text-headline-lg mb-2">Tin Tức & Khuyến Mãi</h2>
          <p class="font-body-md text-body-md text-on-surface-variant">Cập nhật những câu chuyện và ưu đãi mới nhất</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
          <div v-for="article in newsList" :key="article.id" class="flex flex-col">
            <div class="aspect-[1.5/1] overflow-hidden mb-6 group rounded-xl">
              <img :src="article.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="article.title">
            </div>
            <h3 class="font-label-md text-label-md text-on-surface mb-2 leading-snug hover:text-primary transition-colors cursor-pointer uppercase">{{ article.title }}</h3>
            <p class="text-on-surface-variant/60 text-label-sm mb-4">{{ article.date }}</p>
            <p v-if="article.desc" class="text-on-surface-variant text-body-sm line-clamp-2">{{ article.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Chatbot -->
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
        <div class="space-y-4">
          <div class="font-headline-sm text-headline-sm font-bold text-[#333333]">BigBag.vn</div>
          <p class="font-body-sm leading-relaxed text-[#333333]">© 2025 BigBag. Premium Utility Carry Gear.</p>
        </div>
        <div class="flex flex-col gap-2">
          <h4 class="font-label-lg uppercase mb-2 text-[#333333]">VỀ BIGBAG</h4>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Về chúng tôi</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Phát triển bền vững</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Liên hệ</Link>
        </div>
        <div class="flex flex-col gap-2">
          <h4 class="font-label-lg uppercase mb-2 text-[#333333]">HỖ TRỢ KHÁCH HÀNG</h4>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Giao hàng & Trả hàng</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo hành</Link>
          <Link class="hover:text-primary underline transition-all font-body-sm" href="#">Chính sách bảo mật</Link>
        </div>
        <div class="flex flex-col gap-4">
          <h4 class="font-label-lg uppercase mb-2 text-[#333333]">ĐĂNG KÝ NHẬN TIN</h4>
          <div class="flex gap-2">
            <input class="bg-white border border-[#333333] px-3 py-2 text-body-sm w-full focus:border-primary outline-none placeholder:text-[#333333]/50 rounded-none" placeholder="Email của bạn" type="email">
            <button class="bg-transparent border border-[#333333] text-[#333333] px-4 py-2 flex items-center justify-center hover:bg-primary/10 rounded-none transition-colors">
              <span class="material-symbols-outlined">arrow_forward</span>
            </button>
          </div>
          <div class="flex gap-4">
            <span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors">public</span>
            <span class="material-symbols-outlined hover:text-primary cursor-pointer transition-colors">share</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

// Lấy thông tin user từ Laravel (nếu có)
const page = usePage()
const user = computed(() => page.props.auth.user)

// Dữ liệu sản phẩm (giữ nguyên từ file gốc)
const hotSales = ref([
  { id: 1, name: "Solo® Business Pro", price: "1.014.000₫", oldPrice: "1.690.000₫", discountText: "GIẢM 40%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCO8voZHjd7ojHarJN9jIJgSpSl3JXc9T2fjBljHi4hAm1zSQkyHbLzsqOnO4UghVrThOssPTYktjaMWPzFENMkfomSjlrRNYuBPOlGcAxTcpkk3jxl_A6EYApQ78QUpU61BZzcC7WAzfYRbdBrIAwEDad1YDPQs30-7NgFAPQRW_fS0iRfo1nlHTQXrNATcUF9bwvCMI3X2du_5FDlaut1hVLhkI411HeqdyKYiDcGglZ85QB6zg-njUmZU-KBIWi5g4XLwgi3OoM" },
  { id: 2, name: "KingBag Modern Elite", price: "350.000₫", oldPrice: "750.000₫", discountText: "GIẢM 35%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDUJl9NUYw-qW0qcduv9amvNiRUc68vgXUu-jYNtzx8xhzwzWHNJiydkbYH3SnOR3HaOK4Le7H3urpsvJtwaTScNC_M9z-rhWZ6pEn4qKJeWGdW1gmKJo5GczOrLF4Edn-wzGQNa7uOT8XQlI8CDNaiuq6KLoiVCRxThNzLK4eC8OncfEOG-CYPxy3CoNxMuofd3rPXAOZPqCEacYFxxSAbsUHyf5f4-fGkf3CiPMf8VjJX-Ug6Q6Fa2Hugp8aLfHznXnLQoK5-P5c" },
  { id: 3, name: "Nomad Heritage Duffel", price: "2.100.000₫", oldPrice: "2.650.000₫", discountText: "GIẢM 20%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuC32-iw4wvfyhv5PBHkLX8n7vmQffTIaThy6kD8a7VUwqREt7kLih7jE8dxd7TjLDAfwlWmENKc_UUg8XuuqSc3-9_pjAc4rjCYO-hP6Op2STW2RZfmHyn7oCEPeZBdIUIeymoYSrG0tN5mBMq95k5touqNzFD7BPsxYT95nK7PARA_B9kdKv6ZRkeYlXswhWsjAsrbvdEGbs4-hRnQCGsiyTGB2X3Ay0shDYkK5PJn7bmJfj829cjBjuoIfnHyMwx1t5_4DeIEbvs" },
  { id: 4, name: "Urban City Crossbody", price: "450.000₫", oldPrice: "900.000₫", discountText: "GIẢM 50%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDSmEDZQDD58qBCeCbYW2UxVR_tqMgjXdDQRkEERR5QhJK9YvafDTv5QRh6ujRIeLePGagmfdOd_cjAlIe4GwhNkGfJVv1EOi60mktvnlr8gPEf5RA0-eCmr0UCIMz1KKTtCiYu-m1V6385vyvr-yFyOXfEpc4FBB1JzEaWJflnTagh2CNGC-8uodpn0X607KBKdn8_cF3J_7E1KtOh6gRfolKI_AV8UZwQcgeQrw6hGnscEPbO_tBm6AJsBP_V0QHhWawsLeL9DPU" }
])

const trending = ref([
  { id: 5, name: "Tactical Adventure Pro", price: "1.450.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBqOz3_EnU8xeRYoGPuFKvMQRobwrvROdn_dvkql06Tl6bLdg60k1_J3Zlh7Gthd1eqPSTHo_0us4e-gch97CDHK-tC1oOTPfErNyn-vm76BL5WbRJg98zykLSBUV0NiRwv_5cKg0pvzZXXhX2NrVXQOOQ_3PWDudW_reG4gm6AGbBF2npCOamXpyqx7xriJl1tDpfR2COHNNRN9S3JXTlHPXTTP2tZ2KjK-1Jy6BSHvkiSf2OJ6GfIIGDugKYtJFcPZWwvmB-V0HU" },
  { id: 6, name: "Urban City Sleek", price: "890.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDSmEDZQDD58qBCeCbYW2UxVR_tqMgjXdDQRkEERR5QhJK9YvafDTv5QRh6ujRIeLePGagmfdOd_cjAlIe4GwhNkGfJVv1EOi60mktvnlr8gPEf5RA0-eCmr0UCIMz1KKTtCiYu-m1V6385vyvr-yFyOXfEpc4FBB1JzEaWJflnTagh2CNGC-8uodpn0X607KBKdn8_cF3J_7E1KtOh6gRfolKI_AV8UZwQcgeQrw6hGnscEPbO_tBm6AJsBP_V0QHhWawsLeL9DPU" },
  { id: 7, name: "Canvas Weekend Duffel", price: "2.100.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuC32-iw4wvfyhv5PBHkLX8n7vmQffTIaThy6kD8a7VUwqREt7kLih7jE8dxd7TjLDAfwlWmENKc_UUg8XuuqSc3-9_pjAc4rjCYO-hP6Op2STW2RZfmHyn7oCEPeZBdIUIeymoYSrG0tN5mBMq95k5touqNzFD7BPsxYT95nK7PARA_B9kdKv6ZRkeYlXswhWsjAsrbvdEGbs4-hRnQCGsiyTGB2X3Ay0shDYkK5PJn7bmJfj829cjBjuoIfnHyMwx1t5_4DeIEbvs" },
  { id: 8, name: "Everyday City Explorer", price: "650.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB54C_CBWKp2Jv3dsSnpunJIVphSJUIovunCJEgiV0YDFpL-tla3aiRYQpNR4IGTakRDXzTz1h8WyR0QP1U2Ojcr5j3oA3if08IJ8HA7uAxSr7PLlyDN1CkcJaI_FE879pzjOA1tgcLqrDmTaGC9zufRqTtGsLAJYl55bnZNlD4icSseRJLr4eewnQvrR8onWndt9YalIbST1KacZpzx2t80iTFqYwI_-IB3YhY_FLaM_lsjzRRSnLZ6iZEu_nwRuUFpNNAwt3F2qk" }
])

const newProducts = ref([
  { id: 9, name: "Adventure Lite 20L", price: "1.250.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB54C_CBWKp2Jv3dsSnpunJIVphSJUIovunCJEgiV0YDFpL-tla3aiRYQpNR4IGTakRDXzTz1h8WyR0QP1U2Ojcr5j3oA3if08IJ8HA7uAxSr7PLlyDN1CkcJaI_FE879pzjOA1tgcLqrDmTaGC9zufRqTtGsLAJYl55bnZNlD4icSseRJLr4eewnQvrR8onWndt9YalIbST1KacZpzx2t80iTFqYwI_-IB3YhY_FLaM_lsjzRRSnLZ6iZEu_nwRuUFpNNAwt3F2qk" },
  { id: 10, name: "Sleek Urban Carry", price: "1.590.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDSmEDZQDD58qBCeCbYW2UxVR_tqMgjXdDQRkEERR5QhJK9YvafDTv5QRh6ujRIeLePGagmfdOd_cjAlIe4GwhNkGfJVv1EOi60mktvnlr8gPEf5RA0-eCmr0UCIMz1KKTtCiYu-m1V6385vyvr-yFyOXfEpc4FBB1JzEaWJflnTagh2CNGC-8uodpn0X607KBKdn8_cF3J_7E1KtOh6gRfolKI_AV8UZwQcgeQrw6hGnscEPbO_tBm6AJsBP_V0QHhWawsLeL9DPU" },
  { id: 11, name: "Rokin Tactical Force", price: "2.450.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBqOz3_EnU8xeRYoGPuFKvMQRobwrvROdn_dvkql06Tl6bLdg60k1_J3Zlh7Gthd1eqPSTHo_0us4e-gch97CDHK-tC1oOTPfErNyn-vm76BL5WbRJg98zykLSBUV0NiRwv_5cKg0pvzZXXhX2NrVXQOOQ_3PWDudW_reG4gm6AGbBF2npCOamXpyqx7xriJl1tDpfR2COHNNRN9S3JXTlHPXTTP2tZ2KjK-1Jy6BSHvkiSf2OJ6GfIIGDugKYtJFcPZWwvmB-V0HU" },
  { id: 12, name: "Nomad Explorer Duffel", price: "3.200.000₫", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuC32-iw4wvfyhv5PBHkLX8n7vmQffTIaThy6kD8a7VUwqREt7kLih7jE8dxd7TjLDAfwlWmENKc_UUg8XuuqSc3-9_pjAc4rjCYO-hP6Op2STW2RZfmHyn7oCEPeZBdIUIeymoYSrG0tN5mBMq95k5touqNzFD7BPsxYT95nK7PARA_B9kdKv6ZRkeYlXswhWsjAsrbvdEGbs4-hRnQCGsiyTGB2X3Ay0shDYkK5PJn7bmJfj829cjBjuoIfnHyMwx1t5_4DeIEbvs" }
])

const newsList = ref([
  { id: 1, title: "TÚI DU LỊCH CAO CẤP CHÍNH HÃNG KINGBAG SKY GIẢM CỰC SỐC DUY NHẤT HÈ 2026. GIÁ SALE NGỠ NGÀNG - CHỈ CÒN 350K SỞ HỮU TÚI CỰC XỊN", date: "06/05/2026", desc: "", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDUJl9NUYw-qW0qcduv9amvNiRUc68vgXUu-jYNtzx8xhzwzWHNJiydkbYH3SnOR3HaOK4Le7H3urpsvJtwaTScNC_M9z-rhWZ6pEn4qKJeWGdW1gmKJo5GczOrLF4Edn-wzGQNa7uOT8XQlI8CDNaiuq6KLoiVCRxThNzLK4eC8OncfEOG-CYPxy3CoNxMuofd3rPXAOZPqCEacYFxxSAbsUHyf5f4-fGkf3CiPMf8VjJX-Ug6Q6Fa2Hugp8aLfHznXnLQoK5-P5c" },
  { id: 2, title: "Tại sao gọi Rokin EAGLE là chiến binh bất bại TRÊN MỌI HÀNH TRÌNH? Câu trả lời có đầy đủ trong Link này:", date: "21/03/2026", desc: "https://bigbag.vn/balo-da-nang-cao-cap-rokin-eagle. NHANH TAY KHÁM PHÁ NGAY BẠN NHÉ!", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBqOz3_EnU8xeRYoGPuFKvMQRobwrvROdn_dvkql06Tl6bLdg60k1_J3Zlh7Gthd1eqPSTHo_0us4e-gch97CDHK-tC1oOTPfErNyn-vm76BL5WbRJg98zykLSBUV0NiRwv_5cKg0pvzZXXhX2NrVXQOOQ_3PWDudW_reG4gm6AGbBF2npCOamXpyqx7xriJl1tDpfR2COHNNRN9S3JXTlHPXTTP2tZ2KjK-1Jy6BSHvkiSf2OJ6GfIIGDugKYtJFcPZWwvmB-V0HU" },
  { id: 3, title: "Bật mí tính năng nới rộng thông minh của balo MARK RYDEN DELUX", date: "21/03/2026", desc: "Bạn đang tìm kiếm một chiếc balo thông minh tiện dụng? Thì bạn không thể bỏ qua chiếc balo MARK...", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCO8voZHjd7ojHarJN9jIJgSpSl3JXc9T2fjBljHi4hAm1zSQkyHbLzsqOnO4UghVrThOssPTYktjaMWPzFENMkfomSjlrRNYuBPOlGcAxTcpkk3jxl_A6EYApQ78QUpU61BZzcC7WAzfYRbdBrIAwEDad1YDPQs30-7NgFAPQRW_fS0iRfo1nlHTQXrNATcUF9bwvCMI3X2du_5FDlaut1hVLhkI411HeqdyKYiDcGglZ85QB6zg-njUmZU-KBIWi5g4XLwgi3OoM" }
])

// Carousel logic
onMounted(() => {
  const carouselEl = document.getElementById('hero-carousel')
  const prevBtn = document.getElementById('prev-hero')
  const nextBtn = document.getElementById('next-hero')
  const indicatorsContainer = document.getElementById('carousel-indicators')
  if (!carouselEl) return

  const totalSlides = carouselEl.children.length
  let currentIndex = 2
  let autoPlayInterval = null

  function renderIndicators() {
    if (!indicatorsContainer) return
    indicatorsContainer.innerHTML = ''
    for (let i = 0; i < totalSlides; i++) {
      const dot = document.createElement('div')
      dot.className = `w-2 h-2 rounded-full cursor-pointer transition-all duration-300 ${i === currentIndex ? 'bg-white scale-125' : 'bg-white/40'}`
      dot.dataset.index = i
      dot.addEventListener('click', () => {
        currentIndex = i
        updateCarousel()
        resetTimer()
      })
      indicatorsContainer.appendChild(dot)
    }
  }

  function updateCarousel() {
    const slideWidth = carouselEl.clientWidth
    carouselEl.scrollTo({ left: slideWidth * currentIndex, behavior: 'smooth' })
    const dots = document.querySelectorAll('#carousel-indicators div')
    dots.forEach((dot, idx) => {
      if (idx === currentIndex) {
        dot.classList.add('bg-white', 'scale-125')
        dot.classList.remove('bg-white/40')
      } else {
        dot.classList.remove('bg-white', 'scale-125')
        dot.classList.add('bg-white/40')
      }
    })
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides
    updateCarousel()
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides
    updateCarousel()
  }

  function resetTimer() {
    if (autoPlayInterval) clearInterval(autoPlayInterval)
    autoPlayInterval = setInterval(nextSlide, 5000)
  }

  if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetTimer() })
  if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetTimer() })

  carouselEl.addEventListener('scroll', () => {
    const slideWidth = carouselEl.clientWidth
    const newIndex = Math.round(carouselEl.scrollLeft / slideWidth)
    if (newIndex !== currentIndex && newIndex >= 0 && newIndex < totalSlides) {
      currentIndex = newIndex
      const dots = document.querySelectorAll('#carousel-indicators div')
      dots.forEach((dot, idx) => {
        if (idx === currentIndex) {
          dot.classList.add('bg-white', 'scale-125')
          dot.classList.remove('bg-white/40')
        } else {
          dot.classList.remove('bg-white', 'scale-125')
          dot.classList.add('bg-white/40')
        }
      })
    }
  })

  renderIndicators()
  window.addEventListener('resize', () => updateCarousel())
  updateCarousel()
  resetTimer()
})
</script>

<style>
/* ===== CÁC STYLE GỐC TỪ HTML ===== */
.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

.glass-nav {
  backdrop-filter: blur(12px);
  background-color: rgba(251, 249, 245, 0.85);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

#hero-carousel {
  scroll-behavior: smooth;
}

.product-card-hover {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.product-card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
}

.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}

/* ===== BỔ SUNG THEO DESIGN SYSTEM ===== */
.shadow-elevation-1 {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.03);
}
.shadow-elevation-2 {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04), 0 2px 4px rgba(0, 0, 0, 0.02);
}
.shadow-elevation-3 {
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08), 0 4px 12px rgba(0, 0, 0, 0.04);
}
.shadow-elevation-hover {
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.shadow-elevation-hover:hover {
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12), 0 8px 16px rgba(0, 0, 0, 0.06);
  transform: translateY(-4px);
}
.rounded-squircle {
  border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
}
.rounded-asymmetrical {
  border-radius: 3rem 0.5rem 3rem 0.5rem;
}
.hero-image-container {
  border-radius: 2rem 0.5rem 2rem 0.5rem;
  overflow: hidden;
}
.btn-primary {
  background-color: #ff6b00;
  color: #ffffff;
  padding: 0.75rem 1.5rem;
  border-radius: 9999px;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.05em;
  transition: all 0.2s cubic-bezier(0.34, 1.2, 0.64, 1);
}
.btn-primary:hover {
  transform: scale(1.02);
  box-shadow: 0 12px 24px rgba(255, 107, 0, 0.2);
}
.btn-primary:active {
  transform: scale(0.98);
}
.btn-secondary {
  border: 2px solid #ff6b00;
  color: #ff6b00;
  background-color: transparent;
  padding: 0.75rem 1.5rem;
  border-radius: 9999px;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.05em;
  transition: all 0.2s ease;
}
.btn-secondary:hover {
  background-color: rgba(255, 107, 0, 0.1);
  transform: scale(1.02);
}
.input-focus-glow:focus {
  outline: none;
  border-color: #ff6b00;
  box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.2);
}
.checkbox-large {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 0.375rem;
  border: 1px solid #d1d5db;
  accent-color: #ff6b00;
}
.radio-large {
  width: 1.5rem;
  height: 1.5rem;
  accent-color: #ff6b00;
}
.chip {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 500;
}
.chip-primary {
  background-color: rgba(255, 107, 0, 0.1);
  color: #ff6b00;
}
.chip-secondary {
  background-color: rgba(67, 102, 81, 0.1);
  color: #436651;
}
.chip-success {
  background-color: rgba(45, 106, 79, 0.1);
  color: #2d6a4f;
}
.chip-error {
  background-color: rgba(186, 26, 26, 0.1);
  color: #ba1a1a;
}
.font-headline-xl {
  font-size: 3rem;
  line-height: 1.2;
  letter-spacing: -0.02em;
  font-weight: 700;
}
.font-display-lg {
  font-size: 3rem;
  line-height: 1.2;
  letter-spacing: -0.02em;
  font-weight: 700;
}
@media (min-width: 768px) {
  .font-headline-xl,
  .font-display-lg {
    font-size: 4rem;
  }
}
.glass-card {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.transition-smooth {
  transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.1);
}
.product-card-hover:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12);
}
.container-premium {
  max-width: 1280px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 1.25rem;
  padding-right: 1.25rem;
}
@media (min-width: 768px) {
  .container-premium {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}
@media (min-width: 1024px) {
  .container-premium {
    padding-left: 4rem;
    padding-right: 4rem;
  }
}
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}
::-webkit-scrollbar-thumb {
  background: #ff6b00;
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: #e66000;
}
</style>