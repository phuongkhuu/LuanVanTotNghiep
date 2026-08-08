<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 antialiased selection:bg-orange-500 selection:text-white">
    <Head title="Mua sỉ & Dự án Doanh nghiệp - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="py-10 md:py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" id="contact">
        
        <!-- CARD TỔNG CHÍNH -->
        <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/60 overflow-hidden border border-slate-100">
          
          <!-- NỘI DUNG CHÍNH (2 CỘT) -->
          <div class="grid grid-cols-1 lg:grid-cols-12">
            
            <!-- CỘT TRÁI: Cấu hình đơn hàng & Sản phẩm (5 Cols) -->
            <div class="lg:col-span-5 p-6 md:p-8 bg-slate-50/50 border-b lg:border-b-0 lg:border-r border-slate-100 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-base font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    Thông tin đơn hàng
                  </h3>
                </div>

                <!-- Thẻ sản phẩm đã chọn -->
                <div v-if="selectedProduct" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm mb-6 transition-all hover:shadow-md">
                  <div class="flex gap-4">
                    <div class="w-24 h-28 shrink-0 bg-slate-100 rounded-xl overflow-hidden border border-slate-100">
                      <img 
                        :src="selectedProduct.image" 
                        :alt="selectedProduct.name"
                        class="w-full h-full object-cover object-center"
                      />
                    </div>
                    <div class="flex-1 flex flex-col justify-between">
                      <div>
                        <h4 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2">{{ selectedProduct.name }}</h4>
                        <p v-if="selectedProduct.description" class="text-slate-500 text-xs mt-1 line-clamp-1">{{ selectedProduct.description }}</p>
                      </div>

                      <div class="mt-2">
                        <div class="flex items-baseline gap-2 flex-wrap">
                          <span class="text-base font-extrabold text-orange-600">
                            {{ formatPrice(selectedProduct.sale_price || selectedProduct.base_price) }}
                          </span>
                          <span v-if="selectedProduct.original_price && selectedProduct.original_price > selectedProduct.sale_price" class="text-slate-400 line-through text-xs">
                            {{ formatPrice(selectedProduct.original_price) }}
                          </span>
                          <span v-if="selectedProduct.discount_percent > 0" class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                            -{{ selectedProduct.discount_percent }}%
                          </span>
                        </div>
                        <div class="mt-1 flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                          Còn hàng ({{ selectedProduct.stock }} sản phẩm)
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tùy chỉnh thuộc tính B2B -->
                <div class="space-y-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                  
                  <!-- Chọn Số lượng -->
                  <div>
                    <div class="flex justify-between items-center mb-2">
                      <label class="text-xs font-bold uppercase tracking-wider text-slate-600">
                        Số lượng sỉ <span class="text-rose-500">*</span>
                      </label>
                      <span class="text-xs font-medium" :class="orderQuantity >= 50 ? 'text-emerald-600' : 'text-rose-500'">
                        {{ orderQuantity >= 50 ? '✓ Tối thiểu 50 sp' : 'Cần tối thiểu 50 sp' }}
                      </span>
                    </div>

                    <div class="flex items-center gap-3">
                      <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50 w-36">
                        <button 
                          @click="decreaseQuantity" 
                          type="button"
                          class="w-10 h-10 flex items-center justify-center hover:bg-slate-200/60 transition-colors text-slate-600 disabled:opacity-40"
                          :disabled="orderQuantity <= 1"
                        >
                          <span class="material-symbols-outlined text-lg">remove</span>
                        </button>
                        <input 
                          type="number" 
                          v-model.number="orderQuantity" 
                          min="1"
                          class="w-full h-10 text-center bg-transparent border-0 outline-none text-sm font-bold text-slate-800"
                        />
                        <button 
                          @click="increaseQuantity"
                          type="button"
                          class="w-10 h-10 flex items-center justify-center hover:bg-slate-200/60 transition-colors text-slate-600"
                        >
                          <span class="material-symbols-outlined text-lg">add</span>
                        </button>
                      </div>

                      <!-- Quick add quantity options -->
                      <div class="flex gap-1.5">
                        <button @click="orderQuantity = 50" type="button" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:border-orange-500 hover:text-orange-600 transition-colors">50</button>
                        <button @click="orderQuantity = 100" type="button" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:border-orange-500 hover:text-orange-600 transition-colors">100</button>
                        <button @click="orderQuantity = 150" type="button" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:border-orange-500 hover:text-orange-600 transition-colors">150</button>
                      </div>
                    </div>
                  </div>

                  <!-- Chọn Màu sắc & Size -->
                  <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                    <div>
                      <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Màu sắc <span class="text-rose-500">*</span>
                      </label>
                      <select v-model="selectedColor" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm font-medium outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white text-slate-700 transition-all">
                        <option v-for="color in colorOptions" :key="color" :value="color">{{ color }}</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Kích thước <span class="text-rose-500">*</span>
                      </label>
                      <select v-model="selectedSize" class="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm font-medium outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-white text-slate-700 transition-all">
                        <option v-for="size in sizeOptions" :key="size" :value="size">{{ size }}</option>
                      </select>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Nút Submit Desktop -->
              <div class="mt-8">
                <button 
                  @click="submitQuoteRequest"
                  :disabled="loading || orderQuantity < 50"
                  class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/35 transition-all flex items-center justify-center gap-2 uppercase tracking-wider text-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                >
                  <span v-if="loading" class="material-symbols-outlined animate-spin">progress_activity</span>
                  <span v-else class="material-symbols-outlined">send</span>
                  {{ loading ? 'Đang gửi yêu cầu...' : 'Gửi yêu cầu báo giá sỉ' }}
                </button>
                <p v-if="orderQuantity < 50" class="text-center text-xs text-rose-500 mt-2 font-medium">
                  * Vui lòng chọn tối thiểu 50 sản phẩm để gửi báo giá.
                </p>
              </div>
            </div>

            <!-- CỘT PHẢI: Form Doanh Nghiệp (7 Cols) -->
            <div class="lg:col-span-7 p-6 md:p-8 bg-white">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                  Thông tin doanh nghiệp
                </h3>
              </div>

              <form @submit.prevent="submitQuoteRequest" class="space-y-4">
                
                <!-- Nhóm 1: Doanh nghiệp -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Tên công ty -->
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                      Tên công ty / Tổ chức <span class="text-rose-500">*</span>
                    </label>
                    <input 
                      class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-100/50 px-4 py-2.5 outline-none text-sm transition-all text-slate-800 cursor-not-allowed" 
                      placeholder="Chưa có thông tin" 
                      type="text" 
                      v-model="form.company" 
                      readonly
                      disabled
                    >
                  </div>

                  <!-- Mã số thuế -->
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                      Mã số thuế <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                      <input 
                        class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800"
                        :class="{
                          'border-amber-500 focus:ring-amber-500/20 bg-amber-50/30': taxLookupStatus === 'not_found',
                          'border-rose-500 focus:ring-rose-500/20 bg-rose-50/30': taxLookupStatus === 'inactive',
                          'border-emerald-500 focus:ring-emerald-500/20 bg-emerald-50/30': taxLookupStatus === 'active'
                        }"
                        placeholder="Nhập mã số thuế để tự động tra cứu" 
                        type="text" 
                        v-model="form.tax_code"
                        @input="fetchCompanyInfo"
                        required
                      >
                      <span v-if="isSearching" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                      </span>
                      <!-- Icon trạng thái -->
                      <span v-if="!isSearching && taxLookupStatus === 'not_found'" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <span class="material-symbols-outlined text-amber-500 text-xl">error</span>
                      </span>
                      <span v-if="!isSearching && taxLookupStatus === 'inactive'" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <span class="material-symbols-outlined text-rose-500 text-xl">cancel</span>
                      </span>
                      <span v-if="!isSearching && taxLookupStatus === 'active'" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <span class="material-symbols-outlined text-emerald-500 text-xl">check_circle</span>
                      </span>
                    </div>
                    <p class="text-xs mt-1.5" :class="{
                      'text-amber-600': taxLookupStatus === 'not_found',
                      'text-rose-600': taxLookupStatus === 'inactive',
                      'text-emerald-600': taxLookupStatus === 'active',
                      'text-slate-400': !taxLookupStatus
                    }">
                      <span v-if="taxLookupStatus === 'not_found'">
                        ⚠️ Không tìm thấy thông tin công ty với mã số thuế này
                      </span>
                      <span v-else-if="taxLookupStatus === 'inactive'">
                        ⚠️ Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động
                      </span>
                      <span v-else-if="taxLookupStatus === 'active'">
                        ✓ Đã tìm thấy thông tin công ty
                      </span>
                      <span v-else>
                        Nhập mã số thuế để tự động điền thông tin công ty
                      </span>
                    </p>
                  </div>
                </div>

                <!-- Trạng thái hoạt động - ĐÃ SỬA -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                      Trạng thái hoạt động
                    </label>
                    <div class="w-full rounded-xl border border-slate-200 bg-slate-50/30 px-4 py-2.5 text-sm flex items-center gap-2 min-h-[46px]">
                      <!-- Trạng thái: Đang hoạt động -->
                      <span v-if="taxLookupStatus === 'active'" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Đang hoạt động
                      </span>
                      
                      <!-- Trạng thái: Ngưng hoạt động -->
                      <span v-else-if="taxLookupStatus === 'inactive'" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        {{ form.company_status || 'Ngưng hoạt động' }}
                      </span>
                      
                      <!-- Trạng thái: Không tìm thấy -->
                      <span v-else-if="taxLookupStatus === 'not_found'" 
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                        <span class="material-symbols-outlined text-amber-500 text-sm">error</span>
                        Không tìm thấy
                      </span>
                      
                      <!-- Trạng thái: Chưa tra cứu -->
                      <span v-else class="text-slate-400 italic">Chưa có thông tin</span>
                    </div>
                  </div>
                </div>

                <!-- Nhóm 2: Liên hệ -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                      Email làm việc <span class="text-rose-500">*</span>
                    </label>
                    <input 
                      class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800"
                      :class="{ 'border-rose-500 focus:ring-rose-500/20': emailError }"
                      placeholder="email@company.com" 
                      type="email" 
                      v-model="form.email" 
                      @input="validateEmail"
                      @blur="validateEmail"
                      required
                    >
                    <p v-if="emailError" class="text-xs text-rose-500 mt-1">{{ emailError }}</p>
                    <p class="text-xs text-slate-400 mt-1">VD: contact@company.com</p>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                      Số điện thoại người liên hệ <span class="text-rose-500">*</span>
                    </label>
                    <input 
                      class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800"
                      :class="{ 'border-rose-500 focus:ring-rose-500/20': phoneError }"
                      placeholder="09xx xxx xxx (10 số)" 
                      type="tel" 
                      v-model="form.phone" 
                      @input="validatePhone"
                      @blur="validatePhone"
                      maxlength="10"
                      required
                    >
                    <p v-if="phoneError" class="text-xs text-rose-500 mt-1">{{ phoneError }}</p>
                    <p class="text-xs text-slate-400 mt-1">Nhập đúng 10 chữ số, bắt đầu bằng 0</p>
                  </div>
                </div>

                <!-- Nhóm 3: Ngày nhận -->
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1.5">Ngày dự kiến cần nhận hàng</label>
                  <input 
                    class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800" 
                    type="date" 
                    v-model="form.delivery_date" 
                    :min="today"
                  >
                </div>

                <!-- Nhóm 4: Địa chỉ giao hàng -->
                <div class="pt-4 border-t border-slate-100 space-y-4">
                  <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Địa điểm giao hàng</p>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                      <select v-model="form.city" class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-3 py-2.5 outline-none text-sm text-slate-700">
                        <option value="">Chọn Tỉnh / Thành</option>
                        <option value="Hà Nội">Hà Nội</option>
                        <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                        <option value="Đà Nẵng">Đà Nẵng</option>
                        <option value="Hải Phòng">Hải Phòng</option>
                        <option value="Cần Thơ">Cần Thơ</option>
                        <option value="Bình Dương">Bình Dương</option>
                        <option value="Đồng Nai">Đồng Nai</option>
                        <option value="Khác">Khác</option>
                      </select>
                    </div>

                    <div>
                      <select v-model="form.district" class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-3 py-2.5 outline-none text-sm text-slate-700">
                        <option value="">Chọn Quận / Huyện</option>
                        <option value="Quận 1">Quận 1</option>
                        <option value="Quận 2">Quận 2</option>
                        <option value="Quận 3">Quận 3</option>
                        <option value="Quận 7">Quận 7</option>
                        <option value="Bình Thạnh">Bình Thạnh</option>
                        <option value="Khác">Khác</option>
                      </select>
                    </div>

                    <div>
                      <select v-model="form.ward" class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-3 py-2.5 outline-none text-sm text-slate-700">
                        <option value="">Chọn Phường / Xã</option>
                        <option value="Phường Bến Nghé">Phường Bến Nghé</option>
                        <option value="Phường Bến Thành">Phường Bến Thành</option>
                        <option value="Khác">Khác</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                      Địa chỉ chi tiết <span class="text-rose-500">*</span>
                    </label>
                    <input 
                      class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800" 
                      placeholder="Số nhà, tên đường, tên tòa nhà..." 
                      type="text" 
                      v-model="form.address" 
                      required
                    >
                  </div>
                </div>

                <!-- Nhóm 5: Ghi chú & Yêu cầu riêng -->
                <div class="pt-4 border-t border-slate-100 space-y-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Yêu cầu thiết kế / In logo thương hiệu</label>
                    <input 
                      class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800" 
                      placeholder="Ví dụ: In logo công ty ở mặt trước, ..." 
                      type="text" 
                      v-model="form.requirements"
                    >
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Ghi chú thêm</label>
                    <textarea 
                      rows="2"
                      class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 bg-slate-50/30 px-4 py-2.5 outline-none text-sm transition-all text-slate-800 resize-none" 
                      placeholder="Ví dụ: Giao giờ hành chính, đóng gói từng sản phẩm riêng..." 
                      v-model="form.note"
                    ></textarea>
                  </div>
                </div>

                <div class="pt-2">
                  <p class="text-xs text-slate-400 italic">
                    * Thông tin của bạn được cam kết bảo mật. Chuyên viên BigBag B2B sẽ phản hồi báo giá qua email/SĐT trong vòng 30 phút.
                  </p>
                </div>
              </form>
            </div>

          </div>
        </div>

      </div>
    </main>

    <!-- Toast Notification - Hiển thị ở giữa phía dưới -->
    <div v-if="notification.show" 
         class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 max-w-lg w-full mx-4 animate-slideUp"
         @click="notification.show = false">
      <div class="rounded-2xl shadow-2xl p-5 border backdrop-blur-sm transition-all cursor-pointer hover:scale-[1.02]"
           :class="{
             'bg-emerald-50/95 border-emerald-200 text-emerald-800': notification.type === 'success',
             'bg-rose-50/95 border-rose-200 text-rose-800': notification.type === 'error',
             'bg-amber-50/95 border-amber-200 text-amber-800': notification.type === 'warning',
             'bg-blue-50/95 border-blue-200 text-blue-800': notification.type === 'info'
           }">
        <div class="flex items-start gap-4">
          <div class="shrink-0 mt-0.5">
            <span class="material-symbols-outlined text-2xl" 
                  :class="{
                    'text-emerald-500': notification.type === 'success',
                    'text-rose-500': notification.type === 'error',
                    'text-amber-500': notification.type === 'warning',
                    'text-blue-500': notification.type === 'info'
                  }">
              {{ notification.type === 'success' ? 'check_circle' : 
                 notification.type === 'error' ? 'error' : 
                 notification.type === 'warning' ? 'warning' : 'info' }}
            </span>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold">{{ notification.message }}</p>
          </div>
          <button @click.stop="notification.show = false" class="shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
            <span class="material-symbols-outlined text-lg">close</span>
          </button>
        </div>
        <!-- Thanh tiến trình tự động ẩn -->
        <div class="mt-3 h-1 rounded-full overflow-hidden bg-slate-200/50">
          <div class="h-full rounded-full transition-all duration-[5000ms] ease-linear"
               :class="{
                 'bg-emerald-500': notification.type === 'success',
                 'bg-rose-500': notification.type === 'error',
                 'bg-amber-500': notification.type === 'warning',
                 'bg-blue-500': notification.type === 'info'
               }"
               :style="{ width: notification.progress + '%' }">
          </div>
        </div>
      </div>
    </div>

    <!-- Login Modal -->
    <div v-if="showLoginModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click="showLoginModal = false">
      <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl animate-fadeIn" @click.stop>
        <div class="text-center mb-6">
          <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-4xl text-orange-500">login</span>
          </div>
          <h3 class="text-xl font-bold text-slate-800">Đăng nhập để tiếp tục</h3>
          <p class="text-sm text-slate-500 mt-1">Vui lòng đăng nhập để gửi yêu cầu mua sỉ</p>
        </div>
        <div class="flex flex-col gap-3">
          <a href="/login" class="w-full bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white font-semibold py-3 rounded-xl transition-all text-center shadow-lg shadow-orange-500/25">
            Đăng nhập ngay
          </a>
          <button @click="showLoginModal = false" class="w-full border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold py-3 rounded-xl transition-colors">
            Đóng
          </button>
        </div>
      </div>
    </div>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import axios from 'axios'

