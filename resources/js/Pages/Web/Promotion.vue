<template>
  <div>
    <Head title="Khuyến mãi - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="pt-8 bg-gray-50">
      <!-- Hero Banner Section - Hiển thị từ Banner -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 mb-12">
        <div v-if="banners.length > 0" class="relative h-[400px] md:h-[500px] rounded-xl overflow-hidden shadow-lg group">
          <img 
            :src="banners[0].image || defaultImage" 
            :alt="banners[0].title || 'Promotion banner'" 
            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
          >
          <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex flex-col justify-center px-6 md:px-12 text-white">
            <h1 class="font-headline-xl text-3xl md:text-5xl font-bold mb-4">{{ banners[0].title || 'ĐẠI TIỆC KHUYẾN MÃI' }}</h1>
            <p class="text-base md:text-lg mb-6 max-w-lg">{{ banners[0].description || 'Sẵn sàng cho mọi hành trình với ưu đãi lên đến 50% cho tất cả dòng sản phẩm Balo & Túi cao cấp.' }}</p>
            <Link :href="banners[0].link || route('category', { slug: 'sale' })" class="bg-primary text-white font-bold py-3 px-8 w-fit rounded-full hover:bg-primary-dark transition-all shadow-lg text-sm">
              KHÁM PHÁ NGAY
            </Link>
          </div>
        </div>
        <!-- Fallback nếu không có banner -->
        <div v-else class="relative h-[400px] md:h-[500px] rounded-xl overflow-hidden shadow-lg group bg-gradient-to-r from-primary/80 to-secondary/80">
          <div class="absolute inset-0 flex flex-col justify-center items-center px-6 md:px-12 text-white">
            <h1 class="font-headline-xl text-3xl md:text-5xl font-bold mb-4 text-center">ĐẠI TIỆC KHUYẾN MÃI</h1>
            <p class="text-base md:text-lg mb-6 max-w-lg text-center">Sẵn sàng cho mọi hành trình với ưu đãi lên đến 50% cho tất cả dòng sản phẩm Balo & Túi cao cấp.</p>
            <Link :href="route('category', { slug: 'sale' })" class="bg-white text-primary font-bold py-3 px-8 w-fit rounded-full hover:bg-gray-100 transition-all shadow-lg text-sm">
              KHÁM PHÁ NGAY
            </Link>
          </div>
        </div>
      </section>

      <!-- Flash Sale Section - Hiển thị từ Flash Products -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 mb-12" v-if="flashProducts.length > 0">
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
          <div class="flex items-center space-x-4">
            <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-primary uppercase">FLASH SALE</h2>
            <div class="flex space-x-2">
              <span class="bg-primary text-white p-2 rounded-lg font-bold text-sm">{{ flashTimer.hours }}</span>
              <span class="text-primary font-bold self-center">:</span>
              <span class="bg-primary text-white p-2 rounded-lg font-bold text-sm">{{ flashTimer.minutes }}</span>
              <span class="text-primary font-bold self-center">:</span>
              <span class="bg-primary text-white p-2 rounded-lg font-bold text-sm">{{ flashTimer.seconds }}</span>
            </div>
          </div>
          <Link :href="route('category', { slug: 'flash-sale' })" class="text-primary font-semibold hover:underline text-sm">Xem tất cả</Link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 items-stretch">
          <div v-for="item in flashProducts.slice(0, 4)" :key="item.id" class="group relative bg-white rounded-lg p-4 shadow-sm hover:shadow-md transition-all flex flex-col h-full border border-gray-100">
            <div class="absolute top-4 left-4 z-10 bg-primary text-white font-bold py-1 px-3 rounded-full text-xs">-{{ item.discount_percent }}%</div>
            <Link :href="route('product.detail', { id: item.product_id })" class="block">
              <div class="relative h-64 overflow-hidden rounded-lg mb-4">
                <img :src="item.image || defaultImage" class="w-full h-full object-cover transition-transform group-hover:scale-110" :alt="item.name">
              </div>
              <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 min-h-[64px] text-sm">{{ item.name }}</h3>
              <div class="flex items-center space-x-2 mb-4 mt-auto">
                <span class="text-primary font-bold text-lg">{{ formatPrice(item.sale_price) }}</span>
                <span class="text-gray-400 line-through text-sm">{{ formatPrice(item.original_price) }}</span>
              </div>
            </Link>
            <button @click="addToCart(item)" class="w-full bg-primary text-white font-bold py-3 rounded-full hover:bg-primary-dark transition-all text-sm">Mua ngay</button>
          </div>
        </div>
      </section>

      <!-- Voucher Coupons Section - Hiển thị từ Vouchers -->
      <section class="bg-amber-50 py-12 mb-12">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8">
          <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-primary text-center mb-6">MÃ GIẢM GIÁ ĐỘC QUYỀN</h2>
          <div v-if="vouchers.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="voucher in vouchers" :key="voucher.id" class="bg-white rounded-lg border-2 border-dashed border-primary p-4 flex items-center justify-between shadow-sm">
              <div>
                <p class="font-semibold text-sm text-primary">{{ voucher.discount_text }}</p>
                <h4 class="font-bold text-lg text-gray-800">{{ voucher.code }}</h4>
                <p class="text-xs text-gray-500">{{ voucher.condition_text }}</p>
                <p v-if="voucher.expiry" class="text-xs text-gray-400 mt-1">Hạn: {{ voucher.expiry }}</p>
              </div>
              <button @click="copyCode(voucher.code)" class="text-primary font-bold hover:underline text-sm">Sao chép</button>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-8">
            <p>Hiện chưa có mã giảm giá nào</p>
          </div>
        </div>
      </section>

      <!-- Active Campaigns Section - Chiến dịch đang diễn ra -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 mb-12">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-primary mb-6 uppercase">CHIẾN DỊCH ĐANG DIỄN RA</h2>
        <div v-if="activeCampaigns.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="campaign in activeCampaigns" :key="campaign.id" class="bg-white rounded-xl shadow-sm hover:shadow-md transition-all overflow-hidden border border-gray-100 group">
            <!-- PHẦN BANNER - SỬ DỤNG BANNER TỪ BẢNG BANNERS -->
            <div class="relative h-48 overflow-hidden">
              <!-- Hiển thị banner nếu có -->
              <template v-if="campaign.banner && campaign.banner.image">
                <img 
                  :src="campaign.banner.image" 
                  :alt="campaign.banner.title || campaign.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  @error="handleImageError"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </template>
              <!-- Fallback từ banner_url cũ -->
              <template v-else-if="campaign.banner_url">
                <img 
                  :src="campaign.banner_url" 
                  :alt="campaign.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  @error="handleImageError"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </template>
              <!-- Fallback: sử dụng product_image -->
              <template v-else>
                <img 
                  :src="campaign.product_image || defaultImage" 
                  :alt="campaign.name"
                  class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </template>
              
              <!-- Badge trạng thái -->
              <div class="absolute top-4 right-4">
                <span class="px-3 py-1 rounded-full text-xs font-bold text-white bg-green-500">
                  Đang diễn ra
                </span>
              </div>
              
              <!-- Badge giảm giá -->
              <div v-if="campaign.discount_percent > 0" class="absolute bottom-4 left-4 bg-primary text-white font-bold py-1 px-3 rounded-full text-sm">
                -{{ campaign.discount_percent }}%
              </div>
            </div>
            
            <div class="p-5">
              <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-1">{{ campaign.name }}</h3>
              <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ campaign.description || 'Chiến dịch khuyến mãi đặc biệt' }}</p>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">
                  <i class="fas fa-calendar-alt mr-1"></i>
                  {{ campaign.start_date || 'N/A' }} - {{ campaign.end_date || 'N/A' }}
                </span>
                <span class="text-primary font-semibold">
                  {{ campaign.discount_text }}
                </span>
              </div>
              <div class="mt-3 text-xs text-gray-400">
                {{ campaign.product_count }} sản phẩm tham gia
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-12">
          <p>Hiện chưa có chiến dịch khuyến mãi nào đang diễn ra</p>
        </div>
      </section>

      <!-- DANH MỤC ĐANG SALE Section - Giữ nguyên giao diện -->
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
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// Props từ controller
const props = defineProps({
  banners: {
    type: Array,
    default: () => []
  },
  activeCampaigns: {
    type: Array,
    default: () => []
  },
  flashProducts: {
    type: Array,
    default: () => []
  },
  vouchers: {
    type: Array,
    default: () => []
  },
  error: {
    type: String,
    default: null
  }
})

