<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

// Props nhận từ controller
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    }
})

const brands = ref(props.brands)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)
const isLoading = ref(false)

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
    isLoading.value = true
    try {
        const response = await axios.get('/admin/brands')
        brands.value = response.data
    } catch (error) {
        console.error('Lỗi lấy danh sách thương hiệu:', error)
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
        alert('Vui lòng nhập tên thương hiệu')
        return
    }

    isLoading.value = true
    try {
        const dataToSave = {
            name: form.value.name,
            logo: form.value.logo,
            description: form.value.description,
            slug: generateSlug(form.value.name)
        }
        
        if (isEdit.value) {
            await axios.put(`/admin/brands/${form.value.id}`, dataToSave)
            alert('Cập nhật thương hiệu thành công!')
        } else {
            await axios.post('/admin/brands', dataToSave)
            alert('Thêm thương hiệu thành công!')
        }
        await fetchBrands()
        closeModal()
    } catch (error) {
        console.error('Lỗi lưu thương hiệu:', error)
        alert(error.response?.data?.message || 'Có lỗi xảy ra')
    } finally {
        isLoading.value = false
    }
}

const confirmDelete = (brand) => {
    selectedBrand.value = brand
    showDeleteModal.value = true
}

const deleteBrand = async () => {
    isLoading.value = true
    try {
        await axios.delete(`/admin/brands/${selectedBrand.value.id}`)
        alert('Xóa thương hiệu thành công!')
        await fetchBrands()
        showDeleteModal.value = false
    } catch (error) {
        console.error('Lỗi xóa thương hiệu:', error)
        alert('Có lỗi xảy ra')
    } finally {
        isLoading.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    selectedBrand.value = null
}

onMounted(() => {
    if (brands.value.length === 0) {
        fetchBrands()
    }
})
</script>

<template>
    <Head title="Quản lý thương hiệu - BigBag Admin" />
    
    <AdminLayout>
        <!-- Nội dung chính - sẽ hiển thị bên cạnh sidebar -->
        <div class="p-4 md:p-8">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                <Link :href="route('admin.dashboard')" class="hover:text-primary transition-colors">Dashboard</Link>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span class="text-gray-700 font-medium">Quản lý thương hiệu</span>
            </div>

            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý thương hiệu</h1>
                <p class="text-gray-500 text-sm mt-1">Thêm, sửa hoặc xóa các thương hiệu sản phẩm</p>
            </div>

            <!-- Button thêm mới và thống kê -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <button 
                    @click="openCreateModal" 
                    class="bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-lg transition-all flex items-center gap-2 shadow-sm"
                >
                    <span class="material-symbols-outlined text-sm">add</span>
                    Thêm thương hiệu mới
                </button>
                
                <div class="bg-white rounded-lg px-4 py-2 border border-gray-200 shadow-sm">
                    <span class="text-gray-500 text-sm">Tổng số:</span>
                    <span class="font-semibold text-primary ml-1">{{ brands.length }}</span>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-12 bg-white rounded-xl border border-gray-200">
                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-primary border-t-transparent"></div>
                <p class="mt-3 text-gray-500">Đang tải dữ liệu...</p>
            </div>

            <!-- Bảng danh sách -->
            <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left p-4 font-semibold text-gray-700 text-sm">ID</th>
                                <th class="text-left p-4 font-semibold text-gray-700 text-sm">Tên thương hiệu</th>
                                <th class="text-left p-4 font-semibold text-gray-700 text-sm">Slug</th>
                                <th class="text-left p-4 font-semibold text-gray-700 text-sm">Logo</th>
                                <th class="text-left p-4 font-semibold text-gray-700 text-sm">Mô tả</th>
                                <th class="text-left p-4 font-semibold text-gray-700 text-sm">Ngày tạo</th>
                                <th class="text-center p-4 font-semibold text-gray-700 text-sm">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="brand in brands" 
                                :key="brand.id" 
                                class="border-b border-gray-100 hover:bg-orange-50/40 transition-all duration-150"
                            >
                                <td class="p-4 text-gray-700 text-sm">{{ brand.id }}</td>
                                <td class="p-4 font-medium text-gray-800 text-sm">{{ brand.name }}</td>
                                <td class="p-4 text-gray-400 text-sm font-mono">{{ brand.slug }}</td>
                                <td class="p-4">
                                    <div v-if="brand.logo" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                                        <img :src="brand.logo" class="max-w-full max-h-full object-contain" alt="logo">
                                    </div>
                                    <span v-else class="text-gray-400 text-xs">---</span>
                                </td>
                                <td class="p-4 text-gray-500 text-sm max-w-xs truncate">{{ brand.description || '---' }}</td>
                                <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button 
                                            @click="openEditModal(brand)" 
                                            class="text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition text-sm flex items-center gap-1"
                                        >
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                            Sửa
                                        </button>
                                        <button 
                                            @click="confirmDelete(brand)" 
                                            class="text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg hover:bg-red-50 transition text-sm flex items-center gap-1"
                                        >
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="brands.length === 0">
                                <td colspan="7" class="p-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-5xl mb-2">inbox</span>
                                    <p>Chưa có thương hiệu nào</p>
                                    <button 
                                        @click="openCreateModal" 
                                        class="mt-3 text-primary hover:underline text-sm"
                                    >
                                        Thêm thương hiệu đầu tiên
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
            <div class="bg-white rounded-xl w-full max-w-lg p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Sửa thương hiệu' : 'Thêm thương hiệu mới' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên thương hiệu <span class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" 
                            placeholder="VD: BigBag, Solo, KingBag"
                        >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                        <input 
                            v-model="form.logo" 
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" 
                            placeholder="https://example.com/logo.png"
                        >
                        <p class="text-xs text-gray-400 mt-1">Nhập đường dẫn ảnh logo</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none" 
                            placeholder="Mô tả về thương hiệu..."
                        ></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Hủy
                    </button>
                    <button @click="saveBrand" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition shadow-sm">
                        {{ isEdit ? 'Cập nhật' : 'Thêm mới' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xác nhận xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDeleteModal = false">
            <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-500">warning</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Xác nhận xóa</h3>
                </div>
                <p class="text-gray-600">Bạn có chắc chắn muốn xóa thương hiệu <strong class="text-primary">{{ selectedBrand?.name }}</strong>?</p>
                <p class="text-xs text-gray-400 mt-1">Hành động này không thể hoàn tác.</p>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Hủy
                    </button>
                    <button @click="deleteBrand" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition shadow-sm">
                        Xóa
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