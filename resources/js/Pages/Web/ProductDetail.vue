<template>
  <div>
    <Head :title="product.name" />
    <AppHeader />

    <main class="max-w-[1440px] mx-auto px-4 md:px-8 py-6 bg-gray-50">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 mb-6 text-gray-500 text-sm">
        <Link :href="route('home')" class="hover:text-primary">Trang chủ</Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <Link :href="route('category', { slug: product.categorySlug || 'danh-muc' })" class="hover:text-primary">
          {{ product.categoryName || 'Danh mục' }}
        </Link>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span class="text-gray-800 font-bold">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Left Gallery -->
        <div class="md:col-span-7 flex flex-col-reverse md:flex-row gap-4">
          <!-- Danh sách thumbnail -->
          <div 
            v-if="mediaItems.length > 0" 
            class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] custom-scrollbar"
          >
            <div 
              v-for="(media, idx) in mediaItems" 
              :key="idx" 
              class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden cursor-pointer bg-white flex-shrink-0 relative"
              :class="idx === activeThumb ? 'border-primary' : 'border-gray-200 hover:border-primary'"
              @click="activeThumb = idx"
            >
              <!-- Hiển thị ảnh -->
              <img 
                v-if="media.type === 'image'" 
                :src="media.url" 
                class="w-full h-full object-cover" 
                :alt="'Hình ảnh ' + (idx + 1)"
              />
              <!-- Hiển thị icon video -->
              <div v-else-if="media.type === 'video'" class="w-full h-full bg-gray-800 flex items-center justify-center text-white text-xs flex-col">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm2 0v8h12V6H4zm1 1v6l5-3-5-3z"/>
                </svg>
                <span class="mt-1 text-[10px]">Video</span>
              </div>
              <!-- Hiển thị icon YouTube -->
              <div v-else-if="media.type === 'youtube'" class="w-full h-full bg-red-600 flex items-center justify-center text-white text-xs flex-col">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
                <span class="mt-1 text-[10px]">YouTube</span>
              </div>
            </div>
          </div>
          <!-- Nếu không có ảnh, hiển thị placeholder -->
          <div v-else class="flex md:flex-col gap-3">
            <div class="min-w-[80px] w-20 h-20 border-2 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center text-gray-400 text-xs">
              No image
            </div>
          </div>

          <!-- Ảnh chính -->
          <div
            class="flex-1 aspect-[4/5] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 relative image-container"
            @mouseenter="isMagnifying = true"
            @mouseleave="isMagnifying = false"
            @mousemove="(e) => { magnifierPos.x = e.clientX; magnifierPos.y = e.clientY; }"
          >
            <!-- Ảnh -->
            <img
              v-if="currentMedia.type === 'image'"
              :src="currentMedia.url"
              class="w-full h-full object-cover"
              alt="Sản phẩm chính"
            />
            <!-- Video -->
            <video
              v-else-if="currentMedia.type === 'video'"
              :src="currentMedia.url"
              class="w-full h-full object-contain bg-black"
              autoplay
              muted
              loop
              playsinline
              controls
            ></video>
            <!-- YouTube -->
            <iframe
              v-else-if="currentMedia.type === 'youtube'"
              :src="currentMedia.embedUrl"
              class="w-full h-full"
              frameborder="0"
              allowfullscreen
              allow="autoplay; encrypted-media"
            ></iframe>
            <!-- Placeholder -->
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
              Không có ảnh
            </div>

            <!-- Kính lúp (chỉ hiển thị khi là ảnh và đang hover) -->
            <div
              v-if="currentMedia.type === 'image'"
              :style="magnifierStyle"
            ></div>
          </div>
        </div>

        <!-- Right Info -->
        <div class="md:col-span-5 flex flex-col gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <!-- Thông báo -->
          <div v-if="message" 
               class="p-3 rounded-lg text-sm text-center"
               :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            {{ message }}
          </div>

          <div>
            <!-- Hiển thị nhãn Pre-order nếu là sản phẩm pre-order -->
            <span v-if="product.is_preorder && product.is_preorder_active" class="inline-block px-3 py-1 bg-orange-500 text-white text-xs rounded-full mb-2 uppercase font-bold">
              ⏳ Pre-order
            </span>
            <span v-else-if="product.is_preorder && !product.is_preorder_active" class="inline-block px-3 py-1 bg-gray-500 text-white text-xs rounded-full mb-2 uppercase font-bold">
              ⏳ Pre-order đã kết thúc
            </span>
            <span v-else class="inline-block px-3 py-1 bg-primary text-white text-xs rounded-full mb-2 uppercase font-bold">
              Sản Phẩm Mới
            </span>
            <h1 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ product.name }}</h1>
            <div class="flex items-center gap-1 text-amber-400 mb-4">
              <span v-for="n in 5" :key="n" class="material-symbols-outlined text-base" :style="{ fontVariationSettings: n <= 4 ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
              <span class="text-gray-500 text-sm ml-2">({{ product.reviewCount || 0 }} đánh giá)</span>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <!-- ============ GIÁ HIỂN THỊ ============ -->
            <div class="flex items-baseline gap-3 flex-wrap">
              <!-- Giá hiện tại -->
              <span class="font-headline-md text-3xl font-bold" :class="product.hasSale ? 'text-red-600' : 'text-primary'">
                {{ formatPrice(product.displayPrice || variantPrice) }}
              </span>
              
              <!-- Giá gốc (gạch ngang) - chỉ hiển thị khi có sale -->
              <span v-if="product.hasSale && product.originalPrice" class="text-gray-400 line-through text-sm">
                {{ formatPrice(product.originalPrice) }}
              </span>
              
              <!-- Badge % giảm -->
              <span v-if="product.hasSale && product.salePercent > 0" class="text-red-500 font-bold text-sm bg-red-50 px-2 py-0.5 rounded-full">
                -{{ product.salePercent }}%
              </span>
              
              <!-- Badge giảm theo khoảng giá (cho sản phẩm có nhiều giá) -->
              <span v-else-if="product.oldPrice && !product.hasSale" class="text-red-500 font-bold text-sm bg-red-50 px-2 py-0.5 rounded-full">
                {{ product.discount }}
              </span>
            </div>
            <!-- =================================== -->
            
            <p class="text-gray-600 text-sm leading-relaxed">{{ product.description || 'Thiết kế tối giản, chất liệu cao cấp, bền bỉ.' }}</p>
            
            <!-- Hiển thị tồn kho cho sản phẩm thường -->
            <p v-if="!product.is_preorder && selectedVariant" class="text-sm text-gray-500">
              Tồn kho: <span class="font-semibold" :class="selectedVariant.stock > 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedVariant.stock > 0 ? selectedVariant.stock + ' sản phẩm' : 'Hết hàng' }}
              </span>
            </p>
            
            <!-- Hiển thị thông báo Pre-order -->
            <div v-if="product.is_preorder && product.is_preorder_active" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
              <p class="text-sm text-orange-700 font-semibold">
                Sản phẩm này chỉ được đặt trước (Pre-order)
              </p>
              <p class="text-xs text-orange-600 mt-1">
                Thời gian giao hàng dự kiến: 7-14 ngày làm việc
              </p>
            </div>
            
            <!-- Hiển thị thông báo Pre-order đã kết thúc -->
            <div v-if="product.is_preorder && !product.is_preorder_active" class="p-3 bg-gray-100 border border-gray-300 rounded-lg">
              <p class="text-sm text-gray-600 font-semibold">
                ⏳ Chương trình Pre-order đã kết thúc
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Sản phẩm hiện không có sẵn để đặt trước
              </p>
            </div>

            <!-- ============ PRE-ORDER INFO ============ -->
            <div v-if="product.is_preorder && product.is_preorder_active && product.preorderInfo" class="mt-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
              <!-- Progress bar -->
              <div class="mb-2">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>🎯 Đã đặt: <strong>{{ product.preorderInfo.current_buyers }}</strong> người</span>
                  <span>{{ progressPercent }}%</span>
                </div>
                <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-purple-500 to-blue-500 rounded-full transition-all duration-500"
                       :style="{ width: progressPercent + '%' }">
                  </div>
                </div>
              </div>
              
              <!-- Tiers -->
              <div class="mt-3">
                <p class="text-xs font-medium text-gray-600 mb-1.5">📊 Mức giảm giá theo số người đặt:</p>
                <div class="grid grid-cols-3 gap-2">
                  <div v-for="tier in product.preorderInfo.tiers" :key="tier.from"
                       class="text-center p-2 rounded-lg border text-xs"
                       :class="isCurrentTier(tier) ? 'border-purple-500 bg-purple-100 font-bold' : 'border-gray-200 bg-white'">
                    <div class="text-sm font-bold" :class="isCurrentTier(tier) ? 'text-purple-700' : 'text-gray-700'">
                      {{ tier.discount }}%
                    </div>
                    <div class="text-gray-500 text-[10px]">#{{ tier.from }}-{{ tier.to }}</div>
                    <div v-if="isCurrentTier(tier)" class="text-[10px] text-purple-600 font-bold mt-0.5">
                      ▼ Đang áp dụng
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Thông báo tier tiếp theo -->
              <div v-if="product.preorderInfo.next_tier" class="mt-3 p-2.5 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-xs text-yellow-700">
                  🔥 Thêm <strong>{{ product.preorderInfo.next_count }}</strong> người đặt để đạt giảm <strong>{{ product.preorderInfo.next_tier.discount }}%</strong>
                </p>
              </div>
              
              <!-- Đã đạt tier cuối -->
              <div v-else-if="product.preorderInfo.is_in_tier && !product.preorderInfo.next_tier" class="mt-3 p-2.5 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-xs text-green-700">
                  🎉 Đã đạt giảm <strong>{{ product.preorderInfo.current_discount }}%</strong>!
                  <span v-if="product.preorderInfo.current_buyers < product.preorderInfo.max_buyers">
                    Còn <strong>{{ product.preorderInfo.max_buyers - product.preorderInfo.current_buyers }}</strong> suất
                  </span>
                </p>
              </div>
              
              <!-- Chưa ở tier nào (current_buyers = 0) -->
              <div v-else-if="!product.preorderInfo.is_in_tier && product.preorderInfo.tiers && product.preorderInfo.tiers.length > 0" class="mt-3 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-700">
                  🎯 Đã đạt giảm <strong>{{ product.preorderInfo.current_discount }}%</strong> cho <strong>{{ product.preorderInfo.tiers[0]?.to || 50 }}</strong> người đầu tiên!
                </p>
              </div>
              
              <!-- Thời gian -->
              <div class="mt-2 text-xs text-gray-500">
                📅 {{ product.preorderInfo.start_date || 'Chưa xác định' }} - {{ product.preorderInfo.end_date || 'Chưa xác định' }}
              </div>
            </div>
            <!-- ============================================================ -->
          </div>

          <!-- Size selection -->
          <div v-if="product.sizes && product.sizes.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Kích thước (Size):</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="size in product.sizes" 
                :key="size" 
                class="px-6 py-2 border-2 rounded-xl text-sm transition-all"
                :class="selectedSize === size ? 'border-primary text-primary bg-amber-50' : 'border-gray-200 text-gray-600 hover:border-primary'"
                @click="selectSize(size)"
              >{{ size }}</button>
            </div>
          </div>

          <!-- Color selection -->
          <div v-if="product.colors && product.colors.length" class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Màu sắc: {{ selectedColorName }}</span>
            <div class="flex gap-3 flex-wrap">
              <button 
                v-for="color in product.colors" 
                :key="color.value" 
                class="w-10 h-10 rounded-full border-2 p-1"
                :class="selectedColor === color.value ? 'border-primary' : 'border-gray-200 hover:border-primary'"
                @click="selectColor(color.value, color.label)"
              >
                <div class="w-full h-full rounded-full" :style="{ backgroundColor: color.value }"></div>
              </button>
            </div>
          </div>

          <!-- Quantity -->
          <div class="py-4 border-t border-gray-200">
            <span class="block font-semibold text-gray-800 mb-3 uppercase text-sm">Số lượng:</span>
            <div class="flex items-center gap-4">
              <button 
                @click="decreaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="quantity <= 1"
              >
                <span class="material-symbols-outlined">remove</span>
              </button>
              <span class="text-xl font-bold w-12 text-center">{{ quantity }}</span>
              <button 
                @click="increaseQuantity" 
                class="w-10 h-10 border-2 border-gray-200 rounded-xl flex items-center justify-center hover:border-primary transition-colors"
                :disabled="!product.is_preorder && selectedVariant && quantity >= selectedVariant.stock"
              >
                <span class="material-symbols-outlined">add</span>
              </button>
            </div>
            <!-- Hiển thị giới hạn số lượng cho pre-order -->
            <p v-if="product.is_preorder" class="text-xs text-gray-500 mt-1">
              * Pre-order không giới hạn số lượng
            </p>
          </div>

          <!-- Action Buttons - PHÂN BIỆT PRE-ORDER VÀ THƯỜNG -->
          <div class="flex flex-col gap-3 py-6">
            <!-- Nếu là sản phẩm pre-order đang active: CHỈ CÓ NÚT ĐẶT TRƯỚC -->
            <template v-if="product.is_preorder && product.is_preorder_active">
              <!-- Nút Đặt trước ngay (full width) -->
              <button 
                @click="buyNow" 
                :disabled="loading || !selectedVariant"
                class="w-full h-14 bg-orange-500 text-white font-semibold rounded-xl hover:bg-orange-600 transition-all flex items-center justify-center gap-2 shadow-lg shadow-orange-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="material-symbols-outlined" v-if="!loading">bolt</span>
                <span v-if="loading" class="inline-block animate-spin">⟳</span>
                {{ loading ? 'Đang xử lý...' : 'Đặt trước ngay' }}
              </button>
              
              <!-- Nút Tùy chỉnh (full width) -->
              <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
              </Link>
            </template>

            <!-- Nếu là sản phẩm pre-order đã kết thúc: CHỈ HIỂN THỊ THÔNG BÁO -->
            <template v-else-if="product.is_preorder && !product.is_preorder_active">
              <div class="w-full h-14 bg-gray-300 text-gray-600 font-semibold rounded-xl flex items-center justify-center gap-2 cursor-not-allowed">
                <span class="material-symbols-outlined">block</span> Pre-order đã kết thúc
              </div>
            </template>

            <!-- Nếu là sản phẩm thường: 2 nút chia đôi + 1 nút full width -->
            <template v-else>
              <div class="grid grid-cols-2 gap-3">
                <button 
                  @click="addToCart" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined" v-if="!loading">shopping_cart</span>
                  <span v-if="loading" class="inline-block animate-spin">⟳</span>
                  {{ loading ? 'Đang xử lý...' : 'Thêm vào giỏ hàng' }}
                </button>
                
                <button 
                  @click="buyNow" 
                  :disabled="loading || !selectedVariant || selectedVariant.stock <= 0"
                  class="flex-1 h-14 border-2 border-primary text-primary font-semibold rounded-xl hover:bg-primary/5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="material-symbols-outlined">bolt</span> Mua ngay
                </button>
              </div>
              
              <!-- Nút Tùy chỉnh (full width) -->
              <Link :href="route('customize')" class="w-full h-14 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-3 shadow-md group bg-gray-800 hover:bg-gray-900">
                <span class="material-symbols-outlined group-hover:rotate-45 transition-transform">edit_note</span> Tùy chỉnh (Customize)
              </Link>
            </template>
          </div>

          <!-- Features list -->
          <div v-if="product.features && product.features.length" class="bg-gray-50 p-5 rounded-xl space-y-3 border border-gray-100">
            <div v-for="feature in product.features" :key="feature.icon" class="flex items-center gap-3 text-gray-600 text-sm">
              <span class="material-symbols-outlined text-primary">{{ feature.icon }}</span> {{ feature.text }}
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <section v-if="relatedProducts && relatedProducts.length" class="mt-16">
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
        <h2 class="font-headline-lg text-2xl md:text-3xl font-bold text-gray-900 mb-8">
          Đánh giá từ khách hàng
        </h2>

        <!-- Form đánh giá (chỉ hiển thị khi đã đăng nhập) -->
        <div v-if="isAuthenticated" class="mb-10 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Viết đánh giá của bạn</h3>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá của bạn</label>
            <div class="flex gap-2">
              <button
                v-for="star in 5"
                :key="star"
                @click="newReview.rating = star"
                class="text-3xl transition-colors"
                :class="star <= newReview.rating ? 'text-amber-400' : 'text-gray-300 hover:text-amber-200'"
              >
                ★
              </button>
            </div>
          </div>
          <div class="mb-4">
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Nhận xét (tùy chọn)</label>
            <textarea
              id="comment"
              v-model="newReview.comment"
              rows="3"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."
            ></textarea>
          </div>
          <button
            @click="submitReview"
            :disabled="submitting || !newReview.rating"
            class="px-6 py-2 bg-primary text-white rounded-xl hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Đang gửi...' : 'Gửi đánh giá' }}
          </button>
        </div>

        <!-- Danh sách đánh giá -->
        <div v-if="reviewList.length > 0" class="space-y-6">
          <div v-for="review in reviewList" :key="review.id" class="p-6 bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center gap-1 text-amber-400 mb-1">
                  <span v-for="n in 5" :key="n" class="material-symbols-outlined text-sm" :style="{ fontVariationSettings: n <= review.rating ? '\'FILL\' 1' : '\'FILL\' 0' }">star</span>
                </div>
                <span class="font-semibold text-gray-800">{{ review.user?.name || review.author || 'Khách hàng' }}</span>
              </div>
              <span class="text-gray-400 text-sm">{{ review.created_at ? new Date(review.created_at).toLocaleDateString('vi-VN') : review.date || '' }}</span>
            </div>
            <p class="text-gray-600 text-sm">{{ review.comment || review.content }}</p>
          </div>
        </div>
        <div v-else class="text-center text-gray-500 py-8">
          Chưa có đánh giá nào cho sản phẩm này.
        </div>

        <!-- Nút xem tất cả (nếu cần) -->
        <button v-if="reviewList.length > 5" class="mt-8 px-8 py-3 border-2 border-primary text-primary rounded-xl font-semibold text-sm hover:bg-primary/5 transition-all">
          Xem tất cả {{ reviewList.length }} đánh giá
        </button>
      </section>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import { isYouTubeUrl, getYouTubeEmbedUrl } from '@/utils/youtube';
