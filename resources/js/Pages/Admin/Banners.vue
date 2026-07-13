<template>
    <Head title="Quản lý Banner" />
    <AdminLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý Banner</h1>
                <div class="flex gap-3">
                    <button @click="checkAndUpdateStatus" 
                        class="flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                        :disabled="isCheckingStatus">
                        <svg v-if="isCheckingStatus" class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Kiểm tra trạng thái
                    </button>
                    <button @click="openCreateModal" class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Thêm banner mới
                    </button>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="flex flex-col gap-3 mb-6">
                <!-- Search -->
                <div class="relative max-w-md">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm kiếm theo chiến dịch, tên hoặc link..." 
                        class="pl-10 pr-4 py-1.5 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm"
                    >
                </div>
                
                <!-- Filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <select v-model="statusFilter" class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white min-w-[130px]">
                        <option value="all">Tất cả trạng thái</option>
                        <option value="active">Hoạt động</option>
                        <option value="pending">Đang chờ</option>
                        <option value="locked">Đã khóa</option>
                        <option value="inactive">Tạm dừng</option>
                    </select>
                    
                    <select v-model="campaignFilter" class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white min-w-[150px]">
                        <option value="">Chiến dịch</option>
                        <option v-for="camp in campaigns" :key="camp.id" :value="camp.id">
                            {{ camp.name }}
                        </option>
                    </select>
                    
                    <button @click="resetFilters" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors border border-gray-300 bg-white whitespace-nowrap">
                        Xóa lọc
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="isLoading && banners.length === 0" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-10 w-10 border-4 border-orange-500 border-t-transparent"></div>
            </div>

            <!-- Table -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">STT</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Hình ảnh</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tiêu đề</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Chiến dịch</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Link</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Thứ tự</th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ngày tạo</th>
                                <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(banner, index) in paginatedBanners" :key="banner.id" 
                                class="hover:bg-gray-50 transition-colors duration-150"
                                :class="{ 'opacity-75 bg-gray-50': banner.campaign?.status === 'ended' }">
                                <td class="px-3 py-2.5 text-sm text-gray-500 whitespace-nowrap text-center">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="w-14 h-10 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <img v-if="banner.image" :src="banner.image" class="w-full h-full object-cover" :alt="banner.title || 'Banner'" @error="banner.image = null">
                                        <span v-else class="text-xs text-gray-400">No image</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="text-sm font-medium text-gray-700 truncate block max-w-[120px]" :title="banner.title">
                                        {{ banner.title || 'Chưa có tiêu đề' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-sm font-medium text-gray-700">{{ banner.campaign?.name || 'Chưa phân loại' }}</span>
                                        <span class="text-xs" :class="getCampaignStatusClass(banner.campaign?.status)">
                                            {{ getCampaignStatusText(banner.campaign?.status) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <a v-if="banner.link" :href="banner.link" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline truncate block max-w-[120px]">
                                        {{ banner.link }}
                                    </a>
                                    <span v-else class="text-sm text-gray-400">---</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="getStatusBadge(banner).class">
                                            {{ getStatusBadge(banner).text }}
                                        </span>
                                        <span v-if="banner.campaign?.status === 'ended'" class="text-xs text-red-500">
                                            (Đã kết thúc)
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <input type="number" :value="banner.order || 0" 
                                        @change="updateOrder(banner, parseInt($event.target.value))" 
                                        class="w-14 px-2 py-1 border border-gray-300 rounded text-center text-sm focus:outline-none focus:ring-1 focus:ring-orange-500"
                                        min="0">
                                </td>
                                <td class="px-3 py-2.5 text-sm text-gray-500 whitespace-nowrap">{{ formatDate(banner.created_at) }}</td>
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="openEditModal(banner)" 
                                            class="px-2.5 py-1 text-xs font-medium rounded transition-colors duration-200"
                                            :class="canEdit(banner) ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 cursor-not-allowed opacity-50'"
                                            :disabled="!canEdit(banner) || isSaving"
                                            :title="!canEdit(banner) ? 'Chiến dịch đã kết thúc, không thể sửa' : 'Sửa banner'">
                                            Sửa
                                        </button>
                                        <button @click="confirmDelete(banner)" 
                                            class="px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded transition-colors duration-200"
                                            :disabled="isSaving">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedBanners.length === 0 && !isLoading">
                                <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Chưa có banner nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-sm text-gray-500">
                            Hiển thị <span class="font-medium">{{ paginatedBanners.length }}</span> / <span class="font-medium">{{ sortedBanners.length }}</span> banner
                        </div>
                        <div v-if="totalPages > 1" class="flex items-center gap-2">
                            <button @click="currentPage--" :disabled="currentPage === 1"
                                class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                ◄
                            </button>
                            <div class="flex gap-1">
                                <button v-for="page in displayedPages" :key="page" @click="currentPage = page"
                                    class="px-3 py-1 rounded-lg text-sm transition-colors"
                                    :class="currentPage === page ? 'bg-orange-500 text-white' : 'hover:bg-gray-100'">
                                    {{ page }}
                                </button>
                            </div>
                            <button @click="currentPage++" :disabled="currentPage === totalPages"
                                class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                ►
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="handleOverlayClick">
            <div class="bg-white rounded-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-xl z-10">
                    <h3 class="text-lg font-semibold text-gray-800">{{ isEdit ? 'Sửa banner' : 'Thêm banner mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <!-- ✅ Yêu cầu 3: Bắt buộc nhập tiêu đề -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tiêu đề <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.title" type="text" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Nhập tiêu đề banner" :disabled="isSaving">
                        <p v-if="!form.title && showValidation" class="text-xs text-red-500 mt-1">Vui lòng nhập tiêu đề</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chiến dịch <span class="text-red-500">*</span></label>
                        <select v-model="form.campaign_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            :disabled="isSaving">
                            <option value="">-- Chọn chiến dịch --</option>
                            <option v-for="camp in availableCampaigns" :key="camp.id" :value="camp.id">
                                {{ camp.name }} ({{ camp.status === 'active' ? 'Đang diễn ra' : 'Sắp diễn ra' }})
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Chỉ hiển thị chiến dịch đang diễn ra hoặc sắp diễn ra</p>
                        <p class="text-xs text-blue-500 mt-1">* Trạng thái banner sẽ tự động xét: Hoạt động nếu chiến dịch đang diễn ra, Đang chờ nếu chiến dịch sắp diễn ra, Đã khóa nếu chiến dịch đã kết thúc</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh banner <span class="text-red-500">*</span></label>
                        <div class="flex gap-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" 
                                :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']">
                                🔗 Nhập URL
                            </button>
                            <button type="button" @click="imageInputMode = 'file'" 
                                :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']">
                                📁 Tải ảnh lên
                            </button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input v-model="form.image" type="text" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                placeholder="https://example.com/banner.jpg" :disabled="isSaving">
                        </div>
                        <div v-else>
                            <input id="fileInput" type="file" accept="image/*" @change="handleFileChange" 
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                                :disabled="isSaving">
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button v-if="selectedFile" @click="clearFile" class="text-red-500 text-xs mt-1 hover:underline">✕ Xóa file đã chọn</button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG, WEBP. Kích thước tối đa 2MB</p>
                        </div>
                        <div v-if="imagePreview" class="mt-3">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-32 h-20 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img :src="imagePreview" class="max-w-full max-h-full object-contain" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <!-- ✅ Yêu cầu 3: Kiểm tra link -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link</label>
                        <input v-model="form.link" type="text" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="https://example.com" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Nhập URL hợp lệ (ví dụ: https://example.com)</p>
                    </div>

                    <!-- ✅ Yêu cầu 3: STT được kiểm soát -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                        <input v-model.number="form.order" type="number" min="0" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Số nhỏ hơn hiển thị trước. STT sẽ tự động sắp xếp theo thời gian chiến dịch</p>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                    <div v-if="uploadSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-600">✅ Lưu thành công!</p>
                    </div>
                </div>

                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex justify-end gap-3 rounded-b-xl">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors" :disabled="isSaving">Hủy</button>
                    <button @click="saveBanner" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors flex items-center gap-2" :disabled="isSaving || !!fileError">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="handleOverlayClick">
            <div class="bg-white rounded-xl max-w-md w-full p-6" @click.stop>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Xác nhận xóa</h3>
                </div>
                <p class="text-gray-600">Bạn có chắc muốn xóa banner "{{ selectedBanner?.title || '#' + selectedBanner?.id }}" này?</p>
                <p class="text-xs text-gray-400 mt-1">Hành động này không thể hoàn tác</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors" :disabled="isSaving">Hủy</button>
                    <button @click="deleteBanner" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    banners: {
        type: Array,
        default: () => []
    },
    campaigns: {
        type: Array,
        default: () => []
    }
})

// Pagination
const currentPage = ref(1)
const perPage = ref(5)

// Search
const search = ref('')

// Filter
const statusFilter = ref('all')
const campaignFilter = ref('')

const banners = ref(props.banners || [])
const campaigns = ref([])
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBanner = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('')
const uploadSuccess = ref(false)
const isCheckingStatus = ref(false)
const showValidation = ref(false)

const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    title: '',
    campaign_id: '',
    image: '',
    link: '',
    order: 0
})

// Lọc campaigns chỉ hiển thị active và scheduled
const availableCampaigns = computed(() => {
    return campaigns.value.filter(camp => 
        camp.status === 'active' || camp.status === 'scheduled'
    )
})

// Hàm lấy trạng thái badge
const getStatusBadge = (banner) => {
    // Nếu campaign đã kết thúc -> Đã khóa (-1)
    if (banner.campaign?.status === 'ended') {
        return { text: 'Đã khóa', class: 'bg-red-100 text-red-700' }
    }
    // Nếu campaign sắp diễn ra -> Đang chờ (0)
    if (banner.campaign?.status === 'scheduled') {
        return { text: 'Đang chờ', class: 'bg-yellow-100 text-yellow-700' }
    }
    // Nếu campaign đang diễn ra và banner hoạt động -> Hoạt động (1)
    if (banner.campaign?.status === 'active' && banner.status === 1) {
        return { text: 'Hoạt động', class: 'bg-green-100 text-green-700' }
    }
    // Mặc định -> Tạm dừng
    return { text: 'Tạm dừng', class: 'bg-gray-100 text-gray-700' }
}


// Hàm lấy text trạng thái campaign
const getCampaignStatusText = (status) => {
    if (status === 'active') return 'Đang diễn ra'
    if (status === 'scheduled') return 'Sắp diễn ra'
    if (status === 'ended') return 'Đã kết thúc'
    return ''
}

// Hàm lấy class màu cho trạng thái campaign
const getCampaignStatusClass = (status) => {
    if (status === 'active') return 'text-green-600'
    if (status === 'scheduled') return 'text-yellow-600'
    if (status === 'ended') return 'text-red-600'
    return 'text-gray-500'
}

// Kiểm tra có thể sửa không
const canEdit = (banner) => {
    // Không cho sửa nếu campaign đã kết thúc
    if (banner.campaign?.status === 'ended') {
        return false
    }
    // Cho sửa nếu campaign đang diễn ra hoặc sắp diễn ra
    return true
}

// Lọc banners
const filteredBanners = computed(() => {
    if (!banners.value || banners.value.length === 0) return []
    
    let result = banners.value
    
    // Search
    if (search.value) {
        const keyword = search.value.toLowerCase().trim()
        result = result.filter(banner => {
            const campaignName = (banner.campaign?.name || '').toLowerCase()
            const title = (banner.title || '').toLowerCase()
            const link = (banner.link || '').toLowerCase()
            return campaignName.includes(keyword) || title.includes(keyword) || link.includes(keyword)
        })
    }
    
    // Filter theo trạng thái banner
    if (statusFilter.value === 'active') {
        result = result.filter(banner => banner.status === 1 || banner.status === true)
    } else if (statusFilter.value === 'inactive') {
        result = result.filter(banner => banner.status === 0 || banner.status === false)
    } else if (statusFilter.value === 'pending') {
        result = result.filter(banner => banner.campaign?.status === 'scheduled')
    } else if (statusFilter.value === 'locked') {
        result = result.filter(banner => banner.campaign?.status === 'ended')
    }
    
    // Filter theo chiến dịch
    if (campaignFilter.value) {
        result = result.filter(banner => banner.campaign_id === parseInt(campaignFilter.value))
    }
    
    return result
})

// Sắp xếp banners theo order
const sortedBanners = computed(() => {
    return [...filteredBanners.value].sort((a, b) => (a.order || 0) - (b.order || 0))
})

// Pagination
const paginatedBanners = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return sortedBanners.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(sortedBanners.value.length / perPage.value)
})

const displayedPages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    const maxDisplay = 5
    
    if (total <= maxDisplay) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }
    
    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + maxDisplay - 1)
    
    if (end - start < maxDisplay - 1) {
        start = Math.max(1, end - maxDisplay + 1)
    }
    
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

