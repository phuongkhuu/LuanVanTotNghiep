import { ref } from 'vue'
import axios from 'axios'

// Cấu hình axios
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const cartCount = ref(0)
const cartItems = ref([])
const loading = ref(false)

export function useCart() {
    // Hàm lấy URL route, fallback sang đường dẫn không prefix /api
    const getRoute = (name, params = {}) => {
        try {
            // Nếu Ziggy có sẵn, dùng route helper
            return route(name, params)
        } catch (e) {
            // Fallback URLs (không có /api)
            const fallbacks = {
                'cart.index': '/api/cart',
                'cart.add': '/api/cart/add',
                'cart.update': '/api/cart/update',
                'cart.remove': '/api/cart/remove',
                'cart.clear': '/api/cart/clear',
            }
            let url = fallbacks[name]
            if (url && params.id) {
                url = `${url}/${params.id}`
            }
            return url || `/${name.replace('.', '/')}`
        }
    }

    // Lấy giỏ hàng
    const fetchCart = async () => {
        loading.value = true
        try {
            const url = getRoute('cart.index')
            const response = await axios.get(url)
            cartItems.value = response.data.items || []
            cartCount.value = response.data.count || 0
            return response.data
        } catch (error) {
            console.error('Lỗi lấy giỏ hàng:', error)
            // Thử fallback URL nếu lỗi 404
            if (error.response?.status === 404) {
                try {
                    const fallbackResponse = await axios.get('/api/cart')
                    cartItems.value = fallbackResponse.data.items || []
                    cartCount.value = fallbackResponse.data.count || 0
                    return fallbackResponse.data
                } catch (e) {
                    console.error('Fallback vẫn lỗi:', e)
                    throw e
                }
            }
            throw error
        } finally {
            loading.value = false
        }
    }

    // Thêm vào giỏ
    const addToCart = async (variantId, quantity = 1) => {
        try {
            const url = getRoute('cart.add')
            const response = await axios.post(url, {
                variant_id: variantId,
                quantity: quantity
            })
            if (response.data.success) {
                cartCount.value = response.data.cart_count
                await fetchCart()
            }
            return response.data
        } catch (error) {
            console.error('Lỗi thêm giỏ hàng:', error)
            throw error
        }
    }

    // Cập nhật số lượng
    const updateCart = async (variantId, quantity) => {
        try {
            const url = getRoute('cart.update')
            const response = await axios.put(url, {
                variant_id: variantId,
                quantity: quantity
            })
            if (response.data.success) {
                cartCount.value = response.data.cart_count
                await fetchCart()
            }
            return response.data
        } catch (error) {
            console.error('Lỗi cập nhật giỏ hàng:', error)
            throw error
        }
    }

    // Xóa sản phẩm khỏi giỏ (có fallback và optimistic update)
    const removeFromCart = async (variantId) => {
        try {
            // URL chính
            const url = getRoute('cart.remove', { id: variantId })
            const response = await axios.delete(url)
            if (response.data.success) {
                // Optimistic update: xóa ngay lập tức khỏi UI
                cartItems.value = cartItems.value.filter(item => item.id !== variantId)
                cartCount.value = response.data.cart_count ?? cartCount.value - 1
                // Đồng bộ lại từ server (có thể bỏ qua nếu lỗi)
                try {
                    await fetchCart()
                } catch (e) {
                    // Nếu fetch lỗi, vẫn giữ state local
                    console.warn('Không thể đồng bộ giỏ hàng sau khi xóa, vẫn giữ state local')
                }
            }
            return response.data
        } catch (error) {
            console.error('Lỗi xóa giỏ hàng:', error)
            // Nếu lỗi 404, thử fallback URL (không có /api)
            if (error.response?.status === 404) {
                try {
                    const fallbackUrl = `/api/cart/remove/${variantId}`
                    const response = await axios.delete(fallbackUrl)
                    if (response.data.success) {
                        cartItems.value = cartItems.value.filter(item => item.id !== variantId)
                        cartCount.value = response.data.cart_count ?? cartCount.value - 1
                        try {
                            await fetchCart()
                        } catch (e) {
                            // bỏ qua
                        }
                    }
                    return response.data
                } catch (e) {
                    console.error('Fallback xóa vẫn lỗi:', e)
                    throw e
                }
            }
            throw error
        }
    }

    // Xóa toàn bộ giỏ hàng
    const clearCart = async () => {
        try {
            const url = getRoute('cart.clear')
            const response = await axios.delete(url)
            if (response.data.success) {
                cartCount.value = 0
                cartItems.value = []
            }
            return response.data
        } catch (error) {
            console.error('Lỗi xóa giỏ hàng:', error)
            throw error
        }
    }

    // Refresh giỏ hàng (đồng bộ)
    const refreshCart = async () => {
        await fetchCart()
    }

    return {
        cartCount,   
        cartItems,      
        loading,     
        fetchCart,
        addToCart,
        updateCart,
        removeFromCart,
        clearCart,
        refreshCart
    }
}