import { useCart } from '@/utils/useCart'
import axios from 'axios'

const props = defineProps({
  product: { type: Object, required: true },
  relatedProducts: { type: Array, default: () => [] },
  reviews: { type: Array, default: () => [] },
  totalReviews: { type: Number, default: 0 }
})

// Lấy page hiện tại
const page = usePage()
const { addToCart: addToCartGlobal, cartCount, fetchCart } = useCart()

// State
const activeThumb = ref(0)
const selectedSize = ref('')
const selectedColor = ref('')
const selectedColorName = ref('')
const selectedVariant = ref(null)
const quantity = ref(1)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// State cho review
const newReview = ref({ rating: 0, comment: '' });
const submitting = ref(false);
const reviewList = ref([]);

const isMagnifying = ref(false);
const magnifierPos = ref({ x: 0, y: 0 });
const magnifierSize = 150;

// Lấy ảnh hiện tại
const currentImageUrl = computed(() => {
  return currentMedia.value.type === 'image' ? currentMedia.value.url : null;
});

// Kính lúp
const magnifierStyle = computed(() => {
  if (!isMagnifying.value || !currentImageUrl.value) return {};
  
  const container = document.querySelector('.image-container');
  if (!container) return {};
  
  const rect = container.getBoundingClientRect();
  const x = magnifierPos.value.x - rect.left;
  const y = magnifierPos.value.y - rect.top;
  
  const scale = 2.5;
  
  const bgX = (x / rect.width) * 100;
  const bgY = (y / rect.height) * 100;
  
  return {
    backgroundImage: `url(${currentImageUrl.value})`,
    backgroundSize: `${rect.width * scale}px ${rect.height * scale}px`,
    backgroundPosition: `${bgX}% ${bgY}%`,
    width: `${magnifierSize}px`,
    height: `${magnifierSize}px`,
    left: `${x + 20}px`,
    top: `${y - magnifierSize/2}px`,
    borderRadius: '50%',
    border: '2px solid #fff',
    boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
    pointerEvents: 'none',
    position: 'absolute',
    zIndex: 10,
    display: isMagnifying.value ? 'block' : 'none',
  };
});

