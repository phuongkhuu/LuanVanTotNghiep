<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
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

// Pagination - 5 items per page
const currentPage = ref(1)
const perPage = ref(5)

// Search
const search = ref('')

// Lọc banners theo tên chiến dịch hoặc link
const filteredBanners = computed(() => {
    if (!banners.value || banners.value.length === 0) return []
    if (!search.value) return banners.value
    
    const keyword = search.value.toLowerCase().trim()
    return banners.value.filter(banner => {
        const campaignName = (banner.campaign?.name || '').toLowerCase()
        const title = (banner.title || '').toLowerCase()
        const link = (banner.link || '').toLowerCase()
        return campaignName.includes(keyword) || title.includes(keyword) || link.includes(keyword)
    })
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

// Hiển thị số trang (tối đa 5 trang)
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

// Reset về trang 1 khi tìm kiếm
watch(search, () => {
    currentPage.value = 1
})

const banners = ref(props.banners || [])
const campaigns = ref(props.campaigns || [])
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBanner = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('')
const uploadSuccess = ref(false)

// Chọn phương thức nhập ảnh: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    title: '',
    campaign_id: '',
    image: '',
    link: '',
    status: 1,
    order: 0
})

// Xem trước ảnh
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.image) return form.value.image
    return null
})

// Format date
const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

