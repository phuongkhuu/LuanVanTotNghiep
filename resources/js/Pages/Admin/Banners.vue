<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    banners: { type: Array, default: () => [] },
    campaigns: { type: Array, default: () => [] }
})

// Sắp xếp banners theo order (giả sử có trường order)
const sortedBanners = computed(() => {
    return [...banners.value].sort((a, b) => (a.order || 0) - (b.order || 0))
})

const banners = ref(props.banners)
const search = ref('')
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('')

// Pagination
const currentPage = ref(1)
const perPage = ref(8)

// Modal states
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBanner = ref(null)

// Image handling
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

// Form data
const form = ref({
    id: null,
    campaign_id: '',
    image: '',
    link: '',
    status: 1,
    order: 0
})

// Computed: lọc banners
const filteredBanners = computed(() => {
    if (!banners.value.length) return []
    if (!search.value) return banners.value
    const keyword = search.value.toLowerCase().trim()
    return banners.value.filter(banner => 
        (banner.campaign?.name?.toLowerCase().includes(keyword) || false) ||
        (banner.link?.toLowerCase().includes(keyword) || false)
    )
})

// Sắp xếp theo order
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

// Image preview
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.image) return form.value.image
    return null
})

// Reset currentPage khi search hoặc dữ liệu thay đổi
watch(search, () => { currentPage.value = 1 })
watch(() => props.banners, () => { currentPage.value = 1 })

// ========== TỰ ĐỘNG ĐIỀU CHỈNH ORDER ==========

// Khi mở modal thêm mới, gợi ý order = số banner hiện có (thêm vào cuối)
watch(showModal, (newVal) => {
    if (newVal && !isEdit.value) {
        form.value.order = banners.value.length
    }
})

// Khi thay đổi order trong modal, tự động giới hạn trong khoảng hợp lệ
watch(() => form.value.order, (newVal) => {
    if (typeof newVal !== 'number' || isNaN(newVal)) {
        form.value.order = 0
        return
    }
    
    let maxOrder
    if (isEdit.value) {
        // Khi sửa: tối đa là tổng số banner - 1 (vì đang có banner này)
        maxOrder = banners.value.length - 1
    } else {
        // Khi thêm mới: tối đa là tổng số banner (có thể thêm vào cuối)
        maxOrder = banners.value.length
    }
    
    if (newVal < 0) {
        form.value.order = 0
    } else if (newVal > maxOrder) {
        form.value.order = maxOrder
    }
})

// Format date
const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

// Fetch banners
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

// Modal handlers
const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, campaign_id: '', image: '', link: '', status: 1, order: banners.value.length }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

const openEditModal = (banner) => {
    isEdit.value = true
    form.value = { ...banner }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

// File handling
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) return
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg)'
        return
    }
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        return
    }
    selectedFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => { imagePreviewUrl.value = e.target.result }
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