const defaultImage = 'https://via.placeholder.com/400x400/cccccc/666666?text=No+Image'

// Flash timer
const flashTimer = ref({
  hours: '00',
  minutes: '00',
  seconds: '00'
})

let timerInterval = null

const updateFlashTimer = () => {
  const now = new Date()
  const endOfDay = new Date()
  endOfDay.setHours(23, 59, 59, 999)
  const diff = Math.max(0, Math.floor((endOfDay - now) / 1000))
  
  const hours = Math.floor(diff / 3600)
  const minutes = Math.floor((diff % 3600) / 60)
  const seconds = diff % 60
  
  flashTimer.value = {
    hours: String(hours).padStart(2, '0'),
    minutes: String(minutes).padStart(2, '0'),
    seconds: String(seconds).padStart(2, '0')
  }
}

// Hàm xử lý lỗi ảnh
const handleImageError = (e) => {
  e.target.src = defaultImage
}

// Sale Categories - Giữ nguyên giao diện
const saleCategories = ref([
  { 
    title: "Balo Leo Núi & Trekking", 
    desc: "Sức chứa lớn, chống thấm nước tuyệt đối cho hành trình dã ngoại.", 
    badge: "SALE 30%", 
    slug: "balo-leo-nui", 
    image: "https://lh3.googleusercontent.com/aida-public/AB6AXuA8EebQr6sPJECc1T3GGDToz48lDIncb-0vEQhAo3stWtZmJLuuNZOzg4LZm_6XH97yhAxDI0OdvMyAY7cJPhALvW6rMbRzIq4WFimYv2JV9cukVswpFL96mvmDfowIobRhmq3aLr4wWp5w5AiXFXjwY24-3GM1kkbkK7rPYXqU2LsnVILDREZY3VQXKKaTmcat9jG9TpclvSnJ3W2yV_Wsh_1JVHxu3HMnUUSlqcKUfn1tn6vZRLiaitVBuKtnln1AATPB6201wtg" 
  },
  { 
    title: "Cặp Công Sở Cao Cấp", 
    desc: "Đẳng cấp doanh nhân hiện đại với thiết kế thanh lịch.", 
    badge: "SALE 20%", 
    slug: "cap-cong-so", 
    image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAOxS597mNhtp9RVoldYiUj73FMXgAHj0R5N3Pfx1Murb_nmzPpzKPNWDQuiBVcWoZJa5oDwrd91hp7clFwhd-hIZlFA9fW8ZBfO3N4TpXn17R53dAbdvnxlBSB9_W6fWSR5wxuEKFUHjqga3Xmq4lsscOkalGnxSsFXvEw4jt4ITNiCk-UYzKocqQWUmg_cspeU1kfE8xL6ztycBWRYKXC1OswaLF5-PgugS-r7KKJLUYdO7F_CzGH-DLOYGCSWUIwTFVlVAd3XSI" 
  },
  { 
    title: "Túi Đeo Chéo Năng Động", 
    desc: "Phong cách trẻ trung cho các hoạt động thường nhật.", 
    badge: "SALE 15%", 
    slug: "tui-deo-cheo", 
    image: "https://lh3.googleusercontent.com/aida/ADBb0ui8146wV2gztU_4vlvLAMb5UUIOGCRWF-8s51XbGlJXRzQ4nNbFrK4P3NbXQJT3t5Y1uncL4ZE38ZHXv4IMxuZRoMQ25tOsV6mnr7IrLJh1DoxK-Nm1qzst4h8xfjAK9spnCfMSh25ZKtWZxDRkS_sSfPXN4p7BBYHA7LlkY43p17Bjbv1dfSDfz1fz6g76PUBnfM3MoGFQh2NKjbJ6dwsklxUb0yKPvsARXBq9au6ttP1Lhg79mm6QORk" 
  },
  { 
    title: "Balo Laptop Pro", 
    desc: "Bảo vệ thiết bị tối ưu với ngăn đệm chống sốc cao cấp.", 
    badge: "SALE 25%", 
    slug: "balo-laptop", 
    image: "https://lh3.googleusercontent.com/aida/ADBb0ujq1QPSts-OHFMAdIMnRM9uR3jvYefjjDBvGemR6Xu62Js68BibhKIZEwiq4ifyQQ6cW-oP3tmeNPrRYUl_8nGWrmeRMwDjsWD8qfHJkmbupOChFmNzRY48OtqQjCKplxlccRgXvDgd22N6fs1fm3BaQ9f6gvecnVUJCMKTlaM-XnjDuJXvk0dc733b3jiLW8uJJwSTC4I-ecNmm5-189QKvCUoFjj7nYl-YAq0-LIGNk81JM451-6FT18" 
  },
  { 
    title: "Phụ kiện Du lịch", 
    desc: "Tối ưu hóa hành lý với các phụ kiện thông minh.", 
    badge: "SALE 40%", 
    slug: "phu-kien", 
    image: "https://lh3.googleusercontent.com/aida/ADBb0ui8146wV2gztU_4vlvLAMb5UUIOGCRWF-8s51XbGlJXRzQ4nNbFrK4P3NbXQJT3t5Y1uncL4ZE38ZHXv4IMxuZRoMQ25tOsV6mnr7IrLJh1DoxK-Nm1qzst4h8xfjAK9spnCfMSh25ZKtWZxDRkS_sSfPXN4p7BBYHA7LlkY43p17Bjbv1dfSDfz1fz6g76PUBnfM3MoGFQh2NKjbJ6dwsklxUb0yKPvsARXBq9au6ttP1Lhg79mm6QORk" 
  }
])

// Hàm format giá
const formatPrice = (val) => {
  if (!val) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

// Hàm copy code
const copyCode = (code) => {
  navigator.clipboard.writeText(code)
    .then(() => {
      alert(`Đã sao chép mã: ${code}`)
    })
    .catch(() => {
      alert(`Mã giảm giá: ${code}`)
    })
}

// Hàm thêm vào giỏ hàng / xem chi tiết
const addToCart = (item) => {
  router.get(route('product.detail', { id: item.product_id }))
}

// Lifecycle
onMounted(() => {
  updateFlashTimer()
  timerInterval = setInterval(updateFlashTimer, 1000)
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})
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
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>