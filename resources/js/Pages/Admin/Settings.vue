<script setup>
import { ref, reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const activeTab = ref('general');
const isSaving = ref(false);
const showAddUserModal = ref(false);

const settings = reactive({
  storeName: 'BigBag.vn', email: 'contact@bigbag.vn', phone: '1900 1234', address: '123 Đường ABC, Quận 1, TP.HCM',
  taxCode: '0123456789', b2bEmail: 'b2b@bigbag.vn', preorderDeposit: 30, preorderLeadTime: 15,
  payments: { cod: true, bank: true, momo: false, vnpay: false },
  seo: { title: 'BigBag.vn - Balo và Túi xách cao cấp', description: 'BigBag.vn chuyên cung cấp balo, túi xách cao cấp', keywords: 'balo, túi xách, phụ kiện' }
});

const users = ref([
  { id: 1, username: 'admin', email: 'admin@bigbag.vn', role: 'Admin', permission: 'Full', active: true },
  { id: 2, username: 'sale', email: 'sale@bigbag.vn', role: 'Nhân viên', permission: 'Chỉ đơn hàng', active: true }
]);

const passwordForm = reactive({ current_password: '', new_password: '', new_password_confirmation: '' });

const saveSettings = async () => { isSaving.value = true; await router.put('/admin/settings/general', settings); isSaving.value = false; alert('Đã lưu cài đặt'); };
const changePassword = async () => { if (passwordForm.new_password !== passwordForm.new_password_confirmation) return alert('Mật khẩu xác nhận không khớp'); await router.put('/admin/settings/password', passwordForm); alert('Đổi mật khẩu thành công'); };
</script>

<template>
  <Head title="Cài đặt hệ thống" />
  <AdminLayout>
    <div class="p-5">
      <div class="mb-5">
        <h1 class="text-xl font-semibold text-gray-800">Cài đặt</h1>
        <p class="text-sm text-gray-500 mt-0.5">Cấu hình thông tin hệ thống</p>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 border-b border-gray-200 mb-5">
        <button @click="activeTab = 'general'" class="px-4 py-2 text-sm font-medium transition-all" :class="activeTab === 'general' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'">Thông tin chung</button>
        <button @click="activeTab = 'users'" class="px-4 py-2 text-sm font-medium transition-all" :class="activeTab === 'users' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'">Quản lý người dùng</button>
        <button @click="activeTab = 'security'" class="px-4 py-2 text-sm font-medium transition-all" :class="activeTab === 'security' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'">Bảo mật</button>
      </div>

      <!-- General Tab -->
      <div v-if="activeTab === 'general'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
          <div class="bg-white rounded-lg border border-gray-200 p-5"><h3 class="text-base font-medium text-gray-800 mb-4">Thông tin chung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="text-sm text-gray-700 block mb-1">Tên cửa hàng</label><input v-model="settings.storeName" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Email</label><input v-model="settings.email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Số điện thoại</label><input v-model="settings.phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Địa chỉ</label><input v-model="settings.address" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Mã số thuế</label><input v-model="settings.taxCode" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Email B2B</label><input v-model="settings.b2bEmail" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div></div></div>
          <div class="bg-white rounded-lg border border-gray-200 p-5"><h3 class="text-base font-medium text-gray-800 mb-4">Chính sách bán hàng</h3>
            <div class="grid grid-cols-2 gap-4"><div><label class="text-sm text-gray-700 block mb-1">Tiền cọc pre-order (%)</label><input v-model="settings.preorderDeposit" type="number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Thời gian giao pre-order (ngày)</label><input v-model="settings.preorderLeadTime" type="number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div></div></div>
        </div>
        <div class="space-y-5">
          <div class="bg-white rounded-lg border border-gray-200 p-5"><h3 class="text-base font-medium text-gray-800 mb-4">Thanh toán</h3>
            <div class="space-y-2"><label class="flex items-center gap-2"><input type="checkbox" v-model="settings.payments.cod" class="w-4 h-4 rounded"><span class="text-sm">COD</span></label><label class="flex items-center gap-2"><input type="checkbox" v-model="settings.payments.bank" class="w-4 h-4 rounded"><span class="text-sm">Chuyển khoản</span></label><label class="flex items-center gap-2"><input type="checkbox" v-model="settings.payments.momo" class="w-4 h-4 rounded"><span class="text-sm">Momo</span></label><label class="flex items-center gap-2"><input type="checkbox" v-model="settings.payments.vnpay" class="w-4 h-4 rounded"><span class="text-sm">VNPay</span></label></div></div>
          <div class="bg-white rounded-lg border border-gray-200 p-5"><h3 class="text-base font-medium text-gray-800 mb-4">SEO</h3><div><label class="text-sm text-gray-700 block mb-1">Meta Title</label><input v-model="settings.seo.title" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div class="mt-3"><label class="text-sm text-gray-700 block mb-1">Meta Description</label><textarea v-model="settings.seo.description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea></div></div>
        </div>
      </div>

      <!-- Users Tab -->
      <div v-if="activeTab === 'users'" class="bg-white rounded-lg border border-gray-200 p-5">
        <div class="flex justify-between items-center mb-4"><h3 class="text-base font-medium text-gray-800">Người dùng</h3><button @click="showAddUserModal = true" class="px-3 py-1.5 bg-orange-600 text-white text-sm rounded-lg">+ Thêm</button></div>
        <table class="w-full text-sm"><thead class="border-b border-gray-200"><tr><th class="text-left py-3 text-gray-600 font-medium">TÊN</th><th class="text-left py-3 text-gray-600 font-medium">EMAIL</th><th class="text-left py-3 text-gray-600 font-medium">VAI TRÒ</th><th class="text-left py-3 text-gray-600 font-medium">TRẠNG THÁI</th><th class="text-center py-3 text-gray-600 font-medium">THAO TÁC</th></tr></thead>
          <tbody><tr v-for="user in users" :key="user.id" class="border-b border-gray-100"><td class="py-3">{{ user.username }}</td><td class="py-3 text-gray-600">{{ user.email }}</td><td class="py-3">{{ user.role }}</td><td class="py-3"><span class="text-xs px-2 py-0.5 rounded-full" :class="user.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">{{ user.active ? 'Hoạt động' : 'Khóa' }}</span></td><td class="py-3 text-center"><button class="text-gray-500 hover:text-gray-700 text-sm">Sửa</button></td></tr></tbody>
        </table>
      </div>

      <!-- Security Tab -->
      <div v-if="activeTab === 'security'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white rounded-lg border border-gray-200 p-5"><h3 class="text-base font-medium text-gray-800 mb-4">Đổi mật khẩu</h3><div class="space-y-3"><div><label class="text-sm text-gray-700 block mb-1">Mật khẩu hiện tại</label><input v-model="passwordForm.current_password" type="password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Mật khẩu mới</label><input v-model="passwordForm.new_password" type="password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><div><label class="text-sm text-gray-700 block mb-1">Xác nhận mật khẩu</label><input v-model="passwordForm.new_password_confirmation" type="password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></div><button @click="changePassword" class="mt-2 px-4 py-2 bg-orange-600 text-white rounded-lg text-sm">Cập nhật</button></div></div>
        <div class="bg-white rounded-lg border border-gray-200 p-5"><h3 class="text-base font-medium text-gray-800 mb-4">Xác thực 2 lớp</h3><p class="text-sm text-gray-500 mb-4">Tăng cường bảo mật cho tài khoản admin</p><button class="px-4 py-2 border border-orange-600 text-orange-600 rounded-lg text-sm hover:bg-orange-50">Kích hoạt 2FA</button></div>
      </div>

      <!-- Save Button -->
      <div v-if="activeTab === 'general'" class="mt-5 flex justify-end"><button @click="saveSettings" :disabled="isSaving" class="px-5 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700">{{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}</button></div>
    </div>
  </AdminLayout>
</template>