watch([search, statusFilter, campaignFilter], () => {
    currentPage.value = 1
})

const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.image) return form.value.image
    return null
})

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const resetFilters = () => {
    search.value = ''
    statusFilter.value = 'all'
    campaignFilter.value = ''
}

const fetchBanners = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/banners/data')
        if (response.data && Array.isArray(response.data)) {
            banners.value = response.data
        } else {
            banners.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách banner:', error)
        banners.value = []
    } finally {
        isLoading.value = false
    }
}

const fetchCampaigns = async () => {
    try {
        const response = await axios.get('/admin/banners/campaigns')
        if (response.data && Array.isArray(response.data)) {
            campaigns.value = response.data
        } else {
            if (props.campaigns && props.campaigns.length > 0) {
                campaigns.value = props.campaigns
            }
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách chiến dịch:', error)
        if (props.campaigns && props.campaigns.length > 0) {
            campaigns.value = props.campaigns
        }
    }
}

const checkAndUpdateStatus = async () => {
    if (isCheckingStatus.value) return
    isCheckingStatus.value = true
    try {
        const response = await axios.post('/admin/banners/check-status')
        if (response.data && response.data.success) {
            alert(response.data.message)
            await fetchBanners()
        }
    } catch (error) {
        console.error('Lỗi kiểm tra trạng thái:', error)
        alert('Có lỗi xảy ra khi kiểm tra trạng thái')
    } finally {
        isCheckingStatus.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    showValidation.value = false
    form.value = { 
        id: null, 
        title: '',
        campaign_id: '', 
        image: '', 
        link: '', 
        order: 0 
    }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    uploadSuccess.value = false
    showModal.value = true
}

const openEditModal = (banner) => {
    // Kiểm tra có được sửa không
    if (!canEdit(banner)) {
        alert('Chiến dịch đã kết thúc, không thể sửa banner này. Bạn chỉ có thể xóa.')
        return
    }
    
    isEdit.value = true
    showValidation.value = false
    form.value = { 
        id: banner.id,
        title: banner.title || '',
        campaign_id: banner.campaign_id || '', 
        image: banner.image || '', 
        link: banner.link || '', 
        order: banner.order || 0 
    }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    uploadSuccess.value = false
    showModal.value = true
}

const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) {
        return
    }
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg, webp)'
        event.target.value = ''
        return
    }
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        event.target.value = ''
        return
    }
    selectedFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => { 
        imagePreviewUrl.value = e.target.result 
    }
    reader.readAsDataURL(file)
    form.value.image = ''
}

