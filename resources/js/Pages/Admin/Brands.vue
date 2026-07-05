<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    brands: { type: Array, default: () => [] }
})

const brands = ref(props.brands)

// Sắp xếp brands theo ID giảm dần
const sortedBrands = computed(() => {
    return [...brands.value].sort((a, b) => b.id - a.id)
})

// Pagination
const currentPage = ref(1);
const perPage = ref(5);

const paginatedBrands = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return sortedBrands.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(sortedBrands.value.length / perPage.value);
});

const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const fileError = ref('') 

// Chọn phương thức nhập logo: 'url' hoặc 'file'
const imageInputMode = ref('url')
const selectedFile = ref(null)
const imagePreviewUrl = ref('')

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Computed: Lọc brands theo tên
const filteredBrands = computed(() => {
    if (!brands.value || brands.value.length === 0) return []
    if (!search.value) return brands.value
    const keyword = search.value.toLowerCase().trim()
    return brands.value.filter(brand => 
        brand.name.toLowerCase().includes(keyword)
    )
})

// Sắp xếp brands theo ID giảm dần (mới nhất lên đầu)
const sortedBrands = computed(() => {
    return [...filteredBrands.value].sort((a, b) => b.id - a.id)
})

// Xem trước logo
const imagePreview = computed(() => {
    if (imagePreviewUrl.value) return imagePreviewUrl.value
    if (form.value.logo) return form.value.logo
    return null
})

const generateSlug = (name) => {
    if (!name) return ''
    return name
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
    return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands/data')
        if (response.data && Array.isArray(response.data)) {
            brands.value = response.data
        } else {
            brands.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
        brands.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', logo: '', description: '' }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = '' 
    showModal.value = true
    currentPage.value = 1
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    selectedFile.value = null
    imagePreviewUrl.value = ''
    imageInputMode.value = 'url'
    errorMessage.value = ''
    fileError.value = ''
    showModal.value = true
}

const handleFileChange = (event) => {
    const file = event.target.files[0]
    fileError.value = ''
    if (!file) return
    if (!file.type.startsWith('image/')) {
        fileError.value = 'Vui lòng chọn file ảnh (jpg, png, gif, svg, jpeg)'
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
    form.value.logo = ''
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

const saveBrand = async () => {
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên thương hiệu'
        return
    }
    if (fileError.value) {
        errorMessage.value = fileError.value
        return
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
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                response = await axios.post(`/admin/brands/${form.value.id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                response = await axios.put(`/admin/brands/${form.value.id}`, {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                })
            }
            if (response.data && response.data.success) {
                const index = brands.value.findIndex(b => b.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    brands.value[index] = response.data.data
                }
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
                currentPage.value = 1
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            if (selectedFile.value) {
                const formData = new FormData()
                formData.append('name', form.value.name)
                formData.append('description', form.value.description || '')
                formData.append('logo_file', selectedFile.value)
                formData.append('slug', generateSlug(form.value.name))
                response = await axios.post('/admin/brands', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            } else {
                response = await axios.post('/admin/brands', {
                    name: form.value.name,
                    logo: form.value.logo || null,
                    description: form.value.description || null,
                    slug: generateSlug(form.value.name)
                })
            }
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
                showModal.value = false
                form.value = { id: null, name: '', logo: '', description: '' }
                clearFile()
                currentPage.value = 1
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    try {
        const response = await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        if (response.data && response.data.success) {
            showDeleteModal.value = false
            const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
            if (index !== -1) {
                brands.value.splice(index, 1)
            }
            selectedBrand.value = null
            currentPage.value = 1
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
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

// Reset currentPage khi brands thay đổi
watch(brands, () => {
    currentPage.value = 1;
});

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm thương hiệu
                </button>
            </div>

            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(brand, index) in sortedBrands" 
                            :key="brand.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ brand.slug }}</td>
                            <td class="p-4 text-gray-500">
                                <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto object-contain" alt="logo">
                                <span v-else class="text-gray-400">---</span>
                            </td>
                            <td class="p-4 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(brand)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                        :disabled="isSaving"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(brand)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                        :disabled="isSaving"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedBrands.length === 0 && !isLoading">
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                {{ search ? 'Không tìm thấy thương hiệu nào' : 'Chưa có thương hiệu nào' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div 
            v-if="showModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-lg p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới' }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="VD: BigBag, Solo, KingBag"
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Slug tự động sinh từ tên</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <div class="flex gap-2 border-b pb-2 mb-2">
                            <button 
                                type="button" 
                                @click="imageInputMode = 'url'" 
                                :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'url' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']"
                            >
                                🔗 Nhập URL
                            </button>
                            <button 
                                type="button" 
                                @click="imageInputMode = 'file'" 
                                :class="['px-3 py-1 text-sm rounded-full transition-colors', imageInputMode === 'file' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 hover:bg-gray-200']"
                            >
                                📁 Tải ảnh lên
                            </button>
                        </div>
                        <div v-if="imageInputMode === 'url'">
                            <input 
                                v-model="form.logo" 
                                type="text" 
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                                placeholder="https://example.com/logo.png"
                                :disabled="isSaving"
                            >
                            <p class="text-xs text-gray-400 mt-1">Nhập đường dẫn ảnh logo</p>
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
                            <div class="w-24 h-24 border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img 
                                    :src="imagePreview" 
                                    class="max-w-full max-h-full object-contain" 
                                    @error="imagePreviewUrl = ''; form.logo = ''"
                                    alt="Logo preview"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none resize-none" 
                            placeholder="Mô tả về thương hiệu..."
                            :disabled="isSaving"
                        ></textarea>
                    </div>
                    
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
                        @click="saveBrand" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isSaving || !!fileError"
                    >
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa thương hiệu <strong>{{ selectedBrand?.name }}</strong>?</p>
                
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
                        @click="deleteBrand" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
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