// ==================== PROPS ====================
const props = defineProps({
  selectedProduct: {
    type: Object,
    default: null
  },
  suggestedProducts: {
    type: Array,
    default: () => []
  },
  defaultQuantity: {
    type: Number,
    default: 1
  },
  defaultColor: {
    type: String,
    default: ''
  },
  defaultSize: {
    type: String,
    default: ''
  },
  discounts: {
    type: Array,
    default: () => []
  }
})

// ==================== REACTIVE DATA ====================
const selectedProduct = ref(props.selectedProduct)
const suggestedProducts = ref(props.suggestedProducts)

const orderQuantity = ref(props.defaultQuantity || 1)
const selectedColor = ref('')
const selectedSize = ref('')
const loading = ref(false)
const isSearching = ref(false)
const taxLookupStatus = ref('') // '', 'active', 'inactive', 'not_found'
const showLoginModal = ref(false)

// ==================== VALIDATION ERRORS ====================
const emailError = ref('')
const phoneError = ref('')

// ==================== NOTIFICATION ====================
const notification = ref({
  show: false,
  type: 'info',
  message: '',
  progress: 100
})

let notificationTimer = null
let progressTimer = null

// ==================== FORM B2B ====================
const form = ref({
  company: '',
  company_status: '',
  email: '',
  phone: '',
  tax_code: '',
  delivery_date: '',
  city: '',
  district: '',
  ward: '',
  address: '',
  note: '',
  requirements: ''
})

