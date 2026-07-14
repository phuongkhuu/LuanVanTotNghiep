<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import CKEditor from '@/Components/CKEditor.vue'

const props = defineProps({
    news: {
        type: Array,
        default: () => []
    },
    campaigns: {
        type: Array,
        default: () => []
    },
    banners: {
        type: Array,
        default: () => []
    },
    authors: {
        type: Array,
        default: () => []
    },
    currentUser: {
        type: Object,
        default: () => null
    },
    error: {
        type: String,
        default: ''
    }
})

// Search
const search = ref('')

// Filters
const filterAuthor = ref('')
const filterStatus = ref('')
const filterCampaign = ref('')

// Pagination
const currentPage = ref(1)
const perPage = ref(5)

const newsList = ref(props.news || [])
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedNews = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref(props.error || '')

// Lọc banners theo campaign đã chọn
const filteredBanners = computed(() => {
    if (!form.value.campaign_id) return []
    return props.banners.filter(banner => banner.campaign_id === form.value.campaign_id)
})

const form = ref({
    id: null,
    title: '',
    slug: '',
    content: '',
    status: 1,
    campaign_id: '',
    banner_id: ''
})

// Xem trước ảnh từ banner
const imagePreview = computed(() => {
    if (!form.value.banner_id) return null
    const selectedBanner = props.banners.find(b => b.id === form.value.banner_id)
    return selectedBanner ? selectedBanner.image : null
})

// Lọc tin tức
const filteredNews = computed(() => {
    if (!newsList.value || newsList.value.length === 0) return []
    
    let result = newsList.value
    
    if (search.value) {
        const keyword = search.value.toLowerCase().trim()
        result = result.filter(news => {
            const title = (news.title || '').toLowerCase()
            const slug = (news.slug || '').toLowerCase()
            return title.includes(keyword) || slug.includes(keyword)
        })
    }
    
    if (filterAuthor.value) {
        result = result.filter(news => news.author_name === filterAuthor.value)
    }
    
    if (filterStatus.value !== '') {
        result = result.filter(news => news.status === parseInt(filterStatus.value))
    }
    
    if (filterCampaign.value) {
        result = result.filter(news => news.campaign_id === parseInt(filterCampaign.value))
    }
    
    return result
})

// Sắp xếp
const sortedNews = computed(() => {
    return [...filteredNews.value].sort((a, b) => b.id - a.id)
})

// Pagination
const paginatedNews = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value
    return sortedNews.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(sortedNews.value.length / perPage.value)
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

watch([search, filterAuthor, filterStatus, filterCampaign], () => {
    currentPage.value = 1
})

const generateSlug = (title) => {
    if (!title) return ''
    return title
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN')
}

const getStatusText = (status) => {
    return status === 1 ? 'Xuất bản' : 'Nháp'
}

const getStatusClass = (status) => {
    return status === 1 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
}

const fetchNews = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/news/data')
        if (response.data && Array.isArray(response.data)) {
            newsList.value = response.data.map(item => ({
                ...item,
                status: item.status === true || item.status === 1 ? 1 : 0
            }))
        } else {
            newsList.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách tin tức:', error)
        newsList.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { 
        id: null, 
        title: '', 
        slug: '', 
        content: '', 
        status: 1,
        campaign_id: '',
        banner_id: ''
    }
    errorMessage.value = ''
    showModal.value = true
}

const openEditModal = (news) => {
    isEdit.value = true
    form.value = { 
        id: news.id,
        title: news.title || '', 
        slug: news.slug || '', 
        content: news.content || '', 
        status: news.status === 1 ? 1 : 0,
        campaign_id: news.campaign_id || '',
        banner_id: news.banner_id || ''
    }
    errorMessage.value = ''
    showModal.value = true
}