// Kiểm tra đăng nhập
const isAuthenticated = computed(() => {
  return !!page.props.auth?.user
})

// Computed
const thumbnails = computed(() => {
  return props.product.thumbnails?.length ? props.product.thumbnails : (props.product.image_url || [])
})

// ============ VARIANT PRICE ============
const variantPrice = computed(() => {
  if (selectedVariant.value) {
    // Nếu variant có sale_price và đang on sale, ưu tiên sale_price
    if (selectedVariant.value.is_on_sale && selectedVariant.value.sale_price) {
      return selectedVariant.value.sale_price
    }
    return selectedVariant.value.price
  }
  // Fallback: dùng displayPrice từ product
  if (props.product.displayPrice) {
    return props.product.displayPrice
  }
  if (props.product.price) {
    const priceStr = props.product.price.replace(/[₫,.]/g, '').trim()
    return parseInt(priceStr) || 0
  }
  return 0
})

// ============ FORMAT PRICE ============
const formatPrice = (price) => {
  if (!price && price !== 0) return '0₫'
  if (typeof price === 'number') {
    return new Intl.NumberFormat('vi-VN').format(price) + '₫'
  }
  // Nếu là chuỗi, thử parse
  const num = parseInt(String(price).replace(/[^0-9]/g, ''))
  if (!isNaN(num) && num > 0) {
    return new Intl.NumberFormat('vi-VN').format(num) + '₫'
  }
  return price || '0₫'
}

