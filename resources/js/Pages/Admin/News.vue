<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    news: {
        type: Array,
        default: () => []
    },
    productVariants: {
        type: Array,
        default: () => []
    },
})

const newsList = ref(props.news)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedNews = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('')

// Chọn phương thức nhập ảnh: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    product_variant_id: '',
    author_id: '',
    title: '',
    slug: '',
    thumbnail: '',
    content: '',
    status: 1 // thêm trạng thái nếu có
})

// Xem trước ảnh
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.thumbnail) return form.value.thumbnail
    return null
})

// Sắp xếp tin tức mới nhất lên đầu
const sortedNews = computed(() => {
    return [...newsList.value].sort((a, b) => b.id - a.id)
})

// Tạo slug từ title
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

const fetchNews = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/news/data')
        if (response.data && Array.isArray(response.data)) {
            newsList.value = response.data
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
    form.value = { id: null, product_variant_id: '', author_id: '', title: '', slug: '', thumbnail: '', content: '', status: 1 }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

const openEditModal = (news) => {
    isEdit.value = true
    form.value = { ...news }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

// Xử lý khi chọn file
const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) return
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, ...)'
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
    form.value.thumbnail = ''
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

const saveNews = async () => {
    if (!form.value.title.trim()) {
        errorMessage.value = 'Vui lòng nhập tiêu đề'
        return
    }
    if (!form.value.content.trim()) {
        errorMessage.value = 'Vui lòng nhập nội dung'
        return
    }
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
    }

    // Tự động tạo slug nếu chưa có
    if (!form.value.slug || form.value.slug === '') {
        form.value.slug = generateSlug(form.value.title)
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        if (isEdit.value) {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('_method', 'PUT')
                formData.append('title', form.value.title)
                formData.append('slug', form.value.slug)
                formData.append('content', form.value.content)
                formData.append('product_variant_id', form.value.product_variant_id || '')
                formData.append('author_id', form.value.author_id || '')
                formData.append('status', form.value.status || 1)
                formData.append('thumbnail_file', selectedFile.value)
                response = await axios.post(`/admin/news/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    title: form.value.title,
                    slug: form.value.slug,
                    content: form.value.content,
                    product_variant_id: form.value.product_variant_id || null,
                    author_id: form.value.author_id || null,
                    status: form.value.status || 1,
                    thumbnail: form.value.thumbnail || null
                }
                response = await axios.put(`/admin/news/${form.value.id}`, dataToSave)
            }
            if (response.data && response.data.success) {
                const index = newsList.value.findIndex(n => n.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    newsList.value[index] = response.data.data
                }
                showModal.value = false
                form.value = { id: null, product_variant_id: '', author_id: '', title: '', slug: '', thumbnail: '', content: '', status: 1 }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('title', form.value.title)
                formData.append('slug', form.value.slug)
                formData.append('content', form.value.content)
                formData.append('product_variant_id', form.value.product_variant_id || '')
                formData.append('author_id', form.value.author_id || '')
                formData.append('status', form.value.status || 1)
                formData.append('thumbnail_file', selectedFile.value)
                response = await axios.post('/admin/news', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                const dataToSave = {
                    title: form.value.title,
                    slug: form.value.slug,
                    content: form.value.content,
                    product_variant_id: form.value.product_variant_id || null,
                    author_id: form.value.author_id || null,
                    status: form.value.status || 1,
                    thumbnail: form.value.thumbnail || null
                }
                response = await axios.post('/admin/news', dataToSave)
            }
            if (response.data && response.data.data) {
                newsList.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, product_variant_id: '', author_id: '', title: '', slug: '', thumbnail: '', content: '', status: 1 }
                clearFile()
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
    } catch (error) {
        console.error('Lỗi lưu tin tức:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
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
        const response = await axios.delete(`/admin/news/${selectedNews.value.id}`)
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = newsList.value.findIndex(n => n.id === selectedNews.value.id)
            if (index !== -1) {
                newsList.value.splice(index, 1)
            }
            selectedNews.value = null
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
        const response = await axios.patch(`/admin/news/${news.id}/status`, { status: newStatus })
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
    form.value = { id: null, product_variant_id: '', author_id: '', title: '', slug: '', thumbnail: '', content: '', status: 1 }
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
    if (newsList.value.length === 0) {
        fetchNews()
    }
})
</script>

<template>
    <Head title="Quản lý Tin tức" />
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý Tin tức</h1>
            </div>

            <div class="mb-6">
                <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition" :disabled="isSaving">
                    + Thêm tin tức mới
                </button>
            </div>

            <div v-if="isLoading && newsList.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[1000px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ảnh</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tiêu đề</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Sản phẩm</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Trạng thái</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(news, index) in sortedNews" :key="news.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <img v-if="news.thumbnail" :src="news.thumbnail" class="h-12 w-16 object-cover rounded" :alt="news.title">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 font-medium text-gray-700 max-w-xs truncate">{{ news.title }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ news.slug }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ news.product_variant?.name || '---' }}</td>
                            <td class="p-4">
                                <button @click="toggleStatus(news)" class="px-2 py-1 text-xs rounded-full transition" :class="news.status === 1 ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                    {{ news.status === 1 ? 'Xuất bản' : 'Nháp' }}
                                </button>
                            </td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(news.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(news)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50" :disabled="isSaving">Sửa</button>
                                    <button @click="confirmDelete(news)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50" :disabled="isSaving">Xóa</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedNews.length === 0 && !isLoading">
                            <td colspan="9" class="p-8 text-center text-gray-400">Chưa có tin tức nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa tin tức' : 'Thêm tin tức mới' }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề *</label>
                        <input v-model="form.title" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" placeholder="Nhập tiêu đề" :disabled="isSaving">
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tiêu đề</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (để trống để tự tạo)</label>
                        <input v-model="form.slug" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" placeholder="tu-khoa-slug" :disabled="isSaving">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện</label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button type="button" @click="imageInputMode = 'url'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']">🔗 Nhập URL</button>
                            <button type="button" @click="imageInputMode = 'file'" :class="['px-3 py-1 text-sm rounded-full', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-600']">📁 Tải ảnh lên</button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input v-model="form.thumbnail" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" placeholder="https://example.com/thumbnail.jpg" :disabled="isSaving">
                        </div>
                        <div v-else>
                            <input id="fileInput" type="file" accept="image/*" @change="handleFileChange" class="w-full" :disabled="isSaving">
                            <div v-if="fileError" class="text-red-500 text-sm mt-1">{{ fileError }}</div>
                            <button v-if="selectedFile" @click="clearFile" class="text-red-500 text-xs mt-1 hover:underline" type="button">Xóa file đã chọn</button>
                            <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF. Kích thước tối đa 2MB</p>
                        </div>
                        <div v-if="imagePreview" class="mt-2">
                            <p class="text-sm text-gray-600 mb-1">Xem trước:</p>
                            <div class="w-32 h-20 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img :src="imagePreview" class="max-w-full max-h-full object-contain" @error="imagePreviewUrl = ''; form.thumbnail = ''" alt="Thumbnail preview">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sản phẩm liên quan</label>
                        <select v-model="form.product_variant_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" :disabled="isSaving">
                            <option value="">-- Không chọn --</option>
                            <option v-for="variant in productVariants" :key="variant.id" :value="variant.id">{{ variant.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select v-model="form.status" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" :disabled="isSaving">
                            <option :value="1">Xuất bản</option>
                            <option :value="0">Nháp</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung *</label>
                        <textarea v-model="form.content" rows="8" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none resize-none" placeholder="Nội dung bài viết..." :disabled="isSaving"></textarea>
                        <p class="text-xs text-gray-400 mt-1">Hỗ trợ HTML</p>
                    </div>

                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition" :disabled="isSaving">Hủy</button>
                    <button @click="saveNews" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2" :disabled="isSaving || !!fileError">
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