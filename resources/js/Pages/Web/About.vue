<template>
  <div>
    <Head title="Giới thiệu - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <!-- Hero Section với background ảnh -->
    <section 
      class="relative py-20 md:py-28 bg-cover bg-center bg-no-repeat"
      style="background-image: url('https://uploads-ssl.webflow.com/63a782738d62e63a29c33d8a/63a782fb90b0ca654bb47ec4_banner-bigbag.jpg');"
    >
      <!-- Overlay tối để chữ nổi bật -->
      <div class="absolute inset-0 bg-black/50"></div>
      
      <div class="relative z-10 max-w-[1440px] mx-auto px-4 text-center text-white">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">Về Chúng Tôi</h1>
        <p class="text-lg md:text-xl max-w-3xl mx-auto text-white/90">
          BigBag Premium – Hành trình đồng hành cùng những chuyến đi đẳng cấp
        </p>
      </div>
    </section>

    <!-- Nội dung chính -->
    <section class="py-16 bg-white">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Câu Chuyện Của BigBag</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
              BigBag Premium được thành lập với sứ mệnh mang đến những sản phẩm carry gear cao cấp, 
              bền bỉ và đầy phong cách cho những người yêu thích khám phá. Từ những chiếc túi du lịch 
              cho đến ba lô đa năng, chúng tôi chú trọng từng chi tiết nhỏ nhất để tạo nên giá trị thực 
              cho khách hàng.
            </p>
            <p class="text-gray-600 leading-relaxed">
              Với phương châm <span class="font-semibold text-primary">"Premium Utility"</span>, 
              BigBag không chỉ là một thương hiệu, mà còn là người bạn đồng hành đáng tin cậy 
              trên mọi hành trình.
            </p>
          </div>
          <div class="relative">
            <img 
              src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2A-G246mAdLRoOsm83Solt-V_P1koKafC2XnQ_Qk6m1zGNxOgJpKVatQy&s=10" 
              alt="Về BigBag" 
              class="rounded-2xl shadow-lg w-full object-cover h-80 md:h-96"
              @error="handleImageError"
            />
            <!-- Placeholder nếu ảnh không có -->
            <div v-if="imageError" class="absolute inset-0 bg-gray-200 rounded-2xl flex items-center justify-center text-gray-400">
              <span class="material-symbols-outlined text-6xl">business</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sứ mệnh & Tầm nhìn với background ảnh nhẹ -->
    <section 
      class="relative py-16 bg-cover bg-center bg-fixed"
      style="background-image: url('https://images.unsplash.com/photo-1491637639811-60e2756cc1c7?w=1600&h=600&fit=crop');"
    >
      <!-- Overlay trắng mờ -->
      <div class="absolute inset-0 bg-white/85"></div>
      
      <div class="relative z-10 max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Sứ Mệnh &amp; Tầm Nhìn</h2>
          <p class="text-gray-500">Định hướng phát triển bền vững của chúng tôi</p>
        </div>
        <div class="grid md:grid-cols-2 gap-10">
          <div class="bg-white/90 backdrop-blur-sm p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mb-4">
              <span class="material-symbols-outlined text-3xl text-primary">rocket</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Sứ Mệnh</h3>
            <p class="text-gray-600 leading-relaxed">
              Cung cấp giải pháp carry gear thông minh, bền vững, giúp khách hàng tối ưu hóa trải nghiệm 
              di chuyển và khám phá, đồng thời bảo vệ môi trường qua việc sử dụng vật liệu tái chế và 
              quy trình sản xuất thân thiện.
            </p>
          </div>
          <div class="bg-white/90 backdrop-blur-sm p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center mb-4">
              <span class="material-symbols-outlined text-3xl text-primary">visibility</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Tầm Nhìn</h3>
            <p class="text-gray-600 leading-relaxed">
              Trở thành thương hiệu carry gear hàng đầu Đông Nam Á, được công nhận bởi sự sáng tạo, 
              chất lượng và dịch vụ khách hàng xuất sắc, truyền cảm hứng cho một cộng đồng yêu thích 
              khám phá và phong cách sống năng động.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Giá trị cốt lõi -->
    <section class="py-16 bg-white">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Giá Trị Cốt Lõi</h2>
          <p class="text-gray-500">Những nguyên tắc định hình mọi hành động của chúng tôi</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center group">
            <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition">
              <span class="material-symbols-outlined text-4xl text-primary">verified</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Chất Lượng</h4>
            <p class="text-sm text-gray-500">Sản phẩm được kiểm định nghiêm ngặt, bền bỉ theo thời gian</p>
          </div>
          <div class="text-center group">
            <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition">
              <span class="material-symbols-outlined text-4xl text-primary">eco</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Bền Vững</h4>
            <p class="text-sm text-gray-500">Cam kết sử dụng vật liệu tái chế và giảm thiểu tác động môi trường</p>
          </div>
          <div class="text-center group">
            <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition">
              <span class="material-symbols-outlined text-4xl text-primary">lightbulb</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Sáng Tạo</h4>
            <p class="text-sm text-gray-500">Không ngừng cải tiến thiết kế để đáp ứng nhu cầu đa dạng</p>
          </div>
          <div class="text-center group">
            <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition">
              <span class="material-symbols-outlined text-4xl text-primary">groups</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Khách Hàng</h4>
            <p class="text-sm text-gray-500">Luôn đặt lợi ích và trải nghiệm khách hàng lên hàng đầu</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Đội ngũ -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Đội Ngũ Của Chúng Tôi</h2>
          <p class="text-gray-500">Những con người tạo nên thương hiệu BigBag</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto mb-4 overflow-hidden">
              <img 
                src="https://i.pravatar.cc/150?img=1" 
                alt="CEO" 
                class="w-full h-full object-cover" 
                @error="handleImageError"
              />
            </div>
            <h4 class="font-semibold text-gray-900">Nguyễn Văn A</h4>
            <p class="text-sm text-gray-500">CEO &amp; Founder</p>
          </div>
          <div class="text-center">
            <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto mb-4 overflow-hidden">
              <img 
                src="https://i.pravatar.cc/150?img=5" 
                alt="Designer" 
                class="w-full h-full object-cover" 
                @error="handleImageError"
              />
            </div>
            <h4 class="font-semibold text-gray-900">Trần Thị B</h4>
            <p class="text-sm text-gray-500">Trưởng thiết kế</p>
          </div>
          <div class="text-center">
            <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto mb-4 overflow-hidden">
              <img 
                src="https://i.pravatar.cc/150?img=11" 
                alt="Marketing" 
                class="w-full h-full object-cover" 
                @error="handleImageError"
              />
            </div>
            <h4 class="font-semibold text-gray-900">Lê Văn C</h4>
            <p class="text-sm text-gray-500">Trưởng marketing</p>
          </div>
          <div class="text-center">
            <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto mb-4 overflow-hidden">
              <img 
                src="https://i.pravatar.cc/150?img=9" 
                alt="Operation" 
                class="w-full h-full object-cover" 
                @error="handleImageError"
              />
            </div>
            <h4 class="font-semibold text-gray-900">Phạm Thị D</h4>
            <p class="text-sm text-gray-500">Giám đốc vận hành</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Thông tin liên hệ -->
    <section class="py-16 bg-white">
      <div class="max-w-[1440px] mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Liên Hệ</h2>
          <p class="text-gray-500">Chúng tôi luôn sẵn sàng lắng nghe bạn</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="flex flex-col items-center p-6 bg-gray-50 rounded-2xl">
            <span class="material-symbols-outlined text-4xl text-primary mb-3">location_on</span>
            <h4 class="font-semibold text-gray-900">Địa chỉ</h4>
            <p class="text-gray-600 text-sm text-center">123 Đường ABC, Quận 1, TP. Hồ Chí Minh</p>
          </div>
          <div class="flex flex-col items-center p-6 bg-gray-50 rounded-2xl">
            <span class="material-symbols-outlined text-4xl text-primary mb-3">phone</span>
            <h4 class="font-semibold text-gray-900">Hotline</h4>
            <p class="text-gray-600 text-sm">1900 1234</p>
          </div>
          <div class="flex flex-col items-center p-6 bg-gray-50 rounded-2xl">
            <span class="material-symbols-outlined text-4xl text-primary mb-3">email</span>
            <h4 class="font-semibold text-gray-900">Email</h4>
            <p class="text-gray-600 text-sm">info@bigbag.vn</p>
          </div>
        </div>
      </div>
    </section>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const DEFAULT_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23f3f4f6"/%3E%3Ctext x="200" y="195" font-family="Arial" font-size="20" text-anchor="middle" fill="%239ca3af"%3ENo Image%3C/text%3E%3Ctext x="200" y="225" font-family="Arial" font-size="14" text-anchor="middle" fill="%23d1d5db"%3EProduct%3C/text%3E%3C/svg%3E'

const imageError = ref(false)

const handleImageError = (e) => {
  if (e.target.src === DEFAULT_IMAGE) {
    e.target.style.display = 'none'
    imageError.value = true
    return
  }
  e.target.src = DEFAULT_IMAGE
  e.target.onerror = null
}
</script>