// ============ COMPUTED CHO PRE-ORDER ============
const progressPercent = computed(() => {
  if (!props.product.preorderInfo || !props.product.preorderInfo.max_buyers) return 0;
  const current = props.product.preorderInfo.current_buyers || 0;
  const max = props.product.preorderInfo.max_buyers || 100;
  return Math.min(Math.round((current / max) * 100), 100);
});

const isCurrentTier = (tier) => {
  const current = props.product.preorderInfo?.current_buyers || 0;
  return current >= (tier.from || 0) && current <= (tier.to || 999999);
};

// Methods
const selectSize = (size) => {
  selectedSize.value = size
  findVariant()
}

const selectColor = (color, label) => {
  selectedColor.value = color
  selectedColorName.value = label
  findVariant()
}

const findVariant = () => {
  const variants = props.product.variants || []
  
  if (!variants.length) {
    selectedVariant.value = null
    return
  }

  let found = null

  if (selectedColor.value && selectedSize.value) {
    found = variants.find(v => 
      String(v.color_id) === String(selectedColor.value) && 
      v.size_name === selectedSize.value
    )
  }
  
  if (!found && selectedColor.value) {
    found = variants.find(v => String(v.color_id) === String(selectedColor.value))
  }
  
  if (!found && selectedSize.value) {
    found = variants.find(v => v.size_name === selectedSize.value)
  }
  
  if (!found && variants.length > 0) {
    found = variants[0]
    if (found.color_id) {
      const color = props.product.colors?.find(c => c.value === found.color_id)
      if (color) {
        selectedColor.value = color.value
        selectedColorName.value = color.label
      }
    }
    if (found.size_name) {
      selectedSize.value = found.size_name
    }
  }

  selectedVariant.value = found
  
  if (found) {
    quantity.value = 1
  }
}

