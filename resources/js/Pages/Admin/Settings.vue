<script setup>
import { ref, reactive, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] }
});

const activeTab = ref('general');
const isSaving = ref(false);
const isProcessing = ref(false);
const showAddUserModal = ref(false);
const showEditUserModal = ref(false);
const showDeleteModal = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const selectedUser = ref(null);

// Settings data
const settings = reactive({
    store_name: props.settings.store_name || 'BigBag.vn',
    store_email: props.settings.store_email || 'contact@bigbag.vn',
    store_phone: props.settings.store_phone || '1900 1234',
    store_address: props.settings.store_address || '',
    tax_code: props.settings.tax_code || '',
    b2b_email: props.settings.b2b_email || '',
    preorder_deposit: props.settings.preorder_deposit || 30,
    preorder_lead_time: props.settings.preorder_lead_time || 15,
    payments: {
        cod: props.settings.payment_cod === true || props.settings.payment_cod === 'true',
        bank: props.settings.payment_bank === true || props.settings.payment_bank === 'true',
        momo: props.settings.payment_momo === true || props.settings.payment_momo === 'true',
        vnpay: props.settings.payment_vnpay === true || props.settings.payment_vnpay === 'true'
    },
    seo: {
        title: props.settings.seo_title || '',
        description: props.settings.seo_description || '',
        keywords: props.settings.seo_keywords || ''
    }
});

const users = ref(props.users);
const formUser = reactive({
    id: null,
    name: '',
    email: '',
    password: '',
    role: 'staff',
    status: 1
});

const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const roleOptions = [
    { value: 'admin', label: 'Admin' },
    { value: 'staff', label: 'Nhân viên' },
    { value: 'user', label: 'Người dùng' }
];

// Clear messages
const clearMessages = () => {
    errorMessage.value = '';
    successMessage.value = '';
};

