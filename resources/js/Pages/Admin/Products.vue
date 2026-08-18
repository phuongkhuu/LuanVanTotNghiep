<template>
    <Head title="Quản lý sản phẩm - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý sản phẩm</h1>
                </div>
                <button 
                    @click="openModal()" 
                    class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors"
                >
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm sản phẩm
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="tab in productTypes" 
                    :key="tab.value" 
                    @click="changeActiveType(tab.value)"
                    class="px-5 py-2.5 text-sm font-medium transition-all"
                    :class="activeType === tab.value ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                     {{ tab.label }} 
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ getTypeCount(tab.value) }}</span>
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên, danh mục, thương hiệu hoặc chất liệu..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-3 mb-4">
                <div class="w-full sm:w-auto relative">
                    <select 
                        v-model="selectedCategory" 
                        class="border rounded-lg px-3 py-2 text-sm bg-white w-48 appearance-none pr-8"
                        style="min-width: 160px;"
                    >
                        <option :value="null">Tất cả danh mục</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id" 
                            :title="cat.name"
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"
                        >
                            {{ cat.name }}
                        </option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">▼</span>
                </div>
                
                <div class="w-full sm:w-auto relative">
                    <select 
                        v-model="selectedBrand" 
                        class="border rounded-lg px-3 py-2 text-sm bg-white w-48 appearance-none pr-8"
                        style="min-width: 160px;"
                    >
                        <option :value="null">Tất cả thương hiệu</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id"
                            :title="brand.name"
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"
                        >
                            {{ brand.name }}
                        </option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">▼</span>
                </div>
                
                <div class="w-full sm:w-auto relative">
                    <select 
                        v-model="selectedColor" 
                        class="border rounded-lg px-3 py-2 text-sm bg-white w-48 appearance-none pr-8"
                        style="min-width: 160px;"
                    >
                        <option :value="null">Tất cả màu sắc</option>
                        <option v-for="color in colors" :key="color.id" :value="color.id"
                            :title="color.name"
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"
                        >
                            {{ color.name }}
                        </option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">▼</span>
                </div>
                
                <button 
                    @click="selectedCategory=null; selectedBrand=null; selectedColor=null; search=''" 
                    class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap"
                >
                    Xóa lọc
                </button>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-center py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">SẢN PHẨM</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">DANH MỤC</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">GIÁ NHẬP</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">GIÁ BÁN</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">GIÁ SALE</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">GIẢM</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">SL NHẬP</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">TỒN</th>
                                <th class="text-left py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">TRẠNG THÁI</th>
                                <th class="text-center py-2 px-2 md:px-3 text-gray-600 font-semibold whitespace-nowrap">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="product in paginatedProducts" 
                                :key="product.id" 
                                class="border-b border-gray-200 hover:bg-orange-50 transition-colors"
                                :class="product.is_on_sale ? 'bg-green-50/30' : ''"
                            >
                                <td class="py-2 px-2 md:px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="relative w-8 h-8 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                            <img 
                                                :src="product.thumbnail || ''" 
                                                class="w-full h-full object-cover" 
                                                :alt="product.name"
                                            >
                                            <!-- Badge SALE -->
                                            <span v-if="product.is_on_sale && product.sale_percent > 0" 
                                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-[7px] px-1 py-0.5 rounded-full font-bold animate-pulse">
                                                -{{ product.sale_percent }}%
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-800 text-xs md:text-sm">{{ product.name }}</span>
                                            <div class="flex items-center gap-1">
                                                <span v-if="product.is_on_sale" 
                                                      class="text-[9px] bg-green-100 text-green-700 px-1 py-0.5 rounded font-medium">
                                                    {{ product.sale_type === 'campaign' ? 'Chiến dịch' : 'Pre-order' }}
                                                </span>
                                                <span class="text-xs text-gray-400">({{ product.image_url?.length || 0 }})</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-2 md:px-3 text-gray-600 whitespace-nowrap">{{ product.category || '—' }}</td>
                                
                                <!-- GIÁ NHẬP HÀNG -->
                                <td class="py-2 px-2 md:px-3 whitespace-nowrap font-medium text-gray-700 text-center">
                                    {{ product.min_import_price ? formatPrice(product.min_import_price) : '—' }}
                                </td>
                                
                                <!-- GIÁ BÁN -->
                                <td class="py-2 px-2 md:px-3 whitespace-nowrap">
                                    <span :class="product.is_on_sale ? 'text-gray-400 line-through' : 'text-gray-500'">
                                        {{ formatPrice(product.original_price || product.price) }}
                                    </span>
                                </td>
                                
                                <!-- GIÁ SALE -->
                                <td class="py-2 px-2 md:px-3 whitespace-nowrap">
                                    <span v-if="product.is_on_sale && product.sale_price" 
                                          class="font-bold text-red-600 text-xs md:text-sm">
                                        {{ formatPrice(product.sale_price) }}
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                
                                <!-- GIẢM -->
                                <td class="py-2 px-2 md:px-3 whitespace-nowrap">
                                    <span v-if="product.is_on_sale && product.sale_percent > 0" 
                                          class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full text-[10px] font-bold">
                                        -{{ product.sale_percent }}%
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>

                                <!-- SL NHẬP -->
                                <td class="py-2 px-2 md:px-3 whitespace-nowrap text-gray-600 text-center">
                                    {{ product.total_import_quantity || 0 }}
                                </td>
                                
                                <!-- TỒN KHO -->
                                <td class="py-2 px-2 md:px-3 whitespace-nowrap" :class="product.stock < 10 ? 'text-yellow-600 font-semibold' : 'text-gray-600'">
                                    {{ product.stock }}
                                </td>
                                
                                <!-- TRẠNG THÁI -->
                                <td class="py-2 px-2 md:px-3">
                                    <div class="flex items-center gap-1">
                                        <span 
                                            class="text-[10px] px-1.5 py-0.5 rounded-full whitespace-nowrap"
                                            :class="product.stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ product.stock > 0 ? 'Còn hàng' : 'Hết hàng' }}
                                        </span>
                                        <span v-if="product.is_on_sale" 
                                              class="text-[9px] bg-red-100 text-red-700 px-1 py-0.5 rounded-full font-bold whitespace-nowrap">
                                            SALE
                                        </span>
                                    </div>
                                </td>
                                <td class="py-2 px-2 md:px-3 text-center whitespace-nowrap">
                                    <button 
                                        @click="editProduct(product)" 
                                        class="px-2 py-1 text-[10px] md:text-xs text-green-600 hover:bg-green-100 rounded-lg transition-colors font-medium"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="deleteProduct(product.id)" 
                                        class="px-2 py-1 text-[10px] md:text-xs text-red-600 hover:bg-red-100 rounded-lg ml-1 transition-colors font-medium"
                                    >
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="paginatedProducts.length === 0">
                                <td colspan="10" class="text-center py-8 text-gray-500">
                                    {{ search ? 'Không tìm thấy sản phẩm nào' : 'Không có sản phẩm nào' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer phân trang -->
                <div class="p-4 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedProducts.length }} / {{ filteredProducts.length }} sản phẩm
                    </div>
                    
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2">
                        <button
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ◄
                        </button>
                        
                        <div class="flex gap-1">
                            <button
                                v-for="page in displayedPages"
                                :key="page"
                                @click="currentPage = page"
                                class="px-3.5 py-1.5 text-sm rounded-lg transition-colors font-medium"
                                :class="currentPage === page ? 'bg-orange-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ►
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Add/Edit -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-4xl w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ modalTitle }}</h3>
                    <button 
                        @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >✕</button>
                </div>

                <!-- Hiển thị lỗi chung -->
                <div v-if="formErrors.general" class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm">
                    {{ formErrors.general }}
                </div>
                
                <div class="space-y-4">
                    <!-- Thông tin cơ bản -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Tên sản phẩm</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full border rounded-lg px-3 py-2"
                                :class="formErrors.name ? 'border-red-500' : 'border-gray-300'"
                                placeholder="Nhập tên sản phẩm"
                            >
                            <p v-if="formErrors.name" class="text-xs text-red-500 mt-1">{{ formErrors.name }}</p>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Loại sản phẩm</label>
                            <select v-model="form.type" class="w-full border rounded-lg px-3 py-2">
                                <option value="normal">Sản phẩm thường</option>
                                <option value="preorder">Pre-order</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Danh mục</label>
                            <select v-model="form.category_id" class="w-full border rounded-lg px-3 py-2">
                                <option :value="null">-- Chọn danh mục --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="formErrors.category_id" class="text-xs text-red-500 mt-1">{{ formErrors.category_id }}</p>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Thương hiệu</label>
                            <select v-model="form.brand_id" class="w-full border rounded-lg px-3 py-2">
                                <option :value="null">-- Chọn thương hiệu --</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                            </select>
                            <p v-if="formErrors.brand_id" class="text-xs text-red-500 mt-1">{{ formErrors.brand_id }}</p>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Chất liệu</label>
                            <input v-model="form.material" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="VD: Canvas, Da, ...">
                            <p v-if="formErrors.material" class="text-xs text-red-500 mt-1">{{ formErrors.material }}</p>
                        </div>
                        <!-- PHẦN HÌNH ẢNH -->
                        <div>
                            <label class="text-sm block mb-1 text-gray-700 font-medium">Hình ảnh sản phẩm (tối đa 10 ảnh)</label>

                            <div v-if="allImagePreviews.length" class="flex flex-wrap gap-3 mb-3">
                                <div 
                                    v-for="(img, idx) in allImagePreviews" 
                                    :key="idx" 
                                    class="relative w-24 h-24 border rounded overflow-hidden bg-gray-100 group shadow-sm"
                                >
                                    <img v-if="img.mediaType === 'image'" :src="img.url" class="w-full h-full object-cover" />
                                    <video v-else :src="img.url" class="w-full h-full object-cover" muted></video>
                                    <div class="absolute top-0 left-0 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded-br">{{ idx + 1 }}</div>
                                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white text-xs flex justify-around items-center py-1 opacity-0 group-hover:opacity-100 transition">
                                        <button @click="moveImage(idx, img.type, -1)" :disabled="(img.type === 'url' ? idx === 0 : idx === 0)" class="px-1.5 py-0.5 hover:bg-white/20 rounded disabled:opacity-30 disabled:cursor-not-allowed">▲</button>
                                        <button @click="moveImage(idx, img.type, 1)" :disabled="(img.type === 'url' ? idx === form.imageUrls.length - 1 : idx === form.imageFiles.length - 1)" class="px-1.5 py-0.5 hover:bg-white/20 rounded disabled:opacity-30 disabled:cursor-not-allowed">▼</button>
                                        <button @click="removeImage(idx, img.type)" class="px-1.5 py-0.5 hover:bg-red-500/30 rounded text-red-300 hover:text-white">✕</button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-400 mb-2">Chưa có ảnh hoặc video</div>

                            <div class="flex gap-2 border-b pb-2 mb-2">
                                <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">🔗 Nhập URL</button>
                                <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100']">📁 Tải ảnh lên</button>
                            </div>

                            <div v-if="imageInputMode === 'url'" class="flex gap-2">
                                <input id="imageUrlInput" type="text" placeholder="Nhập URL ảnh" class="flex-1 border rounded-lg px-3 py-2 text-sm" />
                                <button @click="addImageUrl" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm">Thêm</button>
                            </div>

                            <div v-else>
                                <input id="productImageInput" type="file" accept="image/*,video/*" multiple @change="handleFileChange" class="w-full text-sm" />
                                <p class="text-xs text-gray-400 mt-1">Chọn nhiều ảnh/video (ảnh tối đa 2MB, video tối đa 20MB mỗi file)</p>
                                <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            </div>
                            <p v-if="formErrors.image_url" class="text-xs text-red-500 mt-1">{{ formErrors.image_url }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm block mb-1 text-gray-700 font-medium">Mô tả</label>
                        <CKEditor :key="editingId" v-model="form.description" />
                        <p v-if="formErrors.description" class="text-xs text-red-500 mt-1">{{ formErrors.description }}</p>
                    </div>

                    <!-- Biến thể (variants) -->
                    <div class="space-y-3">
                        <!-- Tiêu đề và Nút thêm -->
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-base font-semibold text-gray-800">Biến thể <span class="text-sm font-normal text-gray-500">(Màu sắc, Kích thước, Giá, Tồn kho...)</span></label>
                            <button 
                                type="button" 
                                @click="addVariant" 
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-colors duration-200"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Thêm biến thể
                            </button>
                        </div>

                        <!-- Bảng dữ liệu -->
                        <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Màu sắc</th>
                                        <th class="px-4 py-3 font-medium">Kích thước</th>
                                        <th class="px-4 py-3 font-medium">Giá bán (₫)</th>
                                        <th class="px-4 py-3 font-medium">Tồn kho</th>
                                        <th class="px-4 py-3 font-medium">SL nhập</th>
                                        <th class="px-4 py-3 font-medium">Giá nhập (₫)</th>
                                        <th class="px-4 py-3 font-medium text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(variant, idx) in form.variants" :key="idx" class="hover:bg-gray-50 transition-colors">
                                        <!-- Chọn màu -->
                                        <td class="px-4 py-3 align-top min-w-[140px]">
                                            <ColorSelect
                                                v-model="variant.color_id"
                                                :colors="colors"
                                                placeholder="-- Chọn màu --"
                                                :class="{'border-red-500 ring-1 ring-red-500': formErrors[`variants.${idx}.color_id`]}"
                                                @change="clearFieldError(`variants.${idx}.color_id`)"
                                            />
                                            <span v-if="formErrors[`variants.${idx}.color_id`]" class="block mt-1.5 text-xs text-red-500">{{ formErrors[`variants.${idx}.color_id`] }}</span>
                                        </td>

                                        <!-- Kích thước -->
                                        <td class="px-4 py-3 align-top min-w-[120px]">
                                            <input 
                                                type="text" 
                                                v-model="variant.size_name" 
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
                                                :class="{'border-red-500 focus:ring-red-500': formErrors[`variants.${idx}.size_name`]}"
                                                placeholder="VD: S, M, L..."
                                                @input="clearFieldError(`variants.${idx}.size_name`)"
                                            >
                                            <span v-if="formErrors[`variants.${idx}.size_name`]" class="block mt-1.5 text-xs text-red-500">{{ formErrors[`variants.${idx}.size_name`] }}</span>
                                        </td>

                                        <!-- Giá bán -->
                                        <td class="px-4 py-3 align-top min-w-[120px]">
                                            <input 
                                                type="number" 
                                                :value="variant.price"
                                                @input="updatePrice(variant, $event); clearFieldError(`variants.${idx}.price`)"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
                                                placeholder="0"
                                                min="0"
                                                step="1000"
                                                :class="{'border-red-500 focus:ring-red-500': formErrors[`variants.${idx}.price`]}"
                                            >
                                            <span v-if="formErrors[`variants.${idx}.price`]" class="block mt-1.5 text-xs text-red-500">{{ formErrors[`variants.${idx}.price`] }}</span>
                                        </td>

                                        <!-- Tồn kho -->
                                        <td class="px-4 py-3 align-top min-w-[100px]">
                                            <input 
                                                type="number" 
                                                :value="variant.stock"
                                                @input="updateStock(variant, $event); clearFieldError(`variants.${idx}.stock`)"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
                                                placeholder="0"
                                                min="0"
                                                :class="{'border-red-500 focus:ring-red-500': formErrors[`variants.${idx}.stock`]}"
                                            >
                                            <span v-if="formErrors[`variants.${idx}.stock`]" class="block mt-1.5 text-xs text-red-500">{{ formErrors[`variants.${idx}.stock`] }}</span>
                                        </td>

                                        <!-- Số lượng nhập -->
                                        <td class="px-4 py-3 align-top min-w-[100px]">
                                            <input 
                                                type="number" 
                                                v-model="variant.import_quantity"
                                                @input="clearFieldError(`variants.${idx}.import_quantity`)"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
                                                placeholder="0"
                                                min="0"
                                                :class="{'border-red-500 focus:ring-red-500': formErrors[`variants.${idx}.import_quantity`]}"
                                            >
                                            <span v-if="formErrors[`variants.${idx}.import_quantity`]" class="block mt-1.5 text-xs text-red-500">{{ formErrors[`variants.${idx}.import_quantity`] }}</span>
                                        </td>

                                        <!-- Giá nhập -->
                                        <td class="px-4 py-3 align-top min-w-[120px]">
                                            <input 
                                                type="number" 
                                                v-model="variant.import_price"
                                                @input="clearFieldError(`variants.${idx}.import_price`)"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" 
                                                placeholder="0"
                                                min="0"
                                                step="1000"
                                                :class="{'border-red-500 focus:ring-red-500': formErrors[`variants.${idx}.import_price`]}"
                                            >
                                            <span v-if="formErrors[`variants.${idx}.import_price`]" class="block mt-1.5 text-xs text-red-500">{{ formErrors[`variants.${idx}.import_price`] }}</span>
                                        </td>

                                        <!-- Nút Xóa -->
                                        <td class="px-4 py-3 text-center align-top">
                                            <button 
                                                type="button"
                                                @click="removeVariant(idx)" 
                                                class="p-1.5 text-gray-400 bg-gray-50 rounded-md hover:text-red-600 hover:bg-red-50 transition-colors mt-0.5" 
                                                title="Xóa biến thể này"
                                            >X
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Trạng thái trống (Empty State) -->
                                    <tr v-if="form.variants.length === 0">
                                        <td colspan="7" class="py-10 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                <p class="text-base font-medium text-gray-600">Chưa có biến thể nào</p>
                                                <p class="text-sm mt-1">Hãy nhấn <button type="button" @click="addVariant" class="text-blue-600 hover:underline">Thêm biến thể</button> để thiết lập chi tiết sản phẩm.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <!-- Báo lỗi chung cho toàn bộ danh sách biến thể -->
                            <div v-if="formErrors.variants" class="p-3 bg-red-50 border-t border-red-100">
                                <p class="text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                    {{ formErrors.variants }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button 
                        @click="saveProduct" 
                        :disabled="isSubmitting || !!fileError" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
                    >
                        {{ isSubmitting ? 'Đang lưu...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import ColorSelect from '@/Components/ColorSelect.vue';
import CKEditor from '@/Components/CKEditor.vue';
import { isYouTubeUrl, getYouTubeThumbnail } from '@/utils/youtube';

const props = defineProps({
    initialProducts: { type: Array, default: () => [] },
    type: { type: String, default: 'normal' },
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    colors: { type: Array, default: () => [] },
    counts: { 
        type: Object,
        default: () => ({ normal: 0, preorder: 0 })
    }
});

const search = ref('');
const selectedCategory = ref(null);
const selectedBrand = ref(null);
const selectedColor = ref(null);
const activeType = ref(['normal', 'preorder'].includes(props.type) ? props.type : 'normal');

const currentPage = ref(1);
const perPage = ref(5);

const productTypes = [
    { value: 'normal', label: 'Sản phẩm thường' },
    { value: 'preorder', label: 'Pre-order' }
];

const products = ref(props.initialProducts);

// Tính tổng SL nhập và giá nhập thấp nhất
const productsWithImport = computed(() => {
    return products.value.map(p => {
        const variants = p.variants || [];
        const totalImportQty = variants.reduce((sum, v) => sum + (v.import_quantity || 0), 0);
        const importPrices = variants
            .map(v => v.import_price)
            .filter(price => price !== null && price > 0);
        const minImportPrice = importPrices.length ? Math.min(...importPrices) : null;
        return {
            ...p,
            total_import_quantity: totalImportQty,
            min_import_price: minImportPrice,
        };
    });
});

const showModal = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);
const modalTitle = computed(() => editingId.value ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới');

// ============ LƯU LỖI FORM ============
const formErrors = ref({});

// Hàm xóa lỗi khi người dùng sửa input
const clearFieldError = (field) => {
    if (formErrors.value[field]) {
        delete formErrors.value[field];
    }
};

// Hàm gán lỗi từ server
const setErrors = (errors) => {
    formErrors.value = {};
    if (errors) {
        if (typeof errors === 'object') {
            Object.keys(errors).forEach(key => {
                const msg = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
                formErrors.value[key] = msg;
            });
        }
    }
};

// Hàm kiểm tra client-side
const validateForm = () => {
    formErrors.value = {};
    let hasError = false;

    if (!form.value.name.trim()) {
        formErrors.value.name = 'Vui lòng nhập tên sản phẩm';
        hasError = true;
    }

    if (!form.value.category_id) {
        formErrors.value.category_id = 'Vui lòng chọn danh mục';
        hasError = true;
    }

    if (!form.value.brand_id) {
        formErrors.value.brand_id = 'Vui lòng chọn thương hiệu';
        hasError = true;
    }

    const material = form.value.material.trim();
    if (material && !/^[a-zA-ZÀ-ỹ0-9\s\-]+$/.test(material)) {
        formErrors.value.material = 'Chất liệu chỉ được chứa chữ cái (có dấu), chữ số, dấu cách và dấu gạch ngang.';
        hasError = true;
    }

    if (form.value.variants.length === 0) {
        formErrors.value.variants = 'Vui lòng thêm ít nhất một biến thể';
        hasError = true;
    } else {
        for (let i = 0; i < form.value.variants.length; i++) {
            const v = form.value.variants[i];
            if (!v.color_id) {
                formErrors.value[`variants.${i}.color_id`] = `Vui lòng chọn màu`;
                hasError = true;
            }
            if (!v.size_name || !v.size_name.trim()) {
                formErrors.value[`variants.${i}.size_name`] = `Vui lòng nhập kích thước`;
                hasError = true;
            }
            if (v.price <= 0) {
                formErrors.value[`variants.${i}.price`] = `Giá phải lớn hơn 0`;
                hasError = true;
            }
            if (v.stock < 0) {
                formErrors.value[`variants.${i}.stock`] = `Tồn kho không hợp lệ`;
                hasError = true;
            }
            if (v.import_quantity <= 0) {
                formErrors.value[`variants.${i}.import_quantity`] = `Số lượng nhập không hợp lệ`;
                hasError = true;
            }
            if (v.import_price === null || v.import_price < 0) {
                formErrors.value[`variants.${i}.import_price`] = `Vui lòng nhập giá nhập`;
                hasError = true;
            }
            // Ràng buộc: nếu có nhập hàng (import_quantity > 0) thì import_price phải > 0
            if (v.import_quantity > 0 && (v.import_price === null || v.import_price <= 0)) {
                formErrors.value[`variants.${i}.import_price`] = `Khi nhập hàng với số lượng >0, giá nhập phải lớn hơn 0`;
                hasError = true;
            }
            // Ràng buộc: nếu có giá nhập >0 thì import_quantity phải > 0
            if (v.import_price !== null && v.import_price > 0 && v.import_quantity <= 0) {
                formErrors.value[`variants.${i}.import_quantity`] = `Khi có giá nhập, số lượng nhập phải lớn hơn 0`;
                hasError = true;
            }
            // Ràng buộc stock <= import_quantity
            const importQty = v.import_quantity || 0;
            if (importQty > 0 && v.stock > importQty) {
                formErrors.value[`variants.${i}.stock`] = `Tồn kho (${v.stock}) không được vượt quá số lượng nhập (${importQty})`;
                hasError = true;
            }
            // Ràng buộc price >= import_price * 1.3
            if (v.import_price !== null && v.import_price > 0) {
                const minPrice = v.import_price * 1.3;
                if (v.price < minPrice) {
                    formErrors.value[`variants.${i}.price`] = `Giá bán phải cao hơn giá nhập ít nhất 30% (tối thiểu ${Math.round(minPrice)}đ)`;
                    hasError = true;
                }
            }
        }
    }

    if (fileError.value) {
        formErrors.value.image_url = fileError.value;
        hasError = true;
    }

    return !hasError;
};

const imageInputMode = ref('url');
const fileError = ref('');

const form = ref({
    name: '',
    category_id: null,
    brand_id: null,
    type: 'normal',
    imageUrls: [],
    imageFiles: [],
    material: '',
    description: '',
    variants: []
});

const allImagePreviews = computed(() => {
    const urls = form.value.imageUrls.map(url => {
        let mediaType = 'image';
        let thumbnail = null;
        if (isYouTubeUrl(url)) {
            mediaType = 'youtube';
            thumbnail = getYouTubeThumbnail(url);
        } else if (/\.(mp4|mov|avi|wmv|flv|mkv|webm|ogg)$/i.test(url)) {
            mediaType = 'video';
        }
        return { url, type: 'url', mediaType, thumbnail };
    });
    const files = form.value.imageFiles.map(file => {
        const isVideo = file.type.startsWith('video/');
        return {
            url: URL.createObjectURL(file),
            type: 'file',
            file,
            mediaType: isVideo ? 'video' : 'image',
            thumbnail: null
        };
    });
    return [...urls, ...files];
});

const enforceNonNegative = (value) => {
    let num = parseFloat(value);
    if (isNaN(num)) return 0;
    return Math.max(0, num);
};

const updatePrice = (variant, event) => {
    const raw = event.target.value;
    const newVal = enforceNonNegative(raw);
    variant.price = newVal;
    event.target.value = newVal;
    const idx = form.value.variants.indexOf(variant);
    if (idx !== -1) clearFieldError(`variants.${idx}.price`);
};

const updateStock = (variant, event) => {
    const raw = event.target.value;
    const newVal = enforceNonNegative(raw);
    variant.stock = newVal;
    event.target.value = newVal;
    const idx = form.value.variants.indexOf(variant);
    if (idx !== -1) clearFieldError(`variants.${idx}.stock`);
};

const addVariant = () => {
    form.value.variants.push({
        color_id: null,
        size_name: '',
        price: 0,
        stock: 0,
        import_quantity: 0,
        import_price: null
    });
};

const removeVariant = (index) => {
    form.value.variants.splice(index, 1);
    Object.keys(formErrors.value).forEach(key => {
        if (key.startsWith(`variants.${index}`)) {
            delete formErrors.value[key];
        }
    });
};

const filteredProducts = computed(() => {
    if (!productsWithImport.value.length) return [];
    return productsWithImport.value.filter(product => {
        const matchType = product.type === activeType.value;
        const matchSearch = !search.value ||
            product.name.toLowerCase().includes(search.value.toLowerCase()) ||
            (product.category && product.category.toLowerCase().includes(search.value.toLowerCase()));
        const matchCategory = !selectedCategory.value || product.category_id === selectedCategory.value;
        const matchBrand = !selectedBrand.value || product.brand_id === selectedBrand.value;
        
        let matchColor = true;
        if (selectedColor.value) {
            matchColor = product.variants?.some(v => v.color_id === selectedColor.value) || false;
        }
        
        return matchType && matchSearch && matchCategory && matchBrand && matchColor;
    });
});

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredProducts.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredProducts.value.length / perPage.value);
});

const displayedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const maxDisplay = 5;
    
    if (total <= maxDisplay) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    
    let start = Math.max(1, current - 2);
    let end = Math.min(total, start + maxDisplay - 1);
    
    if (end - start < maxDisplay - 1) {
        start = Math.max(1, end - maxDisplay + 1);
    }
    
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const getTypeCount = (type) => props.counts[type] || 0;

const formatPrice = (value) => {
    if (!value || value === 0) return '---';
    return value.toLocaleString('vi-VN') + '₫';
};

const addImageUrl = () => {
    const input = document.getElementById('imageUrlInput');
    const url = input.value.trim();
    if (!url) {
        formErrors.value.image_url = 'Vui lòng nhập URL';
        return;
    }
    if (!url.match(/^https?:\/\/.+/)) {
        formErrors.value.image_url = 'URL không hợp lệ (phải bắt đầu bằng http:// hoặc https://)';
        return;
    }
    if (form.value.imageUrls.length + form.value.imageFiles.length >= 10) {
        formErrors.value.image_url = 'Tối đa 10 ảnh';
        return;
    }
    form.value.imageUrls.push(url);
    input.value = '';
    clearFieldError('image_url');
};

const removeImage = (index, type) => {
    if (type === 'url') {
        form.value.imageUrls.splice(index, 1);
    } else if (type === 'file') {
        form.value.imageFiles.splice(index, 1);
    }
    clearFieldError('image_url');
};

