<template>
  <div>
    <Head title="Khuyến mãi - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="pt-8 bg-gray-50">
      <!-- Hero Promotion Section -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 mb-12">
        <div class="relative h-[400px] md:h-[500px] rounded-xl overflow-hidden shadow-lg group">
          <img alt="Promotion background" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBOGVWpVsLfpXQ1e096jRCQGrdZMWNr3TIgwVp3g2zqPrUsOcSwl54g6ZmhoF-IqagKNET0oKCWNFv0Mk2fL3XAMaFSG3rO2BHFNn2siBRqdzlDaF82ZZtyzmWaAhhWfvPLxVTVPEjQ2HLLJ_--lJSrO3W2eXsXvnMJ_0Kg26bjcIdUqf1LA6swPj5ITElX18cXXHVDYMhm83WOlVNou7ARbbZC-Kn8D29j9QLh15E4kbjvw6MC3sIm7L_SsvEaskpa2hsVKfj_X0Q">
          <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex flex-col justify-center px-6 md:px-12 text-white">
            <h1 class="font-headline-xl text-3xl md:text-5xl font-bold mb-4">ĐẠI TIỆC KHUYẾN MÃI</h1>
            <p class="text-base md:text-lg mb-6 max-w-lg">Sẵn sàng cho mọi hành trình với ưu đãi lên đến 50% cho tất cả dòng sản phẩm Balo & Túi cao cấp.</p>
            <Link :href="route('category', { slug: 'sale' })" class="bg-primary text-white font-bold py-3 px-8 w-fit rounded-full hover:bg-primary-dark transition-all shadow-lg text-sm">
              KHÁM PHÁ NGAY
            </Link>
          </div>
        </div>
      </section>

      <!-- Flash Sale Section -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 mb-12">
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
          <div class="flex items-center space-x-4">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-primary uppercase">FLASH SALE</h2>
            <div class="flex space-x-2">
              <span class="bg-primary text-white p-2 rounded-lg font-bold text-sm">02</span>
              <span class="text-primary font-bold self-center">:</span>
              <span class="bg-primary text-white p-2 rounded-lg font-bold text-sm">45</span>
              <span class="text-primary font-bold self-center">:</span>
              <span class="bg-primary text-white p-2 rounded-lg font-bold text-sm">12</span>
            </div>
          </div>
          <Link :href="route('category', { slug: 'flash-sale' })" class="text-primary font-semibold hover:underline text-sm">Xem tất cả</Link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 items-stretch">
          <div v-for="item in flashItems" :key="item.id" class="group relative bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col h-full border border-gray-100">
            <div class="absolute top-4 left-4 z-10 bg-primary text-white font-bold py-1 px-3 rounded-full text-xs">-{{ item.discount }}%</div>
            <Link :href="route('product.detail', { id: item.id })" class="block">
              <div class="relative h-64 overflow-hidden rounded-lg mb-4">
                <img :src="item.image" class="w-full h-full object-cover transition-transform group-hover:scale-110" :alt="item.name">
              </div>
              <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 min-h-[64px] text-sm">{{ item.name }}</h3>
              <div class="flex items-center space-x-2 mb-4 mt-auto">
                <span class="text-primary font-bold text-lg">{{ formatPrice(item.salePrice) }}</span>
                <span class="text-gray-400 line-through text-sm">{{ formatPrice(item.originalPrice) }}</span>
              </div>
            </Link>
            <button @click="addToCart(item)" class="w-full bg-primary text-white font-bold py-3 rounded-full hover:bg-primary-dark transition-all text-sm">Mua ngay</button>
          </div>
        </div>
      </section>

      <!-- Voucher Coupons Section -->
      <section class="bg-amber-50 py-12 mb-12">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8">
          <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-primary text-center mb-6">MÃ GIẢM GIÁ ĐỘC QUYỀN</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="voucher in vouchers" :key="voucher.code" class="bg-white rounded-lg border-2 border-dashed border-primary p-4 flex items-center justify-between shadow-sm">
              <div>
                <p class="font-semibold text-sm text-primary">{{ voucher.desc }}</p>
                <h4 class="font-bold text-lg text-gray-800">{{ voucher.code }}</h4>
                <p class="text-xs text-gray-500">{{ voucher.condition }}</p>
              </div>
              <button @click="copyCode(voucher.code)" class="text-primary font-bold hover:underline text-sm">Sao chép</button>
            </div>
          </div>
        </div>
      </section>

      <!-- DANH MỤC ĐANG SALE Section -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 mb-12 relative group">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-primary mb-6 uppercase">DANH MỤC ĐANG SALE</h2>
        <div class="relative overflow-hidden">
          <div class="flex space-x-6 no-scrollbar scroll-smooth snap-x snap-mandatory py-4 animate-carousel" style="width: max-content;">
            <div v-for="(cat, idx) in saleCategories" :key="idx" class="min-w-[calc(50%-12px)] h-[400px] relative rounded-lg overflow-hidden snap-start flex-shrink-0 group/item">
              <img :src="cat.image" class="w-full h-full object-cover transition-transform duration-700 group-hover/item:scale-105" :alt="cat.title">
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
              <div class="absolute inset-0 p-6 flex flex-col justify-end text-white">
                <span class="absolute top-6 left-6 bg-primary text-white px-3 py-1 rounded-full text-xs font-bold">{{ cat.badge }}</span>
                <h3 class="font-headline-lg text-xl md:text-2xl font-bold mb-2">{{ cat.title }}</h3>
                <p class="text-sm mb-4 opacity-90">{{ cat.desc }}</p>
                <Link :href="route('category', { slug: cat.slug })" class="bg-primary text-white font-bold py-2 px-6 w-fit rounded-full hover:bg-primary-dark transition-transform hover:scale-105 text-sm">
                  Xem ngay
                </Link>
              </div>
            </div>
          </div>
          <button class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md p-2 rounded-full text-white hover:bg-white/50 transition-colors z-20 hidden md:block">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <button class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/30 backdrop-blur-md p-2 rounded-full text-white hover:bg-white/50 transition-colors z-20 hidden md:block">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

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
  { title: "Balo Leo Núi & Trekking", desc: "Sức chứa lớn, chống thấm nước tuyệt đối cho hành trình dã ngoại.", badge: "SALE 30%", slug: "balo-leo-nui", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuA8EebQr6sPJECc1T3GGDToz48lDIncb-0vEQhAo3stWtZmJLuuNZOzg4LZm_6XH97yhAxDI0OdvMyAY7cJPhALvW6rMbRzIq4WFimYv2JV9cukVswpFL96mvmDfowIobRhmq3aLr4wWp5w5AiXFXjwY24-3GM1kkbkK7rPYXqU2LsnVILDREZY3VQXKKaTmcat9jG9TpclvSnJ3W2yV_Wsh_1JVHxu3HMnUUSlqcKUfn1tn6vZRLiaitVBuKtnln1AATPB6201wtg" },
  { title: "Cặp Công Sở Cao Cấp", desc: "Đẳng cấp doanh nhân hiện đại với thiết kế thanh lịch.", badge: "SALE 20%", slug: "cap-cong-so", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAOxS597mNhtp9RVoldYiUj73FMXgAHj0R5N3Pfx1Murb_nmzPpzKPNWDQuiBVcWoZJa5oDwrd91hp7clFwhd-hIZlFA9fW8ZBfO3N4TpXn17R53dAbdvnxlBSB9_W6fWSR5wxuEKFUHjqga3Xmq4lsscOkalGnxSsFXvEw4jt4ITNiCk-UYzKocqQWUmg_cspeU1kfE8xL6ztycBWRYKXC1OswaLF5-PgugS-r7KKJLUYdO7F_CzGH-DLOYGCSWUIwTFVlVAd3XSI" },
  { title: "Túi Đeo Chéo Năng Động", desc: "Phong cách trẻ trung cho các hoạt động thường nhật.", badge: "SALE 15%", slug: "tui-deo-cheo", image: "https://lh3.googleusercontent.com/aida/ADBb0ui8146wV2gztU_4vlvLAMb5UUIOGCRWF-8s51XbGlJXRzQ4nNbFrK4P3NbXQJT3t5Y1uncL4ZE38ZHXv4IMxuZRoMQ25tOsV6mnr7IrLJh1DoxK-Nm1qzst4h8xfjAK9spnCfMSh25ZKtWZxDRkS_sSfPXN4p7BBYHA7LlkY43p17Bjbv1dfSDfz1fz6g76PUBnfM3MoGFQh2NKjbJ6dwsklxUb0yKPvsARXBq9au6ttP1Lhg79mm6QORk" },
  { title: "Balo Laptop Pro", desc: "Bảo vệ thiết bị tối ưu với ngăn đệm chống sốc cao cấp.", badge: "SALE 25%", slug: "balo-laptop", image: "https://lh3.googleusercontent.com/aida/ADBb0ujq1QPSts-OHFMAdIMnRM9uR3jvYefjjDBvGemR6Xu62Js68BibhKIZEwiq4ifyQQ6cW-oP3tmeNPrRYUl_8nGWrmeRMwDjsWD8qfHJkmbupOChFmNzRY48OtqQjCKplxlccRgXvDgd22N6fs1fm3BaQ9f6gvecnVUJCMKTlaM-XnjDuJXvk0dc733b3jiLW8uJJwSTC4I-ecNmm5-189QKvCUoFjj7nYl-YAq0-LIGNk81JM451-6FT18" },
  { title: "Phụ kiện Du lịch", desc: "Tối ưu hóa hành lý với các phụ kiện thông minh.", badge: "SALE 40%", slug: "phu-kien", image: "https://lh3.googleusercontent.com/aida/ADBb0ui8146wV2gztU_4vlvLAMb5UUIOGCRWF-8s51XbGlJXRzQ4nNbFrK4P3NbXQJT3t5Y1uncL4ZE38ZHXv4IMxuZRoMQ25tOsV6mnr7IrLJh1DoxK-Nm1qzst4h8xfjAK9spnCfMSh25ZKtWZxDRkS_sSfPXN4p7BBYHA7LlkY43p17Bjbv1dfSDfz1fz6g76PUBnfM3MoGFQh2NKjbJ6dwsklxUb0yKPvsARXBq9au6ttP1Lhg79mm6QORk" }
])

const formatPrice = (val) => val.toLocaleString('vi-VN') + '₫'
const copyCode = (code) => { navigator.clipboard.writeText(code); alert(`Đã sao chép: ${code}`) }
const addToCart = (item) => { router.get(route('product.detail', { id: item.id })) }
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.animate-carousel { animation: scroll-carousel 20s linear infinite; }
.animate-carousel:hover { animation-play-state: paused; }
@keyframes scroll-carousel {
  0% { transform: translateX(0); }
  100% { transform: translateX(calc(-50% - 12px)); }
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>