// ==================== COMPUTED ====================
const colorOptions = computed(() => {
  if (!selectedProduct.value) return []
  return selectedProduct.value.colors?.map(c => c.name) || []
})

const sizeOptions = computed(() => {
  if (!selectedProduct.value) return []
  return selectedProduct.value.sizes || []
})

// Format tiền
const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  return new Intl.NumberFormat('vi-VN').format(price) + '₫'
}

// ==================== NGÀY HIỆN TẠI CHO INPUT DATE ====================
const today = new Date().toISOString().split('T')[0]

// ==================== VALIDATION FUNCTIONS ====================
const validateEmail = () => {
  const email = form.value.email.trim()
  
  if (!email) {
    emailError.value = 'Vui lòng nhập email'
    return false
  }
  
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
  if (!emailRegex.test(email)) {
    emailError.value = 'Email không đúng định dạng (VD: contact@company.com)'
    return false
  }
  
  emailError.value = ''
  return true
}

const validatePhone = () => {
  const phone = form.value.phone.trim()
  
  if (!phone) {
    phoneError.value = 'Vui lòng nhập số điện thoại'
    return false
  }
  
  const phoneRegex = /^0[0-9]{9}$/
  if (!phoneRegex.test(phone)) {
    phoneError.value = 'Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số'
    return false
  }
  
  phoneError.value = ''
  return true
}