const handleFileChange = (event) => {
    const files = event.target.files;
    fileError.value = '';
    if (!files.length) return;

    const total = form.value.imageFiles.length + files.length;
    if (total > 10) {
        fileError.value = `Chỉ được tối đa 10 file (ảnh + video), hiện có ${form.value.imageFiles.length}`;
        event.target.value = '';
        return;
    }

    for (let file of files) {
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
            fileError.value = `File ${file.name} không phải ảnh hoặc video`;
            continue;
        }
        const maxSize = file.type.startsWith('video/') ? 20 * 1024 * 1024 : 2 * 1024 * 1024;
        if (file.size > maxSize) {
            fileError.value = `File ${file.name} vượt quá ${maxSize / (1024 * 1024)}MB`;
            continue;
        }
        form.value.imageFiles.push(file);
    }
    event.target.value = '';
    clearFieldError('image_url');
};

const clearFiles = () => {
    form.value.imageFiles = [];
    fileError.value = '';
    const input = document.getElementById('productImageInput');
    if (input) input.value = '';
};

const openModal = (product = null) => {
    editingId.value = product?.id || null;
    imageInputMode.value = 'url';
    fileError.value = '';
    form.value.imageFiles = [];
    formErrors.value = {};

    if (product) {
        form.value = {
            name: product.name,
            category_id: product.category_id,
            brand_id: product.brand_id,
            type: product.type,
            imageUrls: product.image_url || [],
            imageFiles: [],
            material: product.material || '',
            description: product.description || '',
            variants: product.variants ? product.variants.map(v => ({
                id: v.id,
                color_id: v.color_id,
                size_name: v.size_name || '',
                price: v.price,
                stock: v.stock,
                import_quantity: v.import_quantity ?? 0,
                import_price: v.import_price ?? null
            })) : []
        };
    } else {
        form.value = {
            name: '',
            category_id: null,
            brand_id: null,
            type: activeType.value,
            imageUrls: [],
            imageFiles: [],
            material: '',
            description: '',
            variants: [{ 
                color_id: null, 
                size_name: '', 
                price: 0, 
                stock: 0,
                import_quantity: 0,
                import_price: null
            }]
        };
    }
    showModal.value = true;
};

