<script setup>
import { ref, reactive } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Active tab
const activeTab = ref('Chung');
const tabs = ['Chung', 'Phân quyền', 'Bảo mật'];

// Settings data
const settings = reactive({
    storeName: 'BigBag.vn',
    email: 'contact@bigbag.vn',
    phone: '1900 1234',
    address: '123 Đường ABC, Quận 1, TP.HCM',
    taxCode: '0123456789',
    b2bEmail: 'b2b@bigbag.vn',
    preorderDeposit: 30,
    preorderLeadTime: 15,
    payments: {
        cod: true,
        bank: true,
        momo: false,
        vnpay: false
    },
    seo: {
        title: 'BigBag.vn - Balo và Túi xách cao cấp',
        description: 'BigBag.vn chuyên cung cấp balo, túi xách cao cấp chính hãng cho cá nhân và doanh nghiệp',
        keywords: 'balo, túi xách, phụ kiện thời trang, quà tặng doanh nghiệp'
    }
});

// Users data
const users = ref([
    { 
        id: 1, 
        username: 'admin', 
        email: 'admin@bigbag.vn', 
        role: 'Admin', 
        permission: 'Full', 
        active: true 
    },
    { 
        id: 2, 
        username: 'sale', 
        email: 'sale@bigbag.vn', 
        role: 'Nhân viên', 
        permission: 'Chỉ đơn hàng', 
        active: true 
    },
    { 
        id: 3, 
        username: 'warehouse', 
        email: 'kho@bigbag.vn', 
        role: 'Kho', 
        permission: 'Chỉ sản phẩm', 
        active: false 
    },
    { 
        id: 4, 
        username: 'marketing', 
        email: 'marketing@bigbag.vn', 
        role: 'Marketing', 
        permission: 'Chỉ khuyến mãi', 
        active: true 
    }
]);

// Permission options
const permissionOptions = [
    'Full',
    'Chỉ đơn hàng',
    'Chỉ sản phẩm',
    'Chỉ khách hàng',
    'Chỉ báo cáo',
    'Chỉ khuyến mãi'
];

// Role options
const roleOptions = ['Admin', 'Nhân viên', 'Kho', 'Marketing', 'Kế toán'];

// Password form
const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

// Loading state
const isSaving = ref(false);
const isAddingUser = ref(false);
const showAddUserModal = ref(false);

// New user form
const newUser = reactive({
    username: '',
    email: '',
    role: 'Nhân viên',
    permission: 'Chỉ đơn hàng',
    password: '',
    password_confirmation: ''
});

