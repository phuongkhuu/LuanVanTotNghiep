<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Quản lý màu sắc</h1>
      <p class="text-gray-500 mt-1">Thêm, sửa hoặc xóa các màu sắc sản phẩm</p>
    </div>

    <!-- Button thêm mới -->
    <div class="mb-6">
      <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition">
        + Thêm màu sắc mới
      </button>
    </div>

    <!-- Bảng danh sách -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
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
          <tr v-for="color in colors" :key="color.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
            <td class="p-4 text-gray-700">{{ color.id }}</td>
            <td class="p-4 font-medium text-gray-700">
              <div class="flex items-center gap-3">
                <div 
                  class="w-8 h-8 rounded border border-gray-300 shadow-sm" 
                  :style="{ backgroundColor: getColorCodeFromNameOrHex(color.color) }"
                ></div>
                <span>{{ color.color }}</span>
              </div>
            </td>
            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.created_at) }}</td>
            <td class="p-4 text-gray-500 text-sm">{{ formatDate(color.updated_at) }}</td>
            <td class="p-4 text-center">
              <div class="flex items-center justify-center gap-2">
                <button @click="openEditModal(color)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">
                  Sửa
                </button>
                <button @click="confirmDelete(color)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">
                  Xóa
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="colors.length === 0">
            <td colspan="5" class="p-8 text-center text-gray-400">Chưa có màu sắc nào</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-lg w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">{{ isEdit ? 'Sửa màu sắc' : 'Thêm màu sắc mới' }}</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Màu sắc *</label>
            <input 
              v-model="form.color" 
              type="text" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
              placeholder="VD: Đỏ, Xanh Navy, #FF0000, #000080"
              @input="updateColorPreview"
            >
            <p class="text-xs text-gray-400 mt-1">Có thể nhập tên màu (VD: Đỏ) hoặc mã hex (VD: #FF0000)</p>
          </div>
          
          <!-- Xem trước màu chính xác -->
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
        </div>
        
        <div class="flex justify-end gap-3 mt-6">
          <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Hủy
          </button>
          <button @click="saveColor" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
            Lưu
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Xác nhận xóa -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
        <p class="text-gray-600">Bạn có chắc muốn xóa màu <strong>{{ selectedColor?.color }}</strong>?</p>
        <div class="flex justify-end gap-3 mt-6">
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Hủy
          </button>
          <button @click="deleteColor" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const colors = ref([])
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedColor = ref(null)

const form = ref({
  id: null,
  color: ''
})

// Màu xem trước
const previewColor = ref('#CCCCCC')
const previewColorCode = ref('#CCCCCC')

// Hàm kiểm tra có phải mã hex không
const isHexCode = (value) => {
  return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)
}

// Hàm lấy mã màu từ tên hoặc hex
const getColorCodeFromNameOrHex = (input) => {
  if (!input) return '#CCCCCC'
  
  // Nếu là mã hex thì trả về luôn
  if (isHexCode(input)) {
    return input
  }
  
  // Nếu không thì tra từ điển tên màu
  const colorMap = {
    // Đen - Trắng - Xám
    'đen': '#000000',
    'den': '#000000',
    'black': '#000000',
    'trắng': '#FFFFFF',
    'trang': '#FFFFFF',
    'white': '#FFFFFF',
    'xám': '#808080',
    'xam': '#808080',
    'gray': '#808080',
    'xám đậm': '#555555',
    'xám nhạt': '#D3D3D3',
    
    // Đỏ - Hồng - Cam
    'đỏ': '#FF0000',
    'do': '#FF0000',
    'red': '#FF0000',
    'đỏ đô': '#8B0000',
    'hồng': '#FFC0CB',
    'hong': '#FFC0CB',
    'pink': '#FFC0CB',
    'hồng đậm': '#FF1493',
    'cam': '#FFA500',
    'orange': '#FFA500',
    
    // Vàng - Xanh lá
    'vàng': '#FFD700',
    'vang': '#FFD700',
    'yellow': '#FFD700',
    'xanh lá': '#008000',
    'xanh la': '#008000',
    'green': '#008000',
    'xanh mint': '#98FB98',
    
    // Xanh dương - Xanh navy
    'xanh dương': '#0000FF',
    'xanh duong': '#0000FF',
    'blue': '#0000FF',
    'xanh navy': '#000080',
    'navy': '#000080',
    'xanh than': '#2F4F4F',
    
    // Tím - Nâu - Be
    'tím': '#800080',
    'tim': '#800080',
    'purple': '#800080',
    'tím than': '#4B0082',
    'nâu': '#8B4513',
    'nau': '#8B4513',
    'brown': '#8B4513',
    'be': '#F5F5DC'
  }
  
  const key = input.toLowerCase().trim()
  return colorMap[key] || '#CCCCCC'
}

// Hàm cập nhật màu xem trước khi gõ
const updateColorPreview = () => {
  const inputValue = form.value.color.trim()
  
  if (!inputValue) {
    previewColor.value = '#CCCCCC'
    previewColorCode.value = '#CCCCCC'
    return
  }
  
  // Nếu nhập mã hex
  if (isHexCode(inputValue)) {
    previewColor.value = inputValue
    previewColorCode.value = inputValue
    return
  }
  
  // Nếu nhập tên màu
  const colorCode = getColorCodeFromNameOrHex(inputValue)
  previewColor.value = colorCode
  previewColorCode.value = colorCode
}

const formatDate = (date) => {
  if (!date) return '---'
  const d = new Date(date)
  return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN')
}

const fetchColors = async () => {
  try {
    const response = await axios.get('/api/colors')
    colors.value = response.data
  } catch (error) {
    console.error('Lỗi lấy danh sách màu sắc:', error)
  }
}

const openCreateModal = () => {
  isEdit.value = false
  form.value = { id: null, color: '' }
  previewColor.value = '#CCCCCC'
  previewColorCode.value = '#CCCCCC'
  showModal.value = true
}

const openEditModal = (color) => {
  isEdit.value = true
  form.value = { ...color }
  // Cập nhật màu xem trước khi sửa
  const colorCode = getColorCodeFromNameOrHex(color.color)
  previewColor.value = colorCode
  previewColorCode.value = colorCode
  showModal.value = true
}

const saveColor = async () => {
  if (!form.value.color.trim()) {
    alert('Vui lòng nhập tên màu sắc hoặc mã màu')
    return
  }

  try {
    if (isEdit.value) {
      await axios.put(`/api/colors/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/colors', form.value)
    }
    await fetchColors()
    closeModal()
  } catch (error) {
    console.error('Lỗi lưu màu sắc:', error)
    alert('Có lỗi xảy ra, vui lòng thử lại')
  }
}

const confirmDelete = (color) => {
  selectedColor.value = color
  showDeleteModal.value = true
}

const deleteColor = async () => {
  try {
    await axios.delete(`/api/colors/${selectedColor.value.id}`)
    await fetchColors()
    showDeleteModal.value = false
  } catch (error) {
    console.error('Lỗi xóa màu sắc:', error)
    alert('Có lỗi xảy ra, vui lòng thử lại')
  }
}

const closeModal = () => {
  showModal.value = false
  selectedColor.value = null
}

onMounted(() => {
  fetchColors()
})
</script>
