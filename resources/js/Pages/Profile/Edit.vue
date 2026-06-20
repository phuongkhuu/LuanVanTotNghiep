<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';
import Chatbot from '@/Components/Chatbot.vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
    },
});

// Lấy user từ page props
const page = usePage();
const user = page.props.auth.user;

// Profile form - THÊM PHONE
const profileForm = useForm({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '', // 🔥 Thêm trường phone
});

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Delete account form
const deleteForm = useForm({
    password: '',
});

const showDeleteModal = ref(false);
const passwordInput = ref(null);

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            profileForm.reset();
        },
    });
};

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
    setTimeout(() => {
        if (passwordInput.value) {
            passwordInput.value.focus();
        }
    }, 100);
};

const deleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
        },
        onError: () => {
            if (passwordInput.value) {
                passwordInput.value.focus();
            }
        },
        onFinish: () => {
            deleteForm.reset();
        },
    });
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteForm.clearErrors();
    deleteForm.reset();
};
</script>

<template>
    <Head title="Hồ sơ của tôi - BigBag" />
    
    <div class="min-h-screen bg-gray-50">
        <AppHeader />
        
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Tiêu đề trang -->
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-800">Hồ sơ của tôi</h1>
                <p class="text-sm text-gray-500 mt-1">Quản lý thông tin cá nhân và bảo mật tài khoản</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Menu bên trái -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg border border-gray-200 p-5 sticky top-24">
                        <div class="text-center">
                            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-3">
                                <span class="text-2xl font-semibold text-gray-600">{{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                            </div>
                            <h3 class="font-medium text-gray-800">{{ user?.name || 'Người dùng' }}</h3>
                            <p class="text-sm text-gray-500">{{ user?.email || '' }}</p>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="space-y-1">
                                <Link :href="route('profile.edit')" class="block px-3 py-2 rounded-lg text-sm text-orange-600 bg-orange-50 font-medium">
                                    Thông tin cá nhân
                                </Link>
                                <Link :href="route('home')" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                    Đơn hàng của tôi
                                </Link>
                                <Link :href="route('customize')" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                    Yêu cầu tùy chỉnh
                                </Link>
                                <Link :href="route('wholesale')" class="block px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                                    Báo giá B2B
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nội dung chính -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Thông tin cá nhân -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Thông tin cá nhân</h2>
                            <p class="text-sm text-gray-500 mt-1">Cập nhật thông tin hồ sơ và địa chỉ email của bạn</p>
                        </div>
                        
                        <form @submit.prevent="updateProfile" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                                <input 
                                    type="text" 
                                    v-model="profileForm.name" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 text-sm"
                                    required
                                />
                                <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-500">{{ profileForm.errors.name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ email</label>
                                <input 
                                    type="email" 
                                    v-model="profileForm.email" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 text-sm"
                                    required
                                />
                                <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-500">{{ profileForm.errors.email }}</p>
                            </div>

                            <!-- 🔥 THÊM TRƯỜNG SỐ ĐIỆN THOẠI -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                                <input 
                                    type="tel" 
                                    v-model="profileForm.phone" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 text-sm"
                                />
                                <p v-if="profileForm.errors.phone" class="mt-1 text-xs text-red-500">{{ profileForm.errors.phone }}</p>
                            </div>
                            
                            <div class="flex items-center gap-3 pt-2">
                                <button 
                                    type="submit" 
                                    :disabled="profileForm.processing"
                                    class="px-5 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50"
                                >
                                    {{ profileForm.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}
                                </button>
                                <span v-if="profileForm.recentlySuccessful" class="text-sm text-green-600">Đã lưu!</span>
                            </div>
                        </form>
                    </div>

                    <!-- Đổi mật khẩu -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Đổi mật khẩu</h2>
                            <p class="text-sm text-gray-500 mt-1">Đảm bảo tài khoản của bạn được bảo mật với mật khẩu mạnh</p>
                        </div>
                        
                        <form @submit.prevent="updatePassword" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
                                <input 
                                    type="password" 
                                    v-model="passwordForm.current_password" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 text-sm"
                                    required
                                />
                                <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                                <input 
                                    type="password" 
                                    v-model="passwordForm.password" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 text-sm"
                                    required
                                />
                                <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                                <input 
                                    type="password" 
                                    v-model="passwordForm.password_confirmation" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 text-sm"
                                    required
                                />
                                <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-xs text-red-500">{{ passwordForm.errors.password_confirmation }}</p>
                            </div>
                            
                            <div class="flex items-center gap-3 pt-2">
                                <button 
                                    type="submit" 
                                    :disabled="passwordForm.processing"
                                    class="px-5 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition-colors disabled:opacity-50"
                                >
                                    {{ passwordForm.processing ? 'Đang cập nhật...' : 'Cập nhật mật khẩu' }}
                                </button>
                                <span v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600">Đã cập nhật!</span>
                            </div>
                        </form>
                    </div>

                    <!-- Xóa tài khoản -->
                    <div class="bg-white rounded-lg border border-red-200 p-6">
                        <div class="border-b border-red-200 pb-4 mb-4">
                            <h2 class="text-lg font-semibold text-red-600">Xóa tài khoản</h2>
                            <p class="text-sm text-gray-500 mt-1">Khi tài khoản bị xóa, tất cả dữ liệu sẽ bị xóa vĩnh viễn</p>
                        </div>
                        
                        <div>
                            <button 
                                @click="confirmDelete" 
                                class="px-5 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                            >
                                Xóa tài khoản
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal xác nhận xóa tài khoản -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeDeleteModal">
            <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Xóa tài khoản</h3>
                    <button @click="closeDeleteModal" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>
                
                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác. Tất cả dữ liệu của bạn sẽ bị xóa vĩnh viễn.
                    </p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nhập mật khẩu để xác nhận</label>
                        <input 
                            ref="passwordInput"
                            type="password" 
                            v-model="deleteForm.password" 
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-red-400 focus:ring-1 focus:ring-red-400 text-sm"
                            placeholder="Mật khẩu của bạn"
                            @keyup.enter="deleteAccount"
                        />
                        <p v-if="deleteForm.errors.password" class="mt-1 text-xs text-red-500">{{ deleteForm.errors.password }}</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button 
                        @click="closeDeleteModal" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors"
                    >
                        Hủy
                    </button>
                    <button 
                        @click="deleteAccount" 
                        :disabled="deleteForm.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors disabled:opacity-50"
                    >
                        {{ deleteForm.processing ? 'Đang xóa...' : 'Xóa tài khoản' }}
                    </button>
                </div>
            </div>
        </div>

        <Chatbot />
        <AppFooter />
    </div>
</template>