const saveNews = async () => {
    if (!form.value.title.trim()) {
        errorMessage.value = 'Vui lòng nhập tiêu đề'
        return
    }
    if (!form.value.content.trim()) {
        errorMessage.value = 'Vui lòng nhập nội dung'
        return
    }
    if (!form.value.campaign_id) {
        errorMessage.value = 'Vui lòng chọn chiến dịch'
        return
    }
    if (!form.value.banner_id) {
        errorMessage.value = 'Vui lòng chọn banner'
        return
    }

    if (!form.value.slug || form.value.slug === '') {
        form.value.slug = generateSlug(form.value.title)
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        const dataToSave = {
            title: form.value.title,
            slug: form.value.slug,
            content: form.value.content,
            status: form.value.status === 1 ? true : false,
            campaign_id: form.value.campaign_id,
            banner_id: form.value.banner_id
        }
        
        if (isEdit.value) {
            response = await axios.put(`/admin/news/${form.value.id}`, dataToSave, {
                headers: { 'Accept': 'application/json' }
            })
        } else {
            response = await axios.post('/admin/news', dataToSave, {
                headers: { 'Accept': 'application/json' }
            })
        }
        
        if (response.data && response.data.success) {
            const savedData = {
                ...response.data.data,
                status: response.data.data.status === true || response.data.data.status === 1 ? 1 : 0
            }
            
            if (isEdit.value) {
                const index = newsList.value.findIndex(n => n.id === form.value.id)
                if (index !== -1) {
                    newsList.value[index] = savedData
                } else {
                    await fetchNews()
                }
                alert('Cập nhật tin tức thành công!')
            } else {
                newsList.value.unshift(savedData)
                alert('Thêm tin tức thành công!')
            }
            showModal.value = false
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu tin tức:', error)
        if (error.response) {
            errorMessage.value = error.response.data?.message || 'Có lỗi xảy ra'
        } else {
            errorMessage.value = 'Không thể kết nối đến server'
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (news) => {
    selectedNews.value = news
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteNews = async () => {
    if (!selectedNews.value) return
    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    try {
        const response = await axios.delete(`/admin/news/${selectedNews.value.id}`, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = newsList.value.findIndex(n => n.id === selectedNews.value.id)
            if (index !== -1) {
                newsList.value.splice(index, 1)
            }
            selectedNews.value = null
            alert('Xóa tin tức thành công!')
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa tin tức:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const toggleStatus = async (news) => {
    try {
        const newStatus = news.status === 1 ? 0 : 1
        const response = await axios.patch(`/admin/news/${news.id}/status`, { 
            status: newStatus === 1 ? true : false
        }, {
            headers: { 'Accept': 'application/json' }
        })
        if (response.data && response.data.success) {
            news.status = newStatus
        } else {
            alert('Cập nhật trạng thái thất bại')
        }
    } catch (error) {
        console.error('Lỗi cập nhật trạng thái:', error)
        alert('Có lỗi xảy ra')
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedNews.value = null
    form.value = { 
        id: null, 
        title: '', 
        slug: '', 
        content: '', 
        status: 1,
        campaign_id: '',
        banner_id: ''
    }
    errorMessage.value = ''
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

const resetFilters = () => {
    filterAuthor.value = ''
    filterStatus.value = ''
    filterCampaign.value = ''
    search.value = ''
}

onMounted(() => {
    if (newsList.value.length === 0 && !props.error) {
        fetchNews()
    }
    if (props.error) {
        errorMessage.value = props.error
    }
})
</script>

<template>
    <Head title="Quản lý Tin tức" />
    <AdminLayout>
        <div class="p-4 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý Tin tức</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2 hover:bg-orange-700 transition-colors" :disabled="isSaving">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm tin tức mới
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-3">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tiêu đề hoặc slug..." 
                        class="pl-10 pr-4 py-1.5 bg-white border border-gray-300 rounded-full w-full focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-sm"
                    >
                </div>
            </div>

            <!-- Filter - Hàng riêng -->
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <select v-model="filterAuthor" class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 bg-white min-w-[130px]">
                    <option value="">Tác giả</option>
                    <option v-for="author in authors" :key="author" :value="author">{{ author }}</option>
                </select>
                
                <select v-model="filterStatus" class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 bg-white min-w-[130px]">
                    <option value="">Trạng thái</option>
                    <option value="1">Xuất bản</option>
                    <option value="0">Nháp</option>
                </select>
                
                <select v-model="filterCampaign" class="px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 bg-white min-w-[150px]">
                    <option value="">Chiến dịch</option>
                    <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">{{ campaign.name }}</option>
                </select>
                
                <button @click="resetFilters" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors border border-gray-300 bg-white whitespace-nowrap">
                    Xóa lọc
                </button>
            </div>

            <!-- Hiển thị lỗi -->
            <div v-if="errorMessage && !showModal" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">{{ errorMessage }}</p>
            </div>

            <div v-if="isLoading && newsList.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-orange-500 border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px]">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left p-3 font-semibold text-gray-700 w-12 whitespace-nowrap text-sm">STT</th>
                                <th class="text-left p-3 font-semibold text-gray-700 whitespace-nowrap text-sm">Ảnh</th>
                                <th class="text-left p-3 font-semibold text-gray-700 whitespace-nowrap text-sm">Tiêu đề</th>
                                <th class="text-left p-3 font-semibold text-gray-700 whitespace-nowrap text-sm">Chiến dịch</th>
                                <th class="text-left p-3 font-semibold text-gray-700 whitespace-nowrap text-sm">Tác giả</th>
                                <th class="text-left p-3 font-semibold text-gray-700 whitespace-nowrap text-sm">Trạng thái</th>
                                <th class="text-left p-3 font-semibold text-gray-700 whitespace-nowrap text-sm">Ngày tạo</th>
                                <th class="text-center p-3 font-semibold text-gray-700 w-28 whitespace-nowrap text-sm">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(news, index) in paginatedNews" :key="news.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-3 text-gray-500 text-sm whitespace-nowrap text-center">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="p-3">
                                    <img v-if="news.thumbnail" :src="news.thumbnail" class="h-10 w-14 object-cover rounded" :alt="news.title" @error="news.thumbnail = null">
                                    <span v-else class="text-gray-400 text-sm">---</span>
                                </td>
                                <td class="p-3 font-medium text-gray-700 max-w-[150px] truncate text-sm">{{ news.title }}</td>
                                <td class="p-3 text-gray-500 text-sm">{{ news.campaign?.name || '---' }}</td>
                                <td class="p-3 text-gray-500 text-sm">{{ news.author_name || '---' }}</td>
                                <td class="p-3">
                                    <button @click="toggleStatus(news)" 
                                        class="px-2 py-0.5 text-xs rounded-full transition whitespace-nowrap"
                                        :class="getStatusClass(news.status)">
                                        {{ getStatusText(news.status) }}
                                    </button>
                                </td>
                                <td class="p-3 text-gray-500 text-sm whitespace-nowrap">{{ formatDate(news.created_at) }}</td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <button @click="openEditModal(news)" class="px-2.5 py-1 text-xs text-green-600 hover:bg-green-100 rounded transition-colors font-medium" :disabled="isSaving">Sửa</button>
                                        <button @click="confirmDelete(news)" class="px-2.5 py-1 text-xs text-red-600 hover:bg-red-100 rounded transition-colors font-medium" :disabled="isSaving">Xóa</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="paginatedNews.length === 0 && !isLoading">
                                <td colspan="8" class="p-8 text-center text-gray-400">Chưa có tin tức nào</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer với phân trang -->
                <div class="p-3 border-t border-gray-200">
                    <div class="text-center text-sm text-gray-500 mb-2">
                        Hiển thị {{ paginatedNews.length }} / {{ sortedNews.length }} tin tức
                    </div>
                    
                    <div v-if="totalPages > 1" class="flex justify-center items-center gap-2">
                        <button
                            @click="currentPage--"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ◄
                        </button>
                        
                        <div class="flex gap-1">
                            <button
                                v-for="page in displayedPages"
                                :key="page"
                                @click="currentPage = page"
                                class="px-3 py-1 text-sm rounded-lg transition-colors font-medium"
                                :class="currentPage === page ? 'bg-orange-600 text-white' : 'border border-gray-300 hover:bg-gray-50'"
                            >
                                {{ page }}
                            </button>
                        </div>
                        
                        <button
                            @click="currentPage++"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            ►
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">{{ isEdit ? 'Sửa tin tức' : 'Thêm tin tức mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề *</label>
                        <input v-model="form.title" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" placeholder="Nhập tiêu đề" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tiêu đề</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (để trống để tự tạo)</label>
                        <input v-model="form.slug" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" placeholder="tu-khoa-slug" :disabled="isSaving">
                    </div>

                    <!-- Chọn Chiến dịch -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chiến dịch *</label>
                        <select v-model="form.campaign_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                            <option value="">-- Chọn chiến dịch --</option>
                            <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">
                                {{ campaign.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Chọn Banner -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Banner *</label>
                        <select v-model="form.banner_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving || !form.campaign_id">
                            <option value="">-- Chọn banner --</option>
                            <option v-for="banner in filteredBanners" :key="banner.id" :value="banner.id">
                                {{ banner.title || 'Banner #' + banner.id }}
                            </option>
                        </select>
                        <p v-if="!form.campaign_id" class="text-xs text-yellow-600 mt-1">⚠️ Vui lòng chọn chiến dịch trước</p>
                        <p v-else-if="filteredBanners.length === 0" class="text-xs text-yellow-600 mt-1">⚠️ Không có banner nào cho chiến dịch này</p>
                    </div>

                    <!-- Xem trước ảnh từ banner -->
                    <div v-if="imagePreview">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh từ Banner</label>
                        <div class="w-32 h-20 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                            <img :src="imagePreview" class="max-w-full max-h-full object-contain" :alt="'Banner image'">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Ảnh được lấy từ banner đã chọn</p>
                    </div>

                    <!-- Tác giả - Hiển thị thông tin user đang đăng nhập -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tác giả</label>
                        <div class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                            <span class="font-medium">{{ currentUser?.name || 'Admin' }}</span>
                            <span class="text-sm text-gray-400 ml-2">(Tự động lấy từ tài khoản đăng nhập)</span>
                        </div>
                        <p class="text-xs text-blue-500 mt-1">* Tác giả sẽ tự động được lấy từ tài khoản đang đăng nhập</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select v-model="form.status" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 outline-none" :disabled="isSaving">
                            <option :value="1">Xuất bản</option>
                            <option :value="0">Nháp</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung *</label>
                        <CKEditor v-model="form.content" />
                        <p class="text-xs text-gray-400 mt-1">Hỗ trợ HTML và định dạng văn bản phong phú</p>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="saveNews" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition flex items-center gap-2" :disabled="isSaving">
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
                <p class="text-gray-600">Bạn có chắc muốn xóa tin tức <strong>{{ selectedNews?.title }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="deleteNews" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2" :disabled="isSaving">
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