const editProduct = (product) => openModal(product);

const saveProduct = async () => {
    formErrors.value = {};

    if (!validateForm()) {
        const modal = document.querySelector('.bg-white.rounded-xl.max-w-4xl');
        if (modal) modal.scrollTop = 0;
        return;
    }

    isSubmitting.value = true;
    const url = editingId.value
        ? route('admin.products.update', editingId.value)
        : route('admin.products.store');

    const data = {
        ...form.value,
        image_url: form.value.imageUrls,
        variants: form.value.variants.map(v => ({
            ...v,
            import_quantity: v.import_quantity ?? 0,
            import_price: v.import_price ?? null
        }))
    };
    delete data.imageFiles;
    delete data.imageUrls;

    try {
        if (editingId.value) {
            await router.put(url, data, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal.value = false;
                    router.reload({ only: ['initialProducts'] });
                },
                onError: (errors) => {
                    setErrors(errors);
                    const modal = document.querySelector('.bg-white.rounded-xl.max-w-4xl');
                    if (modal) modal.scrollTop = 0;
                }
            });
        } else {
            await router.post(url, data, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal.value = false;
                    router.reload({ only: ['initialProducts'] });
                },
                onError: (errors) => {
                    setErrors(errors);
                    const modal = document.querySelector('.bg-white.rounded-xl.max-w-4xl');
                    if (modal) modal.scrollTop = 0;
                }
            });
        }
    } catch (error) {
        console.error(error);
        formErrors.value.general = error.response?.data?.message || 'Có lỗi xảy ra khi gửi dữ liệu. Vui lòng thử lại.';
        const modal = document.querySelector('.bg-white.rounded-xl.max-w-4xl');
        if (modal) modal.scrollTop = 0;
    } finally {
        isSubmitting.value = false;
    }
};