// ==================== NOTIFICATION HELPER ====================
const showNotification = (type, message) => {
  // Clear existing timers
  if (notificationTimer) {
    clearTimeout(notificationTimer)
    notificationTimer = null
  }
  if (progressTimer) {
    clearInterval(progressTimer)
    progressTimer = null
  }
  
  notification.value = {
    show: true,
    type,
    message,
    progress: 100
  }
  
  // Progress bar animation (5 seconds)
  const duration = 5000
  const interval = 50
  let progress = 100
  
  progressTimer = setInterval(() => {
    progress -= (interval / duration) * 100
    if (progress <= 0) {
      progress = 0
      clearInterval(progressTimer)
      progressTimer = null
    }
    notification.value.progress = progress
  }, interval)
  
  // Auto hide after 5 seconds
  notificationTimer = setTimeout(() => {
    notification.value.show = false
    notificationTimer = null
  }, duration)
}

// ==================== METHODS ====================
const increaseQuantity = () => {
  orderQuantity.value++
}

const decreaseQuantity = () => {
  if (orderQuantity.value > 1) {
    orderQuantity.value--
  }
}

// ===== TRA CỨU THÔNG TIN CÔNG TY QUA MÃ SỐ THUẾ =====
const fetchCompanyInfo = async () => {
  const taxCode = form.value.tax_code.trim()
  
  // Nếu mã số thuế có ít hơn 10 ký tự hoặc rỗng thì reset dữ liệu
  if (!taxCode || taxCode.length < 10) {
    form.value.company = ''
    form.value.company_status = ''
    taxLookupStatus.value = ''
    return
  }

  isSearching.value = true
  
  try {
    const response = await axios.get(`/api/tra-cuu-mst/${taxCode}`, {
      timeout: 5000
    })
    
    if (response.data && response.data.success) {
      // === TRƯỜNG HỢP 1: TÌM THẤY VÀ ĐANG HOẠT ĐỘNG ===
      const data = response.data.data
      
      form.value.company = data.company_name || ''
      form.value.company_status = data.status || 'Đang hoạt động'
      taxLookupStatus.value = 'active'
      
      // Tự động điền email và phone nếu chưa có
      if (!form.value.email && data.email) {
        form.value.email = data.email || ''
        validateEmail()
      }
      if (!form.value.phone && data.phone) {
        form.value.phone = data.phone || ''
        validatePhone()
      }
      
      showNotification('success', response.data.message || 'Tra cứu thành công')
      
    } else {
      // === XỬ LÝ CÁC TRƯỜNG HỢP LỖI ===
      const errorMessage = response.data?.message || 'Không tìm thấy thông tin'
      
      // Kiểm tra nếu là lỗi không tìm thấy
      const isNotFound = response.status === 404 || 
                        (response.data?.message && 
                         (response.data.message.includes('không tìm thấy') || 
                          response.data.message.includes('Không tìm thấy')))
      
      // Kiểm tra nếu doanh nghiệp ngưng hoạt động
      const isInactive = response.data?.status && 
        (response.data.status.includes('Ngưng') || 
         response.data.status.includes('giải thể') || 
         response.data.status.includes('ngừng') ||
         response.data.status.includes('chấm dứt'))
      
      if (isInactive) {
        // === TRƯỜNG HỢP 2: DOANH NGHIỆP NGƯNG HOẠT ĐỘNG ===
        form.value.company = response.data?.data?.company_name || ''
        form.value.company_status = response.data?.status || 'Ngưng hoạt động'
        taxLookupStatus.value = 'inactive'
        showNotification('error', '⚠️ ' + errorMessage)
      } else if (isNotFound) {
        // === TRƯỜNG HỢP 3: KHÔNG TÌM THẤY MÃ SỐ THUẾ ===
        form.value.company = ''
        form.value.company_status = ''
        taxLookupStatus.value = 'not_found'
        showNotification('warning', 'Không tìm thấy thông tin công ty với mã số thuế này. Vui lòng kiểm tra lại hoặc nhập thủ công.')
      } else {
        // === TRƯỜNG HỢP 4: LỖI KHÁC ===
        form.value.company = ''
        form.value.company_status = ''
        taxLookupStatus.value = ''
        showNotification('error', errorMessage)
      }
    }
  } catch (error) {
    console.error('Lỗi tra cứu mã số thuế:', error)
    
    if (error.response) {
      const status = error.response.status
      const data = error.response.data
      
      if (status === 404) {
        // === KHÔNG TÌM THẤY (404) ===
        form.value.company = ''
        form.value.company_status = ''
        taxLookupStatus.value = 'not_found'
        showNotification('warning', 'Không tìm thấy thông tin công ty với mã số thuế này. Vui lòng kiểm tra lại hoặc nhập thủ công.')
      } else if (status === 400 && data?.status && 
                (data.status.includes('Ngưng') || data.status.includes('giải thể') || data.status.includes('ngừng') || data.status.includes('chấm dứt'))) {
        // === DOANH NGHIỆP NGƯNG HOẠT ĐỘNG ===
        form.value.company = data?.data?.company_name || ''
        form.value.company_status = data?.status || 'Ngưng hoạt động'
        taxLookupStatus.value = 'inactive'
        showNotification('error', '⚠️ ' + (data?.message || 'Hộ kinh doanh/Doanh nghiệp đã ngưng hoạt động.'))
      } else {
        // === LỖI KHÁC ===
        form.value.company = ''
        form.value.company_status = ''
        taxLookupStatus.value = ''
        showNotification('error', data?.message || 'Có lỗi xảy ra khi tra cứu mã số thuế.')
      }
    } else {
      // === LỖI KẾT NỐI ===
      form.value.company = ''
      form.value.company_status = ''
      taxLookupStatus.value = ''
      showNotification('error', 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.')
    }
  } finally {
    isSearching.value = false
  }
}

// ===== GỬI YÊU CẦU MUA SỈ =====
const submitQuoteRequest = async () => {
  // Kiểm tra số lượng tối thiểu
  if (orderQuantity.value < 50) {
    showNotification('warning', 'Số lượng đặt tối thiểu là 50 sản phẩm.')
    return
  }

  // Kiểm tra mã số thuế
  if (!form.value.tax_code) {
    showNotification('warning', 'Vui lòng nhập mã số thuế.')
    return
  }

  // Kiểm tra tên công ty
  if (!form.value.company) {
    showNotification('warning', 'Vui lòng nhập mã số thuế hợp lệ để tra cứu thông tin công ty.')
    return
  }
  
  // === KIỂM TRA TRẠNG THÁI HOẠT ĐỘNG ===
  // Không cho submit nếu không tìm thấy
  if (taxLookupStatus.value === 'not_found') {
    showNotification('warning', 'Không tìm thấy thông tin công ty. Vui lòng kiểm tra lại mã số thuế.')
    return
  }
  
  // KHÔNG CHO SUBMIT NẾU DOANH NGHIỆP NGƯNG HOẠT ĐỘNG
  if (taxLookupStatus.value === 'inactive' || 
      (form.value.company_status && 
       (form.value.company_status.includes('Ngưng') || 
        form.value.company_status.includes('giải thể') || 
        form.value.company_status.includes('ngừng') ||
        form.value.company_status.includes('chấm dứt')))) {
    showNotification('error', '❌ Doanh nghiệp đã ngưng hoạt động, không thể đặt hàng. Vui lòng sử dụng mã số thuế của công ty đang hoạt động.')
    return
  }
  
  // Validate email
  if (!validateEmail()) {
    document.querySelector('input[type="email"]')?.focus()
    return
  }
  
  // Validate phone
  if (!validatePhone()) {
    document.querySelector('input[type="tel"]')?.focus()
    return
  }
  
  // Kiểm tra địa chỉ
  if (!form.value.address) {
    showNotification('warning', 'Vui lòng nhập địa chỉ chi tiết.')
    return
  }

  // Kiểm tra ngày cần nhận
  if (form.value.delivery_date) {
    const todayDate = new Date()
    todayDate.setHours(0,0,0,0)
    const deliveryDate = new Date(form.value.delivery_date)
    deliveryDate.setHours(0,0,0,0)
    if (deliveryDate < todayDate) {
      showNotification('warning', 'Ngày cần nhận không được là quá khứ.')
      return
    }
  }

  // Kiểm tra sản phẩm
  if (!selectedProduct.value) {
    showNotification('warning', 'Vui lòng chọn sản phẩm.')
    return
  }

  // Tìm variant dựa trên màu và size
  const variants = selectedProduct.value.variants || []
  let selectedVariant = null

  if (selectedColor.value && selectedSize.value) {
    selectedVariant = variants.find(v => v.color_name === selectedColor.value && v.size_name === selectedSize.value)
  } else if (selectedColor.value) {
    selectedVariant = variants.find(v => v.color_name === selectedColor.value)
  } else if (selectedSize.value) {
    selectedVariant = variants.find(v => v.size_name === selectedSize.value)
  }

  if (!selectedVariant) {
    showNotification('warning', 'Vui lòng chọn màu sắc và kích thước hợp lệ.')
    return
  }

  loading.value = true

  try {
    const response = await axios.post('/mua-si', {
      company: form.value.company,
      email: form.value.email,
      phone: form.value.phone,
      tax_code: form.value.tax_code,
      delivery_date: form.value.delivery_date,
      city: form.value.city,
      district: form.value.district,
      ward: form.value.ward,
      address: form.value.address,
      note: form.value.note,
      requirements: form.value.requirements,
      variant_id: selectedVariant.id,
      quantity: orderQuantity.value,
      color: selectedColor.value,
      size: selectedSize.value,
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      showNotification('success', response.data.message || 'Gửi yêu cầu thành công! Chúng tôi sẽ liên hệ trong 30 phút.')
      setTimeout(() => {
        window.location.href = '/'
      }, 1500)
    } else {
      showNotification('error', response.data.message || 'Có lỗi xảy ra, vui lòng thử lại.')
    }
  } catch (error) {
    console.error('Error submitting quote request:', error)
    let msg = 'Không thể gửi yêu cầu. Vui lòng thử lại!'
    
    if (error.response) {
      // Xử lý lỗi 401 Unauthorized
      if (error.response.status === 401) {
        showLoginModal.value = true
        loading.value = false
        return
      }
      
      if (error.response.data?.message) {
        msg = error.response.data.message
      }
    }
    
    showNotification('error', msg)
  } finally {
    loading.value = false
  }
}

// ==================== WATCH ====================
watch(() => props.selectedProduct, (newVal) => {
  if (newVal) {
    selectedProduct.value = newVal
    if (newVal.colors && newVal.colors.length > 0) {
      selectedColor.value = newVal.colors[0].name
    }
    if (newVal.sizes && newVal.sizes.length > 0) {
      selectedSize.value = newVal.sizes[0]
    }
  }
}, { immediate: true })

onMounted(() => {
  if (props.defaultColor) {
    selectedColor.value = props.defaultColor
  }
  if (props.defaultSize) {
    selectedSize.value = props.defaultSize
  }
})
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}

input:disabled,
input[readonly] {
  background-color: #f1f5f9 !important;
  cursor: not-allowed !important;
  opacity: 0.8;
}

.border-rose-500 {
  border-color: #f43f5e !important;
}
.border-rose-500:focus {
  border-color: #f43f5e !important;
  box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.2) !important;
}

.border-amber-500 {
  border-color: #f59e0b !important;
}
.border-amber-500:focus {
  border-color: #f59e0b !important;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
}

.border-emerald-500 {
  border-color: #10b981 !important;
}
.border-emerald-500:focus {
  border-color: #10b981 !important;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
}

.bg-amber-50\/30 {
  background-color: rgba(255, 251, 235, 0.3) !important;
}

.bg-rose-50\/30 {
  background-color: rgba(255, 241, 242, 0.3) !important;
}

.bg-emerald-50\/30 {
  background-color: rgba(236, 253, 245, 0.3) !important;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translate(-50%, 20px);
  }
  to {
    opacity: 1;
    transform: translate(-50%, 0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}

.animate-slideUp {
  animation: slideUp 0.3s ease-out;
}
</style>