// Save general settings
const saveSettings = async () => {
    isSaving.value = true;
    try {
        await router.put('/admin/settings/general', settings, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Đã lưu cài đặt hệ thống thành công!');
            },
            onError: (errors) => {
                console.error('Lỗi lưu cài đặt:', errors);
                alert('Có lỗi xảy ra khi lưu cài đặt');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    } finally {
        isSaving.value = false;
    }
};

// Update user permission
const updateUserPermission = async (user) => {
    try {
        await router.put(`/admin/settings/users/${user.id}`, {
            permission: user.permission,
            role: user.role,
            active: user.active
        }, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Cập nhật quyền thành công');
            },
            onError: (errors) => {
                console.error('Lỗi cập nhật:', errors);
                alert('Có lỗi xảy ra khi cập nhật quyền');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

// Toggle user status
const toggleUserStatus = async (user) => {
    user.active = !user.active;
    await updateUserPermission(user);
};

// Delete user
const deleteUser = async (id) => {
    const user = users.value.find(u => u.id === id);
    if (!confirm(`Bạn có chắc chắn muốn xóa người dùng "${user?.username}"?`)) {
        return;
    }
    
    try {
        await router.delete(`/admin/settings/users/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                users.value = users.value.filter(u => u.id !== id);
                alert('Xóa người dùng thành công!');
            },
            onError: (errors) => {
                console.error('Lỗi xóa:', errors);
                alert('Có lỗi xảy ra khi xóa người dùng');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    }
};

// Add new user
const addUser = async () => {
    if (!newUser.username || !newUser.email) {
        alert('Vui lòng nhập đầy đủ thông tin');
        return;
    }
    
    if (newUser.password !== newUser.password_confirmation) {
        alert('Mật khẩu xác nhận không khớp');
        return;
    }
    
    isAddingUser.value = true;
    try {
        await router.post('/admin/settings/users', newUser, {
            preserveScroll: true,
            onSuccess: () => {
                users.value.push({
                    id: Date.now(),
                    username: newUser.username,
                    email: newUser.email,
                    role: newUser.role,
                    permission: newUser.permission,
                    active: true
                });
                showAddUserModal.value = false;
                newUser.username = '';
                newUser.email = '';
                newUser.role = 'Nhân viên';
                newUser.permission = 'Chỉ đơn hàng';
                newUser.password = '';
                newUser.password_confirmation = '';
                alert('Thêm người dùng thành công!');
            },
            onError: (errors) => {
                console.error('Lỗi thêm user:', errors);
                alert('Có lỗi xảy ra khi thêm người dùng');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    } finally {
        isAddingUser.value = false;
    }
};

// Change password
const changePassword = async () => {
    if (!passwordForm.current_password || !passwordForm.new_password) {
        alert('Vui lòng nhập đầy đủ thông tin mật khẩu');
        return;
    }
    
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
        alert('Mật khẩu mới và xác nhận không khớp');
        return;
    }
    
    if (passwordForm.new_password.length < 6) {
        alert('Mật khẩu mới phải có ít nhất 6 ký tự');
        return;
    }
    
    isSaving.value = true;
    try {
        await router.put('/admin/settings/password', passwordForm, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Đổi mật khẩu thành công!');
                passwordForm.current_password = '';
                passwordForm.new_password = '';
                passwordForm.new_password_confirmation = '';
            },
            onError: (errors) => {
                console.error('Lỗi đổi mật khẩu:', errors);
                alert('Mật khẩu hiện tại không chính xác');
            }
        });
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Có lỗi xảy ra');
    } finally {
        isSaving.value = false;
    }
};

// Enable 2FA
const enable2FA = () => {
    alert('Tính năng xác thực 2 lớp đang được phát triển');
};

// Upload logo
const uploadLogo = () => {
    alert('Tính năng tải logo đang được phát triển');
};

// Get status class
const getStatusClass = (active) => {
    return active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
};

// Get status label
const getStatusLabel = (active) => {
    return active ? 'Hoạt động' : 'Khóa';
};
</script>

<template>
    <Head title="Cài đặt hệ thống - BigBag Admin" />
    
    <AdminLayout>
        <div class="p-4 md:p-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Cài đặt hệ thống</h1>
                <p class="text-gray-600 text-sm mt-1">Cấu hình thông tin chung, phân quyền và các tham số bán hàng</p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-6 border-b border-gray-200">
                <button 
                    v-for="tab in tabs" 
                    :key="tab" 
                    @click="activeTab = tab" 
                    class="px-5 py-2.5 text-sm font-medium transition-all"
                    :class="activeTab === tab ? 'text-orange-600 border-b-2 border-orange-600' : 'text-gray-500 hover:text-gray-700'"
                >
                    {{ tab }}
                </button>
            </div>

            <!-- Tab: Chung -->
            <div v-if="activeTab === 'Chung'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - General Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Thông tin chung -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4">Thông tin chung</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Tên cửa hàng</label>
                                <input v-model="settings.storeName" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            </div>
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Email</label>
                                <input v-model="settings.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            </div>
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Số điện thoại</label>
                                <input v-model="settings.phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            </div>
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Địa chỉ</label>
                                <input v-model="settings.address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            </div>
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Mã số thuế</label>
                                <input v-model="settings.taxCode" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            </div>
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Email B2B (bán sỉ)</label>
                                <input v-model="settings.b2bEmail" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            </div>
                        </div>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4">Phương thức thanh toán</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.cod" class="w-4 h-4 text-orange-600 rounded">
                                <span class="text-gray-700">COD</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.bank" class="w-4 h-4 text-orange-600 rounded">
                                <span class="text-gray-700">Chuyển khoản</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.momo" class="w-4 h-4 text-orange-600 rounded">
                                <span class="text-gray-700">Momo</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.payments.vnpay" class="w-4 h-4 text-orange-600 rounded">
                                <span class="text-gray-700">VNPay</span>
                            </label>
                        </div>
                    </div>

                    <!-- Chính sách bán hàng -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4">Chính sách bán hàng</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Điều kiện đặt pre-order (tiền cọc %)</label>
                                <input v-model="settings.preorderDeposit" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                                <span class="text-xs text-gray-500">% giá trị đơn hàng</span>
                            </div>
                            <div>
                                <label class="text-sm block mb-1 text-gray-700">Thời gian giao hàng pre-order</label>
                                <input v-model="settings.preorderLeadTime" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                                <span class="text-xs text-gray-500">ngày làm việc</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Logo & SEO -->
                <div class="space-y-6">
                    <!-- Logo -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200 text-center">
                        <div class="w-24 h-24 rounded-full bg-orange-50 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-5xl text-orange-600">storefront</span>
                        </div>
                        <h3 class="font-semibold text-gray-800">Logo cửa hàng</h3>
                        <button @click="uploadLogo" class="mt-3 text-sm text-orange-600 border border-orange-600 px-4 py-1.5 rounded-lg hover:bg-orange-50 transition-colors">
                            Tải lên
                        </button>
                        <p class="text-xs text-gray-400 mt-2">Kích thước khuyến nghị: 200x200px</p>
                    </div>

                    <!-- SEO -->
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-3">Thông tin SEO</h3>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700">Meta Title</label>
                            <input v-model="settings.seo.title" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            <p class="text-xs text-gray-400 mt-1">Tiêu đề hiển thị trên kết quả tìm kiếm</p>
                        </div>
                        <div class="mt-3">
                            <label class="text-sm block mb-1 text-gray-700">Meta Description</label>
                            <textarea v-model="settings.seo.description" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Mô tả ngắn về website</p>
                        </div>
                        <div class="mt-3">
                            <label class="text-sm block mb-1 text-gray-700">Meta Keywords</label>
                            <input v-model="settings.seo.keywords" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            <p class="text-xs text-gray-400 mt-1">Từ khóa SEO, cách nhau bằng dấu phẩy</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Phân quyền -->
            <div v-if="activeTab === 'Phân quyền'" class="bg-white rounded-xl p-6 border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Quản lý người dùng</h3>
                    <button @click="showAddUserModal = true" class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-700 transition-colors">
                        + Thêm người dùng
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 text-gray-600 font-semibold">TÊN ĐĂNG NHẬP</th>
                                <th class="text-left py-3 text-gray-600 font-semibold">EMAIL</th>
                                <th class="text-left py-3 text-gray-600 font-semibold">VAI TRÒ</th>
                                <th class="text-left py-3 text-gray-600 font-semibold">QUYỀN HẠN</th>
                                <th class="text-left py-3 text-gray-600 font-semibold">TRẠNG THÁI</th>
                                <th class="text-center py-3 text-gray-600 font-semibold">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 hover:bg-orange-50">
                                <td class="py-3 font-medium text-gray-800">{{ user.username }}</td>
                                <td class="py-3 text-gray-600">{{ user.email }}</td>
                                <td class="py-3">
                                    <select v-model="user.role" @change="updateUserPermission(user)" class="text-xs px-2 py-1 rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-orange-500">
                                        <option v-for="role in roleOptions" :key="role" :value="role">{{ role }}</option>
                                    </select>
                                </td>
                                <td class="py-3">
                                    <select v-model="user.permission" @change="updateUserPermission(user)" class="text-xs px-2 py-1 rounded-lg border border-gray-300 bg-white focus:outline-none focus:border-orange-500">
                                        <option v-for="perm in permissionOptions" :key="perm" :value="perm">{{ perm }}</option>
                                    </select>
                                </td>
                                <td class="py-3">
                                    <span class="text-xs px-2 py-1 rounded-full" :class="getStatusClass(user.active)">
                                        {{ getStatusLabel(user.active) }}
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    <button @click="toggleUserStatus(user)" class="p-1 text-orange-600 hover:bg-orange-100 rounded-lg transition-colors" title="Đổi trạng thái">
                                        <span class="material-symbols-outlined text-lg">{{ user.active ? 'lock_open' : 'lock' }}</span>
                                    </button>
                                    <button @click="deleteUser(user.id)" class="p-1 text-red-600 hover:bg-red-100 rounded-lg ml-1 transition-colors" title="Xóa">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex gap-2">
                    <button class="border border-orange-600 text-orange-600 px-4 py-2 rounded-lg text-sm hover:bg-orange-50 transition-colors">
                        + Thêm vai trò
                    </button>
                </div>
            </div>

            <!-- Tab: Bảo mật -->
            <div v-if="activeTab === 'Bảo mật'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Change Password -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="font-semibold text-gray-800 mb-4">Thay đổi mật khẩu</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm block mb-1 text-gray-700">Mật khẩu hiện tại</label>
                            <input v-model="passwordForm.current_password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700">Mật khẩu mới</label>
                            <input v-model="passwordForm.new_password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                            <p class="text-xs text-gray-400 mt-1">Mật khẩu phải có ít nhất 6 ký tự</p>
                        </div>
                        <div>
                            <label class="text-sm block mb-1 text-gray-700">Xác nhận mật khẩu mới</label>
                            <input v-model="passwordForm.new_password_confirmation" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20">
                        </div>
                        <button @click="changePassword" :disabled="isSaving" class="mt-2 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                            {{ isSaving ? 'Đang cập nhật...' : 'Cập nhật mật khẩu' }}
                        </button>
                    </div>
                </div>

                <!-- 2FA -->
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <h3 class="font-semibold text-gray-800 mb-4">Xác thực 2 lớp (2FA)</h3>
                    <p class="text-sm text-gray-500 mb-4">Tăng cường bảo mật cho tài khoản admin bằng xác thực hai yếu tố</p>
                    <button @click="enable2FA" class="text-sm border border-orange-600 text-orange-600 px-4 py-2 rounded-lg hover:bg-orange-50 transition-colors">
                        Kích hoạt 2FA
                    </button>
                </div>
            </div>

            <!-- Save Button -->
            <div v-if="activeTab === 'Chung'" class="mt-6 flex justify-end">
                <button @click="saveSettings" :disabled="isSaving" class="bg-orange-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-orange-700 transition-colors">
                    {{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}
                </button>
            </div>
        </div>

        <!-- Modal thêm người dùng -->
        <div v-if="showAddUserModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showAddUserModal = false">
            <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Thêm người dùng mới</h3>
                    <button @click="showAddUserModal = false" class="text-gray-400 hover:text-gray-600 transition-colors text-xl">✕</button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">Tên đăng nhập</label>
                        <input v-model="newUser.username" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500">
                    </div>
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">Email</label>
                        <input v-model="newUser.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500">
                    </div>
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">Vai trò</label>
                        <select v-model="newUser.role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500">
                            <option v-for="role in roleOptions" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">Quyền hạn</label>
                        <select v-model="newUser.permission" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500">
                            <option v-for="perm in permissionOptions" :key="perm" :value="perm">{{ perm }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">Mật khẩu</label>
                        <input v-model="newUser.password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500">
                    </div>
                    <div>
                        <label class="text-sm block mb-1 text-gray-700">Xác nhận mật khẩu</label>
                        <input v-model="newUser.password_confirmation" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-500">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showAddUserModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button @click="addUser" :disabled="isAddingUser" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        {{ isAddingUser ? 'Đang thêm...' : 'Thêm' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* No additional styles needed - using Tailwind classes */
</style>