// Save settings
const saveSettings = async () => {
    if (isSaving.value) return;
    isSaving.value = true;
    clearMessages();
    
    try {
        const response = await axios.put('/admin/settings/general', settings);
        if (response.data.success) {
            successMessage.value = 'Đã lưu cài đặt thành công!';
            setTimeout(() => successMessage.value = '', 3000);
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        console.error('Lỗi lưu cài đặt:', error);
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
    } finally {
        isSaving.value = false;
    }
};

// Change password
const changePassword = async () => {
    clearMessages();
    
    if (!passwordForm.current_password) {
        errorMessage.value = 'Vui lòng nhập mật khẩu hiện tại';
        return;
    }
    
    if (!passwordForm.new_password) {
        errorMessage.value = 'Vui lòng nhập mật khẩu mới';
        return;
    }
    
    if (passwordForm.new_password.length < 6) {
        errorMessage.value = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        return;
    }
    
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
        errorMessage.value = 'Mật khẩu xác nhận không khớp';
        return;
    }
    
    if (isProcessing.value) return;
    isProcessing.value = true;
    
    try {
        const response = await axios.put('/admin/settings/password', {
            current_password: passwordForm.current_password,
            new_password: passwordForm.new_password,
            new_password_confirmation: passwordForm.new_password_confirmation
        });
        
        if (response.data.success) {
            successMessage.value = 'Đổi mật khẩu thành công!';
            passwordForm.current_password = '';
            passwordForm.new_password = '';
            passwordForm.new_password_confirmation = '';
            setTimeout(() => successMessage.value = '', 3000);
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        console.error('Lỗi đổi mật khẩu:', error);
        if (error.response && error.response.data) {
            errorMessage.value = error.response.data.message || 'Có lỗi xảy ra';
        } else {
            errorMessage.value = 'Có lỗi xảy ra khi kết nối server';
        }
    } finally {
        isProcessing.value = false;
    }
};

// Fetch users
const fetchUsers = async () => {
    try {
        const response = await axios.get('/admin/settings/users');
        users.value = response.data;
    } catch (error) {
        console.error('Lỗi lấy users:', error);
    }
};

// Open add user modal
const openAddUserModal = () => {
    formUser.id = null;
    formUser.name = '';
    formUser.email = '';
    formUser.password = '';
    formUser.role = 'staff';
    formUser.status = 1;
    clearMessages();
    showAddUserModal.value = true;
};

// Open edit user modal
const openEditUserModal = (user) => {
    selectedUser.value = user;
    formUser.id = user.id;
    formUser.name = user.name;
    formUser.email = user.email;
    formUser.role = user.role;
    formUser.status = user.status;
    formUser.password = '';
    clearMessages();
    showEditUserModal.value = true;
};

// Save user
const saveUser = async () => {
    if (!formUser.name.trim() || !formUser.email.trim()) {
        errorMessage.value = 'Vui lòng nhập đầy đủ thông tin';
        return;
    }
    
    if (!formUser.id && !formUser.password) {
        errorMessage.value = 'Vui lòng nhập mật khẩu';
        return;
    }
    
    if (isProcessing.value) return;
    isProcessing.value = true;
    clearMessages();
    
    try {
        let response;
        if (formUser.id) {
            response = await axios.put(`/admin/settings/users/${formUser.id}`, {
                name: formUser.name,
                email: formUser.email,
                role: formUser.role,
                status: formUser.status
            });
        } else {
            response = await axios.post('/admin/settings/users', {
                name: formUser.name,
                email: formUser.email,
                password: formUser.password,
                role: formUser.role,
                status: formUser.status
            });
        }
        
        if (response.data.success) {
            successMessage.value = formUser.id ? 'Cập nhật thành công!' : 'Thêm thành công!';
            await fetchUsers();
            closeUserModal();
            setTimeout(() => successMessage.value = '', 3000);
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        console.error('Lỗi lưu user:', error);
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
    } finally {
        isProcessing.value = false;
    }
};

// Toggle user status
const toggleUserStatus = async (user) => {
    if (isProcessing.value) return;
    isProcessing.value = true;
    
    try {
        const response = await axios.patch(`/admin/settings/users/${user.id}/toggle`);
        if (response.data.success) {
            await fetchUsers();
            successMessage.value = response.data.message;
            setTimeout(() => successMessage.value = '', 2000);
        } else {
            alert(response.data.message);
        }
    } catch (error) {
        console.error('Lỗi:', error);
        alert(error.response?.data?.message || 'Có lỗi xảy ra');
    } finally {
        isProcessing.value = false;
    }
};

// Confirm delete user
const confirmDeleteUser = (user) => {
    selectedUser.value = user;
    clearMessages();
    showDeleteModal.value = true;
};

// Delete user
const deleteUser = async () => {
    if (!selectedUser.value) return;
    if (isProcessing.value) return;
    
    isProcessing.value = true;
    clearMessages();
    
    try {
        const response = await axios.delete(`/admin/settings/users/${selectedUser.value.id}`);
        if (response.data.success) {
            successMessage.value = 'Xóa người dùng thành công!';
            await fetchUsers();
            showDeleteModal.value = false;
            selectedUser.value = null;
            setTimeout(() => successMessage.value = '', 3000);
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        console.error('Lỗi xóa user:', error);
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra';
    } finally {
        isProcessing.value = false;
    }
};

// Close modals
const closeUserModal = () => {
    showAddUserModal.value = false;
    showEditUserModal.value = false;
    showDeleteModal.value = false;
    selectedUser.value = null;
    clearMessages();
};

// Close modal when click outside
const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
        closeUserModal();
    }
};

onMounted(() => {
    if (users.value.length === 0) {
        fetchUsers();
    }
});
</script>

<template>
    <Head title="Cài đặt hệ thống" />
    <AdminLayout>
        <div class="p-5">
            <div class="mb-5">
                <h1 class="text-xl font-semibold text-gray-800">Cài đặt</h1>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-600">{{ successMessage }}</p>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-600">{{ errorMessage }}</p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 border-b border-gray-200 mb-5">
                <button 
                    @click="activeTab = 'general'" 
                    class="px-4 py-2 text-sm font-medium transition-all"
                    :class="activeTab === 'general' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    Thông tin chung
                </button>
                <button 
                    @click="activeTab = 'users'" 
                    class="px-4 py-2 text-sm font-medium transition-all"
                    :class="activeTab === 'users' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    Quản lý người dùng
                </button>
                <button 
                    @click="activeTab = 'security'" 
                    class="px-4 py-2 text-sm font-medium transition-all"
                    :class="activeTab === 'security' ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    Bảo mật
                </button>
            </div>

            <!-- General Tab -->
            <div v-if="activeTab === 'general'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-base font-medium text-gray-800 mb-4">Thông tin chung</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Tên cửa hàng</label>
                                <input v-model="settings.store_name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Email</label>
                                <input v-model="settings.store_email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Số điện thoại</label>
                                <input v-model="settings.store_phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Địa chỉ</label>
                                <input v-model="settings.store_address" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Mã số thuế</label>
                                <input v-model="settings.tax_code" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Email B2B</label>
                                <input v-model="settings.b2b_email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-base font-medium text-gray-800 mb-4">Chính sách bán hàng</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Tiền cọc pre-order (%)</label>
                                <input v-model="settings.preorder_deposit" type="number" min="0" max="100" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700 block mb-1">Thời gian giao pre-order (ngày)</label>
                                <input v-model="settings.preorder_lead_time" type="number" min="1" max="365" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-5">
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-base font-medium text-gray-800 mb-4">Thanh toán</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.cod" class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500">
                                <span class="text-sm">COD</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.bank" class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500">
                                <span class="text-sm">Chuyển khoản</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.momo" class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500">
                                <span class="text-sm">Momo</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.vnpay" class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500">
                                <span class="text-sm">VNPay</span>
                            </label>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-base font-medium text-gray-800 mb-4">SEO</h3>
                        <div>
                            <label class="text-sm text-gray-700 block mb-1">Meta Title</label>
                            <input v-model="settings.seo.title" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="mt-3">
                            <label class="text-sm text-gray-700 block mb-1">Meta Description</label>
                            <textarea v-model="settings.seo.description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 resize-none"></textarea>
                        </div>
                        <div class="mt-3">
                            <label class="text-sm text-gray-700 block mb-1">Meta Keywords</label>
                            <input v-model="settings.seo.keywords" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500" placeholder="SEO keywords">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div v-if="activeTab === 'users'" class="bg-white rounded-lg border border-gray-200 p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-medium text-gray-800">Người dùng</h3>
                    <button 
                        @click="openAddUserModal" 
                        class="px-3 py-1.5 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition-colors"
                    >
                        + Thêm
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 text-gray-600 font-medium">TÊN</th>
                                <th class="text-left py-3 text-gray-600 font-medium">EMAIL</th>
                                <th class="text-left py-3 text-gray-600 font-medium">VAI TRÒ</th>
                                <th class="text-left py-3 text-gray-600 font-medium">TRẠNG THÁI</th>
                                <th class="text-center py-3 text-gray-600 font-medium">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3">{{ user.name }}</td>
                                <td class="py-3 text-gray-600">{{ user.email }}</td>
                                <td class="py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="user.role === 'admin' ? 'bg-purple-100 text-purple-700' : (user.role === 'staff' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')">
                                        {{ user.role === 'admin' ? 'Admin' : (user.role === 'staff' ? 'Nhân viên' : 'Người dùng') }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="user.status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                                        {{ user.status ? 'Hoạt động' : 'Khóa' }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <button 
                                        @click="openEditUserModal(user)" 
                                        class="text-blue-600 hover:text-blue-800 mr-2 transition-colors"
                                        :disabled="isProcessing"
                                    >
                                        Sửa
                                    </button>
                                    <button 
                                        @click="toggleUserStatus(user)" 
                                        class="text-yellow-600 hover:text-yellow-800 mr-2 transition-colors"
                                        :disabled="isProcessing"
                                    >
                                        {{ user.status ? 'Khóa' : 'Kích hoạt' }}
                                    </button>
                                    <button 
                                        @click="confirmDeleteUser(user)" 
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        :disabled="isProcessing"
                                    >
                                        Xóa
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="5" class="py-8 text-center text-gray-400">Chưa có người dùng</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Security Tab -->
            <div v-if="activeTab === 'security'" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-medium text-gray-800 mb-4">Đổi mật khẩu</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-700 block mb-1">Mật khẩu hiện tại</label>
                            <input 
                                v-model="passwordForm.current_password" 
                                type="password" 
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                                :disabled="isProcessing"
                                placeholder="Nhập mật khẩu hiện tại"
                            >
                        </div>
                        <div>
                            <label class="text-sm text-gray-700 block mb-1">Mật khẩu mới</label>
                            <input 
                                v-model="passwordForm.new_password" 
                                type="password" 
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                                :disabled="isProcessing"
                                placeholder="Nhập mật khẩu mới"
                            >
                            <p class="text-xs text-gray-400 mt-1">Mật khẩu phải có ít nhất 6 ký tự</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-700 block mb-1">Xác nhận mật khẩu</label>
                            <input 
                                v-model="passwordForm.new_password_confirmation" 
                                type="password" 
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500"
                                :disabled="isProcessing"
                                placeholder="Xác nhận mật khẩu mới"
                            >
                        </div>
                        <button 
                            @click="changePassword" 
                            class="mt-2 px-4 py-2 bg-orange-600 text-white rounded-lg text-sm hover:bg-orange-700 transition-colors disabled:opacity-50"
                            :disabled="isProcessing"
                        >
                            {{ isProcessing ? 'Đang xử lý...' : 'Cập nhật' }}
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-medium text-gray-800 mb-4">Xác thực 2 lớp</h3>
                    <p class="text-sm text-gray-500 mb-4">Tăng cường bảo mật cho tài khoản admin</p>
                    <button class="px-4 py-2 border border-orange-600 text-orange-600 rounded-lg text-sm hover:bg-orange-50 transition-colors">
                        Kích hoạt 2FA
                    </button>
                </div>
            </div>

            <!-- Save Button - Chỉ hiển thị ở tab General -->
            <div v-if="activeTab === 'general'" class="mt-5 flex justify-end">
                <button 
                    @click="saveSettings" 
                    :disabled="isSaving" 
                    class="px-5 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 transition-colors disabled:opacity-50"
                >
                    {{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}
                </button>
            </div>

            <!-- Add/Edit User Modal -->
            <div v-if="showAddUserModal || showEditUserModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
                <div class="bg-white rounded-lg w-full max-w-md p-6">
                    <h3 class="text-lg font-bold mb-4">{{ formUser.id ? 'Sửa người dùng' : 'Thêm người dùng' }}</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1">Họ tên</label>
                            <input v-model="formUser.name" type="text" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1">Email</label>
                            <input v-model="formUser.email" type="email" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div v-if="!formUser.id">
                            <label class="text-sm font-medium text-gray-700 block mb-1">Mật khẩu</label>
                            <input v-model="formUser.password" type="password" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 block mb-1">Vai trò</label>
                            <select v-model="formUser.role" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                                <option value="admin">Admin</option>
                                <option value="staff">Nhân viên</option>
                                <option value="user">Người dùng</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="formUser.status" class="w-4 h-4 rounded text-orange-600">
                                <span class="text-sm">Kích hoạt</span>
                            </label>
                        </div>
                        <div v-if="errorMessage" class="p-2 bg-red-50 text-red-600 text-sm rounded">{{ errorMessage }}</div>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button @click="closeUserModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Hủy</button>
                        <button @click="saveUser" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">Lưu</button>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm Modal -->
            <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="handleOverlayClick">
                <div class="bg-white rounded-lg w-full max-w-sm p-6">
                    <h3 class="text-lg font-bold mb-4">Xác nhận xóa</h3>
                    <p class="text-gray-600">Xóa người dùng <strong class="text-orange-600">{{ selectedUser?.name }}</strong>?</p>
                    <p class="text-xs text-gray-400 mt-1">Hành động này không thể hoàn tác.</p>
                    <div class="flex justify-end gap-2 mt-6">
                        <button @click="closeUserModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Hủy</button>
                        <button @click="deleteUser" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>

</style>