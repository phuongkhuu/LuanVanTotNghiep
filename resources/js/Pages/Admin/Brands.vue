<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Quản lý thương hiệu</h1>
      <p class="text-gray-500 mt-1">Thêm, sửa hoặc xóa các thương hiệu</p>
    </div>

    <!-- Button thêm mới -->
    <div class="mb-6">
      <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition">
        + Thêm thương hiệu mới
      </button>
    </div>

    <!-- Bảng danh sách -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
      <table class="w-full min-w-[800px]">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left p-4 font-semibold text-gray-700">ID</th>
            <th class="text-left p-4 font-semibold text-gray-700">Tên thương hiệu</th>
            <th class="text-left p-4 font-semibold text-gray-700">Logo</th>
            <th class="text-left p-4 font-semibold text-gray-700">Mô tả</th>
            <th class="text-left p-4 font-semibold text-gray-700">Ngày tạo</th>
            <th class="text-center p-4 font-semibold text-gray-700">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="brand in brands" :key="brand.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
            <td class="p-4 text-gray-700">{{ brand.id }}</td>
            <td class="p-4 font-medium text-gray-700">{{ brand.name }}</td>
            <td class="p-4 text-gray-500">
              <img v-if="brand.logo" :src="brand.logo" class="h-8 w-auto" alt="logo">
              <span v-else class="text-gray-400">---</span>
            </td>
            <td class="p-4 text-gray-500 max-w-xs truncate">{{ brand.description || '---' }}</td>
            <td class="p-4 text-gray-500 text-sm">{{ formatDate(brand.created_at) }}</td>
            <td class="p-4 text-center">
              <div class="flex items-center justify-center gap-2">
                <button @click="openEditModal(brand)" class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50">
                  Sửa
                </button>
                <button @click="confirmDelete(brand)" class="text-red-600 hover:text-red-800 px-2 py-1 rounded hover:bg-red-50">
                  Xóa
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="brands.length === 0">
            <td colspan="6" class="p-8 text-center text-gray-400">Chưa có thương hiệu nào</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
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
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
            <input 
              v-model="form.logo" 
              type="text" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
              placeholder="https://example.com/logo.png"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
            <textarea 
              v-model="form.description" 
              rows="3" 
              class="w-full border border-gray-300 rounded-lg p-2 focus:ring-primary focus:border-primary outline-none" 
              placeholder="Mô tả về thương hiệu..."
            ></textarea>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-6">
          <button @click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Hủy
          </button>
          <button @click="saveBrand" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
            Lưu
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Xác nhận xóa -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">Xác nhận xóa</h3>
        <p class="text-gray-600">Bạn có chắc muốn xóa thương hiệu <strong>{{ selectedBrand?.name }}</strong>?</p>
        <div class="flex justify-end gap-3 mt-6">
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Hủy
          </button>
          <button @click="deleteBrand" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
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

const brands = ref([])
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const selectedBrand = ref(null)

const form = ref({
  id: null,
  name: '',
  logo: '',
  description: ''
})

const formatDate = (date) => {
  if (!date) return '---'
  const d = new Date(date)
  return d.toLocaleDateString('vi-VN')
}

const fetchBrands = async () => {
  try {
    const response = await axios.get('/api/brands')
    brands.value = response.data
  } catch (error) {
    console.error('Lỗi lấy danh sách thương hiệu:', error)
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

  try {
    if (isEdit.value) {
      await axios.put(`/api/brands/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/brands', form.value)
    }
    await fetchBrands()
    closeModal()
  } catch (error) {
    console.error('Lỗi lưu thương hiệu:', error)
    alert('Có lỗi xảy ra, vui lòng thử lại')
  }
}

const confirmDelete = (brand) => {
  selectedBrand.value = brand
  showDeleteModal.value = true
}

const deleteBrand = async () => {
  try {
    await axios.delete(`/api/brands/${selectedBrand.value.id}`)
    await fetchBrands()
    showDeleteModal.value = false
  } catch (error) {
    console.error('Lỗi xóa thương hiệu:', error)
    alert('Có lỗi xảy ra, vui lòng thử lại')
  }
}

const closeModal = () => {
  showModal.value = false
  selectedBrand.value = null
}

onMounted(() => {
  fetchBrands()
})
</script>