<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const passwordFieldType = ref('password');
const loading = ref(false);

const togglePasswordVisibility = () => {
    passwordFieldType.value = passwordFieldType.value === 'password' ? 'text' : 'password';
};

const submit = () => {
    loading.value = true;
    form.post(route('login'), {
        onSuccess: () => {
            loading.value = false;
            // Kiểm tra xem có redirect URL không
            const redirectUrl = sessionStorage.getItem('redirectAfterLogin')
            if (redirectUrl) {
                sessionStorage.removeItem('redirectAfterLogin')
                window.location.href = redirectUrl
            }
        },
        onFinish: () => {
            form.reset('password');
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        }
    });
};
</script>

<template>
    <Head title="Đăng nhập - BigBag Premium Utility Carry Gear" />
    
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
            
            <!-- Logo & Title -->
            <div class="text-center">
                <Link :href="route('home')" class="inline-block">
                    <h1 class="text-3xl font-bold">
                        <span class="text-primary">BigBag</span><span class="text-gray-800">.vn</span>
                    </h1>
                </Link>
                <h2 class="mt-6 text-2xl font-bold text-gray-900">Đăng nhập</h2>
                <p class="mt-2 text-sm text-gray-600">Vui lòng nhập thông tin đăng nhập</p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="rounded-lg bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                {{ status }}
            </div>

            <!-- Form Login -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400 text-xl">mail</span>
                            </span>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                class="block w-full pl-10 pr-3 py-2.5 border rounded-lg focus:ring-primary focus:border-primary bg-gray-50 text-gray-900 text-sm"
                                :class="form.errors.email ? 'border-red-500' : 'border-gray-300'"
                                placeholder="example@bigbag.vn"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mật khẩu <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-gray-400 text-xl">lock</span>
                            </span>
                            <input
                                :id="passwordFieldType"
                                :type="passwordFieldType === 'password' ? 'password' : 'text'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="block w-full pl-10 pr-10 py-2.5 border rounded-lg focus:ring-primary focus:border-primary bg-gray-50 text-gray-900 text-sm"
                                :class="form.errors.password ? 'border-red-500' : 'border-gray-300'"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <span class="material-symbols-outlined text-gray-400 text-xl hover:text-gray-600">
                                    {{ passwordFieldType === 'password' ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>
                </div>

                <!-- Remember me & Forgot password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            v-model="form.remember"
                            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded cursor-pointer"
                        />
                        <span class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</span>
                    </label>

                    <Link 
                        v-if="canResetPassword"
                        :href="route('password.request')" 
                        class="text-sm text-primary hover:text-primary-dark font-medium"
                    >
                        Quên mật khẩu?
                    </Link>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing" class="absolute left-4 inset-y-0 flex items-center">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ form.processing ? 'Đang xử lý...' : 'ĐĂNG NHẬP' }}
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center text-sm">
                    <span class="text-gray-600">Chưa có tài khoản?</span>
                    <Link :href="route('register')" class="ml-1 font-medium text-primary hover:text-primary-dark">
                        Đăng ký ngay
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;
}
</style>