// Save banner
const saveBanner = async () => {
    if (!form.value.campaign_id) {
        errorMessage.value = 'Vui lòng chọn chiến dịch'
        return
    }
    if (!form.value.image && !selectedFile.value) {
        errorMessage.value = 'Vui lòng chọn hoặc nhập ảnh banner'
        return
    }
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    // Đảm bảo order hợp lệ trước khi gửi
    let order = form.value.order || 0
    if (isEdit.value) {
        const maxOrder = banners.value.length - 1
        if (order < 0) order = 0
        if (order > maxOrder) order = maxOrder
    } else {
        const maxOrder = banners.value.length
        if (order < 0) order = 0
        if (order > maxOrder) order = maxOrder
    }
    form.value.order = order

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        if (isEdit.value) {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('campaign_id', form.value.campaign_id)
                formData.append('link', form.value.link || '')
                formData.append('status', form.value.status)
                formData.append('order', form.value.order)
                formData.append('image_file', selectedFile.value)
                response = await axios.post(`/admin/banners/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    campaign_id: form.value.campaign_id,
                    link: form.value.link || '',
                    status: form.value.status,
                    order: form.value.order,
                    image: form.value.image || null
                }
                response = await axios.put(`/admin/banners/${form.value.id}`, dataToSave)
            }
            if (response.data && response.data.success) {
                const index = banners.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    banners.value[index] = response.data.data
                }
                showModal.value = false
                form.value = { id: null, campaign_id: '', image: '', link: '', status: 1, order: 0 }
                clearFile()
                currentPage.value = 1
                // Cập nhật lại danh sách để đồng bộ thứ tự từ server
                await fetchBanners()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('campaign_id', form.value.campaign_id)
                formData.append('link', form.value.link || '')
                formData.append('status', form.value.status)
                formData.append('order', form.value.order)
                formData.append('image_file', selectedFile.value)
                response = await axios.post('/admin/banners', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    campaign_id: form.value.campaign_id,
                    link: form.value.link || '',
                    status: form.value.status,
                    order: form.value.order,
                    image: form.value.image || null
                }
                response = await axios.post('/admin/banners', dataToSave)
            }
            if (response.data && response.data.data) {
                banners.value.push(response.data.data)
                showModal.value = false
                form.value = { id: null, campaign_id: '', image: '', link: '', status: 1, order: 0 }
                clearFile()
                currentPage.value = 1
                // Cập nhật lại danh sách để đồng bộ thứ tự từ server
                await fetchBanners()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
    } catch (error) {
        console.error('Lỗi lưu banner:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

// Delete banner
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
        const response = await axios.delete(`/admin/banners/${selectedBanner.value.id}`)
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = banners.value.findIndex(b => b.id === selectedBanner.value.id)
            if (index !== -1) {
                banners.value.splice(index, 1)
            }
            selectedBanner.value = null
            currentPage.value = 1
            // Cập nhật lại danh sách để đồng bộ thứ tự từ server
            await fetchBanners()
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

// Toggle status
const toggleStatus = async (banner) => {
    try {
        const newStatus = banner.status === 1 ? 0 : 1
        const response = await axios.patch(`/admin/banners/${banner.id}/status`, { status: newStatus })
        if (response.data && response.data.success) {
            banner.status = newStatus
        } else {
            alert('Cập nhật trạng thái thất bại')
        }
    } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error)
        alert('Có lỗi xảy ra')
    }
}

// Update order
const updateOrder = async (banner, newOrder) => {
    try {
        const response = await axios.patch(`/admin/banners/${banner.id}/order`, { order: newOrder })
        if (response.data && response.data.success) {
            banner.order = newOrder
            // Sắp xếp lại danh sách
            banners.value = [...banners.value].sort((a, b) => (a.order || 0) - (b.order || 0))
            // Cập nhật lại danh sách để đồng bộ thứ tự từ server
            await fetchBanners()
        }
    } catch (error) {
        console.error('Lỗi cập nhật thứ tự:', error)
        alert('Có lỗi xảy ra khi cập nhật thứ tự')
    }
}

// Close modal
const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBanner.value = null
    form.value = { id: null, campaign_id: '', image: '', link: '', status: 1, order: 0 }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    clearFile()
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (banners.value.length === 0) {
        fetchBanners()
    }
})
</script>

<template>
    <Head title="Quản lý Banner" />
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý Banner</h1>
            </div>

            <div class="mb-6 flex flex-wrap gap-2">
                <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition" :disabled="isSaving">
                    + Thêm banner mới
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo chiến dịch hoặc link..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo chiến dịch hoặc link..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <!-- Loading -->
            <div v-if="isLoading && banners.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Hình ảnh</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Chiến dịch</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Link</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Trạng thái</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Thứ tự</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-40">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(banner, index) in sortedBanners" :key="banner.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <img v-if="banner.image" :src="banner.image" class="h-12 w-20 object-cover rounded" :alt="'Banner ' + banner.id">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 text-gray-700">{{ banner.campaign?.name || 'Chưa phân loại' }}</td>
                            <td class="p-4 text-gray-500 text-sm max-w-xs truncate">{{ banner.link || '---' }}</td>
                            <td class="p-4">
                                <button @click="toggleStatus(banner)" class="px-2 py-1 text-xs rounded-full transition" :class="banner.status === 1 ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                    {{ banner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}
                                </button>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1">
                                    <input type="number" :value="banner.order || 0" @change="updateOrder(banner, parseInt($event.target.value))" class="w-16 px-1 py-1 border rounded text-center text-sm">
                                    <span class="text-xs text-gray-400">#</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(banner.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(banner)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50" :disabled="isSaving">Sửa</button>
                                    <button @click="confirmDelete(banner)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50" :disabled="isSaving">Xóa</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedBanners.length === 0 && !isLoading">
                            <td colspan="8" class="p-8 text-center text-gray-400">Chưa có banner nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Sửa banner' : 'Thêm banner mới' }}</h3>
                    <button 
                        @click="closeModal" 
                        class="text-gray-400 hover:text-gray-600 transition-colors text-xl"
                    >✕</button>
                </div>
                
                <div class="space-y-4">
                    <!-- Campaign -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chiến dịch <span class="text-red-500">*</span></label>
                        <select 
                            v-model="form.campaign_id" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none"
                            :disabled="isSaving"
                        >
                            <option value="">-- Chọn chiến dịch --</option>
                            <option v-for="camp in campaigns" :key="camp.id" :value="camp.id">{{ camp.name }}</option>
                        </select>
                    </div>

                    <!-- Image -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh banner <span class="text-red-500">*</span></label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']">🔗 Nhập URL</button>
                            <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']">📁 Tải ảnh lên</button>
                        </div>
                        
                        <div v-if="imageInputMode === 'url'">
                            <input 
                                v-model="form.image" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                                placeholder="https://example.com/banner.jpg" 
                                :disabled="isSaving"
                            >
                        </div>
                        
                        <div v-else>
                            <input 
                                id="fileInput" 
                                type="file" 
                                accept="image/*" 
                                @change="handleFileChange" 
                                class="w-full"
                                :disabled="isSaving"
                            >
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button 
                                v-if="selectedFile" 
                                @click="clearFile" 
                                class="text-red-500 text-xs mt-1 hover:underline"
                                type="button"
                            >
                                Xóa file đã chọn
                            </button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG. Kích thước tối đa 2MB</p>
                        </div>
                        
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-32 h-20 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img 
                                    :src="imagePreview" 
                                    class="max-w-full max-h-full object-contain" 
                                    @error="imagePreviewUrl = ''; form.image = ''" 
                                    alt="Banner preview"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Link -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link (URL)</label>
                        <input 
                            v-model="form.link" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="https://example.com" 
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Đường dẫn khi người dùng click vào banner</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select 
                            v-model="form.status" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            :disabled="isSaving"
                        >
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Tạm dừng</option>
                        </select>
                    </div>

                    <!-- Order -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                        <input 
                            v-model.number="form.order" 
                            type="number" 
                            min="0" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Số nhỏ hơn hiển thị trước</p>
                    </div>

                    <!-- Error -->
                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="saveBanner" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-2"
                        :disabled="isSaving || !!fileError"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click.self="closeModal"
        >
            <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc chắn muốn xóa banner này?</p>
                <p v-if="selectedBanner" class="text-sm text-gray-500 mt-1">
                    ID: #{{ selectedBanner.id }} - {{ selectedBanner.campaign?.name || 'Chưa phân loại' }}
                </p>
                
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteBanner" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2"
                        :disabled="isSaving"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xóa...' : 'Xóa' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>