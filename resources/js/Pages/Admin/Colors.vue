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

// State
const colors = ref(props.colors)
const search = ref('') // Biến tìm kiếm
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)
const isLoading = ref(false)
const isSaving = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

const form = ref({
    id: null,
    name: '',
    code: ''
})

// Mã hex hiển thị dưới picker
const displayCode = ref('#CCCCCC')

// Computed: lọc màu theo tên hoặc mã hex
const filteredColors = computed(() => {
    if (!colors.value || colors.value.length === 0) return []
    if (!search.value) return colors.value
    const keyword = search.value.toLowerCase().trim()
    return colors.value.filter(color => 
        color.name.toLowerCase().includes(keyword) || 
        (color.code && color.code.toLowerCase().includes(keyword))
    )
})

// Sắp xếp theo ID giảm dần
const sortedColors = computed(() => {
    return [...filteredColors.value].sort((a, b) => b.id - a.id)
})

// Hàm kiểm tra mã hex
const isHexCode = (value) => {
    if (!value) return false
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Chuyển đổi tên màu -> mã hex
const getColorCodeFromName = (name) => {
    if (!name) return '#CCCCCC'
    if (isHexCode(name)) return name.toUpperCase()

    const colorMap = {
        'đen': '#000000', 'den': '#000000', 'black': '#000000',
        'trắng': '#FFFFFF', 'trang': '#FFFFFF', 'white': '#FFFFFF',
        'xám': '#808080', 'xam': '#808080', 'gray': '#808080',
        'đỏ': '#FF0000', 'do': '#FF0000', 'red': '#FF0000',
        'hồng': '#FFC0CB', 'hong': '#FFC0CB', 'pink': '#FFC0CB',
        'cam': '#FFA500', 'orange': '#FFA500',
        'vàng': '#FFD700', 'vang': '#FFD700', 'yellow': '#FFD700',
        'xanh lá': '#008000', 'xanhla': '#008000', 'green': '#008000',
        'xanh dương': '#0000FF', 'xanhduong': '#0000FF', 'blue': '#0000FF',
        'xanh navy': '#000080', 'xanhnavy': '#000080', 'navy': '#000080',
        'tím': '#800080', 'tim': '#800080', 'purple': '#800080',
        'nâu': '#8B4513', 'nau': '#8B4513', 'brown': '#8B4513',
        'be': '#F5F5DC', 'beige': '#F5F5DC',
        'bạc': '#C0C0C0', 'bac': '#C0C0C0', 'silver': '#C0C0C0',
        'tím than': '#490C42', 'tim than': '#490C42'
    }
    const key = name.toLowerCase().trim()
    return colorMap[key] || '#CCCCCC'
}

// Chuyển đổi mã hex -> tên màu (gợi ý)
const suggestColorNameFromCode = (code) => {
    if (!code) return ''
    
    const codeMap = {
        '#000000': 'Đen', '#000': 'Đen',
        '#FFFFFF': 'Trắng', '#FFF': 'Trắng',
        '#808080': 'Xám',
        '#FF0000': 'Đỏ', '#F00': 'Đỏ',
        '#FFC0CB': 'Hồng',
        '#FFA500': 'Cam',
        '#FFD700': 'Vàng',
        '#008000': 'Xanh lá',
        '#0000FF': 'Xanh dương', '#00F': 'Xanh dương',
        '#000080': 'Xanh navy',
        '#800080': 'Tím',
        '#8B4513': 'Nâu',
        '#F5F5DC': 'Be',
        '#C0C0C0': 'Bạc',
        '#6200EE': 'Tím đậm',
        '#9C27B0': 'Tím hồng',
        '#490C42': 'Tím than',
        '#FF5733': 'Cam đỏ',
        '#E91E63': 'Hồng đậm',
        '#2196F3': 'Xanh dương sáng',
        '#00BCD4': 'Xanh cyan',
        '#009688': 'Xanh lá cây',
        '#4CAF50': 'Xanh lá',
        '#FFC107': 'Vàng cam',
        '#FF9800': 'Cam',
        '#795548': 'Nâu đậm',
        '#9E9E9E': 'Xám'
    }
    const upperCode = code.toUpperCase()
    return codeMap[upperCode] || ''
}

// Cập nhật mã hiển thị và tự động điền mã vào ô input nếu tìm thấy
const updateDisplayCode = () => {
    const inputName = form.value.name?.trim() || ''
    const inputCode = form.value.code?.trim() || ''
    
    if (inputCode && isHexCode(inputCode)) {
        displayCode.value = inputCode.toUpperCase()
    } else if (inputName) {
        const code = getColorCodeFromName(inputName)
        displayCode.value = code
        // Tự động điền mã vào ô input nếu tìm thấy
        if (!form.value.code && code !== '#CCCCCC') {
            form.value.code = code
        }
    } else {
        displayCode.value = '#CCCCCC'
    }
}

// Khi thay đổi picker
const onColorPickerChange = (e) => {
    const value = e.target.value
    form.value.code = value
    // Nếu tên trống, tự động gợi ý tên
    if (!form.value.name?.trim()) {
        const suggested = suggestColorNameFromCode(value)
        if (suggested && suggested !== 'Màu khác') {
            form.value.name = suggested
        }
    }
    updateDisplayCode()
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
    displayCode.value = '#CCCCCC'
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const openEditModal = (color) => {
    isEdit.value = true
    form.value = { ...color }
    const code = color.code || getColorCodeFromName(color.name)
    displayCode.value = code
    if (!form.value.code) form.value.code = code
    errorMessage.value = ''
    validationErrors.value = {}
    showModal.value = true
}

const saveColor = async () => {
    // Kiểm tra ít nhất một trong hai trường có dữ liệu
    if (!form.value.name?.trim() && !form.value.code?.trim()) {
        errorMessage.value = 'Vui lòng nhập tên màu hoặc mã hex!'
        return
    }

    if (isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        let response
        const payload = {
            name: form.value.name?.trim() || null,
            code: form.value.code?.trim() || null
        }

        if (isEdit.value) {
            response = await axios.put(`/admin/colors/${form.value.id}`, payload)
        } else {
            response = await axios.post('/admin/colors', payload)
        }
        
        if (response.data?.success) {
            await fetchColors()
            showModal.value = false
            form.value = { id: null, name: '', code: '' }
            displayCode.value = '#CCCCCC'
            errorMessage.value = ''
        } else if (response.data?.message && typeof response.data.message === 'object') {
            validationErrors.value = response.data.message
            errorMessage.value = Object.values(response.data.message).flat()[0]
        } else {
            errorMessage.value = response.data?.message || 'Có lỗi xảy ra'
        }
    } catch (error) {
        console.error('Lỗi lưu màu:', error)
        if (error.response?.data?.message && typeof error.response.data.message === 'object') {
            validationErrors.value = error.response.data.message
            errorMessage.value = Object.values(error.response.data.message).flat()[0]
        } else {
            errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra'
        }
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
            await fetchColors()
            showDeleteModal.value = false
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
    validationErrors.value = {}
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
        <div class="p-4 md:p-8">
            <!-- Header + nút thêm -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Quản lý màu sắc</h1>
                <button @click="openCreateModal" class="bg-orange-600 text-white px-5 py-2 rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Thêm màu sắc
                </button>
            </div>

            <!-- Thanh tìm kiếm -->
            <div class="mb-4">
                <div class="relative max-w-md">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Tìm theo tên màu hoặc mã hex..." 
                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-full w-full focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>
            </div>

            <!-- Bảng -->
            <div v-if="isLoading && colors.length === 0" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
                <p class="mt-2 text-gray-500">Đang tải...</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left p-4 font-semibold text-gray-700 w-16">STT</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Màu sắc</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Mã hex</th>
                            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
                            <th class="text-center p-4 font-semibold text-gray-700 w-32">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr 
                            v-for="(color, index) in sortedColors" 
                            :key="color.id" 
                            class="border-b border-gray-100 hover:bg-gray-50 transition"
                        >
                            <td class="p-4 text-gray-500 text-sm">{{ index + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-8 h-8 rounded border border-gray-300 shadow-sm" 
                                        :style="{ backgroundColor: color.code || getColorCodeFromName(color.name) }"
                                    ></div>
                                    <span class="font-medium text-gray-700">{{ color.name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-gray-500 text-sm font-mono">{{ color.code || '—' }}</td>
                            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button 
                                        @click="openEditModal(color)" 
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="confirmDelete(color)" 
                                        class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50"
                                    >
                                        Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="sortedColors.length === 0 && !isLoading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
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
                    <!-- Tên màu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên màu <span v-if="!form.code" class="text-red-500">*</span></label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full border rounded-lg p-2 focus:ring-primary focus:border-primary" 
                            :class="{ 'border-red-500': validationErrors.name }"
                            placeholder="VD: Đỏ, Xanh Navy, Tím than..." 
                            @input="updateDisplayCode"
                        >
                        <p v-if="validationErrors.name" class="text-xs text-red-500 mt-1">{{ validationErrors.name[0] }}</p>
                    </div>

                    <!-- Color Picker + Mã hex -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chọn màu</label>
                        <div class="flex items-center gap-4">
                            <input 
                                type="color" 
                                :value="form.code || '#CCCCCC'"
                                @input="onColorPickerChange"
                                class="w-14 h-14 p-0 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary transition"
                            >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-600">Mã hex:</div>
                                <div class="text-lg font-mono font-bold text-gray-800">{{ displayCode }}</div>
                            </div>
                        </div>
                        <!-- Ô nhập mã hex thủ công -->
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hoặc nhập mã hex</label>
                            <input 
                                v-model="form.code" 
                                type="text" 
                                class="w-full border rounded-lg p-2 font-mono focus:ring-primary focus:border-primary" 
                                :class="{ 'border-red-500': validationErrors.code }"
                                placeholder="#dc2626, #FFA500, #490C42..." 
                                @input="updateDisplayCode"
                            >
                            <p v-if="validationErrors.code" class="text-xs text-red-500 mt-1">{{ validationErrors.code[0] }}</p>
                        </div>
                    </div>

                    <div v-if="errorMessage && !validationErrors.name && !validationErrors.code" class="p-3 bg-red-50 border border-red-200 rounded-lg">
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
        <div 
            v-if="showDeleteModal" 
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
            @click="handleOverlayClick"
        >
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