const clearFile = () => {
    selectedFile.value = null
    imagePreviewUrl.value = ''
    fileError.value = ''
    if (imageInputMode.value === 'file') {
        const fileInput = document.getElementById('fileInput')
        if (fileInput) fileInput.value = ''
    }
}

const saveBanner = async () => {
    showValidation.value = true
    
    // Kiểm tra tiêu đề
    if (!form.value.title || form.value.title.trim() === '') {
        errorMessage.value = 'Vui lòng nhập tiêu đề banner'
        return
    }
    
    if (!form.value.campaign_id) {
        errorMessage.value = 'Vui lòng chọn chiến dịch'
        return
    }
    
    if (imageInputMode.value === 'url') {
        if (!form.value.image || form.value.image.trim() === '') {
            errorMessage.value = 'Vui lòng nhập URL ảnh'
            return
        }
        const url = form.value.image.trim()
        if (!url.match(/^(https?:\/\/|\/)/)) {
            errorMessage.value = 'URL ảnh không hợp lệ. Vui lòng nhập đúng định dạng'
            return
        }
    } else if (imageInputMode.value === 'file') {
        if (!selectedFile.value) {
            errorMessage.value = 'Vui lòng chọn file ảnh'
            return
        }
        if (fileError.value) {
            errorMessage.value = fileError.value
            return
        }
    } else {
        errorMessage.value = 'Vui lòng chọn phương thức nhập ảnh'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    uploadSuccess.value = false

    try {
        let response
        
        const dataToSave = {
            title: form.value.title.trim(),
            campaign_id: form.value.campaign_id,
            link: form.value.link || '',
            order: parseInt(form.value.order) || 0,
        }

        if (isEdit.value) {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('title', dataToSave.title)
                formData.append('campaign_id', dataToSave.campaign_id)
                formData.append('link', dataToSave.link)
                formData.append('order', dataToSave.order)
                formData.append('image_file', selectedFile.value)
                
                response = await axios.post(`/admin/banners/${form.value.id}`, formData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
            } else {
                if (form.value.image && form.value.image.trim() !== '') {
                    dataToSave.image = form.value.image
                }
                response = await axios.put(`/admin/banners/${form.value.id}`, dataToSave, {
                    headers: { 'Accept': 'application/json' }
                })
            }
            
            if (response.data && response.data.success) {
                await fetchBanners()
                uploadSuccess.value = true
                alert('Cập nhật banner thành công!')
                showModal.value = false
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra khi cập nhật'
            }
        } else {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('title', dataToSave.title)
                formData.append('campaign_id', dataToSave.campaign_id)
                formData.append('link', dataToSave.link)
                formData.append('order', dataToSave.order)
                formData.append('image_file', selectedFile.value)
                
                response = await axios.post('/admin/banners', formData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
            } else {
                dataToSave.image = form.value.image
                response = await axios.post('/admin/banners', dataToSave, {
                    headers: { 'Accept': 'application/json' }
                })
            }
            
            if (response.data && response.data.success) {
                await fetchBanners()
                uploadSuccess.value = true
                alert('Thêm banner thành công!')
                showModal.value = false
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra khi thêm mới'
            }
        }
    } catch (error) {
        console.error('Lỗi lưu banner:', error)
        if (error.response) {
            if (error.response.data.errors) {
                const errors = error.response.data.errors
                const firstKey = Object.keys(errors)[0]
                const firstError = errors[firstKey]
                errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError
            } else {
                errorMessage.value = error.response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            errorMessage.value = 'Không thể kết nối đến server'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (banner) => {
    selectedBanner.value = banner
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBanner = async () => {
    if (!selectedBanner.value) return
    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    try {
        const response = await axios.delete(`/admin/banners/${selectedBanner.value.id}`, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            await fetchBanners()
            selectedBanner.value = null
            alert('Xóa banner thành công!')
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa banner:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const updateOrder = async (banner, newOrder) => {
    try {
        const response = await axios.patch(`/admin/banners/${banner.id}/order`, { 
            order: newOrder 
        }, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            banner.order = newOrder
            banners.value = [...banners.value].sort((a, b) => (a.order || 0) - (b.order || 0))
        }
    } catch (error) {
        console.error('Lỗi cập nhật thứ tự:', error)
        alert('Có lỗi xảy ra khi cập nhật thứ tự')
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBanner.value = null
    form.value = { id: null, title: '', campaign_id: '', image: '', link: '', order: 0 }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    uploadSuccess.value = false
    showValidation.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    fetchBanners()
    fetchCampaigns()
})
</script>

<style scoped>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>