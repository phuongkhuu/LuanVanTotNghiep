// useCart.js
import { ref, computed } from 'vue'
import axios from 'axios'
import { CartEvents } from '@/events/CartEvents'

// ============= STATE TOÀN CỤC (Singleton) =============
const cartItems = ref([])
const cartCount = ref(0)
const cartTotal = ref(0)
const loading = ref(false)
const couponCode = ref('')
const discountAmount = ref(0)
const appliedCoupon = ref(null)
const couponError = ref('')
let isFetching = false

// ============= HÀM TIỆN ÍCH =============
const getUserId = () => {
    if (window.user && window.user.id) {
        return String(window.user.id)
    }
    return 'guest'
}

const getStorageKey = () => {
    return `bigbag_cart_${getUserId()}`
}

const saveToLocalStorage = (items) => {
    try {
        const key = getStorageKey()
        localStorage.setItem(key, JSON.stringify(items))
    } catch (e) {
        // ignore
    }
}

const loadFromLocalStorage = () => {
    try {
        const key = getStorageKey()
        const data = localStorage.getItem(key)
        if (data) {
            return JSON.parse(data)
        }
    } catch (e) {
        console.error('Error loading cart:', e)
    }
    return []
}

// ============= COMPUTED =============
const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const total = computed(() => {
    return subtotal.value - discountAmount.value
})

// ============= CẬP NHẬT SỐ LƯỢNG =============
const updateCounts = () => {
    const newCount = cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
    cartCount.value = newCount
    cartTotal.value = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    CartEvents.emitUpdated(newCount)
}

// ============= VOUCHER =============
const setVoucherFromSession = (code, discount) => {
    if (code && discount > 0) {
        couponCode.value = code
        discountAmount.value = discount
        appliedCoupon.value = {
            code: code,
            discount_type: 'fixed',
            discount_value: discount
        }
        couponError.value = ''
        return true
    }
    return false
}

const getVoucherFromStorage = () => {
    try {
        const key = `bigbag_voucher_${getUserId()}`
        const data = localStorage.getItem(key)
        if (data) {
            return JSON.parse(data)
        }
    } catch (e) {
        console.error('Error loading voucher:', e)
    }
    return null
}

const saveVoucherToStorage = (code, discount) => {
    try {
        const key = `bigbag_voucher_${getUserId()}`
        localStorage.setItem(key, JSON.stringify({ code, discount }))
    } catch (e) {
        console.error('Error saving voucher:', e)
    }
}

const clearVoucherStorage = () => {
    try {
        const key = `bigbag_voucher_${getUserId()}`
        localStorage.removeItem(key)
    } catch (e) {
        console.error('Error clearing voucher:', e)
    }
}

// ============= CÁC HÀNH ĐỘNG =============
const fetchCart = async () => {
    if (isFetching) return
    isFetching = true
    loading.value = true

    try {
        const localItems = loadFromLocalStorage()

        if (localItems.length === 0) {
            cartItems.value = []
            updateCounts()
            isFetching = false
            loading.value = false
            return
        }

        const cartData = {}
        localItems.forEach(item => {
            cartData[item.id] = {
                quantity: item.quantity,
                price: item.price
            }
        })

        const response = await axios.get('/api/cart', {
            params: {
                cart: JSON.stringify(cartData)
            },
            timeout: 10000
        })

        if (response.data.success) {
            cartItems.value = response.data.items || []
            updateCounts()
            saveToLocalStorage(cartItems.value)
        } else {
            cartItems.value = localItems
            updateCounts()
        }
    } catch (error) {
        console.error('Error fetching cart:', error)
        const localItems = loadFromLocalStorage()
        cartItems.value = localItems
        updateCounts()
    } finally {
        loading.value = false
        isFetching = false
    }
}

