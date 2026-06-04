<template>
  <div>
    <Head title="Tùy chỉnh sản phẩm - BigBag Premium Utility Carry Gear" />
    <!-- Header giống trên -->
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
            <Link :href="route('wholesale')" class="font-label-md text-label-md text-primary border-b-2 border-primary pb-1">Mua sỉ</Link>
            <Link :href="route('promotion')" class="font-label-md text-label-md text-on-surface-variant hover:text-primary">Khuyến mãi</Link>
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

    <main class="max-w-container-max-width mx-auto px-margin-desktop py-section-gap">
      <div class="flex flex-col lg:flex-row gap-gutter">
        <!-- Left Side: Customization Form -->
        <section class="flex-1 space-y-stack-lg">
          <div class="space-y-stack-sm">
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Tùy chỉnh sản phẩm</h1>
            <p class="font-body-md text-body-md text-secondary">Cá nhân hóa chiếc balo của bạn với logo hoặc hình ảnh riêng biệt. Đội ngũ thiết kế của chúng tôi sẽ xem xét và phản hồi trong vòng 24h.</p>
          </div>
          <form class="space-y-stack-md bg-white p-stack-lg rounded-xl border border-gray-warm shadow-sm" @submit.prevent="submitRequest">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
              <div><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Họ và tên</label><input class="w-full border border-outline-variant focus:border-primary focus:ring-0 rounded-lg p-3" placeholder="Nhập tên của bạn" type="text" v-model="form.fullName"></div>
              <div><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Email</label><input class="w-full border border-outline-variant focus:border-primary focus:ring-0 rounded-lg p-3" placeholder="email@example.com" type="email" v-model="form.email"></div>
              <div class="md:col-span-2"><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Số điện thoại</label><input class="w-full border border-outline-variant focus:border-primary focus:ring-0 rounded-lg p-3" placeholder="090 xxx xxxx" type="tel" v-model="form.phone"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
              <div><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Vị trí in</label><select class="w-full border border-outline-variant focus:border-primary focus:ring-0 rounded-lg p-3" v-model="form.position"><option value="">Chọn vị trí</option><option value="front">Mặt trước</option><option value="back">Mặt sau</option><option value="side">Bên hông</option></select></div>
              <div><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Kích thước in</label><select class="w-full border border-outline-variant focus:border-primary focus:ring-0 rounded-lg p-3" v-model="form.size"><option value="">Chọn kích thước</option><option value="small">Nhỏ (S)</option><option value="medium">Vừa (M)</option><option value="large">Lớn (L)</option></select></div>
            </div>
            <div><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Ghi chú</label><textarea class="w-full border border-outline-variant focus:border-primary focus:ring-0 rounded-lg p-3" placeholder="Nhập ghi chú hoặc yêu cầu chi tiết của bạn tại đây..." rows="4" v-model="form.note"></textarea></div>
            <div><label class="font-label-md text-label-md text-secondary px-1 uppercase tracking-wider">Tải lên Logo/Hình in</label>
              <div class="border-2 border-dashed border-outline-variant rounded-xl p-stack-lg flex flex-col items-center justify-center cursor-pointer group" @click="triggerFileUpload">
                <span class="material-symbols-outlined text-4xl text-outline group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                <p class="font-label-lg text-label-lg text-on-surface">Kéo và thả hoặc nhấp để tải lên</p>
                <p class="font-body-sm text-body-sm text-secondary mt-1">PNG, JPG hoặc AI (Tối đa 10MB)</p>
                <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" accept=".png,.jpg,.jpeg,.ai,.pdf">
              </div>
              <p v-if="uploadedFileName" class="text-body-sm text-success-green mt-2">Đã tải lên: {{ uploadedFileName }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-stack-md pt-stack-md">
              <button class="flex-1 bg-primary text-white py-4 px-8 rounded-lg hover:scale-[1.02] active:scale-100 flex items-center justify-center gap-2" type="submit"><span class="material-symbols-outlined">send</span> Gửi yêu cầu</button>
              <button class="flex-1 border border-on-surface text-on-surface py-4 px-8 rounded-lg hover:bg-surface-container transition-all flex items-center justify-center gap-2" type="button" @click="saveDesign"><span class="material-symbols-outlined">save</span> Lưu thiết kế</button>
            </div>
          </form>
        </section>
        <!-- Right Side: Preview -->
        <aside class="flex-1 flex flex-col gap-stack-md">
          <div class="relative bg-surface-container rounded-xl overflow-hidden min-h-[500px] flex items-center justify-center border border-gray-warm">
            <img alt="Product Preview Background" class="absolute inset-0 w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAIuYarNQELuLodDVmZv6QLnAEMluYgDRFJCW1CgEkrPMHKnk3lLZx8Yft-54EN03R4XXwvc3DUbFEiPJgsA9PK6RZWhuzg95c9wcn8G_IHVdD4479ZgJsORXcrz5VPqHrO4yirXaJHTrvKVQOE17TGOPe4oT_gLmDXTA_wBn1v0yVK7q2bbqL5bu-B-iJGWZz8QpAAPg2ioON7c7wtOb92fUmqQ0rzmq18Ik6z10ZYQ_h3CGfqFwhVNImwvmGXDNAA1XtVuRsjBaQB">
            <div class="relative z-10 w-32 h-32 border-2 border-dashed border-white/50 flex items-center justify-center preview-glass rounded-lg">
              <div class="text-center p-4"><span class="material-symbols-outlined text-charcoal-deep opacity-30 text-4xl">add_photo_alternate</span><p class="text-[10px] text-charcoal-deep font-bold mt-1 uppercase">VỊ TRÍ IN</p></div>
            </div>
            <div class="absolute bottom-6 left-6 right-6 flex justify-between items-end">
              <div class="preview-glass p-4 rounded-lg shadow-sm border border-white/20"><p class="font-label-md text-label-md text-on-surface">Thông tin in: <span class="font-bold">Sẵn sàng tùy chỉnh</span></p><p class="font-body-sm text-body-sm text-secondary">Kích thước: Tự chọn</p></div>
              <div class="flex flex-col gap-2"><button class="bg-white p-2 rounded-full shadow-md"><span class="material-symbols-outlined">zoom_in</span></button><button class="bg-white p-2 rounded-full shadow-md"><span class="material-symbols-outlined">360</span></button></div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-stack-md">
            <div class="bg-surface-container-high p-stack-md rounded-xl"><span class="material-symbols-outlined text-primary">verified</span><h4 class="font-label-lg text-label-lg">Công nghệ in UV</h4><p class="font-body-sm text-body-sm text-secondary">Độ bền màu cực cao, chống trầy xước và bong tróc.</p></div>
            <div class="bg-surface-container-high p-stack-md rounded-xl"><span class="material-symbols-outlined text-primary">timer</span><h4 class="font-label-lg text-label-lg">Sản xuất nhanh</h4><p class="font-body-sm text-body-sm text-secondary">Gia công và giao hàng chỉ trong 3-5 ngày làm việc.</p></div>
          </div>
        </aside>
      </div>
    </main>

    <!-- Footer & Chatbot -->
    ...
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

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
const submitRequest = () => { alert('Gửi yêu cầu (demo)') }
const saveDesign = () => { alert('Lưu thiết kế (demo)') }
</script>