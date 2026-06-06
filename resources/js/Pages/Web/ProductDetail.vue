<template>
  <div>
    <Head :title="product.name" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-6 bg-gray-50">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-6 text-gray-500 text-sm">
        <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link :href="route('category', { slug: 'business' })" class="hover:text-primary">Business</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-gray-800 font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <div class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar">
            <div v-for="(thumb, idx) in product.thumbnails" :key="idx" 
                 class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-white"
                 :class="idx === activeThumb ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                 @click="activeThumb = idx">
              <img :src="thumb" class="w-full h-full object-cover" alt="Thumbnail">
            </div>
          </div>
          <div class="flex-1 aspect-[4/5] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <img :src="product.thumbnails[activeThumb]" class="w-full h-full object-cover" alt="Main product">
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <div>
            <span class="inline-block px-3 py-1 bg-primary text-white text-xs rounded-full mb-2 uppercase font-bold">Sản Phẩm Mới</span>
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-amber-400 mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined text-base" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-gray-500 text-sm ml-2">({{ product.reviewCount }} đánh giá)</span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-baseline gap-3">
              <span class="font-headline-md text-2xl text-primary font-bold">{{ product.price }}</span>
              <span class="text-gray-400 line-through text-sm">{{ product.oldPrice }}</span>
              <span class="text-red-500 font-bold text-sm">{{ product.discount }}</span>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">Thiết kế tối giản, chất liệu vải Ballistic Nylon chống nước tuyệt đối. Ngăn laptop 16 inch riêng biệt với đệm bảo vệ 360 độ. Lựa chọn hàng đầu cho các chuyến công tác chuyên nghiệp.</p>
          </div>

          <!-- Size selection -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Kích thước (Size):</span>
            <div class="flex gap-3">
              <button v-for="size in product.sizes" :key="size" 
                      class="px-6 py-2 border-2 rounded-xl text-sm transition-all"
                      :class="selectedSize === size ? 'border-primary text-primary bg-amber-50' : 'border-gray-200 text-gray-600 hover:border-primary'"
                      @click="selectedSize = size">{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3">
              <button v-for="color in product.colors" :key="color.value" 
                      class="w-10 h-10 rounded-full border-2 p-1"
                      :class="selectedColor === color.value ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                      @click="selectedColor = color.value; selectedColorName = color.label">
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col gap-3 py-6">
            <div class="grid grid-cols-2 gap-3">
              <button @click="addToCart" class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">shopping_cart</span> Thêm vào giỏ hàng
              </button>
              <Link :href="route('checkout')" class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">event_repeat</span> Đặt hàng trước
              </Link>
            </div>
            <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
              <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
            </Link>
          </div>

          <!-- Features list -->
          <div class="bg-gray-50 p-5 rounded-xl space-y-3 border border-gray-100">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-gray-600 text-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Product Highlights -->
      <section class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Đặc điểm nổi bật</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 bg-white rounded-2xl p-8 flex flex-col justify-between group overflow-hidden border border-gray-100 shadow-sm">
            <div>
              <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-3">Vật liệu siêu bền</h3>
              <p class="text-gray-600 max-w-md">Sử dụng vải Nylon 1680D có độ bền kéo cực cao, chống mài mòn và thấm nước tuyệt đối.</p>
            </div>
            <img alt="Material" class="w-full h-48 object-cover rounded-xl mt-6 group-hover:scale-105 transition-transform duration-500" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAAXkYc03HJQmTinK1YAQbj736ihy99OstGxMcUxfWyDse1xtCXg628v2N8vSfTXVOHSiaOscLyeVJWCULvAkl2DZNGbcFY61CXOK0Qvc3SDDq5GnTDdUapS_7qmce8NhJ5yu68yhMSt_ejolkow3sghIYvDw_hwUTmAKrrzVQU7SEDxad6b7kyBmB7Rj06_r49-hBFawQJtCo8Q-rYddCiDj_V1vpZWFZMtA9BOH73zqME0z-wW07uXfYHhwQF9j2QON12Tc4CJBKH">
          </div>
          <div class="bg-primary text-white rounded-2xl p-8 flex flex-col items-center text-center justify-center shadow-xl shadow-primary/10">
            <span class="material-symbols-outlined text-6xl mb-4">laptop_mac</span>
            <h3 class="font-headline-md text-xl font-bold mb-2 text-white">Ngăn Laptop 16"</h3>
            <p class="text-white/80 text-sm">Đệm chống sốc dạng tổ ong bảo vệ thiết bị tối đa khỏi va đập mạnh từ mọi phía.</p>
          </div>
          <div class="bg-white rounded-2xl p-8 text-center flex flex-col items-center border border-gray-100 shadow-sm">
            <span class="material-symbols-outlined text-6xl mb-4 text-primary">lock</span>
            <h3 class="font-headline-md text-xl font-bold text-gray-800 mb-2">An toàn tuyệt đối</h3>
            <p class="text-gray-600 text-sm">Ngăn bí mật mặt lưng để điện thoại và hộ chiếu, cùng dây kéo YKK chống trộm.</p>
          </div>
          <div class="md:col-span-2 text-white rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 bg-gray-800">
            <div class="flex-1">
              <h3 class="font-headline-md text-xl font-bold mb-3 text-white">Tùy biến theo chất riêng</h3>
              <p class="text-sm mb-6 text-white/80">Dịch vụ in/khắc logo doanh nghiệp: Tải lên hình ảnh logo, chọn vị trí in (trước, sau, quai đeo) và để lại lời nhắn chi tiết cho chúng tôi.</p>
              <Link :href="route('customize')" class="px-8 py-3 bg-white text-primary rounded-xl hover:bg-opacity-90 transition-colors font-bold text-sm">
                Yêu cầu In Logo & Tùy chỉnh
              </Link>
            </div>
            <div class="w-32 h-32 flex items-center justify-center rounded-full shadow-lg bg-primary">
              <span class="material-symbols-outlined text-6xl text-white">brush</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Products -->
      <section class="mt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center">Các sản phẩm liên quan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="item in relatedProducts" :key="item.id" class="flex flex-col group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-100">
            <Link :href="route('product.detail', { id: item.id })" class="block">
              <div class="aspect-[3/4] bg-gray-100 overflow-hidden relative">
                <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" :alt="item.name">
              </div>
              <div class="p-4">
                <span class="text-gray-500 text-xs uppercase mb-1 block">{{ item.brand }}</span>
                <h3 class="font-semibold text-gray-800 mb-2 truncate">{{ item.name }}</h3>
                <div class="flex items-center gap-2 mb-4">
                  <span class="font-bold text-primary">{{ item.price }}</span>
                </div>
              </div>
            </Link>
            <div class="px-4 pb-4">
              <button @click="addToCartSimple(item)" class="w-full py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 text-sm">
                <span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ hàng
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section class="mt-16 border-t border-gray-200 pt-16">
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8">Đánh giá từ khách hàng</h2>
        <div class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center gap-1 text-amber-400 mb-1">
                  <span v-for="n in 5" :key="n" class="material-symbols-outlined text-sm" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
                </div>
                <span class="font-semibold text-gray-800">{{ review.author }}</span>
              </div>
              <span class="text-gray-400 text-sm">{{ review.date }}</span>
            </div>
            <p class="text-gray-600 text-sm">{{ review.content }}</p>
          </div>
        </div>
        <button class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-semibold text-sm hover:bg-primary/5 transition-all">
          Xem tất cả {{ totalReviews }} đánh giá
        </button>
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

const props = defineProps({
  id: Number
})

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
  router.get(route('cart'))
}

const addToCartSimple = (item) => {
  router.get(route('product.detail', { id: item.id }))
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E85D04; border-radius: 10px; }
</style>