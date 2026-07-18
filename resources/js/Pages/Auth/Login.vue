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