const moveImage = (index, type, direction) => {
    if (type === 'url') {
        const arr = form.value.imageUrls;
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= arr.length) return;
        [arr[index], arr[newIndex]] = [arr[newIndex], arr[index]];
    } else if (type === 'file') {
        const arr = form.value.imageFiles;
        const newIndex = index + direction;
        if (newIndex < 0 || newIndex >= arr.length) return;
        [arr[index], arr[newIndex]] = [arr[newIndex], arr[index]];
    }
};

const deleteProduct = async (id) => {
    const product = products.value.find(p => p.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa sản phẩm "${product?.name}"?`)) return;

    try {
        await router.delete(`/admin/products/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                products.value = products.value.filter(p => p.id !== id);
            },
            onError: (errors) => {
                console.error(errors);
                setErrors(errors);
            }
        });
    } catch (error) {
        console.error(error);
        formErrors.value.general = 'Có lỗi xảy ra khi xóa sản phẩm. Vui lòng thử lại.';
    }
};

const closeModal = () => {
    showModal.value = false;
    clearFiles();
    formErrors.value = {};
};

const changeActiveType = (typeValue) => {
    if (activeType.value === typeValue) return;
    activeType.value = typeValue;
    search.value = '';
    currentPage.value = 1;
    router.get(route('admin.products.index', { type: typeValue }), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch([search, activeType, selectedCategory, selectedBrand, selectedColor], () => {
    currentPage.value = 1;
});

watch(() => props.type, (newType) => {
    if (newType && ['normal', 'preorder'].includes(newType)) {
        activeType.value = newType;
        search.value = '';
        currentPage.value = 1;
    }
});

watch(() => props.initialProducts, (val) => {
    products.value = val;
    currentPage.value = 1;
}, { immediate: true });

// Xóa lỗi khi người dùng thay đổi input
watch(
    () => form.value.name,
    () => clearFieldError('name')
);
watch(
    () => form.value.category_id,
    () => clearFieldError('category_id')
);
watch(
    () => form.value.brand_id,
    () => clearFieldError('brand_id')
);
watch(
    () => form.value.material,
    () => clearFieldError('material')
);
watch(
    () => form.value.description,
    () => clearFieldError('description')
);
</script>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>