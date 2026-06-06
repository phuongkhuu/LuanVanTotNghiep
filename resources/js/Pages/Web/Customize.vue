<template>
  <div>
    <Head title="Tùy chỉnh sản phẩm - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-12 bg-gray-50">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Side: Customization Form -->
        <section class="flex-1 space-y-6">
          <div class="space-y-2">
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900">Tùy chỉnh sản phẩm</h1>
            <p class="text-gray-600 text-sm">Cá nhân hóa chiếc balo của bạn với logo hoặc hình ảnh riêng biệt. Đội ngũ thiết kế của chúng tôi sẽ xem xét và phản hồi trong vòng 24h.</p>
          </div>

          <form class="space-y-4 bg-white p-6 rounded-xl border border-gray-100 shadow-sm" @submit.prevent="submitRequest">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Họ và tên</label>
                <input class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="Nhập tên của bạn" type="text" v-model="form.fullName">
              </div>
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Email</label>
                <input class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="email@example.com" type="email" v-model="form.email">
              </div>
              <div class="md:col-span-2">
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Số điện thoại</label>
                <input class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="090 xxx xxxx" type="tel" v-model="form.phone">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Vị trí in</label>
                <select class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50 text-gray-700" v-model="form.position">
                  <option value="">Chọn vị trí</option>
                  <option value="front">Mặt trước</option>
                  <option value="back">Mặt sau</option>
                  <option value="side">Bên hông</option>
                </select>
              </div>
              <div>
                <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Kích thước in</label>
                <select class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50 text-gray-700" v-model="form.size">
                  <option value="">Chọn kích thước</option>
                  <option value="small">Nhỏ (S)</option>
                  <option value="medium">Vừa (M)</option>
                  <option value="large">Lớn (L)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Ghi chú</label>
              <textarea class="w-full border border-gray-200 focus:border-primary focus:ring-0 rounded-lg p-3 bg-gray-50" placeholder="Nhập ghi chú hoặc yêu cầu chi tiết của bạn tại đây..." rows="4" v-model="form.note"></textarea>
            </div>

            <div>
              <label class="font-semibold text-xs text-gray-600 px-1 uppercase tracking-wider">Tải lên Logo/Hình in</label>
              <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer group hover:border-primary transition-colors" @click="triggerFileUpload">
                <span class="material-symbols-outlined text-4xl text-gray-400 group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                <p class="font-semibold text-sm text-gray-700">Kéo và thả hoặc nhấp để tải lên</p>
                <p class="text-xs text-gray-500 mt-1">PNG, JPG hoặc AI (Tối đa 10MB)</p>
                <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" accept=".png,.jpg,.jpeg,.ai,.pdf">
              </div>
              <p v-if="uploadedFileName" class="text-xs text-green-600 mt-2">Đã tải lên: {{ uploadedFileName }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
              <button class="flex-1 bg-primary text-white py-4 px-8 rounded-lg hover:bg-primary-dark transition-colors flex items-center justify-center gap-2" type="submit">
                <span class="material-symbols-outlined">send</span> Gửi yêu cầu
              </button>
              <button class="flex-1 border border-gray-300 text-gray-700 py-4 px-8 rounded-lg hover:bg-gray-100 transition-all flex items-center justify-center gap-2" type="button" @click="saveDesign">
                <span class="material-symbols-outlined">save</span> Lưu thiết kế
              </button>
            </div>
          </form>
        </section>

        <!-- Right Side: Preview -->
        <aside class="flex-1 flex flex-col gap-4">
          <div class="relative bg-white rounded-xl overflow-hidden min-h-[500px] flex items-center justify-center border border-gray-100 shadow-sm">
            <img alt="Product Preview Background" class="absolute inset-0 w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAIuYarNQELuLodDVmZv6QLnAEMluYgDRFJCW1CgEkrPMHKnk3lLZx8Yft-54EN03R4XXwvc3DUbFEiPJgsA9PK6RZWhuzg95c9wcn8G_IHVdD4479ZgJsORXcrz5VPqHrO4yirXaJHTrvKVQOE17TGOPe4oT_gLmDXTA_wBn1v0yVK7q2bbqL5bu-B-iJGWZz8QpAAPg2ioON7c7wtOb92fUmqQ0rzmq18Ik6z10ZYQ_h3CGfqFwhVNImwvmGXDNAA1XtVuRsjBaQB">
            <div class="relative z-10 w-32 h-32 border-2 border-dashed border-gray-400 flex items-center justify-center preview-glass rounded-lg">
              <div class="text-center p-4">
                <span class="material-symbols-outlined text-gray-500 opacity-30 text-4xl">add_photo_alternate</span>
                <p class="text-[10px] text-gray-600 font-bold mt-1 uppercase">VỊ TRÍ IN</p>
              </div>
            </div>
            <div class="absolute bottom-6 left-6 right-6 flex justify-between items-end">
              <div class="preview-glass p-4 rounded-lg shadow-sm border border-white/20 bg-white/80">
                <p class="font-semibold text-sm text-gray-800">Thông tin in: <span class="font-bold">Sẵn sàng tùy chỉnh</span></p>
                <p class="text-xs text-gray-600">Kích thước: Tự chọn</p>
              </div>
              <div class="flex flex-col gap-2">
                <button class="bg-white p-2 rounded-full shadow-md"><span class="material-symbols-outlined text-gray-600">zoom_in</span></button>
                <button class="bg-white p-2 rounded-full shadow-md"><span class="material-symbols-outlined text-gray-600">360</span></button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
              <span class="material-symbols-outlined text-primary">verified</span>
              <h4 class="font-semibold text-sm text-gray-800">Công nghệ in UV</h4>
              <p class="text-xs text-gray-500">Độ bền màu cực cao, chống trầy xước và bong tróc.</p>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
              <span class="material-symbols-outlined text-primary">timer</span>
              <h4 class="font-semibold text-sm text-gray-800">Sản xuất nhanh</h4>
              <p class="text-xs text-gray-500">Gia công và giao hàng chỉ trong 3-5 ngày làm việc.</p>
            </div>
          </div>
        </aside>
      </div>
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

const form = ref({
  fullName: '', email: '', phone: '', position: '', size: '', note: ''
})
const uploadedFileName = ref('')
const fileInput = ref(null)

const triggerFileUpload = () => { fileInput.value.click() }
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) uploadedFileName.value = file.name
}
const submitRequest = () => {
  router.get(route('home'))
  alert('Gửi yêu cầu thành công! Chúng tôi sẽ phản hồi trong vòng 24h.')
}
const saveDesign = () => { alert('Đã lưu thiết kế (demo)') }
</script>

<style scoped>
.preview-glass {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(4px);
}
</style>