// Lấy tên chiến dịch từ ID
const getCampaignName = (campaignId) => {
    if (!campaignId) return 'Chưa phân loại'
    const campaign = campaigns.value.find(c => c.id === campaignId)
    return campaign ? campaign.name : 'Chiến dịch đã xóa'
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
        const response = await axios.get('/admin/promotions/campaigns/list')
        if (response.data && Array.isArray(response.data)) {
            campaigns.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách chiến dịch:', error)
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { 
        id: null, 
        title: '',
        campaign_id: '', 
        image: '', 
        link: '', 
        status: 1, 
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
    isEdit.value = true
    form.value = { 
        id: banner.id,
        title: banner.title || '',
        campaign_id: banner.campaign_id || '', 
        image: banner.image || '', 
        link: banner.link || '', 
        status: banner.status !== undefined ? banner.status : 1, 
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

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) {
        return
    }
    // Kiểm tra định dạng file
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg, webp)'
        event.target.value = ''
        return
    }
    // Kiểm tra kích thước (2MB)
    if (file.size > 2 * 1024 * 1024) {
        fileError.value = 'Kích thước ảnh không quá 2MB'
        event.target.value = ''
        return
    }
    selectedFile.value = file
    // Tạo preview
    const reader = new FileReader()
    reader.onload = (e) => { 
        imagePreviewUrl.value = e.target.result 
    }
    reader.readAsDataURL(file)
    // Xóa URL cũ nếu có
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
    // Validate
    if (!form.value.campaign_id) {
        errorMessage.value = 'Vui lòng chọn chiến dịch'
        return
    }
    
    // Kiểm tra có ảnh không (URL hoặc file)
    if (!form.value.image && !selectedFile.value) {
        errorMessage.value = 'Vui lòng nhập URL ảnh hoặc tải ảnh lên'
        return
    }
    
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    uploadSuccess.value = false

    try {
        let response
        
        if (isEdit.value) {
            // Cập nhật banner
            if (selectedFile.value) {
                // Có file mới -> upload
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('title', form.value.title || '')
                formData.append('campaign_id', form.value.campaign_id)
                formData.append('link', form.value.link || '')
                formData.append('status', form.value.status)
                formData.append('order', form.value.order || 0)
                formData.append('image_file', selectedFile.value)
                
                response = await axios.post(`/admin/banners/${form.value.id}`, formData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
            } else {
                // Không có file mới, chỉ cập nhật thông tin
                const dataToSave = {
                    title: form.value.title || '',
                    campaign_id: form.value.campaign_id,
                    link: form.value.link || '',
                    status: form.value.status,
                    order: form.value.order || 0,
                    image: form.value.image || null
                }
                response = await axios.put(`/admin/banners/${form.value.id}`, dataToSave, {
                    headers: { 'Accept': 'application/json' }
                })
            }
            
            if (response.data && response.data.success) {
                // Cập nhật danh sách
                await fetchBanners()
                uploadSuccess.value = true
                alert('Cập nhật banner thành công!')
                showModal.value = false
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra khi cập nhật'
            }
        } else {
            // Thêm mới banner
            if (selectedFile.value) {
                // Upload file
                const formData = new FormData()
                formData.append('title', form.value.title || '')
                formData.append('campaign_id', form.value.campaign_id)
                formData.append('link', form.value.link || '')
                formData.append('status', form.value.status)
                formData.append('order', form.value.order || 0)
                formData.append('image_file', selectedFile.value)
                
                response = await axios.post('/admin/banners', formData, {
                    headers: { 
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json'
                    }
                })
            } else {
                // Dùng URL
                const dataToSave = {
                    title: form.value.title || '',
                    campaign_id: form.value.campaign_id,
                    link: form.value.link || '',
                    status: form.value.status,
                    order: form.value.order || 0,
                    image: form.value.image || null
                }
                response = await axios.post('/admin/banners', dataToSave, {
                    headers: { 'Accept': 'application/json' }
                })
            }
            
            if (response.data && response.data.success && response.data.data) {
                banners.value.push(response.data.data)
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
            errorMessage.value = error.response.data?.message || 'Có lỗi xảy ra'
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
            const index = banners.value.findIndex(b => b.id === selectedBanner.value.id)
            if (index !== -1) {
                banners.value.splice(index, 1)
            }
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

const toggleStatus = async (banner) => {
    try {
        const newStatus = banner.status === 1 ? 0 : 1
        const response = await axios.patch(`/admin/banners/${banner.id}/status`, { 
            status: newStatus 
        }, {
            headers: { 'Accept': 'application/json' }
        })
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

const updateOrder = async (banner, newOrder) => {
    try {
        const response = await axios.patch(`/admin/banners/${banner.id}/order`, { 
            order: newOrder 
        }, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            banner.order = newOrder
            // Sắp xếp lại danh sách
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
    form.value = { id: null, title: '', campaign_id: '', image: '', link: '', status: 1, order: 0 }
    errorMessage.value = ''
    fileError.value = ''
    isSaving.value = false
    uploadSuccess.value = false
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

<template>
    <Head title="Quản lý Banner" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý Banner</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors" :disabled="isSaving">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm banner mới
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo chiến dịch, tên hoặc link..." 
                        class="pl-10 pr-4 py-2 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <div v-if="isLoading && banners.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left p-4 font-semibold text-gray-700 w-16 whitespace-nowrap">STT</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Hình ảnh</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap min-w-[150px]">Chiến dịch</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Link</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Trạng thái</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Thứ tự</th>
                                <th class="text-left p-4 font-semibold text-gray-700 whitespace-nowrap">Ngày tạo</th>
                                <th class="text-center p-4 font-semibold text-gray-700 w-40 whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(banner, index) in paginatedBanners" :key="banner.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-500 text-sm whitespace-nowrap">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="p-4">
                                    <img v-if="banner.image" :src="banner.image" class="h-12 w-20 object-cover rounded" :alt="banner.title || 'Banner'" @error="banner.image = null">
                                    <span v-else class="text-gray-400">---</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              :class="banner.campaign_id ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500'">
                                            {{ banner.campaign?.name || 'Chưa phân loại' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500 text-sm max-w-xs truncate">
                                    <a v-if="banner.link" :href="banner.link" target="_blank" class="text-blue-600 hover:underline">
                                        {{ banner.link }}
                                    </a>
                                    <span v-else class="text-gray-400">---</span>
                                </td>
                                <td class="p-4">
                                    <button @click="toggleStatus(banner)" class="px-2 py-1 text-xs rounded-full transition whitespace-nowrap" :class="banner.status === 1 ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                        {{ banner.status === 1 ? 'Hoạt động' : 'Tạm dừng' }}
                                    </button>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1">
                                        <input type="number" :value="banner.order || 0" @change="updateOrder(banner, parseInt($event.target.value))" class="w-16 px-1 py-1 border rounded text-center text-sm" min="0">
                                        <span class="text-xs text-gray-400">#</span>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500 text-sm whitespace-nowrap">{{ formatDate(banner.created_at) }}</td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(banner)" class="px-3 py-1.5 text-xs text-green-600 hover:bg-green-100 rounded-lg transition-colors font-medium" :disabled="isSaving">Sửa</button>
                                        <button @click="confirmDelete(banner)" class="px-3 py-1.5 text-xs text-red-600 hover:bg-red-100 rounded-lg transition-colors font-medium" :disabled="isSaving">Xóa</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedBanners.length === 0 && !isLoading">
                                <td colspan="8" class="p-8 text-center text-gray-400">Chưa có banner nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer với phân trang căn giữa -->
                <div class="p-4 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-3">
                        Hiển thị {{ paginatedBanners.length }} / {{ sortedBanners.length }} banner
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

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Sửa banner' : 'Thêm banner mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" 
                            placeholder="Tiêu đề banner (tùy chọn)"
                            :disabled="isSaving"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chiến dịch *</label>
                        <select v-model="form.campaign_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                            <option value="">-- Chọn chiến dịch --</option>
                            <option v-for="camp in campaigns" :key="camp.id" :value="camp.id">
                                {{ camp.name }}
                                <span v-if="camp.status" class="text-xs text-gray-400">({{ camp.status === 'active' ? 'Đang diễn ra' : camp.status === 'scheduled' ? 'Sắp diễn ra' : 'Đã kết thúc' }})</span>
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Banner sẽ thuộc về chiến dịch này</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh banner *</label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']">🔗 Nhập URL</button>
                            <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']">📁 Tải ảnh lên</button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input v-model="form.image" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" placeholder="https://example.com/banner.jpg" :disabled="isSaving">
                        </div>
                        <div v-else>
                            <input id="fileInput" type="file" accept="image/*" @change="handleFileChange" class="w-full" :disabled="isSaving">
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button v-if="selectedFile" @click="clearFile" class="text-red-500 text-xs mt-1 hover:underline" type="button">✕ Xóa file đã chọn</button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF, SVG, WEBP. Kích thước tối đa 2MB</p>
                        </div>
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-32 h-20 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img :src="imagePreview" class="max-w-full max-h-full object-contain" @error="imagePreviewUrl = ''; form.image = ''" alt="Banner preview">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link (URL)</label>
                        <input v-model="form.link" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" placeholder="https://example.com" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Đường dẫn khi người dùng click vào banner</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select v-model="form.status" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Tạm dừng</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
                        <input v-model.number="form.order" type="number" min="0" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Số nhỏ hơn hiển thị trước</p>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                    
                    <div v-if="uploadSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-600">✅ Lưu thành công!</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="saveBanner" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-2" :disabled="isSaving || !!fileError">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa banner "{{ selectedBanner?.title || '#' + selectedBanner?.id }}" này?</p>
                <p class="text-xs text-gray-400 mt-1">Hành động này không thể hoàn tác</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="deleteBanner" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2" :disabled="isSaving">
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