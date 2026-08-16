// resources/js/utils/useCart.js
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
const stockErrors = ref({})
const stockWarnings = ref({})
const toastMessage = ref('') // <--- THÊM: Thông báo toast
const toastType = ref('') // <--- THÊM: Loại toast (success, error, warning)
const showToast = ref(false) // <--- THÊM: Hiển thị toast
let isFetching = false
let toastTimeout = null

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

// ============= TOAST =============
const showToastMessage = (message, type = 'error', duration = 3000) => {
    if (toastTimeout) {
        clearTimeout(toastTimeout)
    }
    toastMessage.value = message
    toastType.value = type
    showToast.value = true
    
    toastTimeout = setTimeout(() => {
        showToast.value = false
        toastTimeout = null
    }, duration)
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

// ============= HÀM SO SÁNH META ĐỂ GỘP GIỎ HÀNG =============
const isMetaEqual = (metaA, metaB) => {
    if (!metaA && !metaB) return true
    if (!metaA || !metaB) return false
    return JSON.stringify(metaA) === JSON.stringify(metaB)
}

// ============= KIỂM TRA VÀ ĐIỀU CHỈNH TỒN KHO =============
const validateAndFixStock = (items) => {
    const errors = {}
    const warnings = {}
    let hasError = false
    let hasWarning = false
    let fixedItems = [...items]
    
    fixedItems = fixedItems.map(item => {
        if (item.stock !== undefined && item.quantity > item.stock) {
            if (item.stock === 0) {
                errors[item.id] = `Sản phẩm đã hết hàng, đã xóa khỏi giỏ`
                hasError = true
                showToastMessage(`"${item.name}" đã hết hàng, đã xóa khỏi giỏ`, 'error')
                return null
            } else {
                warnings[item.id] = `Số lượng đã được điều chỉnh từ ${item.quantity} xuống ${item.stock} (tồn kho tối đa)`
                hasWarning = true
                showToastMessage(`"${item.name}" đã được điều chỉnh xuống ${item.stock} (tồn kho tối đa)`, 'warning')
                return {
                    ...item,
                    quantity: item.stock
                }
            }
        }
        return item
    }).filter(item => item !== null)
    
    stockErrors.value = errors
    stockWarnings.value = warnings
    
    return {
        fixedItems,
        hasError,
        hasWarning,
        errors,
        warnings
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
            stockErrors.value = {}
            stockWarnings.value = {}
            updateCounts()
            isFetching = false
            loading.value = false
            return
        }

        const cartData = {}
        localItems.forEach(item => {
            cartData[item.id] = {
                quantity: item.quantity,
                price: item.price,
                meta: item.meta || null
            }
        })

        const response = await axios.get('/api/cart', {
            params: {
                cart: JSON.stringify(cartData)
            },
            timeout: 10000
        })

        if (response.data.success) {
            let itemsWithMeta = response.data.items.map((item) => {
                const localItem = localItems.find(l => l.id === item.id)
                return {
                    ...item,
                    meta: localItem?.meta || null
                }
            })
            
            const result = validateAndFixStock(itemsWithMeta)
            cartItems.value = result.fixedItems
            saveToLocalStorage(cartItems.value)
            updateCounts()
        } else {
            const result = validateAndFixStock(localItems)
            cartItems.value = result.fixedItems
            saveToLocalStorage(cartItems.value)
            updateCounts()
        }
    } catch (error) {
        console.error('Error fetching cart:', error)
        const localItems = loadFromLocalStorage()
        const result = validateAndFixStock(localItems)
        cartItems.value = result.fixedItems
        saveToLocalStorage(cartItems.value)
        updateCounts()
    } finally {
        loading.value = false
        isFetching = false
    }
}

const addToCart = async (variantId, quantity = 1, meta = null) => {
    try {
        // ============ KIỂM TRA TỒN KHO TRƯỚC KHI GỌI API ============
        // Tìm sản phẩm trong giỏ hàng hiện tại
        const existingItem = cartItems.value.find(item => 
            item.id === variantId && 
            isMetaEqual(item.meta || null, meta)
        )
        
        // Nếu sản phẩm đã có trong giỏ, kiểm tra tổng số lượng
        if (existingItem) {
            const totalQuantity = existingItem.quantity + quantity
            if (existingItem.stock !== undefined && totalQuantity > existingItem.stock) {
                const errorMsg = `Không thể thêm. Sản phẩm chỉ còn ${existingItem.stock} sản phẩm trong kho. Bạn đang có ${existingItem.quantity} sản phẩm.`
                stockErrors.value = {
                    ...stockErrors.value,
                    [variantId]: errorMsg
                }
                showToastMessage(errorMsg, 'error', 5000)
                
                setTimeout(() => {
                    const newErrors = { ...stockErrors.value }
                    delete newErrors[variantId]
                    stockErrors.value = newErrors
                }, 5000)
                
                throw new Error(errorMsg)
            }
        }

        const response = await axios.post('/api/cart/add', {
            variant_id: variantId,
            quantity: quantity,
            meta: meta
        }, {
            timeout: 10000
        })

        if (response.data.success) {
            const currentCart = loadFromLocalStorage()
            const existingIndex = currentCart.findIndex(item => 
                item.id === variantId && 
                isMetaEqual(item.meta || null, meta)
            )

            const newItem = {
                ...response.data.item,
                quantity: quantity,
                meta: meta || null
            }

            if (existingIndex > -1) {
                const oldQuantity = currentCart[existingIndex].quantity
                currentCart[existingIndex].quantity += quantity
                currentCart[existingIndex].price = newItem.price
                // Hiển thị thông báo thành công
                showToastMessage(`Đã thêm ${quantity} sản phẩm "${newItem.name}" vào giỏ hàng`, 'success')
            } else {
                currentCart.push(newItem)
                showToastMessage(`Đã thêm "${newItem.name}" vào giỏ hàng`, 'success')
            }

            const result = validateAndFixStock(currentCart)
            saveToLocalStorage(result.fixedItems)
            cartItems.value = result.fixedItems
            
            updateCounts()

            setTimeout(() => {
                fetchCart()
            }, 500)

            return response.data
        }
    } catch (error) {
        console.error('Error adding to cart:', error)
        // Nếu chưa có thông báo từ logic kiểm tra stock
        if (!error.message?.includes('Không thể thêm')) {
            const errorMsg = error.response?.data?.message || 'Không thể thêm sản phẩm vào giỏ hàng'
            showToastMessage(errorMsg, 'error', 4000)
        }
        throw error
    }
}

