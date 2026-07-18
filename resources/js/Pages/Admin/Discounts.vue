<template>
  <AdminLayout>
    <div class="p-4 md:p-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý chiết khấu</h1>
        <button @click="openCreateModal" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition">
          + Thêm chiết khấu
        </button>
      </div>

      <!-- Danh sách -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Giảm giá (%)</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số lượng tối thiểu</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loại</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="discount in discounts" :key="discount.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ discount.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ discount.discount_percent }}%</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ discount.min_quantity ?? 'Không' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ discount.type ?? 'Chung' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="discount.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs">
                  {{ discount.is_active ? 'Hoạt động' : 'Vô hiệu' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <button @click="editDiscount(discount)" class="text-blue-600 hover:text-blue-900 mr-2">Sửa</button>
                <button @click="deleteDiscount(discount.id)" class="text-red-600 hover:text-red-900">Xóa</button>
              </td>
            </tr>
            <tr v-if="!discounts.length">
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">Chưa có chiết khấu nào</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Create/Edit (giản lược) -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">{{ editingId ? 'Sửa chiết khấu' : 'Thêm chiết khấu' }}</h2>
        <form @submit.prevent="saveDiscount">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Phần trăm giảm (%)</label>
            <input v-model.number="form.discount_percent" type="number" step="0.01" min="0" max="100" class="w-full border rounded px-3 py-2" required>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Số lượng tối thiểu</label>
            <input v-model.number="form.min_quantity" type="number" min="0" class="w-full border rounded px-3 py-2">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Loại</label>
            <select v-model="form.type" class="w-full border rounded px-3 py-2">
              <option value="">Chung</option>
              <option value="percentage">Phần trăm</option>
              <option value="fixed">Cố định</option>
            </select>
          </div>
          <div class="flex items-center mb-4">
            <input type="checkbox" v-model="form.is_active" class="mr-2">
            <label class="text-sm font-medium text-gray-700">Hoạt động</label>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="closeModal" class="px-4 py-2 border rounded">Hủy</button>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark">Lưu</button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';

const discounts = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const form = ref({
  discount_percent: 0,
  min_quantity: null,
  type: '',
  is_active: true
});

const fetchDiscounts = async () => {
  try {
    const res = await axios.get('/admin/discounts/data');
    discounts.value = res.data;
  } catch (error) {
    alert('Lỗi tải dữ liệu');
  }
};

const openCreateModal = () => {
  editingId.value = null;
  form.value = { discount_percent: 0, min_quantity: null, order_code: '', type: '', is_active: true };
  showModal.value = true;
};

const editDiscount = (discount) => {
  editingId.value = discount.id;
  form.value = { ...discount };
  showModal.value = true;
};

const saveDiscount = async () => {
  try {
    if (editingId.value) {
      await axios.put(`/admin/discounts/${editingId.value}`, form.value);
    } else {
      await axios.post('/admin/discounts', form.value);
    }
    closeModal();
    fetchDiscounts();
  } catch (error) {
    alert(error.response?.data?.message || 'Lỗi lưu');
  }
};

const deleteDiscount = async (id) => {
  if (!confirm('Bạn có chắc chắn muốn xóa?')) return;
  try {
    await axios.delete(`/admin/discounts/${id}`);
    fetchDiscounts();
  } catch (error) {
    alert(error.response?.data?.message || 'Lỗi xóa');
  }
};

const closeModal = () => {
  showModal.value = false;
};

onMounted(() => {
  fetchDiscounts();
});
</script>