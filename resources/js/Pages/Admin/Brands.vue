<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

// Sắp xếp brands theo ID giảm dần (mới nhất lên đầu)
const sortedBrands = computed(() => {
    return [...brands.value].sort((a, b) => b.id - a.id)
})

const brands = ref(props.brands)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)

const form = ref({
    id: null,
    name: '',
    logo: '',
    description: ''
})

// Hàm tạo slug từ name
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
        const response = await axios.get('/admin/brands')
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
    showModal.value = true
}

const openEditModal = (brand) => {
    isEdit.value = true
    form.value = { ...brand }
    showModal.value = true
}

const saveBrand = async () => {
    if (!form.value.name.trim()) {
        // Không alert, có thể thêm toast sau
        console.warn('Vui lòng nhập tên thương hiệu')
        return
    }

    if (isSaving.value) return
    isSaving.value = true

    try {
        const dataToSave = {
            name: form.value.name,
            logo: form.value.logo || null,
            description: form.value.description || null,
            slug: generateSlug(form.value.name)
        }
        
        let response
        if (isEdit.value) {
            response = await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            // Cập nhật brand trong danh sách
            const index = brands.value.findIndex(b => b.id === form.value.id)
            if (index !== -1 && response.data && response.data.data) {
                brands.value[index] = response.data.data
            }
        } else {
            response = await axios.post('/admin/brands', dataToSave)
            // Thêm brand mới vào đầu danh sách (vì ID lớn nhất)
            if (response.data && response.data.data) {
                brands.value.unshift(response.data.data)
            }
        }
        
        // Đóng modal
        showModal.value = false
        
        // Reset form
        form.value = { id: null, name: '', logo: '', description: '' }
        
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    
    try {
        await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        
        // Đóng modal xóa
        showDeleteModal.value = false
        
        // Xóa khỏi danh sách thủ công
        const index = brands.value.findIndex(b => b.id === selectedBrand.value.id)
        if (index !== -1) {
            brands.value.splice(index, 1)
        }
        
        selectedBrand.value = null
        
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedBrand.value = null
    form.value = { id: null, name: '', logo: '', description: '' }
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu" />
    
    <AdminLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <p class="text-gray-500 mt-1">Thêm, sửa hoặc xóa các thương hiệu</p>
            </div>

            <!-- Button thêm mới -->
            <div class="mb-6">
                <button 
                    @click="openCreateModal" 
                    class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition"
                    :disabled="isSaving"
                >
                    + Thêm thương hiệu mới
                </button>
            </div>

            <!-- Loading - CHỈ HIỂN THỊ KHI LOAD LẦN ĐẦU -->
            <div v-if="isLoading && brands.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <!-- Bảng danh sách - Sắp xếp theo ID giảm dần (mới nhất lên đầu) -->
            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700">ID</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Slug</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="brand in sortedBrands" 
                            :key="brand.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-700">{{ brand.id }}</td>
                            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ brand.slug }}</td>
                            <td class="p-4 text-gray-500">
                                <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto" alt="logo">
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
                                Chưa có thương hiệu nào
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
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                        <input 
                            v-model="form.logo" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="https://example.com/logo.png"
                            :disabled="isSaving"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="Mô tả về thương hiệu..."
                            :disabled="isSaving"
                        ></textarea>
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
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2"
                        :disabled="isSaving"
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
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>