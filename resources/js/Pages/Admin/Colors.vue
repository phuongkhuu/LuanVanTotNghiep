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

// Sắp xếp màu sắc theo ID giảm dần (mới nhất lên đầu)
const sortedColors = computed(() => {
    return [...colors.value].sort((a, b) => b.id - a.id)
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
    name: ''
})

const previewColor = ref('#CCCCCC')
const previewColorCode = ref('#CCCCCC')

const isHexCode = (value) => {
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

const getColorCodeFromNameOrHex = (input) => {
    if (!input) return '#CCCCCC'
    
    if (isHexCode(input)) {
        return input
    }
    
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
        'vàng kem': '#FFF8DC', 'cream': '#FFF8DC',
        'bạc': '#C0C0C0', 'silver': '#C0C0C0',
        'vàng gold': '#FFD700', 'gold': '#FFD700'
    }
    
    const key = input.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

const updateColorPreview = () => {
    const inputValue = form.value.name.trim()
    
    if (!inputValue) {
        previewColor.value = '#CCCCCC'
        previewColorCode.value = '#CCCCCC'
        return
    }
    
    if (isHexCode(inputValue)) {
        previewColor.value = inputValue
        previewColorCode.value = inputValue
        return
    }
    
    const colorCode = getColorCodeFromNameOrHex(inputValue)
    previewColor.value = colorCode
    previewColorCode.value = colorCode
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
        } else {
            colors.value = []
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách màu sắc:', error)
        colors.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    isEdit.value = false
    form.value = { id: null, name: '' }
    previewColor.value = '#CCCCCC'
    previewColorCode.value = '#CCCCCC'
    errorMessage.value = ''
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const colorCode = getColorCodeFromNameOrHex(color.name)
    previewColor.value = colorCode
    previewColorCode.value = colorCode
    errorMessage.value = ''
    showModal.value = true
}

const saveColor = async () => {
    if (!form.value.name.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu sắc hoặc mã màu'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''

    try {
        let response
        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, { name: form.value.name })
            // Cập nhật màu trong danh sách
            const index = colors.value.findIndex(c => c.id === form.value.id)
            if (index !== -1 && response.data && response.data.data) {
                colors.value[index] = response.data.data
            }
        } else {
            response = await axios.post('/admin/colors', { name: form.value.name })
            // Thêm màu mới vào đầu danh sách
            if (response.data && response.data.data) {
                colors.value.unshift(response.data.data)
            }
        }
        
        // Đóng modal
        showModal.value = false
        
        // Reset form
        form.value = { id: null, name: '' }
        previewColor.value = '#CCCCCC'
        previewColorCode.value = '#CCCCCC'
        
    } catch (error) {
        console.error('Lỗi lưu màu sắc:', error)
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
        
        if (response.data && response.data.success) {
            // Đóng modal xóa
            showDeleteModal.value = false
            
            // Xóa khỏi danh sách
            const index = colors.value.findIndex(c => c.id === selectedColor.value.id)
            if (index !== -1) {
                colors.value.splice(index, 1)
            }
            
            selectedColor.value = null
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
        
    } catch (error) {
        console.error('Lỗi xóa màu sắc:', error)
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi xóa'
    } finally {
        isSaving.value = false
    }
}

const closeModal = () => {
    showModal.value = false
    showDeleteModal.value = false
    selectedColor.value = null
    form.value = { id: null, name: '' }
    errorMessage.value = ''
    isSaving.value = false
}

const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeModal()
    }
}

onMounted(() => {
    if (colors.value.length === 0) {
        fetchColors()
    }
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
                <button 
                    @click="openCreateModal" 
                    class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition"
                    :disabled="isSaving"
                >
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
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Cập nhật</th>
                            <th class="text-center p-4 font-semibold text-gray-700">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="color in sortedColors" 
                            :key="color.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-700">{{ color.id }}</td>
                            <td class="p-4 font-medium text-gray-700">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-8 h-8 rounded border border-gray-300 shadow-sm" 
                                        :style="{ backgroundColor: getColorCodeFromNameOrHex(color.name) }"
                                    ></div>
                                    <span>{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.updated_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(color)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                        :disabled="isSaving"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(color)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                        :disabled="isSaving"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedColors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">
                                Chưa có màu sắc nào
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
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Màu sắc *</label>
                        <input 
                            v-model="form.name"
                            type="text" 
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
                            placeholder="VD: Đỏ, Xanh Navy, #FF0000, #000080"
                            @input="updateColorPreview"
                            :disabled="isSaving"
                        >
                        <p class="text-xs text-gray-400 mt-1">Có thể nhập tên màu (VD: Đỏ) hoặc mã hex (VD: #FF0000)</p>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <div 
                            class="w-12 h-12 rounded-lg border border-gray-300 shadow-md" 
                            :style="{ backgroundColor: previewColor }"
                        ></div>
                        <div class="text-sm text-gray-600">
                            Xem trước màu<br>
                            <span class="text-xs text-gray-400">{{ previewColorCode }}</span>
                        </div>
                    </div>
                    
                    <!-- Hiển thị lỗi -->
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
                        @click="saveColor" 
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
                <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.name }}</strong>?</p>
                <p v-if="errorMessage" class="text-sm text-red-600 mt-2">{{ errorMessage }}</p>
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                        :disabled="isSaving"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteColor" 
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