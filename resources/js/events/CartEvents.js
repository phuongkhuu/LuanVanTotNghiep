// resources/js/events/CartEvents.js

/**
 * Event bus cho giỏ hàng
 * Dùng để đồng bộ giữa các component
 */
export const CartEvents = {
    /**
     * Dispatch event khi cart thay đổi
     * @param {number} count - Số lượng sản phẩm trong giỏ
     */
    emitUpdated: (count) => {
        window.dispatchEvent(new CustomEvent('cart-updated', { 
            detail: { count, timestamp: Date.now() }
        }))
    },
    
    /**
     * Lắng nghe event cart-updated
     * @param {Function} callback - Hàm xử lý
     */
    onUpdated: (callback) => {
        window.addEventListener('cart-updated', callback)
    },
    
    /**
     * Hủy lắng nghe event
     * @param {Function} callback - Hàm xử lý cần hủy
     */
    offUpdated: (callback) => {
        window.removeEventListener('cart-updated', callback)
    },
    
    /**
     * Dispatch event khi user thay đổi (login/logout)
     * @param {string|number} userId - ID của user
     */
    emitUserChanged: (userId) => {
        window.dispatchEvent(new CustomEvent('user-changed', { 
            detail: { userId, timestamp: Date.now() }
        }))
    },
    
    /**
     * Lắng nghe event user-changed
     * @param {Function} callback - Hàm xử lý
     */
    onUserChanged: (callback) => {
        window.addEventListener('user-changed', callback)
    },
    
    /**
     * Hủy lắng nghe event user-changed
     * @param {Function} callback - Hàm xử lý cần hủy
     */
    offUserChanged: (callback) => {
        window.removeEventListener('user-changed', callback)
    }
}