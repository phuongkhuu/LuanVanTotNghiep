<template>
  <div>
    <Head title="Mua sỉ - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main>
      
      <!-- CTA Section -->
      <section class="max-w-[1440px] mx-auto px-4 md:px-8 py-12 md:py-16" id="contact">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
          <!-- KHUNG CAM "BẮT ĐẦU DỰ ÁN DOANH NGHIỆP" -->
          <div class="bg-orange-600 p-6 md:p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -mr-24 -mt-24"></div>
            <div class="z-10">
              <h2 class="text-xl md:text-2xl font-bold text-white mb-2">Bắt Đầu Dự Án Doanh Nghiệp</h2>
              <p class="text-orange-100 text-sm max-w-xl">Đội ngũ chuyên viên tư vấn của BigBag sẵn sàng hỗ trợ bạn thiết kế và báo giá chi tiết trong vòng 30 phút.</p>
            </div>
            <div class="flex flex-col md:flex-row gap-4 md:gap-8 z-10 text-white text-sm flex-shrink-0">
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined bg-white/20 p-2 rounded-full">call</span>
                <div>
                  <p class="text-orange-200 text-xs">Hotline</p>
                  <p class="font-semibold">1900 1234</p>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="material-symbols-outlined bg-white/20 p-2 rounded-full">mail</span>
                <div>
                  <p class="text-orange-200 text-xs">Email</p>
                  <p class="font-semibold">b2b@bigbag.vn</p>
                </div>
              </div>
            </div>
          </div>

          <!-- PHẦN NỘI DUNG BÊN DƯỚI: 2 CỘT -->
          <div class="grid grid-cols-1 lg:grid-cols-2">
            <!-- CỘT TRÁI: Thông tin sản phẩm + Bộ lọc -->
            <div class="p-6 md:p-8 border-r border-gray-100">
              <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                Thông tin đặt hàng
              </h3>
              
              <!-- Sản phẩm đang chọn -->
              <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="w-full sm:w-[120px] flex-shrink-0">
                  <div class="aspect-[4/5] bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
                    <img 
                      :src="selectedProduct.image" 
                      :alt="selectedProduct.name"
                      class="w-full h-full object-cover"
                    />
                  </div>
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-800 text-sm">{{ selectedProduct.name }}</h4>
                  <p class="text-gray-500 text-xs mb-2">{{ selectedProduct.description }}</p>
                  <div class="flex items-baseline gap-2">
                    <span class="text-lg font-bold text-red-600">{{ selectedProduct.salePrice }}</span>
                    <span class="text-gray-400 line-through text-xs">{{ selectedProduct.originalPrice }}</span>
                    <span class="text-red-500 text-xs bg-red-50 px-2 py-0.5 rounded-full">-{{ selectedProduct.discount }}%</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Tồn kho: <span class="font-semibold text-green-600">{{ selectedProduct.stock }} sản phẩm</span></p>
                </div>
              </div>

              <!-- BỘ LỌC: Số lượng, Màu sắc, Kích thước -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Số lượng -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Số lượng</label>
                  <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                    <button   
                      @click="decreaseQuantity" 
                      class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                      :disabled="quantity <= 1"
                      >
                      <span class="material-symbols-outlined text-lg">remove</span>
                    </button>
                    <input 
                      type="number" 
                      v-model="orderQuantity" 
                      min="1"
                      class="w-full h-10 text-center outline-none text-sm font-semibold bg-white"
                    />
                    <button 
                      @click="increaseQuantity"
                      class="w-10 h-10 flex items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors text-gray-600 border-l border-gray-200"
                    >
                      <span class="material-symbols-outlined text-lg">add</span>
                    </button>
                  </div>
                </div>

                <!-- Màu sắc -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Màu sắc</label>
                  <select v-model="selectedColor" class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-orange-400 bg-white text-gray-700">
                    <option v-for="color in colorOptions" :key="color" :value="color">{{ color }}</option>
                  </select>
                </div>

                <!-- Kích thước -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Kích thước</label>
                  <select v-model="selectedSize" class="w-full h-10 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-orange-400 bg-white text-gray-700">
                    <option v-for="size in sizeOptions" :key="size" :value="size">{{ size }}</option>
                  </select>
                </div>
              </div>

              <!-- THÔNG TIN ĐƠN HÀNG -->
              <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tạm tính</span>
                    <span class="font-semibold text-gray-800">{{ formatPrice(selectedProduct.basePrice * orderQuantity) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Phí vận chuyển</span>
                    <span class="font-semibold text-green-600">Miễn phí</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Chiết khấu</span>
                    <span class="font-semibold text-red-500">- {{ formatPrice(discountAmount) }}</span>
                  </div>
                  <div class="border-t border-gray-200 pt-2 mt-2 flex justify-between">
                    <span class="font-bold text-gray-800">Tổng cộng</span>
                    <span class="text-xl font-bold text-orange-600">{{ formatPrice(totalPrice) }}</span>
                  </div>
                </div>
              </div>

              <!-- ===== PHƯƠNG THỨC THANH TOÁN ===== -->
              <div class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wider">
                  PHƯƠNG THỨC THANH TOÁN
                </h4>
                
                <div class="space-y-3">
                  <!-- COD - Thanh toán khi nhận hàng -->
                  <label 
                    class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:border-primary/50"
                    :class="paymentMethod === 'cod' ? 'border-primary bg-primary/5' : 'border-gray-200'"
                  >
                    <input 
                      type="radio" 
                      name="payment_method" 
                      value="cod"
                      v-model="paymentMethod"
                      class="mt-1 mr-3 accent-primary w-4 h-4 flex-shrink-0"
                    />
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-800 text-sm">Thanh toán khi nhận hàng (COD)</span>
                        <span class="text-xs text-gray-400">Trả tiền mặt</span>
                      </div>
                      <p class="text-xs text-gray-500 mt-0.5">Trả tiền mặt khi nhận hàng</p>
                    </div>
                  </label>
                  
                  <!-- Chuyển khoản ngân hàng -->
                  <label 
                    class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:border-primary/50"
                    :class="paymentMethod === 'bank_transfer' ? 'border-primary bg-primary/5' : 'border-gray-200'"
                  >
                    <input 
                      type="radio" 
                      name="payment_method" 
                      value="bank_transfer"
                      v-model="paymentMethod"
                      class="mt-1 mr-3 accent-primary w-4 h-4 flex-shrink-0"
                    />
                    <div class="flex-1">
                      <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-800 text-sm">Chuyển khoản ngân hàng</span>
                        <span class="text-xs text-gray-400">Thanh toán qua chuyển khoản</span>
                      </div>
                      <p class="text-xs text-gray-500 mt-0.5">Thanh toán qua chuyển khoản ngân hàng</p>
                      
                      <!-- Hiển thị thông tin ngân hàng khi chọn -->
                      <div v-if="paymentMethod === 'bank_transfer'" class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs font-medium text-gray-700 mb-2">Thông tin chuyển khoản:</p>
                        <div class="space-y-1 text-xs">
                          <div class="flex justify-between">
                            <span class="text-gray-500">Ngân hàng:</span>
                            <span class="font-medium">Vietcombank</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Số tài khoản:</span>
                            <span class="font-medium font-mono">123456789</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Chủ tài khoản:</span>
                            <span class="font-medium">CÔNG TY BIGBAG</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Chi nhánh:</span>
                            <span class="font-medium">Hà Nội</span>
                          </div>
                          <div class="mt-1 pt-1 border-t border-gray-200">
                            <p class="text-gray-500">Nội dung chuyển:</p>
                            <p class="font-medium text-orange-600 text-xs">[Mã đơn hàng] - [Họ tên]</p>
                          </div>
                        </div>
                        
                        <button 
                          @click="copyBankInfo" 
                          class="mt-2 text-xs text-orange-600 font-medium hover:underline flex items-center"
                        >
                          <i class="fas fa-copy mr-1"></i> Sao chép thông tin
                        </button>
                      </div>
                    </div>
                  </label>
                  
                  <!-- QR Code (hiển thị khi chọn chuyển khoản) -->
                  <div v-if="paymentMethod === 'bank_transfer'" class="flex justify-center mt-2">
                    <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-200 text-center">
                      <img 
                        src="/images/qr-code.jpg" 
                        alt="QR Code thanh toán" 
                        class="w-32 h-32 object-contain mx-auto"
                        @error="(e) => e.target.src = '/images/default-qr.png'"
                      />
                      <p class="text-xs text-gray-400 mt-1">Quét mã QR để thanh toán</p>
                    </div>
                  </div>
                </div>
                
                <!-- Lưu ý thanh toán -->
                <div class="mt-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                  <p class="text-xs text-amber-700 flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                    <span>Vui lòng kiểm tra kỹ thông tin thanh toán trước khi xác nhận đơn hàng. 
                    Đối với chuyển khoản, đơn hàng sẽ được xác nhận sau khi chúng tôi nhận được thanh toán.</span>
                  </p>
                </div>
              </div>

              <!-- Nút đặt hàng -->
              <button 
                @click="addToOrder"
                class="w-full mt-4 bg-orange-600 text-white py-4 rounded-xl font-semibold hover:bg-orange-700 transition-all flex items-center justify-center gap-2 uppercase tracking-wide text-sm"
              >
                <span class="material-symbols-outlined">shopping_cart</span>
                TIẾN HÀNH ĐẶT HÀNG
              </button>

              <!-- Badge bảo mật -->
              <div class="mt-4 flex flex-wrap justify-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-green-500 text-sm">verified</span>
                  Thanh toán an toàn 100%
                </span>
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-green-500 text-sm">local_shipping</span>
                  Giao hàng nhanh toàn quốc (2-4 ngày)
                </span>
                <span class="flex items-center gap-1">
                  <span class="material-symbols-outlined text-green-500 text-sm">autorenew</span>
                  Đổi trả miễn phí trong 7 ngày
                </span>
              </div>
            </div>

            <!-- CỘT PHẢI: Form báo giá -->
            <div class="p-6 md:p-8 bg-gray-50">
              <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                Yêu Cầu Báo Giá (B2B)
              </h3>
              <form class="space-y-4" @submit.prevent="submitRequest">
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Tên công ty</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Nhập tên doanh nghiệp của bạn" type="text" v-model="form.company">
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Email</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="email@congty.com" type="email" v-model="form.email">
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1 text-gray-600">Số điện thoại</label>
                  <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="09xx xxx xxx" type="tel" v-model="form.phone">
                </div>
                
                <!-- ĐỊA CHỈ CHI TIẾT -->
                <div class="space-y-3 border-t border-gray-200 pt-3">
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-sm font-medium mb-1 text-gray-600">Tỉnh / Thành</label>
                      <select v-model="form.city" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                        <option value="">Chọn tỉnh / thành </option>
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
                      <label class="block text-sm font-medium mb-1 text-gray-600">Quận / Huyện</label>
                      <select v-model="form.district" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                        <option value="">Chọn quận / huyện </option>
                        <option value="Quận 1">Quận 1</option>
                        <option value="Quận 2">Quận 2</option>
                        <option value="Quận 3">Quận 3</option>
                        <option value="Quận 4">Quận 4</option>
                        <option value="Quận 5">Quận 5</option>
                        <option value="Quận 6">Quận 6</option>
                        <option value="Quận 7">Quận 7</option>
                        <option value="Quận 8">Quận 8</option>
                        <option value="Quận 9">Quận 9</option>
                        <option value="Quận 10">Quận 10</option>
                        <option value="Quận 11">Quận 11</option>
                        <option value="Quận 12">Quận 12</option>
                        <option value="Bình Thạnh">Bình Thạnh</option>
                        <option value="Gò Vấp">Gò Vấp</option>
                        <option value="Tân Bình">Tân Bình</option>
                        <option value="Tân Phú">Tân Phú</option>
                        <option value="Phú Nhuận">Phú Nhuận</option>
                        <option value="Khác">Khác</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Phường / Xã</label>
                    <select v-model="form.ward" class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm">
                      <option value="">Chọn phường / xã </option>
                      <option value="Phường Bến Nghé">Phường Bến Nghé</option>
                      <option value="Phường Bến Thành">Phường Bến Thành</option>
                      <option value="Phường Cầu Kho">Phường Cầu Kho</option>
                      <option value="Phường Cầu Ông Lãnh">Phường Cầu Ông Lãnh</option>
                      <option value="Phường Cô Giang">Phường Cô Giang</option>
                      <option value="Phường Đa Kao">Phường Đa Kao</option>
                      <option value="Phường Nguyễn Thái Bình">Phường Nguyễn Thái Bình</option>
                      <option value="Khác">Khác</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
                    <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Số nhà, tên đường..." type="text" v-model="form.address">
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600">Ghi chú giao hàng</label>
                    <input class="w-full rounded-lg border-gray-200 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white px-4 py-3 outline-none text-sm" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..." type="text" v-model="form.note">
                  </div>
                </div>
                <p class="text-center text-xs text-gray-400 mt-2 italic">Chúng tôi sẽ phản hồi trong vòng 30 phút</p>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// Dữ liệu sản phẩm mẫu cho trang mua sỉ
const wholesaleProducts = ref([
  { id: 1, name: "Balo Doanh Nhân 'Elite'", desc: "Mẫu quà tặng cao cấp nhất", badge: "Best Seller", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuB2moOzz6ydHoJfraJitRWBuhER7pbb6LXfkQIb0qAqGtaP1XIqseW0VZsekc10A4VzG9yfRQf5H5WgSBahClC4oNshtyBBXXpRYCsIeE1ZPwLAoGM13Iu_NvqijHnL8uQ7nCIPsrkKmid7qIGL7BrC3D9BHPlwY9e5xz4Z0fGThAjp_IqneXLLXvtV0V2p9zmMtsDhpE6FMBTy8cyLTr1zQ4EEPRNGEP4rg3DcwDUM5X0eoUpCEKa5LrE3dTEMJ0JaEboY82dQDGER" },
  { id: 2, name: "Balo Công Sở 'Commuter'", desc: "Tối ưu cho laptop 15.6 inch", badge: "Đa năng", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuCIIGnYF1bk7O3WW8_rRR7HTixjVlOIm21_O5gIkElDeZKANejYSlLRzVv5TN0HmbeZMxv3fNQRTnn3ZxTSxg8La_G5F7wO_ayA1wY2xCmzXvdbZTigUlL8df-JW68zWYcu-uzT80fNo6XbssHyv7NGYTnMB746ubigSN24VA5d0UsNiPxx1WBvb46BdfVHMrkzKArcyePMpSKLEwZvRAbO14_OiDbLI6nHNDzpPTr_zctRXqBVLityHCxYlIz67RxCmKsAB9biQGtd" },
  { id: 3, name: "Balo Tech 'Nova'", desc: "Thiết kế hiện đại, chống trộm", badge: "New Tech", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuBWw0G7zsHP75ACx9IlLRIhUPTiFkgGORQiFhhDgoXa09BMwQG3X1whWr5Oj9NKjEmR9kd-df43FnYQEfJdGeVb4X7VIqCgCzw1G3ikd4dwYvhb7Mkb4RQOwkcBufnmi_GTUWoWSiypSTFn2aY0Sfn1wWprjNTGYxLy-8OZx3eHtQiJvR3Qdr73ws0K338b70C1s4A827U1wV3RRv9AM5IS-OF0sqAEzRTWIobGtECTyqIFA8vbtZywXJGmNJrZdoq9jCbjoV2OoamS" },
  { id: 4, name: "Balo Du Lịch 'Nomad'", desc: "Sức chứa lớn cho chuyến công tác", badge: "Tiện dụng", image: "https://lh3.googleusercontent.com/aida-public/AB6AXuDLPnuw842DL1jTFiGRfKjFnJgXSvR8yTw3Cln1fCp4pGDTHWYCsQvF0tp8WoRrHOZCb3zW32KXqShreOEPEA-0o0NkTvNnp4NbQC--zjsc4DDK3STOFH8PN7_xEHzSJ3wqzB5KfJR8WD_eo_V3RRmwPNQLQpsDEHl-m41AKinNlruJFAUuPu5rWXgrjhyPesqtU0GmLECTQ2GOO7gBMYn89pvopxOB9cB82Z3yi7LTPiHicIft9trIGs_giSsQUuKUsL0rpeWz1KQF" }
])

// Sản phẩm được chọn để hiển thị bên trái
const selectedProduct = ref({
  name: "Balo Solo Adventure 40L",
  description: "Balo du lịch dung tích lớn, nhiều ngăn tiện lợi.",
  salePrice: "1.995.000₫",
  originalPrice: "2.100.000₫",
  discount: 5,
  stock: 25,
  basePrice: 1995000,
  image: "https://lh3.googleusercontent.com/aida-public/AB6AXuAPOEllp3eC6aMxqqXyMehfbhNDerSNmtfj3mZhrjOnXP623TDYL8i26XGXe5IHdKHyh6MSnOeYFdMNcx4jUjR6Vu6OX17G3f7FeYXKawZConl3d-PTsLdlf35jOIGCSDoHgk_hKzs6uCxwhdIcuSeRkPbLXdV1VcOIjVPgproadEWitavl6uh7PmPjOh72gxXswaBAt6cMugz9Kue06bvlJbTmIsKfj_Av_omrfNJXndz4xEzjGUjE8Jg1nGbwciTPfishwijGWvtS"
})

// Bộ lọc
const orderQuantity = ref(1)
const selectedColor = ref('DEN')
const selectedSize = ref('L')

const colorOptions = ref(['DEN', 'BLACK', 'NAVY', 'KHAKI'])
const sizeOptions = ref(['S', 'M', 'L', 'XL'])

// ===== PHẦN THÊM: PHƯƠNG THỨC THANH TOÁN =====
// Payment method
const paymentMethod = ref('cod')

// Sao chép thông tin ngân hàng
const copyBankInfo = () => {
  const bankInfo = `Ngân hàng: Vietcombank
Số tài khoản: 123456789
Chủ tài khoản: CÔNG TY BIGBAG
Chi nhánh: Hà Nội
Nội dung: [Mã đơn hàng] - [Họ tên]`
  
  navigator.clipboard.writeText(bankInfo)
    .then(() => {
      alert('✅ Đã sao chép thông tin chuyển khoản!')
    })
    .catch(() => {
      alert('📋 Thông tin chuyển khoản:\n\n' + bankInfo)
    })
}
// ===== KẾT THÚC PHẦN THÊM =====

// Giảm giá
const discountAmount = 30000

// Tính tổng tiền
const totalPrice = computed(() => {
  return (selectedProduct.value.basePrice * orderQuantity.value) - discountAmount
})

// Format tiền
const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price) + '₫'
}

// Methods cho số lượng
const decreaseQuantity = () => {
  if (orderQuantity.value > 1) {
    orderQuantity.value--
  }
}

const increaseQuantity = () => {
  orderQuantity.value++
}

// Form B2B với địa chỉ chi tiết
const form = ref({
  company: '',
  email: '',
  phone: '',
  city: '',
  district: '',
  ward: '',
  address: '',
  note: '',
  requirements: ''
})

const submitRequest = () => {
  // Kiểm tra địa chỉ chi tiết bắt buộc
  if (!form.value.address) {
    alert('Vui lòng nhập địa chỉ chi tiết!')
    return
  }
  
  // Hiển thị thông tin đã nhập
  alert(`Yêu cầu báo giá đã được gửi!\n\n📋 THÔNG TIN DOANH NGHIỆP:\n- Công ty: ${form.value.company || 'Chưa nhập'}\n- Email: ${form.value.email || 'Chưa nhập'}\n- SĐT: ${form.value.phone || 'Chưa nhập'}\n\n📍 ĐỊA CHỈ GIAO HÀNG:\n- Tỉnh/Thành: ${form.value.city || 'Chưa nhập'}\n- Quận/Huyện: ${form.value.district || 'Chưa nhập'}\n- Phường/Xã: ${form.value.ward || 'Chưa nhập'}\n- Địa chỉ: ${form.value.address}\n- Ghi chú: ${form.value.note || 'Không có'}\n\n📝 YÊU CẦU:\n${form.value.requirements || 'Không có yêu cầu đặc biệt'}\n\nChúng tôi sẽ liên hệ trong vòng 30 phút.`)
  
  // Reset form
  form.value = {
    company: '',
    email: '',
    phone: '',
    city: '',
    district: '',
    ward: '',
    address: '',
    note: '',
    requirements: ''
  }
}

const addToOrder = () => {
  alert(`Đã thêm vào đơn hàng:\n- Sản phẩm: ${selectedProduct.value.name}\n- Số lượng: ${orderQuantity.value}\n- Màu: ${selectedColor.value}\n- Kích thước: ${selectedSize.value}\n- Tổng: ${formatPrice(totalPrice.value)}\n- Phương thức thanh toán: ${paymentMethod.value === 'cod' ? 'COD (Thanh toán khi nhận hàng)' : 'Chuyển khoản ngân hàng'}`)
}
</script>

<style scoped>
/* Ẩn mũi tên lên xuống của input number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}
</style>