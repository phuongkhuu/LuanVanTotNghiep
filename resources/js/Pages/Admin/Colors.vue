<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    colors: {
        type: Array,
        default: () => []
    }
})

const colors = ref(props.colors)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')

const form = ref({
    id: null,
    name: '',
    code: ''
})

const previewColor = ref('#CCCCCC')
const previewColorCode = ref('#CCCCCC')

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex (mặc định)
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'silver': '#C0C0C0',
        'vàng gold': '#FFD700', 'gold': '#FFD700'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

const updateColorPreview = () => {
    const inputName = form.value.name.trim()
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        previewColor.value = inputCode
        previewColorCode.value = inputCode
        return
    }
    
    if (inputName) {
        const code = getColorCodeFromName(inputName)
        previewColor.value = code
        previewColorCode.value = code
        // Tự động gợi ý mã hex vào ô code nếu chưa có
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        previewColor.value = '#CCCCCC'
        previewColorCode.value = '#CCCCCC'
    }
}

const formatDate = (date) => {
    if (!date) return '---'
    const d = new Date(date)
    return d.toLocaleDateString('vi-VN')
}

const fetchColors = async () => {
    if (isLoading.value) return
    isLoading.value = true
    try {
        const response = await axios.get('/admin/colors/data')
        if (response.data && Array.isArray(response.data)) {
            colors.value = response.data
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu:', error)
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '', code: '' }
    previewColor.value = '#CCCCCC'
    previewColorCode.value = '#CCCCCC'
    errorMessage.value = ''
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    previewColor.value = code
    previewColorCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    showModal.value = true
}

const saveColor = async () => {
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu sắc'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        const payload = {
            name: form.value.name.trim(),
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
            if (response.data?.success) {
                const index = colors.value.findIndex(c => c.id === form.value.id)
                if (index !== -1 && response.data.data) {
                    colors.value[index] = response.data.data
                }
                showModal.value = false
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        } else {
            response = await axios.post('/admin/colors', payload)
            if (response.data?.data) {
                colors.value.unshift(response.data.data)
                showModal.value = false
            } else {
                errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
            }
        }
        
        if (response.data?.success || response.data?.data) {
            form.value = { id: null, name: '', code: '' }
            previewColor.value = '#CCCCCC'
            previewColorCode.value = '#CCCCCC'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = (color) => {
    selectedColor.value = color
    errorMessage.value = ''
    showDeleteModal.value = true
}

const deleteColor = async () => {
    if (!selectedColor.value) return
    if (isSaving.value) return
    
    isSaving.value = true
    errorMessage.value = ''
    
    try {
        const response = await axios.delete(`/admin/colors/${selectedColor.value.id}`)
        if (response.data?.success) {
            showDeleteModal.value = false
            const index = colors.value.findIndex(c => c.id === selectedColor.value.id)
            if (index !== -1) colors.value.splice(index, 1)
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi xóa màu:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '', code: '' }
    errorMessage.value = ''
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) closeModal()
}

onMounted(() => {
    if (colors.value.length === 0) fetchColors()
})
</script>

<template>
    <Head title="Quản lý màu sắc" />
    <AdminLayout>
        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <p class="text-gray-500 mt-1">Thêm, sửa hoặc xóa các màu sắc sản phẩm</p>
            </div>

            <div class="mb-6">
                <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition">
                    + Thêm màu sắc mới
                </button>
            </div>

            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700">ID</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="color in colors" :key="color.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 text-gray-700">{{ color.id }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded border border-gray-300 shadow-sm" :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"></div>
                                    <span class="font-medium text-gray-700">{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(color)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">Sửa</button>
                                    <button @click="confirmDelete(color)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">Xóa</button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="colors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Thêm/Sửa -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu *</label>
                        <input v-model="form.name" type="text" class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" placeholder="VD: Đỏ, Xanh Navy" @input="updateColorPreview">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã hex (tùy chọn)</label>
                        <input v-model="form.code" type="text" class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" placeholder="#dc2626" @input="updateColorPreview">
                        <p class="text-xs text-gray-400 mt-1">Để trống sẽ tự động sinh mã từ tên màu</p>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="w-12 h-12 rounded-lg border border-gray-300 shadow-md" :style="{ backgroundColor: previewColor }"></div>
                        <div class="text-sm text-gray-600">
                            Xem trước màu<br>
                            <span class="text-xs text-gray-400 font-mono">{{ previewColorCode }}</span>
                        </div>
                    </div>
                    <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-600">{{ errorMessage }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center gap-2" :disabled="isSaving">
                        <span v-if="isSaving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        {{ isSaving ? 'Đang xử lý...' : 'Lưu' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errorMessage }}</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2" :disabled="isSaving">
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
.animate-spin { animation: spin 1s linear infinite; }
</style>