const increaseQuantity = () => {
  // Cho pre-order: không giới hạn số lượng
  if (props.product.is_preorder) {
    quantity.value++
    return
  }
  
  // Cho sản phẩm thường: kiểm tra stock
  if (selectedVariant.value && quantity.value < selectedVariant.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const showMessage = (msg, type = 'success') => {
  message.value = msg
  messageType.value = type
  setTimeout(() => { 
    message.value = '' 
  }, 3000)
}

const goToLogin = () => {
  sessionStorage.setItem('redirectAfterLogin', window.location.href)
  router.get(route('login'))
}

// ===== HÀM MUA NGAY (XỬ LÝ CẢ PRE-ORDER VÀ THƯỜNG) =====
const buyNow = async () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để mua hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  // Kiểm tra stock (chỉ cho sản phẩm thường)
  if (!props.product.is_preorder && selectedVariant.value.stock <= 0) {
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (!props.product.is_preorder && quantity.value > selectedVariant.value.stock) {
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  try {
    // ✅ PRE-ORDER: Lưu vào session và chuyển thẳng đến checkout
    if (props.product.is_preorder && props.product.is_preorder_active) {
      await axios.post('/api/pre-order/session', {
        variant_id: selectedVariant.value.id,
        quantity: quantity.value
      }, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        withCredentials: true
      })
      
      loading.value = false
      router.get(route('checkout'))
      return
    }

    // ✅ SẢN PHẨM THƯỜNG: Thêm vào giỏ hàng rồi chuyển đến checkout
    await addToCartGlobal(selectedVariant.value.id, quantity.value)
    
    loading.value = false
    router.get(route('checkout'))
    
  } catch (error) {
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
    loading.value = false
  }
}

// ===== THÊM VÀO GIỎ HÀNG (CHỈ DÀNH CHO SẢN PHẨM THƯỜNG) =====
const addToCart = async () => {
  // KIỂM TRA ĐĂNG NHẬP
  if (!isAuthenticated.value) {
    showMessage('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error')
    setTimeout(() => {
      goToLogin()
    }, 1500)
    return
  }
  
  // Validate
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước', 'error')
    return
  }

  if (selectedVariant.value.stock <= 0) {
    showMessage('Sản phẩm đã hết hàng', 'error')
    return
  }

  if (quantity.value > selectedVariant.value.stock) {
    showMessage(`Sản phẩm chỉ còn ${selectedVariant.value.stock} sản phẩm`, 'error')
    return
  }

  loading.value = true

  try {
    await addToCartGlobal(selectedVariant.value.id, quantity.value)
    showMessage('✅ Đã thêm vào giỏ hàng thành công!', 'success')
    
    // Cập nhật lại số lượng trên header
    await fetchCart()
    
  } catch (error) {
    if (error.response && error.response.status === 401) {
      showMessage('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'error')
      setTimeout(() => {
        goToLogin()
      }, 1500)
      return
    }
    
    const msg = error.response?.data?.message || 'Không thể kết nối đến server. Vui lòng thử lại.'
    showMessage(msg, 'error')
  } finally {
    loading.value = false
  }
}

const addToCartSimple = (item) => {
  router.get(route('product.detail', { id: item.id }))
}

// ===== GỬI ĐÁNH GIÁ =====
const submitReview = async () => {
  if (!selectedVariant.value) {
    showMessage('Vui lòng chọn màu sắc và kích thước trước khi đánh giá', 'error')
    return
  }
  if (newReview.value.rating === 0) {
    showMessage('Vui lòng chọn số sao đánh giá', 'error')
    return
  }

  submitting.value = true
  try {
    const response = await axios.post('/reviews', {
      product_variant_id: selectedVariant.value.id,
      rating: newReview.value.rating,
      comment: newReview.value.comment
    }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      withCredentials: true
    })

    if (response.status === 201) {
      // Thêm review mới vào đầu danh sách
      reviewList.value.unshift(response.data.review)
      // Reset form
      newReview.value = { rating: 0, comment: '' }
      showMessage('Cảm ơn bạn đã đánh giá!', 'success')
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Không thể gửi đánh giá. Vui lòng thử lại.'
    showMessage(msg, 'error')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  reviewList.value = props.reviews || [];

  // Khởi tạo màu và size mặc định
  if (props.product.colors && props.product.colors.length > 0) {
    const firstColor = props.product.colors[0]
    selectedColor.value = firstColor.value
    selectedColorName.value = firstColor.label
  }

  if (props.product.sizes && props.product.sizes.length > 0) {
    selectedSize.value = props.product.sizes[0]
  }

  findVariant()
})

// Phân loại media (ảnh/video)
const mediaItems = computed(() => {
  const list = thumbnails.value || [];
  return list.map(url => {
    if (!url) return null;
    if (isYouTubeUrl(url)) {
      return {
        url,
        type: 'youtube',
        embedUrl: getYouTubeEmbedUrl(url)
      };
    }
    const ext = url.split('.').pop().toLowerCase();
    const videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', 'ogg'];
    return {
      url,
      type: videoExtensions.includes(ext) ? 'video' : 'image'
    };
  }).filter(Boolean);
});

// Media đang được chọn
const currentMedia = computed(() => {
  if (!mediaItems.value.length) {
    return { url: '', type: 'image' };
  }
  return mediaItems.value[activeThumb.value] || mediaItems.value[0];
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E85D04; border-radius: 10px; }
.product-card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.product-card-hover:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>