const addToCart = async (variantId, quantity = 1) => {
    try {
        const response = await axios.post('/api/cart/add', {
            variant_id: variantId,
            quantity: quantity
        }, {
            timeout: 10000
        })

        if (response.data.success) {
            const currentCart = loadFromLocalStorage()
            const existingIndex = currentCart.findIndex(item => item.id === variantId)

            if (existingIndex > -1) {
                currentCart[existingIndex].quantity += quantity
            } else {
                currentCart.push({
                    ...response.data.item,
                    quantity: quantity
                })
            }

            saveToLocalStorage(currentCart)
            cartItems.value = currentCart
            updateCounts()

            setTimeout(() => {
                fetchCart()
            }, 500)

            return response.data
        }
    } catch (error) {
        console.error('Error adding to cart:', error)
        throw error
    }
}

const updateCart = async (variantId, quantity) => {
    try {
        await axios.put('/api/cart/update', {
            variant_id: variantId,
            quantity: quantity
        })

        const currentCart = loadFromLocalStorage()
        const index = currentCart.findIndex(item => item.id === variantId)
        if (index > -1) {
            if (quantity <= 0) {
                currentCart.splice(index, 1)
            } else {
                currentCart[index].quantity = quantity
            }
            saveToLocalStorage(currentCart)
            cartItems.value = currentCart
            updateCounts()

            setTimeout(() => {
                fetchCart()
            }, 500)
        }
        return { success: true }
    } catch (error) {
        console.error('Error updating cart:', error)
        throw error
    }
}

const removeFromCart = async (variantId) => {
    try {
        await axios.delete(`/api/cart/remove/${variantId}`)

        const currentCart = loadFromLocalStorage()
        const index = currentCart.findIndex(item => item.id === variantId)
        if (index > -1) {
            currentCart.splice(index, 1)
            saveToLocalStorage(currentCart)
            cartItems.value = currentCart
            updateCounts()

            setTimeout(() => {
                fetchCart()
            }, 500)
        }
        return { success: true }
    } catch (error) {
        console.error('Error removing from cart:', error)
        throw error
    }
}

const clearCart = async () => {
    try {

        await axios.delete('/api/cart/clear')

        cartItems.value = []

        updateCounts()

        const key = getStorageKey()
        localStorage.removeItem(key)

        clearVoucherStorage()

        return { success: true }
    } catch (error) {
        console.error('❌ Lỗi trong clearCart:', error)
        throw error
    }
}

const applyCoupon = async (code) => {
    couponError.value = ''
    try {
        const response = await axios.post('/api/cart/apply-coupon', {
            code: code,
            subtotal: subtotal.value
        })
        if (response.data.success) {
            discountAmount.value = response.data.discount_amount || 0
            appliedCoupon.value = response.data.coupon
            couponError.value = ''
            saveVoucherToStorage(code, discountAmount.value)
            return response.data
        }
    } catch (error) {
        couponError.value = error.response?.data?.message || 'Có lỗi xảy ra khi áp dụng mã'
        throw error
    }
}

const removeCoupon = async () => {
    try {
        await axios.post('/api/cart/remove-coupon')

        discountAmount.value = 0
        appliedCoupon.value = null
        couponCode.value = ''
        couponError.value = ''

        clearVoucherStorage()
        return { success: true }
    } catch (error) {
        console.error('Error removing coupon:', error)
        discountAmount.value = 0
        appliedCoupon.value = null
        couponCode.value = ''
        couponError.value = ''
        clearVoucherStorage()
        throw error
    }
}

const reloadCart = () => {
    fetchCart()
}

const restoreVoucher = () => {
    const voucher = getVoucherFromStorage()
    if (voucher) {
        couponCode.value = voucher.code
        discountAmount.value = voucher.discount
        appliedCoupon.value = {
            code: voucher.code,
            discount_type: 'fixed',
            discount_value: voucher.discount
        }
        return true
    }
    return false
}

// ============= EXPORT =============
export function useCart() {
    return {
        cartItems,
        cartCount,
        cartTotal,
        loading,
        subtotal,
        total,
        couponCode,
        discountAmount,
        appliedCoupon,
        couponError,
        fetchCart,
        addToCart,
        updateCart,
        removeFromCart,
        clearCart,
        applyCoupon,
        removeCoupon,
        reloadCart,
        getUserId,
        setVoucherFromSession,
        restoreVoucher,
    }
}