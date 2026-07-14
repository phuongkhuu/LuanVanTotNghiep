<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <Head title="Đăng nhập - BigBag Premium Utility Carry Gear" />
    
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
      <!-- Logo -->
      <div class="text-center">
        <Link :href="route('home')" class="inline-block">
          <span class="text-3xl font-bold text-primary">BigBag</span>
          <span class="text-3xl font-bold text-gray-800">.vn</span>
        </Link>
        <h2 class="mt-6 text-2xl font-bold text-gray-900">Đăng nhập</h2>
        <p class="mt-2 text-sm text-gray-600">Chào mừng bạn quay trở lại</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="mt-8 space-y-6">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autofocus
            class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-colors"
            placeholder="Email của bạn"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Mật khẩu
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-colors pr-12"
              placeholder="Mật khẩu của bạn"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <span class="material-symbols-outlined text-xl">
                {{ showPassword ? 'visibility_off' : 'visibility' }}
              </span>
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
        </div>

        <!-- Remember & Forgot -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-primary focus:ring-primary/50 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-700">
              Ghi nhớ đăng nhập
            </label>
          </div>

          <Link :href="route('password.request')" class="text-sm text-primary hover:underline">
            Quên mật khẩu?
          </Link>
        </div>

        <!-- Submit -->
        <div>
          <button
            type="submit"
            :disabled="processing"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <span v-if="!processing">Đăng nhập</span>
            <span v-else class="flex items-center gap-2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Đang xử lý...
            </span>
          </button>
        </div>

        <!-- Register link -->
        <div class="text-center text-sm text-gray-600">
          Chưa có tài khoản?
          <Link :href="route('register')" class="font-medium text-primary hover:underline">
            Đăng ký ngay
          </Link>
        </div>
      </form>

      <!-- Social Login -->
      <div class="mt-6">
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Hoặc đăng nhập với</span>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
          <button
            type="button"
            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Google
          </button>
          <button
            type="button"
            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="#1877F2" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Facebook
          </button>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600 text-center">{{ errorMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'
import { CartEvents } from '@/events/CartEvents'

const { reloadCart } = useCart()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const errors = ref({})
const errorMessage = ref('')
const processing = ref(false)
const showPassword = ref(false)

const submit = async () => {
  processing.value = true
  errors.value = {}
  errorMessage.value = ''

  try {
    await router.post(route('login'), form.value, {
      preserveState: false,
      onSuccess: (page) => {
        if (page.props.auth && page.props.auth.user) {
          window.user = page.props.auth.user
          console.log('Login success, user:', window.user)
          
          // Sử dụng CartEvents để dispatch event
          CartEvents.emitUserChanged(window.user.id)
          
          setTimeout(() => {
            reloadCart()
          }, 100)
        }
        
        router.visit('/')
      },
      onError: (err) => {
        errors.value = err
        if (err.email) {
          errorMessage.value = err.email
        } else if (err.password) {
          errorMessage.value = err.password
        } else {
          errorMessage.value = 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.'
        }
      }
    })
  } catch (error) {
    console.error('Login error:', error)
    errorMessage.value = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.'
  } finally {
    processing.value = false
  }
}
</script>