const updateCart = async (variantId, quantity) => {
    try {
        const currentItem = cartItems.value.find(item => item.id === variantId)
        if (!currentItem) {
            throw new Error('Không tìm thấy sản phẩm')
        }

        // ============ KIỂM TRA TỒN KHO KHI TĂNG SỐ LƯỢNG ============
        if (quantity > currentItem.quantity) {
            if (currentItem.stock !== undefined && quantity > currentItem.stock) {
                const errorMsg = `Số lượng vượt quá tồn kho. Sản phẩm chỉ còn ${currentItem.stock} sản phẩm.`
                stockErrors.value = {
                    ...stockErrors.value,
                    [variantId]: errorMsg
                }
                showToastMessage(errorMsg, 'error', 5000)
                
                setTimeout(() => {
                    const newErrors = { ...stockErrors.value }
                    delete newErrors[variantId]
                    stockErrors.value = newErrors
                }, 5000)
                
                throw new Error(errorMsg)
            }
        }

        // Xóa lỗi nếu có
        if (stockErrors.value[variantId]) {
            const newErrors = { ...stockErrors.value }
            delete newErrors[variantId]
            stockErrors.value = newErrors
        }
        
        if (stockWarnings.value[variantId]) {
            const newWarnings = { ...stockWarnings.value }
            delete newWarnings[variantId]
            stockWarnings.value = newWarnings
        }

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
            
            const result = validateAndFixStock(currentCart)
            saveToLocalStorage(result.fixedItems)
            cartItems.value = result.fixedItems
            
            updateCounts()

            setTimeout(() => {
                fetchCart()
            }, 500)
        }
        return { success: true }
    } catch (error) {
        console.error('Error updating cart:', error)
        if (error.response?.data?.message?.includes('tồn kho')) {
            const errorMsg = error.response.data.message
            stockErrors.value = {
                ...stockErrors.value,
                [variantId]: errorMsg
            }
            showToastMessage(errorMsg, 'error', 5000)
            setTimeout(() => {
                const newErrors = { ...stockErrors.value }
                delete newErrors[variantId]
                stockErrors.value = newErrors
            }, 5000)
        }
        throw error
    }
}

const removeFromCart = async (variantId) => {
    try {
        await axios.delete(`/api/cart/remove/${variantId}`)

        const currentCart = loadFromLocalStorage()
        const index = currentCart.findIndex(item => item.id === variantId)
        if (index > -1) {
            const removedItem = currentCart[index]
            currentCart.splice(index, 1)
            saveToLocalStorage(currentCart)
            cartItems.value = currentCart
            
            if (stockErrors.value[variantId]) {
                const newErrors = { ...stockErrors.value }
                delete newErrors[variantId]
                stockErrors.value = newErrors
            }
            if (stockWarnings.value[variantId]) {
                const newWarnings = { ...stockWarnings.value }
                delete newWarnings[variantId]
                stockWarnings.value = newWarnings
            }
            
            showToastMessage(`Đã xóa "${removedItem.name}" khỏi giỏ hàng`, 'success')
            updateCounts()

            setTimeout(() => {
                fetchCart()
            }, 500)
        }
        return { success: true }
    } catch (error) {
        console.error('Error removing from cart:', error)
        showToastMessage('Xóa sản phẩm thất bại', 'error')
        throw error
    }
}

const clearCart = async () => {
    try {
        await axios.delete('/api/cart/clear')

        cartItems.value = []
        stockErrors.value = {}
        stockWarnings.value = {}
        updateCounts()

        const key = getStorageKey()
        localStorage.removeItem(key)

        clearVoucherStorage()
        showToastMessage('Đã xóa toàn bộ giỏ hàng', 'success')

        return { success: true }
    } catch (error) {
        console.error('❌ Lỗi trong clearCart:', error)
        showToastMessage('Xóa giỏ hàng thất bại', 'error')
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
            showToastMessage(`Đã áp dụng mã giảm giá ${code}`, 'success')
            return response.data
        }
    } catch (error) {
        couponError.value = error.response?.data?.message || 'Có lỗi xảy ra khi áp dụng mã'
        showToastMessage(couponError.value, 'error', 4000)
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
        showToastMessage('Đã xóa mã giảm giá', 'success')
        return { success: true }
    } catch (error) {
        console.error('Error removing coupon:', error)
        discountAmount.value = 0
        appliedCoupon.value = null
        couponCode.value = ''
        couponError.value = ''
        clearVoucherStorage()
        showToastMessage('Xóa mã giảm giá thất bại', 'error')
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
        stockErrors,
        stockWarnings,
        toastMessage,
        toastType,
        showToast,
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
        validateAndFixStock,
        clearVoucherStorage,
        showToastMessage 
    }
}