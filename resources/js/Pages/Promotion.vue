<template>
  <div>
    <Head title="Khuyến mãi - BigBag Premium Utility Carry Gear" />

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
            <Link :href="route('promotion')" class="font-label-md text-label-md text-primary border-b-2 border-primary pb-1">Khuyến mãi</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">Giới thiệu</Link>
            <Link class="font-label-md text-label-md text-on-surface-variant hover:text-primary" href="#">New Arrivals</Link>
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
            <span class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">2</span>
          </Link>
        </div>
      </nav>
    </header>

    <main class="pt-8">
      <!-- Hero Promotion Section -->
      <section class="max-w-7xl mx-auto px-margin-desktop mb-xl">
        <div class="relative h-[500px] rounded-xl overflow-hidden shadow-lg group">
          <img alt="Forest background" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBOGVWpVsLfpXQ1e096jRCQGrdZMWNr3TIgwVp3g2zqPrUsOcSwl54g6ZmhoF-IqagKNET0oKCWNFv0Mk2fL3XAMaFSG3rO2BHFNn2siBRqdzlDaF82ZZtyzmWaAhhWfvPLxVTVPEjQ2HLLJ_--lJSrO3W2eXsXvnMJ_0Kg26bjcIdUqf1LA6swPj5ITElX18cXXHVDYMhm83WOlVNou7ARbbZC-Kn8D29j9QLh15E4kbjvw6MC3sIm7L_SsvEaskpa2hsVKfj_X0Q">
          <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex flex-col justify-center px-lg text-white">
            <h1 class="font-headline-xl text-headline-xl mb-base">ĐẠI TIỆC KHUYẾN MÃI</h1>
            <p class="font-body-lg text-body-lg mb-lg max-w-lg">Sẵn sàng cho mọi hành trình với ưu đãi lên đến 50% cho tất cả dòng sản phẩm Balo & Túi cao cấp.</p>
            <button class="bg-primary text-white font-bold py-4 px-10 w-fit rounded-full hover:scale-105 transition-all active:scale-95 shadow-lg">KHÁM PHÁ NGAY</button>
          </div>
        </div>
      </section>

      <!-- Flash Sale Section -->
      <section class="max-w-7xl mx-auto px-margin-desktop mb-xl">
        <div class="flex items-center justify-between mb-lg">
          <div class="flex items-center space-x-md">
            <h2 class="font-headline-lg text-headline-lg text-primary uppercase">FLASH SALE</h2>
            <div class="flex space-x-xs">
              <span class="bg-primary text-white p-2 rounded-lg font-bold">02</span><span class="text-primary font-bold self-center">:</span>
              <span class="bg-primary text-white p-2 rounded-lg font-bold">45</span><span class="text-primary font-bold self-center">:</span>
              <span class="bg-primary text-white p-2 rounded-lg font-bold">12</span>
            </div>
          </div>
          <a class="text-primary font-semibold hover:underline" href="#">Xem tất cả</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-gutter items-stretch">
          <div v-for="item in flashItems" :key="item.id" class="group relative bg-surface-container-low rounded-lg p-md shadow-sm hover:shadow-md transition-all flex flex-col h-full">
            <div class="absolute top-4 left-4 z-10 bg-primary text-white font-bold py-1 px-3 rounded-full text-sm">-{{ item.discount }}%</div>
            <div class="relative h-64 overflow-hidden rounded-lg mb-sm"><img :src="item.image" class="w-full h-full object-cover transition-transform group-hover:scale-110" :alt="item.name"></div>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-2 line-clamp-2 min-h-[64px]">{{ item.name }}</h3>
            <div class="flex items-center space-x-sm mb-md mt-auto"><span class="text-primary font-bold text-lg">{{ formatPrice(item.salePrice) }}</span><span class="text-on-surface-variant line-through text-sm">{{ formatPrice(item.originalPrice) }}</span></div>
            <button class="w-full bg-primary text-white font-bold py-3 rounded-full hover:scale-[1.02] active:scale-95 transition-all">Mua ngay</button>
          </div>
        </div>
      </section>

      <!-- Voucher Coupons Section -->
      <section class="bg-primary-fixed/30 py-xl mb-xl">
        <div class="max-w-7xl mx-auto px-margin-desktop">
          <h2 class="font-headline-lg text-headline-lg text-primary text-center mb-lg">MÃ GIẢM GIÁ ĐỘC QUYỀN</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div v-for="voucher in vouchers" :key="voucher.code" class="bg-surface rounded-lg border-2 border-dashed border-primary p-md flex items-center justify-between shadow-sm">
              <div><p class="font-label-md text-label-md text-primary">{{ voucher.desc }}</p><h4 class="font-headline-md text-headline-md">{{ voucher.code }}</h4><p class="text-xs text-on-surface-variant">{{ voucher.condition }}</p></div>
              <button @click="copyCode(voucher.code)" class="text-primary font-bold hover:underline">Sao chép</button>
            </div>
          </div>
        </div>
      </section>

      <!-- DANH MỤC ĐANG SALE Section (carousel) -->
      <section class="max-w-7xl mx-auto px-margin-desktop mb-xl relative group">
        <h2 class="font-headline-lg text-headline-lg text-primary mb-lg uppercase">DANH MỤC ĐANG SALE</h2>
        <div class="relative overflow-hidden">
          <div class="flex space-x-gutter no-scrollbar scroll-smooth snap-x snap-mandatory py-4 animate-carousel" style="width: max-content;">
            <div v-for="(cat, idx) in saleCategories" :key="idx" class="min-w-[calc(50%-12px)] h-[450px] relative rounded-lg overflow-hidden snap-start flex-shrink-0 group/item">
              <img :src="cat.image" class="w-full h-full object-cover transition-transform duration-700 group-hover/item:scale-105" :alt="cat.title">
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
              <div class="absolute inset-0 p-lg flex flex-col justify-end text-white">
                <span class="absolute top-6 left-6 bg-primary text-white px-3 py-1 rounded-full text-xs font-bold">{{ cat.badge }}</span>
                <h3 class="font-headline-lg text-headline-lg mb-2">{{ cat.title }}</h3>
                <p class="font-body-md text-body-md mb-md opacity-90">{{ cat.desc }}</p>
                <button class="bg-primary text-white font-bold py-2 px-8 w-fit rounded-full transition-transform hover:scale-105">Xem ngay</button>
              </div>
            </div>
          </div>
          <button class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md p-2 rounded-full text-white hover:bg-white/50 transition-colors z-20"><span class="material-symbols-outlined">chevron_left</span></button>
          <button class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md p-2 rounded-full text-white hover:bg-white/50 transition-colors z-20"><span class="material-symbols-outlined">chevron_right</span></button>
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
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const flashItems = ref([
  { id: 1, name: "Balo Pro-Adventurer", salePrice: 1200000, originalPrice: 2000000, discount: 40, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDKCfoSzLMJrOxoHCA3HhoGLKcBD41mK1GiHo2rLvQqPfSDlu-iahA2iuCYG3FJDmGBl32Eq8xRBwW7WTKPl2BigNWLHyLH6uHEAc6FLqY8knmx_85YLpOOguaESmWmXVZywiadplzMKqeL-5xgxQhmUg4yYFqLQa8H_AACoZb3an6NGiQFheINurt9rEw70lX-05QMAGEsKFccFhoh_Sh-T9L3FkeWvgsiA_ut4yvwWd64HTv_b1aRrTL1g6KiNN4Nnn4XSDefVm0" },
  { id: 2, name: "Túi Đeo Urban Lite", salePrice: 850000, originalPrice: 1135000, discount: 25, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCvEDV1gsTxEIibIDu4S3QzNtSVp4GBZsBBkh9DtJNFMqQVKZarzzovagKQ2-rpY-P0m8JCygvQGs6IUjAW0YNq28zBNwJJ2_Yw6HM5gfw456itxkJq2lZHwsYJc6lf9uy0jUiVdmzSCU0SAxkI_poJReAYvx2ZwLcYWITiv4DWBljbpj4nPAST83-RoMjniZOfvQ36HL6oJ_hT-mKR2F1kHURPsCZs2Fz7rUTiq7XVhnlBjETiiMHTKH0BMT5nbKWNodrzdwA5B8U" },
  { id: 3, name: "Túi Du Lịch Nomad", salePrice: 1500000, originalPrice: 3000000, discount: 50, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBH-DP7FYlcPM4RrlDSZA3TWeYCu1gXCNrq-k0KcEQ9_QutwRHPU-bhZhIkh53wzoThREL0YhVruTvwLSE_0MCihlrrbWU9VB-8P44Z-MBz0Me7tJ81e2IYjToodSlNd8Q68zEYazimeHIV1g6ST-DwaWg4AH2DWbI3vAxmRgH6worof33qyIEWVMbcnlUYeNf0mTIKDBEZqOBmm97d4EEYvBB4TUU48-loBgTnpDV8iPx7oWOCEhzlp_knh-evc9YXn2KOKZHCYX0" },
  { id: 4, name: "Balo Camera Pro", salePrice: 2100000, originalPrice: 3000000, discount: 30, image: "https://lh3.googleusercontent.com/aida-public/AB6AXuARsoA0xltUBXuQ8xmaNtA5fZeFM2M1BToc1VAvBUl_r5xcSNbJgJhGOqYrvrsmA1gQUitXLz9tKrKw6wVkITTrcp2kK8vav1hjRFNPxqfrA1n8bdieJ9Z2ROnTqCFduPoeAgRjZRcLU3zS-dbF8cgrpmh3els2Vlfr9dZzZJkN4MHqHPvW00WKTB50IyyRkQNOwgxQd4Xa24BOrG6xnX3033_i7pdP94v6kDMkQet0v0cL2msQ_AU5iQbt4NsajdY6Q6_7WuxUfZA" }
])

const vouchers = ref([
  { code: "BIGBAG50", desc: "GIẢM 50K", condition: "Cho đơn từ 500k" },
  { code: "SUMMER100", desc: "GIẢM 100K", condition: "Cho đơn từ 1.2M" },
  { code: "FREEWILD", desc: "FREESHIP", condition: "Đơn hàng toàn quốc" }
])

const saleCategories = ref([
  { title: "Balo Leo Núi & Trekking", desc: "Sức chứa lớn, chống thấm nước tuyệt đối cho hành trình dã ngoại.", badge: "SALE 30%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuA8EebQr6sPJECc1T3GGDToz48lDIncb-0vEQhAo3stWtZmJLuuNZOzg4LZm_6XH97yhAxDI0OdvMyAY7cJPhALvW6rMbRzIq4WFimYv2JV9cukVswpFL96mvmDfowIobRhmq3aLr4wWp5w5AiXFXjwY24-3GM1kkbkK7rPYXqU2LsnVILDREZY3VQXKKaTmcat9jG9TpclvSnJ3W2yV_Wsh_1JVHxu3HMnUUSlqcKUfn1tn6vZRLiaitVBuKtnln1AATPB6201wtg" },
  { title: "Cặp Công Sở Cao Cấp", desc: "Đẳng cấp doanh nhân hiện đại với thiết kế thanh lịch.", badge: "SALE 20%", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAOxS597mNhtp9RVoldYiUj73FMXgAHj0R5N3Pfx1Murb_nmzPpzKPNWDQuiBVcWoZJa5oDwrd91hp7clFwhd-hIZlFA9fW8ZBfO3N4TpXn17R53dAbdvnxlBSB9_W6fWSR5wxuEKFUHjqga3Xmq4lsscOkalGnxSsFXvEw4jt4ITNiCk-UYzKocqQWUmg_cspeU1kfE8xL6ztycBWRYKXC1OswaLF5-PgugS-r7KKJLUYdO7F_CzGH-DLOYGCSWUIwTFVlVAd3XSI" },
  { title: "Túi Đeo Chéo Năng Động", desc: "Phong cách trẻ trung cho các hoạt động thường nhật.", badge: "SALE 15%", image: "https://lh3.googleusercontent.com/aida/ADBb0ui8146wV2gztU_4vlvLAMb5UUIOGCRWF-8s51XbGlJXRzQ4nNbFrK4P3NbXQJT3t5Y1uncL4ZE38ZHXv4IMxuZRoMQ25tOsV6mnr7IrLJh1DoxK-Nm1qzst4h8xfjAK9spnCfMSh25ZKtWZxDRkS_sSfPXN4p7BBYHA7LlkY43p17Bjbv1dfSDfz1fz6g76PUBnfM3MoGFQh2NKjbJ6dwsklxUb0yKPvsARXBq9au6ttP1Lhg79mm6QORk" },
  { title: "Balo Laptop Pro", desc: "Bảo vệ thiết bị tối ưu với ngăn đệm chống sốc cao cấp.", badge: "SALE 25%", image: "https://lh3.googleusercontent.com/aida/ADBb0ujq1QPSts-OHFMAdIMnRM9uR3jvYefjjDBvGemR6Xu62Js68BibhKIZEwiq4ifyQQ6cW-oP3tmeNPrRYUl_8nGWrmeRMwDjsWD8qfHJkmbupOChFmNzRY48OtqQjCKplxlccRgXvDgd22N6fs1fm3BaQ9f6gvecnVUJCMKTlaM-XnjDuJXvk0dc733b3jiLW8uJJwSTC4I-ecNmm5-189QKvCUoFjj7nYl-YAq0-LIGNk81JM451-6FT18" },
  { title: "Phụ kiện Du lịch", desc: "Tối ưu hóa hành lý với các phụ kiện thông minh.", badge: "SALE 40%", image: "https://lh3.googleusercontent.com/aida/ADBb0ui8146wV2gztU_4vlvLAMb5UUIOGCRWF-8s51XbGlJXRzQ4nNbFrK4P3NbXQJT3t5Y1uncL4ZE38ZHXv4IMxuZRoMQ25tOsV6mnr7IrLJh1DoxK-Nm1qzst4h8xfjAK9spnCfMSh25ZKtWZxDRkS_sSfPXN4p7BBYHA7LlkY43p17Bjbv1dfSDfz1fz6g76PUBnfM3MoGFQh2NKjbJ6dwsklxUb0yKPvsARXBq9au6ttP1Lhg79mm6QORk" }
])

const formatPrice = (val) => { return val.toLocaleString('vi-VN') + '₫' }
const copyCode = (code) => { navigator.clipboard.writeText(code); alert(`Đã sao chép: ${code}`) }
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.animate-carousel { animation: scroll-carousel 20s linear infinite; }
.animate-carousel:hover { animation-play-state: paused; }
@keyframes scroll-carousel {
  0% { transform: translateX(0); }
  100% { transform: translateX(calc(-50% - 12px)); }
}
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