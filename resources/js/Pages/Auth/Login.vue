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

      <!-- Error Message tổng quát (chỉ hiển thị khi không có lỗi email/password) -->
      <div v-if="errorMessage && !errors.email && !errors.password" class="p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-sm text-red-600 text-center">{{ errorMessage }}</p>
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
            :class="{ 'border-red-500 focus:ring-red-500/50 focus:border-red-500': errors.email }"
            placeholder="Email của bạn"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">error</span>
            {{ errors.email }}
          </p>
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
              :class="{ 'border-red-500 focus:ring-red-500/50 focus:border-red-500': errors.password }"
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
          <p v-if="errors.password" class="mt-1 text-sm text-red-600 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">error</span>
            {{ errors.password }}
          </p>
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
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'
import { CartEvents } from '@/events/CartEvents'

const { reloadCart } = useCart()
const page = usePage()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const errors = ref({})
const errorMessage = ref('')
const processing = ref(false)
const showPassword = ref(false)

// Hàm dịch lỗi từ tiếng Anh sang tiếng Việt
const translateError = (message) => {
  if (!message) return ''
  const translations = {
    'These credentials do not match our records.': 'Email hoặc mật khẩu không đúng.',
    'The email field is required.': 'Vui lòng nhập email.',
    'The password field is required.': 'Vui lòng nhập mật khẩu.',
    'The email must be a valid email address.': 'Email không hợp lệ (cần có ký tự @).',
    'The email has already been taken.': 'Email này đã được sử dụng.',
    'The password must be at least 8 characters.': 'Mật khẩu phải có ít nhất 8 ký tự.',
    'The password confirmation does not match.': 'Xác nhận mật khẩu không khớp.',
    'The password must be at least 8 characters.': 'Mật khẩu phải có ít nhất 8 ký tự.',
  }
  return translations[message] || message
}

// Theo dõi errors từ page.props và dịch sang tiếng Việt
watch(() => page.props.errors, (newErrors) => {
  if (newErrors && Object.keys(newErrors).length > 0) {
    const translated = {}
    for (const [key, value] of Object.entries(newErrors)) {
      // Nếu value là mảng, lấy phần tử đầu tiên
      const msg = Array.isArray(value) ? value[0] : value
      translated[key] = translateError(msg)
    }
    errors.value = translated

    // Nếu có lỗi email/password, không hiển thị thông báo chung
    if (!translated.email && !translated.password) {
      const firstError = Object.values(translated)[0]
      errorMessage.value = firstError || 'Đăng nhập thất bại. Vui lòng kiểm tra lại thông tin.'
    } else {
      errorMessage.value = '' // xóa thông báo chung để tránh trùng lặp
    }
  } else {
    errors.value = {}
    errorMessage.value = ''
  }
}, { immediate: true, deep: true })

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
          CartEvents.emitUserChanged(window.user.id)
          setTimeout(() => {
            reloadCart()
          }, 100)
        }
        router.visit('/')
      },
      onError: (err) => {
        // Fallback nếu watch không bắt được (thường không cần)
        // Không cần xử lý vì watch đã bắt từ page.props.errors
      }
    })
  } catch (error) {
    errorMessage.value = 'Đã có lỗi xảy ra. Vui lòng thử lại sau.'
  } finally {
    processing.value = false
  }
}
</script>