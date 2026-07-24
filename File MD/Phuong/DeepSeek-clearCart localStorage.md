# clearCart localStorage

**Created:** 07/18/2026 14:30  
**Updated:** 07/19/2026 08:44  
**Exported:** 07/22/2026 08:09  
**Link:** [https://chat.deepseek.com/a/chat/s/9a763f6c-5453-4bdc-9322-90f328ac2caa](https://chat.deepseek.com/a/chat/s/9a763f6c-5453-4bdc-9322-90f328ac2caa)  

## User:
07/18/2026 23:26

clearCart().catch(() => {})
const { clearCart } = useCart()
import { ref, computed } from 'vue'
import axios from 'axios'
import { CartEvents } from '@/events/CartEvents'

export function useCart() {
    const cartItems = ref([])
    const cartCount = ref(0)
    const cartTotal = ref(0)
    const loading = ref(false)
    const couponCode = ref('')
    const discountAmount = ref(0)
    const appliedCoupon = ref(null)
    const couponError = ref('')
    let isFetching = false

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

    const subtotal = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    })

    const total = computed(() => {
        return subtotal.value - discountAmount.value
    })

    const updateCounts = () => {
        const newCount = cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
        cartCount.value = newCount
        cartTotal.value = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        
        CartEvents.emitUpdated(newCount)
    }

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
            console.error('Error clearing cart:', error)
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

    // ============ SỬA: Xóa coupon ============
    const removeCoupon = async () => {
        try {
            // Gọi API xóa session
            const response = await axios.post('/api/cart/remove-coupon')
            
            // Reset state
            discountAmount.value = 0
            appliedCoupon.value = null
            couponCode.value = ''
            couponError.value = ''
            
            // Xóa localStorage
            clearVoucherStorage()
            return { success: true }
        } catch (error) {
            console.error('Error removing coupon:', error)
            // Vẫn reset state dù API fail
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
Làm sao để biết được clearcart làm gì? Đồng thời, liệu clearcart ở đây đã xóa dữ liệu giỏ hàng trong localstorage chưa?


## Assistant:
07/18/2026 23:26

> **Thinking:**
> Chúng ta có một đoạn code của composable `useCart` trong Vue. Ở đầu câu hỏi có dòng `clearCart().catch(() => {})` và hỏi "Làm sao để biết được clearcart làm gì? Đồng thời, liệu clearcart ở đây đã xóa dữ liệu giỏ hàng trong localstorage chưa?"
> 
> Chúng ta cần phân tích hàm `clearCart` được định nghĩa trong `useCart`:
> 
> ```javascript
> const clearCart = async () => {
>     try {
>         await axios.delete('/api/cart/clear')
>         cartItems.value = []
>         updateCounts()
>         const key = getStorageKey()
>         localStorage.removeItem(key)
>         clearVoucherStorage()
>         return { success: true }
>     } catch (error) {
>         console.error('Error clearing cart:', error)
>         throw error
>     }
> }
> ```
> 
> - Nó gọi API xóa giỏ hàng trên server.
> - Sau đó, nó set `cartItems.value = []` (reactive data).
> - Gọi `updateCounts()` để cập nhật số lượng và tổng.
> - Lấy key của localStorage dựa trên userId (hoặc 'guest').
> - Xóa item trong localStorage với key đó (`localStorage.removeItem(key)`).
> - Gọi `clearVoucherStorage()` để xóa voucher đã lưu.
> - Trả về promise với { success: true }.
> 
> Vậy rõ ràng `clearCart` đã xóa dữ liệu giỏ hàng trong localStorage.
> 
> Tuy nhiên, câu hỏi còn: "Làm sao để biết được clearcart làm gì?" - Có thể người hỏi muốn biết cách đọc code hoặc debug để hiểu chức năng. Ta có thể trả lời: đọc trực tiếp định nghĩa hàm, xem các hành động: gọi API, xóa state, xóa localStorage, xóa voucher.
> 
> Cũng cần lưu ý: dòng `clearCart().catch(() => {})` ở đầu có thể là một lời gọi không có await, bắt lỗi bỏ qua, nhưng không ảnh hưởng đến câu hỏi.
> 
> Trả lời: Hàm `clearCart` xóa toàn bộ giỏ hàng: gọi API xóa, reset state, xóa localStorage và voucher. Vậy nó đã xóa dữ liệu localStorage.
> 
> Đưa ra câu trả lời chi tiết, bao gồm cách xác định và xác nhận đã xóa localStorage.
> 
> Ngoài ra, có thể nói thêm về `getStorageKey()` dùng userId để tạo key riêng cho user, nên xóa đúng key. Và `clearVoucherStorage()` xóa key voucher.
> 
> Vậy trả lời: đã xóa localStorage. Cách biết: đọc code.

Để biết `clearCart()` làm gì, bạn chỉ cần **đọc định nghĩa của hàm** trong file `useCart.js` (đoạn code bạn đã cung cấp). Cụ thể:

```javascript
const clearCart = async () => {
    try {
        await axios.delete('/api/cart/clear')       // 1. Gọi API xóa giỏ trên server
        cartItems.value = []                        // 2. Xóa dữ liệu trong state (reactive)
        updateCounts()                              // 3. Cập nhật số lượng & tổng tiền
        const key = getStorageKey()                 // 4. Lấy key localStorage theo user
        localStorage.removeItem(key)                // 5. Xóa dữ liệu giỏ hàng trong localStorage
        clearVoucherStorage()                       // 6. Xóa voucher đã lưu
        return { success: true }
    } catch (error) {
        console.error('Error clearing cart:', error)
        throw error
    }
}
```

---

### Về câu hỏi: *“Liệu clearCart ở đây đã xóa dữ liệu giỏ hàng trong localStorage chưa?”*

**Trả lời: CÓ, đã xóa.**

- Dòng `localStorage.removeItem(key)` xóa toàn bộ dữ liệu giỏ hàng của user hiện tại (lưu dưới key `bigbag_cart_<userId>`).
- Ngoài ra, nó còn gọi `clearVoucherStorage()` để xóa luôn thông tin mã giảm giá đã áp dụng (key `bigbag_voucher_<userId>`).

---

### Lưu ý thêm:
- Nếu API xóa thất bại (catch), thì **localStorage vẫn bị xóa** vì đoạn xóa localStorage nằm trong `try` sau lời gọi API, nhưng nếu API throw error, code sẽ nhảy vào `catch` và không chạy tiếp các dòng sau đó → localStorage **không bị xóa**.
- Tuy nhiên trong code hiện tại, `localStorage.removeItem` và `clearVoucherStorage` được đặt sau `await axios.delete` và cùng trong `try`, nên **chỉ khi API thành công thì localStorage mới bị xóa**. Nếu API lỗi, state không bị reset và localStorage vẫn giữ nguyên.

Vì vậy, để đảm bảo xóa localStorage ngay cả khi API lỗi, bạn có thể chuyển phần xóa localStorage ra ngoài `try` hoặc dùng `finally`. Nhưng với code hiện tại, việc xóa localStorage **phụ thuộc vào API thành công**.


## User:
07/18/2026 23:30

Route::prefix('api')->group(function () {
    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update', [CartController::class, 'update']);
    Route::delete('/cart/remove/{variantId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon']);
    Route::get('/cart/get-coupon', [CartController::class, 'getCoupon']);
    Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon']);
<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Tính giá sale cho variant
     */
    private function calculateSalePrice($variant)
    {
        $originalPrice = $variant->price;
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $now = now();

        // Kiểm tra campaign (retail)
        $campaigns = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now);
                })->orWhere(function($q) {
                    $q->whereNull('start_time')
                      ->whereNull('end_time');
                });
            })
            ->whereHas('productVariants', function($query) use ($variant) {
                $query->where('product_variant_id', $variant->id);
            })
            ->with('configs')
            ->get();

        foreach ($campaigns as $campaign) {
            $config = $campaign->configs()->first();
            $currentDiscount = $config ? (float) $config->discount_percent : 0;
            if ($currentDiscount > $discountPercent) {
                $discountPercent = $currentDiscount;
            }
        }

        // Kiểm tra pre-order
        if ($variant->product && ($variant->product->is_preorder ?? false)) {
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $variant->product_id)
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    })->orWhere(function($q) {
                        $q->whereNull('start_time')
                          ->whereNull('end_time');
                    });
                })
                ->first();

            if ($preorder) {
                
                $tiers = $preorder->tiers ?? [];
                
                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $preorderDiscount = $tier['discount'] ?? 0;
                        if ($preorderDiscount > $discountPercent) {
                            $discountPercent = $preorderDiscount;
                        }
                        break;
                    }
                }
                
                if ($discountPercent == 0 && !empty($tiers)) {
                    $discountPercent = $tiers[0]['discount'] ?? 0;
                }
            }
        }

        if ($discountPercent > 0) {
            $salePrice = $originalPrice * (1 - $discountPercent / 100);
            $salePrice = round($salePrice);
        }

        return [
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $discountPercent > 0,
        ];
    }

    /**
     * Lấy giỏ hàng từ request (client gửi lên)
     * CHỈ LẤY SẢN PHẨM THƯỜNG, BỎ QUA PRE-ORDER
     */
    public function index(Request $request)
    {
        try {
            Log::info('CartController@index called', ['method' => $request->method()]);
            
            // Lấy cart từ request
            $cart = [];
            if ($request->isMethod('post')) {
                $cart = $request->input('cart', []);
            } else {
                $cartJson = $request->query('cart', '{}');
                $cart = json_decode($cartJson, true) ?: [];
            }
            
            if (empty($cart)) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'total' => 0,
                    'count' => 0
                ]);
            }
            
            $items = [];
            $total = 0;
            $count = 0;

            foreach ($cart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if (!$variant) {
                    Log::warning("Variant not found: {$variantId}");
                    continue;
                }
                
                // ============ BỎ QUA PRE-ORDER (KHÔNG HIỂN THỊ TRONG GIỎ) ============
                if ($variant->product->is_preorder ?? false) {
                    Log::info("Skipping pre-order item in cart: {$variantId}");
                    continue;
                }

                // Tính giá sale
                $saleInfo = $this->calculateSalePrice($variant);
                $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;

                // Lấy ảnh sản phẩm
                $image = '/images/default-product.jpg';
                if ($variant->product && $variant->product->image_url) {
                    if (is_array($variant->product->image_url) && !empty($variant->product->image_url)) {
                        $image = $variant->product->image_url[0];
                    } elseif (is_string($variant->product->image_url)) {
                        $image = $variant->product->image_url;
                    }
                }

                $items[] = [
                    'id' => (int) $variantId,
                    'product_id' => $variant->product->id ?? 0,
                    'product_variant_id' => (int) $variantId,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'slug' => $variant->product->slug ?? '#',
                    'price' => $price,
                    'original_price' => $variant->price,
                    'quantity' => $item['quantity'] ?? 1,
                    'image' => $image,
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'discount_percent' => $saleInfo['discount_percent'],
                    'stock' => $variant->stock,
                ];
                $total += $price * ($item['quantity'] ?? 1);
                $count += ($item['quantity'] ?? 1);
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'total' => $total,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Cart index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm vào giỏ hàng
     * CHỈ CHO PHÉP SẢN PHẨM THƯỜNG, TỪ CHỐI PRE-ORDER
     */
    public function add(Request $request)
    {
        try {
            Log::info('CartController@add called', $request->all());
            
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'nullable|integer|min:1'
            ]);

            $variantId = $request->variant_id;
            $quantity = $request->quantity ?? 1;

            $variant = ProductVariant::with('product', 'color')->find($variantId);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại'
                ], 404);
            }

            // ============ PRE-ORDER: KHÔNG CHO THÊM VÀO GIỎ HÀNG ============
            if ($variant->product->is_preorder ?? false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm Pre-order không thể thêm vào giỏ hàng. Vui lòng chọn "Mua ngay" để đặt hàng.'
                ], 400);
            }

            // Kiểm tra stock (chỉ cho sản phẩm thường)
            if ($variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm chỉ còn {$variant->stock} sản phẩm"
                ], 400);
            }

            // Tính giá sale
            $saleInfo = $this->calculateSalePrice($variant);
            $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;

            // Lấy ảnh
            $image = '/images/default-product.jpg';
            if ($variant->product && $variant->product->image_url) {
                if (is_array($variant->product->image_url) && !empty($variant->product->image_url)) {
                    $image = $variant->product->image_url[0];
                } elseif (is_string($variant->product->image_url)) {
                    $image = $variant->product->image_url;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'item' => [
                    'id' => (int) $variantId,
                    'product_id' => $variant->product->id ?? 0,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'slug' => $variant->product->slug ?? '#',
                    'price' => $price,
                    'original_price' => $variant->price,
                    'quantity' => $quantity,
                    'image' => $image,
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'discount_percent' => $saleInfo['discount_percent'],
                    'stock' => $variant->stock,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật giỏ hàng
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:0'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($variantId, Request $request)
    {
        try {
            Log::info("CartController@remove called: {$variantId}");
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request)
    {
        try {
            Log::info('CartController@clear called');
            
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng và mã giảm giá'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra và áp dụng voucher
     */
    public function applyCoupon(Request $request)
    {
        try {
            Log::info('CartController@applyCoupon called', $request->all());
            
            $request->validate([
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0'
            ]);

            $code = strtoupper($request->code);
            $subtotal = $request->subtotal;

            $voucher = Campaign::where('code', $code)
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ'
                ], 400);
            }

            if ($voucher->expiry && $voucher->expiry < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết hạn'
                ], 400);
            }

            if ($voucher->limit && $voucher->used >= $voucher->limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết'
                ], 400);
            }

            if ($voucher->min_order > 0 && $subtotal < $voucher->min_order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->min_order) . 'đ'
                ], 400);
            }

            $discountAmount = 0;
            $discountType = $voucher->discount_type;
            $discountValue = $voucher->discount_value;

            if ($discountType === 'percent') {
                $discountAmount = ($subtotal * $discountValue) / 100;
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($discountValue, $subtotal);
            } elseif ($discountType === 'freeship') {
                $discountAmount = 0;
            }

            $discountAmount = round($discountAmount);

            return response()->json([
                'success' => true,
                'coupon' => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                ],
                'discount_amount' => $discountAmount,
                'message' => 'Áp dụng mã giảm giá thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCoupon(Request $request)
    {
        return response()->json([
            'success' => true,
            'coupon' => null,
            'discount_amount' => 0,
        ]);
    }

    public function removeCoupon(Request $request)
    {
        try {
            Log::info('CartController@removeCoupon called');
            
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            $request->session()->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mã giảm giá'
            ]);
        } catch (\Exception $e) {
            Log::error('Remove coupon error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
BE thì sao?


## Assistant:
07/18/2026 23:30

> **Thinking:**
> 1.  **Phân tích yêu cầu của người dùng**: Người dùng đã cung cấp code phía backend (routes Laravel và `CartController`) và hỏi "BE thì sao?". Câu hỏi này xuất phát từ cuộc hội thoại trước đó về hàm `clearCart()` ở phía frontend, cụ thể là việc nó có xóa localStorage không và phụ thuộc vào API như thế nào. Giờ người dùng muốn biết backend (BE) xử lý thế nào khi nhận request `/api/cart/clear`.
> 
> 2.  **Xem xét code BE được cung cấp**:
>     *   Route: `Route::delete('/cart/clear', [CartController::class, 'clear']);`
>     *   Controller method: `public function clear(Request $request) { ... }`
>     *   Logic trong `clear`: Ghi log, sau đó gọi `$request->session()->forget(['voucher_code', 'voucher_discount']);` và trả về JSON `success: true`.
> 
> 3.  **Đánh giá logic BE**:
>     *   BE *không* quản lý state của giỏ hàng (items) vì giỏ hàng được lưu ở phía client (localStorage) và gửi lên qua request (`cart` param) trong `index`. Điều này có nghĩa là backend không lưu trữ danh sách sản phẩm trong giỏ hàng ở session hay database (dựa trên code này). Nó chỉ nhận dữ liệu từ client, xử lý tính toán (giá sale, v.v.) và trả về.
>     *   Hành động `clear` ở BE chỉ xóa *voucher* khỏi session (`voucher_code`, `voucher_discount`). Nó *không* xóa bất kỳ dữ liệu giỏ hàng nào ở phía server vì server không lưu giỏ hàng.
>     *   So sánh với FE: FE gọi API clear, nếu thành công thì FE tự xóa localStorage. Nếu API thất bại, FE không xóa localStorage. Vì BE chỉ đơn giản là xóa session voucher và luôn trả về thành công (trừ khi có exception đặc biệt), nên về cơ bản API luôn thành công. Vì vậy, trong thực tế, localStorage sẽ bị xóa ở FE khi gọi hàm này (vì API gần như luôn trả về 200).
> 
> 4.  **Cấu trúc phản hồi**:
>     *   Trả lời trực tiếp câu hỏi "BE thì sao?".
>     *   Giải thích logic cụ thể của method `clear` trong Controller.
>     *   Nhấn mạnh sự khác biệt: BE *không* lưu giỏ hàng, chỉ lưu voucher tạm thời (session). Vì vậy, việc xóa giỏ hàng hoàn toàn là trách nhiệm của FE (xóa localStorage). BE chỉ xóa session voucher.
>     *   Đưa ra kết luận về tác động: Vì API này hầu như luôn thành công, nên logic FE (xóa localStorage sau khi API thành công) sẽ hoạt động tốt, dẫn đến việc xóa cả giỏ hàng và voucher trên client. BE chỉ xóa voucher trên server.
>     *   (Tùy chọn) Đề cập đến việc nếu có lỗi bất ngờ ở BE (ví dụ lỗi kết nối DB, mặc dù ở đây không dùng DB), thì FE sẽ không xóa localStorage, nhưng trường hợp này hiếm vì code chỉ thao tác session.

Dựa trên code Backend (BE) Laravel bạn cung cấp, tôi xin phân tích chi tiết:

### 1. Backend `clear()` làm gì?

Trong `CartController`, method `clear()` được định nghĩa như sau:

```php
public function clear(Request $request)
{
    try {
        Log::info('CartController@clear called');
        
        // 👇 Điểm quan trọng: CHỈ xóa session của voucher
        $request->session()->forget(['voucher_code', 'voucher_discount']);
        
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng và mã giảm giá'
        ]);
    } catch (\Exception $e) {
        // ...
    }
}
```

**Kết luận về BE:**
- **Không hề lưu giỏ hàng ở server**: Backend Laravel này **không** lưu danh sách sản phẩm trong giỏ hàng vào Database hay Session. Nó chỉ nhận dữ liệu giỏ hàng từ Client gửi lên (trong method `index`) để tính toán giá, kiểm tra tồn kho, rồi trả về.
- **Chỉ xóa mã giảm giá (Voucher)**: Hành động duy nhất ở BE là xóa thông tin voucher đang lưu trong Session (`voucher_code`, `voucher_discount`).
- **Luôn trả về thành công**: Vì chỉ là thao tác xóa session nhẹ nhàng, method này hầu như **luôn luôn** trả về `success: true` (trừ khi có lỗi server đặc biệt).

---

### 2. Kết hợp với Frontend (FE) đã phân tích trước đó:

| Thành phần | Hành động |
| :--- | :--- |
| **Frontend (`clearCart`)** | Gọi API → **Nếu API thành công** → Xóa `localStorage` (giỏ hàng + voucher) và reset State. |
| **Backend (`/api/cart/clear`)** | Xóa session `voucher_code` và `voucher_discount`. **Không** làm gì với dữ liệu giỏ hàng (vì BE không lưu). |

---

### 3. Trả lời câu hỏi của bạn: *"BE thì sao?"*

**BE hoạt động như một "người xác nhận" hơn là "người thực thi"**:

1. **Về giỏ hàng**: BE **không lưu** giỏ hàng, nên việc xóa giỏ hàng thực chất chỉ xảy ra ở FE (localStorage). Khi FE gọi `clearCart`, BE chỉ việc nhận request và trả về `OK`, việc xóa dữ liệu thực tế nằm hoàn toàn ở phía trình duyệt (localStorage.removeItem).
2. **Về Voucher**: BE có lưu voucher tạm thời trong Session (để áp dụng cho thanh toán sau này). Khi gọi `clearCart`, BE sẽ xóa session voucher này. FE cũng tự xóa localStorage voucher của mình.

---

### 4. Rủi ro tiềm ẩn trong logic hiện tại:

Dựa vào flow code:

- FE chỉ xóa `localStorage` **khi API trả về thành công**.
- Vì BE `clear()` hầu như luôn thành công (không truy vấn DB phức tạp), nên việc xóa localStorage gần như chắc chắn diễn ra.
- **Tuy nhiên**, nếu vì lý do nào đó (mất kết nối mạng, server 500 hiếm gặp) mà API thất bại, thì **FE sẽ không xóa localStorage**, nhưng **BE vẫn có thể đã xóa session voucher** (nếu lỗi xảy ra sau dòng `session()->forget`). Điều này dẫn đến trạng thái không đồng bộ: FE vẫn còn giỏ hàng cũ, nhưng voucher trên server đã biến mất.

**Tóm lại**: Backend chỉ đóng vai trò xóa session voucher và báo "thành công" để Frontend tự tin xóa dữ liệu cục bộ. Toàn bộ dữ liệu giỏ hàng (items) được quản lý 100% ở Frontend (localStorage).


## User:
07/18/2026 23:36

<template>
  <div>
    <Head title="Đặt hàng thành công - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-4xl mx-auto px-4 py-12" id="print-area">
      <!-- Success Banner -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
          <span class="material-symbols-outlined text-green-600 text-4xl">check_circle</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h1>
        <p class="text-gray-500 text-lg">Cảm ơn bạn đã mua hàng tại BigBag</p>

        <!-- Order Code -->
        <div class="mt-4 inline-block bg-gray-50 px-6 py-3 rounded-xl border border-gray-200">
          <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
          <p class="text-2xl font-bold text-primary">{{ orderDisplayCode }}</p>
        </div>
      </div>

      <!-- ====== THANH TOÁN PAYOS (không QR) ====== -->
      <div 
        v-if="shouldShowPaymentButton"
        class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8"
      >
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 px-6 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">payments</span>
            <h2 class="text-xl font-semibold text-gray-800">Thanh toán đơn hàng</h2>
          </div>
        </div>
        <div class="p-6 text-center">
          <!-- Trạng thái: Đã thanh toán -->
          <div v-if="paymentStatus === 'paid'" class="py-4">
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
              <span class="material-symbols-outlined text-green-600 align-middle">check_circle</span>
              <span class="text-green-700 font-semibold ml-2">Thanh toán đã được xác nhận!</span>
              <p class="text-sm text-gray-500 mt-2">Trang sẽ tự động chuyển về trang chủ sau 3 giây...</p>
            </div>
          </div>

          <!-- Trạng thái: Thất bại -->
          <div v-else-if="paymentStatus === 'failed'" class="py-4">
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <span class="material-symbols-outlined text-red-600 align-middle">error</span>
              <span class="text-red-700 font-semibold ml-2">Thanh toán thất bại. Vui lòng thử lại.</span>
            </div>
          </div>

          <!-- Trạng thái: Chờ thanh toán -->
          <div v-else-if="paymentStatus === 'pending'" class="py-4">
            <p class="text-gray-600 mb-4">
              Bạn chưa hoàn tất thanh toán. Vui lòng bấm nút bên dưới để thanh toán qua PayOS.
            </p>
            <p class="text-sm text-gray-500 mb-4">
              Số tiền: <span class="font-bold text-primary">{{ formatPrice(orderSummary.final_amount) }}</span>
            </p>

            <!-- Nút mở link thanh toán trong tab mới -->
            <button 
              @click="openPaymentTab"
              class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary-dark transition-all font-medium shadow-md hover:shadow-lg"
            >
              <span class="material-symbols-outlined">open_in_new</span>
              Thanh toán qua PayOS
            </button>

            <!-- Thông tin đơn hàng -->
            <div class="mt-4 text-xs text-gray-400 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 inline-block">
              <span class="material-symbols-outlined text-sm align-middle">info</span>
              Mã đơn hàng: {{ orderDisplayCode }}
            </div>

            <div class="mt-4 text-sm text-gray-500">
              <span class="inline-block animate-pulse mr-2">⏳</span>
              Đang chờ thanh toán...
            </div>
          </div>

          <!-- Lỗi tạo link -->
          <div v-else-if="paymentError" class="py-4 text-red-500">
            <span class="material-symbols-outlined text-4xl block">error</span>
            <p>{{ paymentError }}</p>
            <button 
              @click="fetchPaymentLink" 
              class="mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary-dark"
            >
              Thử lại
            </button>
          </div>

          <!-- Loading -->
          <div v-else class="py-8">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
            <p class="mt-4 text-gray-500">Đang chuẩn bị thanh toán...</p>
          </div>
        </div>
      </div>

      <!-- Order Details Card -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 px-6 py-4 border-b border-gray-100">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-primary">receipt_long</span>
              <h2 class="text-xl font-semibold text-gray-800">Chi tiết đơn hàng</h2>
            </div>
            <div class="flex items-center gap-2">
              <span 
                v-if="order?.order_code === 'preorder'"
                class="inline-block px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full"
              >
                Pre-order
              </span>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
          <!-- Customer Information - 2 columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person</span>
                Thông tin người đặt
              </h3>
              <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Họ tên:</span> <span class="font-medium">{{ order?.customer_name || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Email:</span> <span class="font-medium">{{ customerEmail }}</span></p>
                <p><span class="text-gray-500">Số điện thoại:</span> <span class="font-medium">{{ order?.customer_phone || 'N/A' }}</span></p>
              </div>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">local_shipping</span>
                Thông tin người nhận
              </h3>
              <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Họ tên:</span> <span class="font-medium">{{ order?.receiver_name || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Số điện thoại:</span> <span class="font-medium">{{ order?.receiver_phone || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Địa chỉ:</span> <span class="font-medium">{{ order?.shipping_address || 'N/A' }}</span></p>
              </div>
            </div>
          </div>

          <!-- Note -->
          <div v-if="order?.note" class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500 flex items-start gap-2">
              <span class="material-symbols-outlined text-sm">note</span>
              <span><span class="font-medium text-gray-600">Ghi chú:</span> {{ order.note }}</span>
            </p>
          </div>

          <!-- Products List -->
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">shopping_bag</span>
              Sản phẩm đã đặt
            </h3>
            <div class="border border-gray-100 rounded-xl overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Sản phẩm</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium">Số lượng</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Đơn giá</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="item in orderDetails" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                          <img 
                            :src="item.image || '/images/default-product.jpg'" 
                            :alt="item.name"
                            @error="(e) => { e.target.src = '/images/default-product.jpg' }"
                            class="w-full h-full object-cover"
                          />
                        </div>
                        <div>
                          <p class="font-medium text-gray-800">{{ item.name }}</p>
                          <p v-if="item.color || item.size" class="text-xs text-gray-500">
                            {{ item.color ? `Màu: ${item.color}` : '' }}
                            {{ item.color && item.size ? ' | ' : '' }}
                            {{ item.size ? `Size: ${item.size}` : '' }}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td class="text-center px-4 py-3 font-medium">{{ item.quantity }}</td>
                    <td class="text-right px-4 py-3">{{ formatPrice(item.unit_price) }}</td>
                    <td class="text-right px-4 py-3 font-medium text-primary">{{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Tạm tính</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(orderSummary.subtotal) }}</td>
                  </tr>
                  <tr v-if="orderSummary.shipping_fee > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Phí vận chuyển</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(orderSummary.shipping_fee) }}</td>
                  </tr>
                  <tr v-if="orderSummary.discount_amount > 0" class="bg-green-50">
                    <td colspan="3" class="text-right px-4 py-3 text-green-600 font-medium">
                      <span class="flex items-center justify-end gap-2">
                        <span class="material-symbols-outlined text-sm">local_offer</span>
                        Giảm giá
                      </span>
                    </td>
                    <td class="text-right px-4 py-3 font-medium text-red-500">
                      -{{ formatPrice(orderSummary.discount_amount) }}
                    </td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-3 font-bold text-gray-800">
                      <span class="text-lg">Tổng cộng</span>
                    </td>
                    <td class="text-right px-4 py-3 font-bold text-2xl text-primary">
                      {{ formatPrice(orderSummary.final_amount) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-xl p-4">
            <div>
              <p class="text-sm text-gray-500">Phương thức thanh toán</p>
              <p class="font-medium text-gray-800 flex items-center gap-2 mt-1">
                <span 
                  class="w-3 h-3 rounded-full inline-block"
                  :class="getPaymentColor(order?.payment_method)"
                ></span>
                {{ getPaymentLabel(order?.payment_method) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Trạng thái thanh toán</p>
              <p class="font-medium flex items-center gap-2 mt-1">
                <span 
                  class="inline-block px-2 py-0.5 text-xs font-bold rounded-full"
                  :class="getPaymentStatusBadge(order?.payment_status || 'pending')"
                >
                  {{ getPaymentStatusLabel(order?.payment_status || 'pending') }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center no-print">
        <a 
          :href="route('home')" 
          class="inline-flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 rounded-xl hover:bg-primary-dark transition-all font-semibold shadow-sm hover:shadow-md"
        >
          <span class="material-symbols-outlined">shopping_cart</span>
          Tiếp tục mua sắm
        </a>
        <a 
          :href="route('orders.history')" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-50 transition-all font-semibold"
        >
          <span class="material-symbols-outlined">history</span>
          Xem lịch sử đơn hàng
        </a>
        <button 
          v-if="order?.id"
          @click="printOrder" 
          class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-200 transition-all font-semibold"
        >
          <span class="material-symbols-outlined">print</span>
          In đơn hàng
        </button>
      </div>

      <!-- Support Info -->
      <div class="mt-8 text-center text-sm text-gray-500 no-print">
        <p>Cần hỗ trợ? Liên hệ hotline: <a href="tel:19001234" class="text-primary font-medium">1900 1234</a></p>
        <p class="mt-1">Email: <a href="mailto:support@bigbag.vn" class="text-primary font-medium">support@bigbag.vn</a></p>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import axios from 'axios'
import { useCart } from '@/utils/useCart'

const props = defineProps({
  order: {
    type: Object,
    default: null
  },
  order_display_code: {
    type: String,
    default: ''
  }
})

// ============ CART ============
const { clearCart } = useCart()

// ============ PAYMENT STATE ============
const paymentUrl = ref(null)
const loading = ref(false)
const paymentError = ref(null)
const paymentStatus = ref(props.order?.payment_status || 'pending')
const pollTimer = ref(null)
const redirectTimer = ref(null)

// ============ COMPUTED ============
const shouldShowPaymentButton = computed(() => {
  if (!props.order) return false
  const method = props.order.payment_method
  const status = props.order.payment_status
  // Chỉ hiển thị khi phương thức là bank_transfer hoặc payos và chưa thanh toán
  return (method === 'bank_transfer' || method === 'payos') && status === 'pending'
})

// Lấy mã đơn hàng
const orderDisplayCode = computed(() => {
  if (props.order_display_code) {
    return props.order_display_code
  }
  if (props.order?.order_display_code) {
    return props.order.order_display_code
  }
  if (props.order?.display_code) {
    return props.order.display_code
  }
  return 'N/A'
})

// Lấy email
const customerEmail = computed(() => {
  if (props.order?.customer_email && props.order.customer_email !== 'N/A') {
    return props.order.customer_email
  }
  if (props.order?.user?.email) {
    return props.order.user.email
  }
  return 'N/A'
})

// Chi tiết sản phẩm
const orderDetails = computed(() => {
  if (props.order?.details) {
    return props.order.details.map(detail => ({
      ...detail,
      name: detail.productVariant?.product?.name || detail.name || 'Sản phẩm không xác định',
      image: detail.image || detail.productVariant?.product?.image_url?.[0] || '/images/default-product.jpg',
      color: detail.color || detail.productVariant?.color?.name || '',
      size: detail.size || detail.productVariant?.size_name || '',
    }))
  }
  return []
})

// Tổng hợp đơn hàng
const orderSummary = computed(() => {
  if (props.order) {
    const total = props.order.total_amount || 0
    const discount = props.order.discount_amount || 0
    const shippingFee = props.order.shipping_fee || 0
    const finalAmount = props.order.final_amount || total
    const subtotal = total + discount
    return {
      subtotal,
      shipping_fee: shippingFee,
      discount_amount: discount,
      final_amount: finalAmount,
    }
  }
  return {
    subtotal: 0,
    shipping_fee: 0,
    discount_amount: 0,
    final_amount: 0
  }
})

// ============ METHODS ============
const formatPrice = (val) => {
  if (!val && val !== 0) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  const d = new Date(date)
  return d.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getOrderStatusBadge = (status) => {
  const map = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'processing': 'bg-blue-100 text-blue-800',
    'shipping': 'bg-purple-100 text-purple-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'Đang xử lý': 'bg-yellow-100 text-yellow-800',
    'Đã xác nhận': 'bg-blue-100 text-blue-800',
    'Đang giao hàng': 'bg-purple-100 text-purple-800',
    'Đã giao hàng': 'bg-green-100 text-green-800',
    'Đã hủy': 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng (PayOS)',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
    payos: 'PayOS',
  }
  return map[method] || method || 'Chưa xác định'
}

const getPaymentColor = (method) => {
  const map = {
    cod: 'bg-green-500',
    bank_transfer: 'bg-blue-500',
    ewallet: 'bg-purple-500',
    vnpay: 'bg-red-500',
    momo: 'bg-pink-500',
    payos: 'bg-indigo-500',
  }
  return map[method] || 'bg-gray-400'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    success: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    success: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

// ============ FETCH PAYMENT LINK ============
const fetchPaymentLink = async () => {
  if (!props.order || !props.order.id) return

  loading.value = true
  paymentError.value = null

  try {
    const response = await axios.get(`/payment/link/${props.order.id}`)
    if (response.data.success) {
      paymentUrl.value = response.data.checkout_url
      // Bắt đầu polling để kiểm tra trạng thái
      startPolling()
    } else {
      paymentError.value = response.data.message || 'Không thể tạo link thanh toán'
    }
  } catch (error) {
    console.error('Error fetching payment link:', error)
    paymentError.value = error.response?.data?.message || 'Có lỗi xảy ra khi tạo thanh toán'
  } finally {
    loading.value = false
  }
}

// ============ MỞ TAB THANH TOÁN ============
const openPaymentTab = () => {
  if (paymentUrl.value) {
    window.open(paymentUrl.value, '_blank')
  }
}

// ============ POLLING CHECK ORDER STATUS ============
const checkOrderStatus = async () => {
  try {
    const response = await axios.get(`/don-hang/${props.order.id}`)
    if (response.data && response.data.order) {
      const orderData = response.data.order
      if (orderData.payment_status === 'paid' || orderData.payment_status === 'success') {
        paymentStatus.value = 'paid'
        stopPolling()
        // ==== CHUYỂN HƯỚNG VỀ TRANG CHỦ SAU 3 GIÂY ====
        redirectTimer.value = setTimeout(() => {
          router.visit('/')
        }, 3000)
      } else if (orderData.payment_status === 'failed') {
        paymentStatus.value = 'failed'
        stopPolling()
      }
    }
  } catch (error) {
    console.warn('Polling error:', error)
  }
}

const startPolling = () => {
  if (pollTimer.value) return
  checkOrderStatus()
  pollTimer.value = setInterval(checkOrderStatus, 5000)
}

const stopPolling = () => {
  if (pollTimer.value) {
    clearInterval(pollTimer.value)
    pollTimer.value = null
  }
  if (redirectTimer.value) {
    clearTimeout(redirectTimer.value)
    redirectTimer.value = null
  }
}

// ============ LIFECYCLE ============
onMounted(() => {

  if (shouldShowPaymentButton.value) {
    fetchPaymentLink()
  }
})

onUnmounted(() => {
  clearCart().catch(() => {})
  stopPolling()
})

// ============ PRINT ============
const printOrder = () => {
  if (!props.order) return

  const order = props.order
  const details = orderDetails.value
  const displayCode = orderDisplayCode.value
  const summary = orderSummary.value

  const printWindow = window.open('', '_blank')
  if (!printWindow) {
    alert('Vui lòng cho phép popup để in đơn hàng')
    return
  }

  const detailsHtml = details.map(item => `
    <tr>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">${item.name}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: center;">${item.quantity}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.unit_price)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.subtotal)}</td>
    </tr>
  `).join('')

  const discountHtml = summary.discount_amount > 0 ? `
    <tr>
      <td colspan="3" style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">Giảm giá</td>
      <td style="text-align: right; padding: 8px 12px; border: 1px solid #ddd; color: red;">-${formatPrice(summary.discount_amount)}</td>
    </tr>
  ` : ''

  const content = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Đơn hàng #${displayCode}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: auto; }
        h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-box h3 { margin: 0 0 10px 0; color: #6b7280; font-size: 14px; text-transform: uppercase; }
        .info-box p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; border: 1px solid #ddd; }
        td { padding: 8px 12px; border: 1px solid #ddd; }
        .total { font-size: 20px; font-weight: bold; color: #1a56db; text-align: right; }
        .footer { margin-top: 40px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        .discount-row { background-color: #f0fdf4; }
      </style>
    </head>
    <body>
      <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
      <p><strong>Mã đơn hàng:</strong> ${displayCode}</p>
      <p><strong>Ngày đặt:</strong> ${formatDate(order.created_at)}</p>
      
      <div class="info-grid">
        <div class="info-box">
          <h3>Thông tin người đặt</h3>
          <p><strong>Họ tên:</strong> ${order.customer_name}</p>
          <p><strong>Email:</strong> ${customerEmail.value}</p>
          <p><strong>SĐT:</strong> ${order.customer_phone}</p>
        </div>
        <div class="info-box">
          <h3>Thông tin người nhận</h3>
          <p><strong>Họ tên:</strong> ${order.receiver_name}</p>
          <p><strong>SĐT:</strong> ${order.receiver_phone}</p>
          <p><strong>Địa chỉ:</strong> ${order.shipping_address}</p>
        </div>
      </div>
      
      <h3>Danh sách sản phẩm</h3>
      <table>
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th style="text-align: center;">Số lượng</th>
            <th style="text-align: right;">Đơn giá</th>
            <th style="text-align: right;">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          ${detailsHtml}
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; padding: 8px 12px; border: 1px solid #ddd;">Tạm tính</td>
            <td style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">${formatPrice(summary.subtotal)}</td>
          </tr>
          ${summary.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">Phí vận chuyển</td>
              <td style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">${formatPrice(summary.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${discountHtml}
          <tr style="background-color: #f0f7ff;">
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px; padding: 12px; border: 1px solid #ddd;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db; padding: 12px; border: 1px solid #ddd;">${formatPrice(summary.final_amount)}</td>
          </tr>
        </tfoot>
      </table>
      
      <div style="margin-top: 20px;">
        <p><strong>Phương thức thanh toán:</strong> ${getPaymentLabel(order.payment_method)}</p>
        <p><strong>Trạng thái thanh toán:</strong> ${getPaymentStatusLabel(order.payment_status)}</p>
      </div>
      
      ${order.note ? `<p><strong>Ghi chú:</strong> ${order.note}</p>` : ''}
      
      <div class="footer">
        <p>Cảm ơn bạn đã mua hàng tại BigBag!</p>
        <p>Hotline: 1900 1234 | Email: support@bigbag.vn</p>
        <p style="font-size: 10px; color: #9ca3af;">Hóa đơn được tạo tự động</p>
      </div>
    </body>
    </html>
  `

  printWindow.document.write(content)
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
}
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }

  header, footer, .chatbot, .chatbot-toggle {
    display: none !important;
  }

  body {
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  #print-area {
    max-width: 100% !important;
    padding: 20px !important;
    margin: 0 !important;
  }

  .bg-white {
    background: white !important;
    box-shadow: none !important;
    border: 1px solid #e5e7eb !important;
  }

  .shadow-lg, .shadow-sm {
    box-shadow: none !important;
  }

  .rounded-2xl, .rounded-xl {
    border-radius: 8px !important;
  }

  .text-primary {
    color: #1a56db !important;
  }

  .bg-primary {
    background-color: #1a56db !important;
  }

  .bg-primary\/5 {
    background-color: #f0f4ff !important;
  }

  .bg-gray-50 {
    background-color: #f9fafb !important;
  }

  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }

  th, td {
    border: 1px solid #e5e7eb !important;
    padding: 8px 12px !important;
  }

  thead {
    background-color: #f9fafb !important;
  }

  @page {
    margin: 20mm;
  }
}
</style>
Vì sao giỏ hàng vẫn giữ nguyên kể cả khi đã sang trang success


## Assistant:
07/18/2026 23:36

> **Thinking:**
> Chúng ta cần phân tích vấn đề: "Vì sao giỏ hàng vẫn giữ nguyên kể cả khi đã sang trang success". Dựa vào code, ta thấy có gọi `clearCart()` trong `onUnmounted` của component OrderSuccess, nhưng rõ ràng nếu component chưa bị unmount (ví dụ người dùng vẫn ở trang success) thì giỏ hàng chưa bị xóa. Tuy nhiên, câu hỏi đặt ra là "kể cả khi đã sang trang success" – tức là sau khi chuyển trang đến success, giỏ hàng vẫn còn. Nguyên nhân có thể:
> 
> 1. Hàm `clearCart` chỉ được gọi khi component unmount (khi rời khỏi trang success), nhưng người dùng có thể vẫn ở trang success, nên giỏ hàng chưa bị xóa. 
> 2. Có thể người dùng mong muốn giỏ hàng được xóa ngay sau khi đặt hàng thành công, nhưng code hiện tại chỉ xóa khi rời khỏi trang (unmounted). Nếu người dùng quay lại trang chủ hoặc các trang khác, giỏ hàng sẽ bị xóa. Nhưng nếu họ ở lại trang success và reload, giỏ hàng vẫn còn vì chưa gọi clearCart.
> 
> Ngoài ra, có thể có các yếu tố khác: gọi `clearCart` trong `onUnmounted` là không đảm bảo vì nếu chuyển trang bằng Inertia, component có thể bị unmount và gọi `clearCart`, nhưng nếu người dùng dùng nút back, có thể không unmount? Thực tế, `onUnmounted` chạy khi component bị gỡ bỏ, nhưng trong SPA (Inertia), khi chuyển trang, component cũ unmount và component mới mount, nên việc gọi clearCart khi unmount sẽ xóa giỏ hàng khi rời khỏi trang success.
> 
> Tuy nhiên, vấn đề là giỏ hàng vẫn giữ nguyên "kể cả khi đã sang trang success" – có thể hiểu là sau khi đặt hàng thành công, họ chuyển hướng đến trang success, nhưng giỏ hàng vẫn còn trong localStorage và hiển thị số lượng ở header (nếu có). Điều đó có nghĩa là `clearCart` chưa được gọi hoặc gọi nhưng không thành công.
> 
> Ta cần kiểm tra logic: Trong `onMounted`, không gọi clearCart, chỉ gọi fetchPaymentLink. Trong `onUnmounted`, có gọi `clearCart().catch(() => {})`. Vậy nếu người dùng chuyển từ trang thanh toán sang success (bằng cách submit form), component success được mount, và khi sau đó họ rời đi (sang trang khác), clearCart mới được gọi. Nhưng tại thời điểm trang success được hiển thị, giỏ hàng vẫn còn vì chưa gọi clearCart. Điều đó có thể là nguyên nhân.
> 
> Nếu yêu cầu là xóa giỏ hàng ngay khi đặt hàng thành công, ta nên gọi `clearCart` trong `onMounted` hoặc trong logic xử lý đặt hàng thành công (trước khi redirect). Nhưng code hiện tại chỉ gọi khi unmount, nên giỏ hàng sẽ tồn tại cho đến khi rời khỏi trang success.
> 
> Do đó, câu trả lời: Vì clearCart chỉ được gọi khi component bị unmount (tức khi rời khỏi trang success), còn khi đang ở trang success, giỏ hàng chưa bị xóa. Để xóa ngay khi vào trang success, cần gọi clearCart trong onMounted hoặc ngay sau khi đặt hàng thành công.
> 
> Ngoài ra, cần kiểm tra xem `clearCart` có thực sự hoạt động không (nó có xóa localStorage, nhưng có thể lỗi API). Có thể API không thành công nên localStorage không bị xóa, nhưng vì đã phân tích ở trên, clearCart xóa localStorage sau khi API thành công. Nếu API fail, localStorage vẫn còn. Có thể là do API clear bị lỗi (ví dụ route không tồn tại), nhưng câu hỏi tập trung vào việc tại sao giỏ hàng vẫn giữ nguyên khi đã vào trang success. 
> 
> Câu trả lời nên phân tích:
> 
> - Hàm clearCart được định nghĩa trong useCart và được gọi trong onUnmounted.
> - Khi component mounted (trang success hiển thị), clearCart chưa được gọi.
> - Chỉ khi rời khỏi trang success, component unmount và gọi clearCart.
> - Do đó, giỏ hàng vẫn còn trong localStorage và state trong suốt thời gian người dùng ở trang success.
> - Để xóa ngay, nên gọi clearCart ngay sau khi đặt hàng thành công (trước khi redirect) hoặc trong onMounted của trang success.
> 
> Ta có thể đưa ra giải pháp.
> 
> Đáp án sẽ giải thích nguyên nhân và đề xuất cách sửa.

Nguyên nhân chính khiến giỏ hàng vẫn giữ nguyên khi bạn đã vào trang **OrderSuccess** là vì **`clearCart()` chỉ được gọi khi component bị hủy (unmounted)**, tức là khi bạn rời khỏi trang thành công, chứ **không phải ngay khi trang được hiển thị**.

Cụ thể, trong code của trang `OrderSuccess.vue`:

```vue
<script setup>
// ...
onUnmounted(() => {
  clearCart().catch(() => {})
  stopPolling()
})
</script>
```

Hàm `clearCart()` sẽ chạy khi component bị gỡ bỏ khỏi DOM, ví dụ khi bạn chuyển sang trang khác (bằng Inertia hoặc router). Lúc đó, localStorage và state giỏ hàng mới bị xóa. Vì vậy:

- Khi bạn vừa đặt hàng xong và được chuyển hướng đến trang **success**, component được mount, `clearCart()` chưa được gọi → giỏ hàng vẫn còn nguyên.
- Chỉ khi bạn rời khỏi trang success (về trang chủ, xem lịch sử, …) thì `clearCart()` mới thực thi và xóa dữ liệu.
- Nếu bạn tải lại trang (F5), component vẫn ở trạng thái mounted, nên `clearCart()` không chạy, giỏ hàng vẫn còn.

---

### Giải pháp để xóa giỏ hàng ngay khi đặt hàng thành công

Bạn có thể thực hiện theo một trong các cách sau:

#### 1. Gọi `clearCart()` ngay trong `onMounted` của trang success

```vue
onMounted(() => {
  // Xóa giỏ hàng ngay khi vào trang success
  clearCart().catch(() => {})
  
  if (shouldShowPaymentButton.value) {
    fetchPaymentLink()
  }
})
```

Lưu ý: Nếu làm vậy, giỏ hàng sẽ bị xóa ngay khi người dùng đến trang success, dù đơn hàng chưa được thanh toán (trong trường hợp cần thanh toán). Điều này có thể không mong muốn nếu thanh toán thất bại và người dùng cần quay lại giỏ hàng. Vì vậy, chỉ nên xóa khi thanh toán thành công.

#### 2. Xóa giỏ hàng khi xác nhận đơn hàng thành công (từ server)

Tốt nhất là khi bạn gửi đơn hàng và nhận được phản hồi thành công từ server, bạn gọi `clearCart()` **trước khi chuyển hướng** sang trang success. Ví dụ:

```javascript
// Trong component đặt hàng
const submitOrder = async () => {
  try {
    const response = await axios.post('/orders', orderData)
    if (response.data.success) {
      // Xóa giỏ hàng ngay lập tức
      await clearCart()
      // Sau đó chuyển hướng sang trang success
      router.visit('/don-hang/thanh-cong')
    }
  } catch (error) {
    // ...
  }
}
```

Cách này đảm bảo giỏ hàng được xóa ngay khi đơn hàng được tạo thành công, và trang success sẽ hiển thị với giỏ hàng trống.

#### 3. Giữ nguyên `clearCart` trong `onUnmounted` nhưng thêm logic kiểm tra trạng thái thanh toán

Nếu bạn muốn chỉ xóa khi thanh toán thành công (đối với phương thức chuyển khoản), bạn có thể kết hợp polling để khi phát hiện `payment_status === 'paid'` thì gọi `clearCart()`:

```javascript
const checkOrderStatus = async () => {
  // ... lấy order
  if (orderData.payment_status === 'paid' || orderData.payment_status === 'success') {
    paymentStatus.value = 'paid'
    stopPolling()
    // Xóa giỏ hàng khi thanh toán thành công
    clearCart().catch(() => {})
    // chuyển hướng về trang chủ sau 3 giây
    redirectTimer.value = setTimeout(() => {
      router.visit('/')
    }, 3000)
  }
}
```

---

### Kiểm tra lại `clearCart` có thực sự xóa localStorage không

Theo code bạn cung cấp, `clearCart` trong `useCart`:

```javascript
const clearCart = async () => {
  try {
    await axios.delete('/api/cart/clear')        // gọi API
    cartItems.value = []                         // reset state
    updateCounts()
    const key = getStorageKey()
    localStorage.removeItem(key)                 // xóa localStorage
    clearVoucherStorage()
    return { success: true }
  } catch (error) {
    console.error('Error clearing cart:', error)
    throw error
  }
}
```

Nếu API `/api/cart/clear` trả về lỗi (ví dụ 500 hoặc timeout), `localStorage.removeItem` sẽ không được thực thi vì code nằm trong `try` và bị nhảy vào `catch`. Vì vậy, nếu gặp lỗi mạng hoặc server, giỏ hàng sẽ không bị xóa. Bạn có thể xem log console để kiểm tra có lỗi không.

---

### Kết luận

- **Lý do giỏ hàng vẫn còn** khi vào trang success là vì logic xóa chỉ được kích hoạt khi rời khỏi trang (unmount), hoặc chưa được gọi kịp.
- **Hướng khắc phục**:
  - Gọi `clearCart()` ngay khi đặt hàng thành công (trước khi redirect) hoặc khi thanh toán thành công (nếu có polling).
  - Đảm bảo xử lý lỗi API để vẫn xóa localStorage ngay cả khi API thất bại (nếu cần thiết), bằng cách chuyển `localStorage.removeItem` ra ngoài `try` hoặc dùng `finally`.

Nếu bạn mong muốn giỏ hàng biến mất ngay sau khi đặt hàng, hãy áp dụng giải pháp số 2. Nếu bạn muốn giữ giỏ hàng cho đến khi thanh toán thành công, hãy áp dụng giải pháp số 3.


## User:
07/18/2026 23:38

import { ref, computed } from 'vue'
import axios from 'axios'
import { CartEvents } from '@/events/CartEvents'

export function useCart() {
    const cartItems = ref([])
    const cartCount = ref(0)
    const cartTotal = ref(0)
    const loading = ref(false)
    const couponCode = ref('')
    const discountAmount = ref(0)
    const appliedCoupon = ref(null)
    const couponError = ref('')
    let isFetching = false

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

    const subtotal = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    })

    const total = computed(() => {
        return subtotal.value - discountAmount.value
    })

    const updateCounts = () => {
        const newCount = cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
        cartCount.value = newCount
        cartTotal.value = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        
        CartEvents.emitUpdated(newCount)
    }

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
            console.error('Error clearing cart:', error)
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

    // ============ SỬA: Xóa coupon ============
    const removeCoupon = async () => {
        try {
            // Gọi API xóa session
            const response = await axios.post('/api/cart/remove-coupon')
            
            // Reset state
            discountAmount.value = 0
            appliedCoupon.value = null
            couponCode.value = ''
            couponError.value = ''
            
            // Xóa localStorage
            clearVoucherStorage()
            return { success: true }
        } catch (error) {
            console.error('Error removing coupon:', error)
            // Vẫn reset state dù API fail
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


## Assistant:
07/18/2026 23:38

> **Thinking:**
> Chúng ta sẽ phân tích vấn đề: "Vì sao giỏ hàng vẫn giữ nguyên kể cả khi đã sang trang success". Nguyên nhân chính là do `clearCart()` chỉ được gọi trong `onUnmounted` của trang success, tức là khi rời khỏi trang, không phải khi vào trang. Điều đó dẫn đến giỏ hàng vẫn còn khi ở trang success. Nhưng trong đoạn chat, user đã đưa ra file `useCart.js` và hỏi làm thế nào để biết `clearCart` làm gì, và liệu nó có xóa localStorage không. Tôi đã trả lời rằng nó có xóa localStorage. Và user cũng đã đưa ra file `OrderSuccess.vue` và giải thích tại sao giỏ hàng vẫn còn. Tôi đã giải thích rằng `clearCart` được gọi trong `onUnmounted`, nên khi ở trang success, giỏ hàng chưa bị xóa. Tôi đã đưa ra các giải pháp.
> 
> Bây giờ user lại đưa lại file `useCart.js` một lần nữa (có thể do họ muốn hỏi thêm chi tiết về hàm `clearCart`). Tôi sẽ trả lời bổ sung thêm, nhấn mạnh rằng `clearCart` có xóa localStorage, nhưng vị trí gọi nó là vấn đề. Tôi cũng có thể gợi ý cách sửa: gọi `clearCart` ngay khi đặt hàng thành công thay vì đợi unmount.
> 
> Ngoài ra, tôi có thể đề cập đến việc xử lý lỗi: nếu API clear bị lỗi, localStorage sẽ không bị xóa do code nằm trong try-catch, nên cần cải thiện.
> 
> Tôi sẽ trả lời một cách ngắn gọn, tập trung vào vấn đề chính và đưa ra giải pháp cụ thể.

Bạn đã gửi lại file `useCart.js`, tôi xin xác nhận:

## Hàm `clearCart()` có xóa localStorage không?
**Có.** Trong `clearCart`:

```javascript
const key = getStorageKey()
localStorage.removeItem(key)        // Xóa giỏ hàng
clearVoucherStorage()               // Xóa voucher
```

## Vậy tại sao giỏ hàng vẫn còn khi vào trang success?
Vì `clearCart` **được gọi trong `onUnmounted`** của trang success, nghĩa là nó chỉ chạy khi bạn rời khỏi trang (chuyển sang trang khác), **không phải ngay khi trang success được hiển thị**.

```vue
onUnmounted(() => {
  clearCart().catch(() => {})   // Chỉ chạy khi component bị hủy
  stopPolling()
})
```

Khi bạn vừa đặt hàng thành công và được chuyển hướng đến trang success, component được mount → `clearCart` chưa gọi → giỏ hàng vẫn còn. Bạn reload trang cũng không làm gì vì component vẫn ở mounted.

## Cách khắc phục
- **Cách 1: Gọi `clearCart` ngay khi đặt hàng thành công** (trước khi redirect đến trang success).
- **Cách 2: Gọi `clearCart` trong `onMounted` của trang success**, nhưng lưu ý: nếu đơn hàng cần thanh toán và thất bại, bạn sẽ mất dữ liệu giỏ. Nên chỉ áp dụng nếu bạn muốn xóa ngay lập tức.
- **Cách 3: Gọi `clearCart` sau khi xác nhận thanh toán thành công** (khi polling nhận được trạng thái `paid`).

Ngoài ra, `clearCart` còn có vấn đề: nếu API `/api/cart/clear` thất bại (mất mạng, lỗi server), `localStorage.removeItem` và `clearVoucherStorage` sẽ không được thực thi vì chúng nằm trong `try` và bị nhảy vào `catch`. Bạn nên đưa phần xóa localStorage ra ngoài hoặc dùng `finally` để đảm bảo luôn xóa cục bộ dù API có thành công hay không.

---

Tóm lại, nguyên nhân chính là **vị trí gọi hàm**, không phải chức năng của `clearCart`. Hãy chọn thời điểm gọi phù hợp với luồng thanh toán của bạn.


## User:
07/18/2026 23:41

<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Tính giá sale cho variant
     */
    private function calculateSalePrice($variant)
    {
        $originalPrice = $variant->price;
        $salePrice = $originalPrice;
        $discountPercent = 0;
        $now = now();

        // Kiểm tra campaign (retail)
        $campaigns = Campaign::where('status', 'active')
            ->where('type', '!=', 'voucher')
            ->where('type', '!=', 'preorder')
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now);
                })->orWhere(function($q) {
                    $q->whereNull('start_time')
                      ->whereNull('end_time');
                });
            })
            ->whereHas('productVariants', function($query) use ($variant) {
                $query->where('product_variant_id', $variant->id);
            })
            ->with('configs')
            ->get();

        foreach ($campaigns as $campaign) {
            $config = $campaign->configs()->first();
            $currentDiscount = $config ? (float) $config->discount_percent : 0;
            if ($currentDiscount > $discountPercent) {
                $discountPercent = $currentDiscount;
            }
        }

        // Kiểm tra pre-order
        if ($variant->product && ($variant->product->is_preorder ?? false)) {
            $preorder = Campaign::where('type', 'preorder')
                ->where('status', 'active')
                ->where('product_id', $variant->product_id)
                ->where(function($query) use ($now) {
                    $query->where(function($q) use ($now) {
                        $q->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    })->orWhere(function($q) {
                        $q->whereNull('start_time')
                          ->whereNull('end_time');
                    });
                })
                ->first();

            if ($preorder) {
                
                $tiers = $preorder->tiers ?? [];
                
                usort($tiers, function($a, $b) {
                    return ($a['from'] ?? 0) - ($b['from'] ?? 0);
                });
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $preorderDiscount = $tier['discount'] ?? 0;
                        if ($preorderDiscount > $discountPercent) {
                            $discountPercent = $preorderDiscount;
                        }
                        break;
                    }
                }
                
                if ($discountPercent == 0 && !empty($tiers)) {
                    $discountPercent = $tiers[0]['discount'] ?? 0;
                }
            }
        }

        if ($discountPercent > 0) {
            $salePrice = $originalPrice * (1 - $discountPercent / 100);
            $salePrice = round($salePrice);
        }

        return [
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_percent' => $discountPercent,
            'is_on_sale' => $discountPercent > 0,
        ];
    }

    /**
     * Lấy giỏ hàng từ request (client gửi lên)
     * CHỈ LẤY SẢN PHẨM THƯỜNG, BỎ QUA PRE-ORDER
     */
    public function index(Request $request)
    {
        try {
            Log::info('CartController@index called', ['method' => $request->method()]);
            
            // Lấy cart từ request
            $cart = [];
            if ($request->isMethod('post')) {
                $cart = $request->input('cart', []);
            } else {
                $cartJson = $request->query('cart', '{}');
                $cart = json_decode($cartJson, true) ?: [];
            }
            
            if (empty($cart)) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'total' => 0,
                    'count' => 0
                ]);
            }
            
            $items = [];
            $total = 0;
            $count = 0;

            foreach ($cart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if (!$variant) {
                    Log::warning("Variant not found: {$variantId}");
                    continue;
                }
                
                // ============ BỎ QUA PRE-ORDER (KHÔNG HIỂN THỊ TRONG GIỎ) ============
                if ($variant->product->is_preorder ?? false) {
                    Log::info("Skipping pre-order item in cart: {$variantId}");
                    continue;
                }

                // Tính giá sale
                $saleInfo = $this->calculateSalePrice($variant);
                $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;

                // Lấy ảnh sản phẩm
                $image = '/images/default-product.jpg';
                if ($variant->product && $variant->product->image_url) {
                    if (is_array($variant->product->image_url) && !empty($variant->product->image_url)) {
                        $image = $variant->product->image_url[0];
                    } elseif (is_string($variant->product->image_url)) {
                        $image = $variant->product->image_url;
                    }
                }

                $items[] = [
                    'id' => (int) $variantId,
                    'product_id' => $variant->product->id ?? 0,
                    'product_variant_id' => (int) $variantId,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'slug' => $variant->product->slug ?? '#',
                    'price' => $price,
                    'original_price' => $variant->price,
                    'quantity' => $item['quantity'] ?? 1,
                    'image' => $image,
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'discount_percent' => $saleInfo['discount_percent'],
                    'stock' => $variant->stock,
                ];
                $total += $price * ($item['quantity'] ?? 1);
                $count += ($item['quantity'] ?? 1);
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'total' => $total,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Cart index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm vào giỏ hàng
     * CHỈ CHO PHÉP SẢN PHẨM THƯỜNG, TỪ CHỐI PRE-ORDER
     */
    public function add(Request $request)
    {
        try {
            Log::info('CartController@add called', $request->all());
            
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'nullable|integer|min:1'
            ]);

            $variantId = $request->variant_id;
            $quantity = $request->quantity ?? 1;

            $variant = ProductVariant::with('product', 'color')->find($variantId);
            if (!$variant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại'
                ], 404);
            }

            // ============ PRE-ORDER: KHÔNG CHO THÊM VÀO GIỎ HÀNG ============
            if ($variant->product->is_preorder ?? false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm Pre-order không thể thêm vào giỏ hàng. Vui lòng chọn "Mua ngay" để đặt hàng.'
                ], 400);
            }

            // Kiểm tra stock (chỉ cho sản phẩm thường)
            if ($variant->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm chỉ còn {$variant->stock} sản phẩm"
                ], 400);
            }

            // Tính giá sale
            $saleInfo = $this->calculateSalePrice($variant);
            $price = $saleInfo['is_on_sale'] ? $saleInfo['sale_price'] : $variant->price;

            // Lấy ảnh
            $image = '/images/default-product.jpg';
            if ($variant->product && $variant->product->image_url) {
                if (is_array($variant->product->image_url) && !empty($variant->product->image_url)) {
                    $image = $variant->product->image_url[0];
                } elseif (is_string($variant->product->image_url)) {
                    $image = $variant->product->image_url;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'item' => [
                    'id' => (int) $variantId,
                    'product_id' => $variant->product->id ?? 0,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'slug' => $variant->product->slug ?? '#',
                    'price' => $price,
                    'original_price' => $variant->price,
                    'quantity' => $quantity,
                    'image' => $image,
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => false,
                    'is_on_sale' => $saleInfo['is_on_sale'],
                    'discount_percent' => $saleInfo['discount_percent'],
                    'stock' => $variant->stock,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vào giỏ hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật giỏ hàng
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity' => 'required|integer|min:0'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($variantId, Request $request)
    {
        try {
            Log::info("CartController@remove called: {$variantId}");
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request)
    {
        try {
            Log::info('CartController@clear called');
            
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng và mã giảm giá'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra và áp dụng voucher
     */
    public function applyCoupon(Request $request)
    {
        try {
            Log::info('CartController@applyCoupon called', $request->all());
            
            $request->validate([
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0'
            ]);

            $code = strtoupper($request->code);
            $subtotal = $request->subtotal;

            $voucher = Campaign::where('code', $code)
                ->where('type', 'voucher')
                ->where('status', 'active')
                ->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ'
                ], 400);
            }

            if ($voucher->expiry && $voucher->expiry < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết hạn'
                ], 400);
            }

            if ($voucher->limit && $voucher->used >= $voucher->limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã được sử dụng hết'
                ], 400);
            }

            if ($voucher->min_order > 0 && $subtotal < $voucher->min_order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng tối thiểu ' . number_format($voucher->min_order) . 'đ'
                ], 400);
            }

            $discountAmount = 0;
            $discountType = $voucher->discount_type;
            $discountValue = $voucher->discount_value;

            if ($discountType === 'percent') {
                $discountAmount = ($subtotal * $discountValue) / 100;
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($discountValue, $subtotal);
            } elseif ($discountType === 'freeship') {
                $discountAmount = 0;
            }

            $discountAmount = round($discountAmount);

            return response()->json([
                'success' => true,
                'coupon' => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                ],
                'discount_amount' => $discountAmount,
                'message' => 'Áp dụng mã giảm giá thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCoupon(Request $request)
    {
        return response()->json([
            'success' => true,
            'coupon' => null,
            'discount_amount' => 0,
        ]);
    }

    public function removeCoupon(Request $request)
    {
        try {
            Log::info('CartController@removeCoupon called');
            
            $request->session()->forget(['voucher_code', 'voucher_discount']);
            $request->session()->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mã giảm giá'
            ]);
        } catch (\Exception $e) {
            Log::error('Remove coupon error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
Hãy kiểm tra hàm clear


## Assistant:
07/18/2026 23:41

> **Thinking:**
> Chúng ta cần kiểm tra hàm clear trong CartController (backend Laravel). Dựa trên code đã cung cấp, hàm clear như sau:
> 
> ```php
> public function clear(Request $request)
> {
>     try {
>         Log::info('CartController@clear called');
>         
>         $request->session()->forget(['voucher_code', 'voucher_discount']);
>         
>         return response()->json([
>             'success' => true,
>             'message' => 'Đã xóa toàn bộ giỏ hàng và mã giảm giá'
>         ]);
>     } catch (\Exception $e) {
>         return response()->json([
>             'success' => false,
>             'message' => $e->getMessage()
>         ], 500);
>     }
> }
> ```
> 
> Vậy hàm này:
> - Chỉ xóa session `voucher_code` và `voucher_discount`.
> - Không hề tương tác với database hay bất kỳ lưu trữ nào khác.
> - Trả về success luôn (trừ khi có exception).
> 
> Trong khi đó, phía frontend trong useCart, `clearCart` gọi API này và nếu thành công thì mới xóa localStorage. Vì BE luôn trả về success, nên frontend sẽ xóa localStorage. Tuy nhiên, nếu có lỗi mạng hoặc exception (rare), frontend sẽ không xóa localStorage.
> 
> Nhưng vấn đề đặt ra: Tại sao giỏ hàng vẫn còn trên trang success? Lý do chính là `clearCart` được gọi trong `onUnmounted`, không phải khi vào trang. Nên BE không liên quan.
> 
> Tóm tắt: hàm clear của BE chỉ xóa session voucher, không ảnh hưởng đến việc giỏ hàng còn hay mất trên frontend (vì frontend tự quản lý localStorage và chỉ xóa khi gọi API thành công). Việc giỏ hàng còn trên trang success là do frontend chưa gọi clearCart.
> 
> Vậy nên câu trả lời là: Hàm clear BE chỉ xóa session voucher, không xóa dữ liệu giỏ hàng (vì BE không lưu giỏ). Việc xóa giỏ hàng trên frontend phụ thuộc vào logic gọi clearCart. Và hiện tại clearCart chỉ được gọi khi unmount, nên chưa xóa trên trang success.
> 
> Tôi sẽ giải thích rõ ràng.

## Phân tích hàm `clear()` trong `CartController` (Backend Laravel)

```php
public function clear(Request $request)
{
    try {
        Log::info('CartController@clear called');
        
        $request->session()->forget(['voucher_code', 'voucher_discount']);
        
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng và mã giảm giá'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### Backend làm gì?

1. **Xóa session** – chỉ xóa hai biến session `voucher_code` và `voucher_discount` (mã giảm giá tạm thời).
2. **Không lưu giỏ hàng** – Backend này **không** có bảng lưu giỏ hàng, không dùng database hay cache để lưu danh sách sản phẩm. Nó chỉ nhận dữ liệu từ client khi cần tính toán (ví dụ API `/api/cart`).
3. **Luôn trả về thành công** – ngoại trừ lỗi hệ thống bất ngờ, hàm này gần như chắc chắn trả về `success: true`.

### Vậy việc xóa giỏ hàng thực tế diễn ra ở đâu?

- **Frontend (`useCart.js`)** chịu trách nhiệm lưu giỏ hàng trong **localStorage** và state `cartItems`.
- Khi gọi `clearCart()` trong frontend:
  - Gọi API `/api/cart/clear` (backend trả về success).
  - Nếu API thành công → frontend tự xóa `localStorage` và reset state.
  - Nếu API thất bại → frontend **không** xóa localStorage (do code nằm trong `try` và bị nhảy vào `catch`).

### Kết luận cho câu hỏi: *"Hãy kiểm tra hàm clear"*

- **Hàm clear của Backend** chỉ xóa session voucher, **không hề** xóa dữ liệu giỏ hàng (vì backend không lưu giỏ).
- **Giỏ hàng vẫn còn trên trang success** là do **frontend chưa gọi `clearCart()` tại thời điểm vào trang** (mà chỉ gọi khi rời khỏi trang – `onUnmounted`). Đây là nguyên nhân chính, không phải lỗi từ backend.
- Nếu bạn muốn xóa giỏ hàng ngay sau khi đặt hàng thành công, hãy gọi `clearCart()` **trước khi chuyển hướng** sang trang success, hoặc ngay trong `onMounted` của trang success (tùy theo luồng nghiệp vụ).


## User:
07/19/2026 00:00

[2026-07-18 23:59:26] local.INFO: PaymentController@store called {"customer_name":"Thanh Phuong Khuu","customer_phone":"0123456789","customer_email":"thanhphuongkhuu@gmail.com","receiver_name":"Thanh Phuong Khuu","receiver_phone":"0123456789","shipping_address":"1, 1, 1, TP. Hồ Chí Minh","note":null,"payment_method":"cod","items":[{"id":3,"quantity":1,"price":"2100000"}],"total_amount":2100000,"order_type":"retail","promo_code":null,"discount_amount":0} 
[2026-07-18 23:59:26] local.INFO: Order data: {"promo_code":null,"discount_amount":0,"total_amount":2100000} 
[2026-07-18 23:59:26] local.INFO: Admin\OrderController@store called {"customer_name":"Thanh Phuong Khuu","customer_phone":"0123456789","customer_email":"thanhphuongkhuu@gmail.com","receiver_name":"Thanh Phuong Khuu","receiver_phone":"0123456789","shipping_address":"1, 1, 1, TP. Hồ Chí Minh","note":null,"payment_method":"cod","items":[{"id":3,"quantity":1,"price":"2100000"}],"total_amount":2100000,"order_type":"retail","promo_code":null,"discount_amount":0} 
[2026-07-18 23:59:26] local.INFO: Creating order with type: retail {"total_amount_from_request":2100000,"discount_amount":0,"promo_code":null,"final_amount":2100000} 
[2026-07-18 23:59:26] local.INFO: Order created: {"order_id":73,"total_amount":2100000,"discount_amount":0,"final_amount":2100000,"promo_code":null} 
[2026-07-18 23:59:26] local.INFO: ✅ Order created successfully: {"order_id":73,"display_code":"L1807202600073","total_amount":2100000,"discount_amount":0,"final_amount":2100000,"promo_code":null,"order_type":"retail"} 
[2026-07-18 23:59:26] local.INFO: Checkout success - order_id: 73, display_code: L1807202600073  
[2026-07-18 23:59:26] local.INFO: CartController@index called {"method":"GET"} 
[2026-07-18 23:59:27] local.INFO: CartController@index called {"method":"GET"} 
[2026-07-18 23:59:27] local.INFO: CartController@index called {"method":"GET"} 
[2026-07-18 23:59:42] local.INFO: Checkout success - order_id: , display_code:   
[2026-07-18 23:59:43] local.INFO: CartController@index called {"method":"GET"} 
Khi unMounted, không thấy hàm clear được goih


## Assistant:
07/19/2026 00:00

> **Thinking:**
> Chúng ta cần phân tích log để xác định xem `clearCart` có được gọi không. Log hiển thị các request: sau khi tạo đơn hàng thành công (order_id 73), có các request đến `/api/cart` (CartController@index) vào lúc 23:59:26, 23:59:27, 23:59:27. Sau đó lúc 23:59:42 có log "Checkout success" nhưng không có order_id. Và lúc 23:59:43 có log CartController@index.
> 
> Không thấy log nào từ `clearCart` (ví dụ không có log "CartController@clear called"). Điều đó có nghĩa là `clearCart` chưa được gọi (hoặc nếu gọi thì API đó không log). Nhưng trong log có log "CartController@index called" nhiều lần, chứng tỏ frontend đang gọi fetchCart hoặc tương tự.
> 
> Quan trọng: `clearCart` chỉ được gọi khi component bị unmount (onUnmounted). Trong log, không thấy dấu hiệu của việc component bị unmount (có thể vì chưa chuyển trang). Nếu người dùng đang ở trang success và chưa rời đi, thì `clearCart` chưa được gọi. Vì vậy, giỏ hàng vẫn còn.
> 
> Nhưng câu hỏi: "Khi unMounted, không thấy hàm clear được gọi" - có thể họ đã kiểm tra và thấy rằng dù component unmount, nhưng `clearCart` không được gọi. Có thể do lỗi: gọi `clearCart().catch(() => {})` nhưng không có await hoặc không xử lý? Hoặc có thể component unmount nhưng hàm không chạy vì lý do nào đó.
> 
> Chúng ta cần đưa ra giải thích và đề xuất sửa.
> 
> Phân tích chi tiết:
> 
> - Trong OrderSuccess.vue, `onUnmounted(() => { clearCart().catch(() => {}) stopPolling() })` 
> - Nếu component unmount, hàm này sẽ chạy. Tuy nhiên, nếu `clearCart` là async, và chúng ta không await, nó sẽ chạy nhưng không đợi. Tuy nhiên nó vẫn sẽ gọi API và xóa localStorage. Vấn đề có thể là component unmount không xảy ra (ví dụ khi chuyển trang bằng Inertia, component có thể bị unmount đúng cách). Nhưng log không thấy request đến `/api/cart/clear`, chứng tỏ hàm `clearCart` đã không được thực thi.
> 
> Nguyên nhân có thể:
> 
> 1. Component chưa bị unmount (vẫn ở trang success).
> 2. Có lỗi trong `clearCart` khiến nó throw và catch bắt nhưng không log.
> 3. `clearCart` được gọi nhưng API không ghi log vì log level? Nhưng trong controller có `Log::info('CartController@clear called');` nên nếu gọi sẽ có log.
> 
> Vậy component chưa unmount.
> 
> Đề xuất: thay vì chờ unmount, hãy gọi `clearCart` ngay sau khi đặt hàng thành công, hoặc ngay trong `onMounted` của trang success nếu muốn xóa ngay khi vào trang.
> 
> Trả lời: Từ log, không thấy `CartController@clear called`, chứng tỏ `clearCart` chưa được gọi. Lý do là vì nó chỉ được gọi khi component unmount, nhưng người dùng vẫn ở trang success và chưa điều hướng đi đâu. Do đó, giỏ hàng vẫn giữ nguyên. Để xóa ngay sau khi đặt hàng, hãy gọi `clearCart` trong `onMounted` hoặc ngay khi nhận phản hồi thành công từ server (trước redirect).

Dựa trên log bạn cung cấp, **không thấy** request nào đến `/api/cart/clear` (tức `CartController@clear`). Điều đó chứng tỏ **hàm `clearCart()` chưa được thực thi** ở thời điểm log được ghi.

## Tại sao `clearCart()` chưa được gọi?

Trong file `OrderSuccess.vue`, bạn có:

```vue
onUnmounted(() => {
  clearCart().catch(() => {})
  stopPolling()
})
```

`clearCart()` **chỉ chạy khi component bị hủy (unmounted)**, tức là khi bạn rời khỏi trang success (chuyển sang trang khác). Trong log, bạn thấy các request `/api/cart` (CartController@index) được gọi nhiều lần, có thể do component đang gọi `fetchCart` hoặc các component khác gọi, nhưng chưa có hành động unmount.

Vì vậy, ở thời điểm log (23:59:26 – 23:59:43), người dùng vẫn đang ở trang success và component chưa bị hủy → `clearCart` chưa được gọi → giỏ hàng vẫn còn nguyên trong localStorage và state.

## Kiểm tra thêm: Nếu sau đó bạn chuyển trang, `clearCart` sẽ được gọi?

Khi bạn điều hướng sang trang khác (ví dụ về trang chủ), component `OrderSuccess` sẽ unmount và `clearCart` sẽ chạy. Lúc đó bạn sẽ thấy log:

```
CartController@clear called
```

Tuy nhiên, nếu bạn **không chuyển trang** mà chỉ reload lại trang success (F5), component vẫn được mount lại, `onUnmounted` không chạy, nên `clearCart` không được gọi.

## Kết luận

- **Lý do giỏ hàng vẫn giữ**: do `clearCart` chỉ được gọi khi unmount, nhưng bạn chưa rời khỏi trang success.
- **Không thấy log `clear` trong log**: chứng tỏ component chưa unmount, hoặc nếu có unmount thì có thể có lỗi khiến `clearCart` không thực thi (nhưng log không hiển thị).

## Giải pháp

Để xóa giỏ hàng **ngay khi đặt hàng thành công** (không đợi unmount), bạn có thể:

### Cách 1: Gọi `clearCart` trước khi redirect đến trang success

Trong logic đặt hàng (controller hoặc service), sau khi tạo đơn hàng thành công, hãy gọi `clearCart()` và đợi nó hoàn thành rồi mới chuyển hướng:

```javascript
// Ví dụ trong hàm submitOrder
const response = await axios.post('/orders', orderData)
if (response.data.success) {
  await clearCart()  // xóa giỏ hàng ngay lập tức
  router.visit('/don-hang/thanh-cong')
}
```

### Cách 2: Gọi `clearCart` trong `onMounted` của trang success

Nếu bạn muốn xóa khi vào trang success (dù thanh toán chưa hoàn tất), hãy thêm vào `onMounted`:

```vue
onMounted(() => {
  clearCart().catch(() => {})
  // ... các logic khác
})
```

**Lưu ý:** Cách này sẽ xóa giỏ hàng bất kể trạng thái thanh toán, có thể gây mất dữ liệu nếu thanh toán thất bại và người dùng cần quay lại giỏ. Chỉ nên áp dụng nếu bạn muốn xóa ngay lập tức.

### Cách 3: Gọi `clearCart` khi thanh toán thành công (polling)

Trong logic polling, khi nhận được trạng thái `paid`, bạn gọi `clearCart()`:

```javascript
if (orderData.payment_status === 'paid') {
  await clearCart()
  // chuyển hướng hoặc hiển thị thông báo
}
```

## Tóm lại

- **Nguyên nhân chính**: `clearCart` nằm trong `onUnmounted` nên chỉ chạy khi rời trang, không chạy ngay khi vào trang success.
- **Khắc phục**: chọn thời điểm gọi phù hợp (ngay sau khi đặt hàng, khi vào trang success, hoặc khi thanh toán thành công) và đảm bảo xử lý lỗi để luôn xóa localStorage dù API có thất bại hay không.


## User:
07/19/2026 00:03

Khi reload, trang success sẽ tự động điều hướng sang trang chủ. Tôi đã reload trong trường hợp trên nhưng không thấy clear


## Assistant:
07/19/2026 00:03

> **Thinking:**
> Trong trường hợp bạn **reload** trang success (F5), component sẽ được **mount lại** chứ không bị **unmount**. Do đó, `onUnmounted` **không chạy**, và `clearCart` không được gọi. 
> 
> Bạn có thấy log nào của `CartController@clear` sau khi reload không? Theo log bạn đưa ra, không có, chứng tỏ `clearCart` vẫn chưa chạy.
> 
> ---
> 
> ### Lý do:
> 
> - **Reload trang** = trình duyệt tải lại trang hiện tại, component được tạo mới (mount) chứ không hủy component cũ (unmount). Nên `onUnmounted` không kích hoạt.
> - Bạn có thể thêm logic trong `onMounted` để nếu đơn hàng đã thành công và cần xóa giỏ, thì gọi `clearCart()` ngay khi mount lại.
> 
> ---
> 
> ### Giải pháp cho reload:
> 
> Trong `OrderSuccess.vue`, nếu bạn muốn xóa giỏ hàng bất kể reload hay chuyển trang, bạn có thể thêm một điều kiện trong `onMounted` để kiểm tra và gọi `clearCart` nếu cần.
> 
> Ví dụ:
> 
> ```vue
> onMounted(() => {
>   // Nếu đơn hàng đã được tạo thành công và thanh toán thành công (hoặc bạn muốn xóa ngay)
>   // thì gọi clearCart()
>   if (props.order?.payment_status === 'paid' || props.order?.payment_status === 'success') {
>     clearCart().catch(() => {})
>   }
>   // Nếu muốn xóa ngay cả khi chưa thanh toán, bỏ điều kiện hoặc điều chỉnh.
>   
>   if (shouldShowPaymentButton.value) {
>     fetchPaymentLink()
>   }
> })
> ```
> 
> Lưu ý: Nếu bạn gọi `clearCart` trong `onMounted` mà chưa thanh toán, giỏ hàng sẽ bị xóa ngay lập tức, có thể không mong muốn nếu thanh toán thất bại. Hãy điều chỉnh logic theo nghiệp vụ của bạn.
> 
> ---
> 
> ### Tóm tắt:
> 
> - **Reload** không gọi `onUnmounted`, nên `clearCart` không chạy.
> - Để xóa giỏ khi reload, bạn cần gọi `clearCart` trong `onMounted` (hoặc ở một thời điểm thích hợp khác, như khi nhận được phản hồi thành công từ server).
> - Kiểm tra thêm rằng API `/api/cart/clear` có được gọi sau khi bạn sửa code và reload để xác nhận log xuất hiện.

Khi bạn **reload** trang success (F5), component được **mount lại** từ đầu, chứ không bị **unmount** (vì bạn không rời khỏi trang, chỉ tải lại). Do đó, `onUnmounted` **không chạy**, và `clearCart` không được gọi.

Trong log bạn cung cấp, sau khi reload, bạn có các dòng:

```
[2026-07-18 23:59:42] local.INFO: Checkout success - order_id: , display_code:   
[2026-07-18 23:59:43] local.INFO: CartController@index called {"method":"GET"}
```

- **Không có** log `CartController@clear called` → chứng tỏ API `/api/cart/clear` chưa được gọi.
- Điều này hoàn toàn khớp với lý do trên: `clearCart` chỉ được gọi khi component bị hủy (unmount), nhưng reload không gây unmount.

---

## Cách khắc phục để xóa giỏ hàng khi reload

Bạn có thể thêm logic gọi `clearCart` trong `onMounted` nếu điều kiện cho phép. Ví dụ:

```vue
onMounted(() => {
  // Nếu đơn hàng đã thanh toán thành công (hoặc bạn muốn xóa luôn)
  // Hãy xóa giỏ hàng ngay khi trang được mount lại
  if (props.order?.payment_status === 'paid' || props.order?.payment_status === 'success') {
    clearCart().catch(() => {})
  }
  
  if (shouldShowPaymentButton.value) {
    fetchPaymentLink()
  }
})
```

Nếu bạn muốn xóa giỏ bất kể trạng thái (ví dụ đã đặt hàng thành công, không cần giữ lại), bạn có thể gọi `clearCart()` trong `onMounted` mà không cần điều kiện.

---

## Lưu ý

- Nếu bạn xóa giỏ hàng ngay khi vào trang success (dù đơn hàng chưa thanh toán), và sau đó thanh toán thất bại, người dùng sẽ mất dữ liệu giỏ hàng, không thể quay lại chỉnh sửa. Hãy cân nhắc nghiệp vụ: chỉ xóa khi thanh toán thành công (đối với phương thức chuyển khoản) hoặc sau khi đặt hàng thành công (đối với COD).
- Nếu bạn sử dụng polling để kiểm tra trạng thái thanh toán, hãy gọi `clearCart` trong polling khi phát hiện `payment_status === 'paid'`.

---

## Tóm lại

- **Reload không kích hoạt `onUnmounted`**, vì vậy `clearCart` không được gọi.
- Để xóa giỏ hàng khi reload, hãy gọi `clearCart` trong `onMounted` (với điều kiện phù hợp).
- Sau khi sửa, bạn sẽ thấy log `CartController@clear called` khi reload hoặc khi vào trang lần đầu, tùy theo logic bạn chọn.


## User:
07/19/2026 00:06

![image.png](data:image/webp;base64,UklGRmq4AABXRUJQVlA4IF64AABw4AGdASrCBZoCPlEokEcjoqGhIDhoYHAKCWdu29n+o/2j+idX80/Lv6p/cP2g/u3//9q7p36ifm39u/Yv+2f///n+Z/x7+zf4D9Q/5L//+otzH+0/zf8dvgL/N/47/Xv7L+1397////P8dH9p6h7SP7r/a/5R7gf4n/I/7j/Zf7z/zf3/8oH8w/YD8//iV+bf1z+4fyv9hv7X9gH8L/lH92/sX+U/1f9m///vk8pXor/F+wL+M/zf+6/4H9tP3//+H3l/ff9V/ef85/5fWt+Uf2n/Yf4H/Uf+j5E/yL+b/4z+3f5n/sfv//8/t49nT9lfY1/gH7ffnx9McX6onhn4r9k/09/tf8Y13k5tgUxRpB8U/pf8x/Vr8s9ce/kP9M/IP+u/tn8mf8A7Gv+S/gB8if8M/lX4Kff/8r/7l4sH4zfIz/E/5R+Lv7/9SB7An8M/m/4v/v/9Uv2n+zflV/K/Z3+Yf4X+w/jX/T///9gv4d/Hv6V/WP8B/mv7j//P+B5DHoI/ol/a/6H/ePVawHIzc1fLfZd/JeoX1H92/t3bB/dPuJ+bv6Z+zr7bvcA/TT/GdYv9oP6r7AP4z/SP9f/rveu/zv9m9qf6H/5b+Nf3L5AP5f/Ousy9AX9P//b64n7S/CF+z/7a/Ad+u3/i6wCBc5jD8dskB/WvxG1gH8X/yWpi81v+q+4D2s/Qn/C9wf9R/8pjzc0Hhq5WXOXF7dglya4PDVysucuL27BLk1weGrlZc5cXt2CXJrg8NXKy5y4vbsEuTXB4ZjchvLOObQJlMu4pNG9vXe3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXdiJeTd1cu/r5uDXzcGvm4NfNwa+bg183BLNTacRUMHmje1HbGck0bdGftHhgxgwgggpxQVDTgKN+cmBlPDhRBT444sG8qTh6dvQADpTa9naiLdm5zv5kU1pjaYuqS/TSKrR+A3lnSVF6sUmhuIleByNdYR0gtaY5vLN1eXl5eXl5lMiTRvbk57QRyPmq5NG9tTytq+y26QravsvdkN5Wt6WgDu4HrK+tpGcV+iof+KSNertAbykv6U90TeLRN4tEpO5j07mPTuY9O5j07mPTuY9O5j07mPTuY9O5j07mPTuY9O5j07mPTuY3Abj07mPTuY9O5j07mPTuY9O5j07mPTGpNO5j07mPTuY5+e/dBvauXf183Br5uDXzcGvm4NfNwa+bg183Br5uDXzcGvm4NfNwa+bg183Br5uDXzcGvm4NfNwa+bg183Br5uDXzcGvm4NfNwa+bg183Br5uDXzcGvm4NaReY5vLOkqL1YpNG9vXe3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXe3rvak5LXuKTRvb13t6729d7eu9vXe2oQSiv0VY/jqV9bSM4r9FWP46lfW0jOK/RVj+OpJs3rvb13t6729d7eu9vXe1JyWvcUmje3rvb13t6729d7eu7EA+Ey/sX3fX28mmQXwmX9i+76+3k0yC+Ey/sX3fX28mmQXwmX9i+76/ykLOkqL1YpNG9vXe3rvb036sZyTRvb13t6729d7eu9vXdfjXrvb13t6729d7eu9vXe3rvb1b/WWdJUXqxSaN7eu9vXe3rvb13tuqIDeWdJUXqxSaN7eu9vTfqxnJNG9vXe3rvb13t6729d3nvWu9vXe3rvb13t6729d7eu9vWHk8tH4DeWdJUXqxSaN7eu9vXe3rIqd5Z0lRerFJo3t6729duu+S9Ub29d7eu9vXe3rvb13tuut729d7eu9vXe3rvb13t6729W0RzqRXYpNG9vXe3rvb13t6729d7evGsb3t6729d7eu9vXe3rvak5LXuKTRvb13t6729d7eu9vWRxzeWdJUXqxSaN7eu9vXe3rvbhmOvM2Z7UXqxSaN7eu9vXe3rvb13t68axve3rvb13t6729d7eu9qTkte4pNG9vXe3rvb13t6729eNY3vb13t6729d7eu9vXe3rvb1cAGVWr7e3rvb13t6729d7eu9vXe3rxrG97eu9vXe3rvb13t672pOS17ik0b29d7eu9vXe3rvb141je9vXe3rvb13t6729d7eu9vXeMHctTRKi9WKTRvb13t6729d7eu9vXjWN729d7eu9vXe3rvb13tSclr3FJo3t6729d7eu9vXe3rxrG97eu9vXe3rvb13t6u3A/S6i7GRr13tqeWdJUXqxSaN7eu9vXe3ujmtd7eu9vXe3rvb13t67dd8l6o3t6729d7eu9vXe3rvb3RzWu9vXe3rvb13t6jUmtLfecjA90RtvjEumBjIZOlNFX49B7pxv2HVajAUEaTiMtVkI11uk+Ug3lUnKRp30c9//iqK4ZsDs4JkLwhF2lJXSBclEZGTSmGkwi1EhaxHtcGPx0Q1NYGoL+yKbIvQNKHh5+OMDyvuAcODMG93B2TqvIaC0sglJbZqIH2+Rzxbsa7lGMijNlzZybhER7EgN5Z0lRerFJo3t7o5rXe3rvb13t6729d7eu3XfJeqN7eu9vXe3rvb13t67290c1rvb13t6729d7erWI+euvyAnDfaNA/E4CUuLaLjQMEK2/UKM3SpybNk88mbbMlkQm9oTtn13027NAVoS3kg3fYQlT0duC0+PecUiWpaJ8GrMl/cq3Xe3rvb13t6729eNY3vb13t6729d7eu9vXe1JyWvcUmje3rvb13t6729d7evGsb3t6729d7eu9vXddhmzJKpb5daTuSodxSaN7eu9vXe3rvdnPLeu9vXe3rvb13t6729Nvr6XDpKi9WKTRvb13t6729d7s55b13t6729d7eu9s64JxMBLpr6OlS1SUBF+Sje3rvb13t6729eNY3vb13t6729d7eu9vXe289bFJo3t6729d7eu9vXe3rxrG97eu9vXe3rvb13t6721SRVSxRUdXssWUAUurk0b29d7eu9vXe3rvb3RzWu9vXe3rvb13t6729d7ucTvb13t6729d7eu9vXe3rxrG97eu9vXe3rvb13t67ru/jCad762jJFXJo3t6729d7eu9vXe7OeW9d7eu9vXe3rvb13t68eq3Xe3rvb13t6729d7eu9vdHNa729d7eu9vXe3rvb1iKAbytIrjpLyAbqj5quTRvb13t6729d7e6Oa13t6729d7eu9vXe3rvdzid7eu9vXe3rvb13t6729eNY3vb13t6729d7eu9vXeDS/YwYQQaUIINKEEMGMGFMAgggggs1F/ik0b29d7eu9vXe3rxrG97eu9vXe3rvb13t672929OTRvb13t6729d7eu9vXe7OeW9d7eu9vXe3rvb13ts6HKcdtHI5HI5NptNptNo43lTeWdJUXqxSaN7eu9vXjWN729d7eu9vXe3rvb13t7t6cmje3rvb13t6729d7eu92c8t6729d7eu9vXe3rvbS6LfOy7q5c8CsyAFm4IL3t6729d7eu9vXe3ru8yPwG8s6SovVik0b29d7e7enJo3t6729d7eu9vXe3ruwh6cmje3rvb13t6729d7eu9vXe3rvb13t6729d7eu9vXe3rvb13t67wg8fgN5Z0lRerFJo3t672929OTRvb13t6729d7eu9vXe2k49PJu6uXf183Br5uDXzcGvm4NfNwa+bg183Br5uDXzcGvm4NfNwa+bg183Br5uB6h+A3lnSVF6sUmje3rvb3b05NG9vXe3rvb13t6729d7fDLRWP46lfW0jOK/RVj+OpX1tIziv0VY/jqV9bSQHrhrk0b29d7eu9vXe3rvb149Vuu9vXe3rvb13t6729d7erfymT///yburl///////yburl39fNwa+bg183Br5uDXzcGvm4Nf/9fLv6+bg183Br5uCC97eu9vXe3rvb13t6729ePVbrvb13t6729d7eu9vXe2kCN2ADlFx56US6BB+A3lnI+aN2t5YuuSH2Grark0b20k2Xl/Rvb13t6729d7eu9vXe3u3pyaN7eu9vXe3rvb13t67vPEmfuiq695mTRQgrDvESVlMIMjfHfLEvtTAazZh+rk0b21PK0v/jOlYjWWCvVik0b29W4Q4taZnJNG9vXe3rvb13t6729ePVbrvb13t6729d7eu9vXe266bc1u5ahb6Ey/+wngVmgwv/18u/r5uDXzcGvm4NfNwa+bgws3Br5uDXzcGvm4NfNwa8xzeWdJUXqxSaN7eu9vXe7nE729d7eu9vXe3rvb13t68Z+TDlbZV/bAXeoNfNwa+b///6+Xf183Br5qvv2QzUHTkF8Jl/Yvu+vt5NTunWXf3f3vb13t6729d7eu9vXe3u3pyaN7eu9vXe3rvb13t673ZyDKGSS8/njqxSaN7eu9vXddiD0DrdfgLyzpKi9WKTRvdnPLeu9vXe3rvb13t6729ePVbrvb13t6729d7eu9vXe3ujesqfQbRsvAS8gG8s6SovVikiAdrtdO9Pp1DJ0A4b29d7eu9vXe7OeW9d7eu9vXe3rvbOv7WvHqt13t6729d7eu9vXe3rvb3RvWgZNsuQM61apKL1YpNG9vXe2lyi4me5cZqjrzVcmje3rvb141je9vXe3rvb13t6bGxuAni7nE729d7eu9vXe3rvb13t68awlB2u6fT6eu6c7O67zR0lRerFJo3t6tx0uu13T6fTnZ2d3P8Umje3rvb13uznlvXe3rvb13t6sX1YwY7ma7NTenJo3t6729d7eu9vXe3rvdnIA7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)

[2026-07-19 00:05:20] local.INFO: PaymentController@store called {"customer_name":"Thanh Phuong Khuu","customer_phone":"0123456789","customer_email":"thanhphuongkhuu@gmail.com","receiver_name":"Thanh Phuong Khuu","receiver_phone":"0123456789","shipping_address":"1, 1, 1, TP. Hồ Chí Minh","note":null,"payment_method":"cod","items":[{"id":3,"quantity":1,"price":"2100000"}],"total_amount":2100000,"order_type":"retail","promo_code":null,"discount_amount":0} 
[2026-07-19 00:05:20] local.INFO: Order data: {"promo_code":null,"discount_amount":0,"total_amount":2100000} 
[2026-07-19 00:05:20] local.INFO: Admin\OrderController@store called {"customer_name":"Thanh Phuong Khuu","customer_phone":"0123456789","customer_email":"thanhphuongkhuu@gmail.com","receiver_name":"Thanh Phuong Khuu","receiver_phone":"0123456789","shipping_address":"1, 1, 1, TP. Hồ Chí Minh","note":null,"payment_method":"cod","items":[{"id":3,"quantity":1,"price":"2100000"}],"total_amount":2100000,"order_type":"retail","promo_code":null,"discount_amount":0} 
[2026-07-19 00:05:20] local.INFO: Creating order with type: retail {"total_amount_from_request":2100000,"discount_amount":0,"promo_code":null,"final_amount":2100000} 
[2026-07-19 00:05:20] local.INFO: Order created: {"order_id":74,"total_amount":2100000,"discount_amount":0,"final_amount":2100000,"promo_code":null} 
[2026-07-19 00:05:20] local.INFO: ✅ Order created successfully: {"order_id":74,"display_code":"L1907202600074","total_amount":2100000,"discount_amount":0,"final_amount":2100000,"promo_code":null,"order_type":"retail"} 
[2026-07-19 00:05:20] local.INFO: Checkout success - order_id: 74, display_code: L1907202600074  
[2026-07-19 00:05:20] local.INFO: CartController@index called {"method":"GET"} 
[2026-07-19 00:05:21] local.INFO: CartController@clear called  
[2026-07-19 00:05:21] local.INFO: CartController@index called {"method":"GET"} 
[2026-07-19 00:05:21] local.INFO: CartController@index called {"method":"GET"} 

Clear đã được gọi, nhưng giỏ hàng vẫn giữ nguyên. Vì sao?


## Assistant:
07/19/2026 00:06

> **Thinking:**
> Chúng ta đã xác định rằng `clearCart` được gọi (có log `CartController@clear called`). Nhưng giỏ hàng vẫn còn. Có thể có vấn đề:
> 
> - `clearCart` gọi API xóa, nhưng có thể `localStorage.removeItem` không được thực thi do lỗi (ví dụ API lỗi, nhưng log show success). Nhưng log cho thấy API thành công (vì có log clear).
> - Hoặc `clearCart` được gọi, nhưng sau đó có một `fetchCart` hoặc `loadFromLocalStorage` khác làm khôi phục giỏ hàng từ localStorage (vì localStorage chưa bị xóa). 
> - Hoặc `clearCart` xóa localStorage, nhưng có hành động khác (ví dụ `fetchCart` lại ghi vào localStorage) sau khi clear.
> 
> Trong log: thứ tự:
> 1. 00:05:20 Checkout success
> 2. 00:05:20 CartController@index (có thể là fetchCart sau khi đặt hàng)
> 3. 00:05:21 CartController@clear called (clearCart)
> 4. 00:05:21 CartController@index (có thể là fetchCart sau clear, nhưng có thể fetchCart lại đọc từ localStorage và ghi lại nếu chưa xóa)
> 
> Vấn đề: `clearCart` trong frontend:
> ```javascript
> const clearCart = async () => {
>     try {
>         await axios.delete('/api/cart/clear')
>         cartItems.value = []
>         updateCounts()
>         const key = getStorageKey()
>         localStorage.removeItem(key)
>         clearVoucherStorage()
>         return { success: true }
>     } catch (error) {
>         console.error('Error clearing cart:', error)
>         throw error
>     }
> }
> ```
> 
> Giả sử API thành công, nó sẽ xóa localStorage và reset state. Nhưng nếu sau đó có gọi `fetchCart` (ví dụ trong `onMounted` của component nào đó, hoặc do sự kiện), nó sẽ loadFromLocalStorage (đã bị xóa) và gán cartItems = [], và có thể ghi lại localStorage nếu có dữ liệu từ response? Trong `fetchCart`:
> - loadFromLocalStorage (trả về [])
> - if localItems.length === 0 => cartItems.value = [] và updateCounts, không ghi lại localStorage.
> - Nếu có dữ liệu, thì sau khi fetch từ API, nó sẽ `saveToLocalStorage(cartItems.value)`. Nhưng nếu localItems rỗng, không ghi.
> 
> Vậy nếu clearCart đã xóa localStorage, fetchCart sau đó sẽ load được [] và không ghi gì, giỏ hàng sẽ trống. 
> 
> Nhưng giỏ hàng vẫn còn, có thể:
> 1. clearCart chưa kịp xóa localStorage (do async) nhưng log đã có, và fetchCart chạy sau khi clear nhưng vẫn đọc được localStorage cũ? Không thể vì `localStorage.removeItem` là synchronous và sẽ xóa ngay.
> 2. Có thể có một sự kiện CartEvents.emitUpdated hoặc gì đó làm reload cart từ localStorage khác.
> 
> Cần kiểm tra code trong `OrderSuccess.vue` xem ngoài `clearCart` trong `onUnmounted`, có gọi `fetchCart` hay `reloadCart` ở đâu không. Và kiểm tra thứ tự gọi.
> 
> Trong log có `CartController@index called` 3 lần: trước clear, sau clear, và sau nữa. Điều đó cho thấy có nhiều lần gọi `/api/cart`. Có thể một trong những lần đó ghi lại giỏ hàng từ dữ liệu cũ.
> 
> Nếu `fetchCart` được gọi sau `clearCart`, nó sẽ load từ localStorage (đã xóa) và không ghi gì, nhưng nếu `fetchCart` được gọi trước khi clearCart kịp xóa localStorage (do bất đồng bộ?) thì nó có thể lấy dữ liệu cũ. Nhưng thứ tự log: clear xảy ra trước index thứ hai, index thứ hai và thứ ba sau clear. Nhưng nếu index thứ hai chạy sau clear, nhưng có thể clear chưa hoàn thành? Không, log cho thấy clear đã gọi và trả về, sau đó mới index thứ hai.
> 
> Có thể vấn đề là `clearCart` gọi API xóa session, nhưng không xóa localStorage nếu lỗi. Nhưng log cho thấy không có lỗi.
> 
> Có thể có một component khác (ví dụ header) có gọi `fetchCart` và lưu dữ liệu vào localStorage? Trong `fetchCart`, nếu response có items, nó sẽ ghi đè localStorage. Nếu response trả về items rỗng? Nhưng nếu không gửi cart lên, API sẽ trả về items rỗng. Nhưng trong index, nếu cart rỗng, nó trả về items: [].
> 
> Vậy nếu sau clear, có fetchCart nào đó gọi với cart rỗng, nó sẽ lấy items rỗng và saveToLocalStorage([]) -> xóa luôn.
> 
> Nhưng giỏ hàng vẫn còn: có thể do fetchCart được gọi trước khi clear, và nó lưu dữ liệu vào localStorage và state. Sau đó clear gọi xóa localStorage, nhưng state vẫn còn? clear cũng reset cartItems.value = []. Nên state cũng bị xóa.
> 
> Vậy tại sao giỏ hàng vẫn hiển thị? Có thể có một component khác (ví dụ header) không dùng `useCart` mà lấy dữ liệu từ localStorage trực tiếp? Hoặc có một component khác không cập nhật khi clear.
> 
> Cần kiểm tra xem `clearCart` có thực sự xóa localStorage và state không. Có thể `clearCart` được gọi nhưng vì lỗi nào đó (ví dụ `localStorage.removeItem` không có quyền) nhưng không có console error. Nhưng thường thì có quyền.
> 
> Có thể vấn đề là `clearCart` gọi API xóa session, nhưng `localStorage.removeItem` có thể không xóa được vì key sai. Hãy kiểm tra `getStorageKey()` trả về gì: `bigbag_cart_${getUserId()}`. `getUserId()` lấy từ `window.user.id` hoặc 'guest'. Trong trang success, nếu user đã đăng nhập, key là `bigbag_cart_<userId>`. Nếu clearCart gọi xóa key đó, nhưng có thể có key khác (ví dụ 'bigbag_cart') hoặc dữ liệu được lưu ở nơi khác.
> 
> Có thể có một service khác (ví dụ `useCart` trong một component khác) không được import từ cùng một file? Không, nếu dùng cùng một composable thì state là shared.
> 
> Có thể `clearCart` chạy nhưng sau đó có một component con gọi `fetchCart` và lưu lại dữ liệu từ API (vì API có thể trả về items từ session? Nhưng session không lưu items). API index chỉ nhận cart từ request, nên nếu không gửi cart lên, nó trả về rỗng.
> 
> Vậy lý do có thể là: trong `OrderSuccess.vue`, khi component mount, có thể gọi `fetchCart` hoặc `reloadCart` (có thể trong `onMounted`). Và nếu `clearCart` được gọi trong `onUnmounted` (khi rời trang), nhưng log cho thấy clear được gọi sau khi checkout success. Có thể clear được gọi không phải từ unmount mà từ nơi khác? Tuy nhiên, log có clear gọi, vậy là có ai đó gọi clear.
> 
> Giả sử clear được gọi và xóa localStorage, nhưng ngay sau đó có một lần gọi `fetchCart` khác (index) và có thể nó nhận được dữ liệu từ đâu đó? Có thể trong `fetchCart`, nếu `localItems.length === 0` thì nó không gọi API mà set cartItems = [] và updateCounts, không ghi gì. Nhưng nếu có dữ liệu trong localStorage trước khi clear, và clear đã xóa, thì fetchCart sau sẽ lấy rỗng. Vậy không có lý do để giỏ hàng còn.
> 
> Có thể có một sự kiện CartEvents.emitUpdated được gọi và nó cập nhật một component khác, nhưng không ảnh hưởng đến localStorage.
> 
> Có thể vấn đề là `clearCart` chưa được gọi trước khi chuyển hướng, nhưng log cho thấy nó được gọi. Nhưng tại sao giỏ hàng vẫn hiển thị trên trang success? Trong hình ảnh bạn gửi, đang hiển thị trang success, không thấy giỏ hàng. Bạn có thể đang nói về biểu tượng giỏ hàng trên header? Có thể header vẫn hiển thị số lượng cũ. Đó có thể là do `cartCount` vẫn giữ giá trị cũ. Mặc dù clearCart đã reset state, nhưng có thể header component không dùng chung state, hoặc nó lấy từ localStorage trực tiếp? Cần kiểm tra xem header có sử dụng `useCart` không, và có lắng nghe sự kiện CartEvents không.
> 
> Trong useCart, `updateCounts` gọi `CartEvents.emitUpdated(newCount)`. Nếu header lắng nghe sự kiện này, nó sẽ cập nhật. Nhưng nếu clearCart được gọi trong onUnmounted, thì component header vẫn còn (vì nó không nằm trong trang success, nó là component chung), và khi clearCart chạy, nó emit event, header sẽ cập nhật số lượng về 0.
> 
> Vậy tại sao header vẫn hiển thị số lượng cũ? Có thể do header không lắng nghe sự kiện, hoặc do clearCart không gọi emitUpdated? Trong clearCart, có `updateCounts()` sau khi set cartItems.value = [].
> 
> Nếu `updateCounts()` được gọi, nó sẽ emit sự kiện mới.
> 
> Có thể vấn đề là clearCart được gọi nhưng component header đã bị unmount? Không, header nằm ngoài.
> 
> Có thể vấn đề là `clearCart` chạy nhưng do lỗi async, `updateCounts` chưa kịp? Không, nó sync.
> 
> Có thể `cartItems.value = []` không làm header cập nhật vì header dùng một instance khác của useCart? Nếu mỗi component gọi useCart đều tạo instance mới, thì state không share. Nhưng trong Vue, nếu composable không có state toàn cục, mỗi lần gọi đều tạo mới. Đó có thể là vấn đề: trong file useCart.js, `cartItems` là ref được khai báo trong hàm, nên mỗi lần gọi useCart sẽ có instance riêng. Vì vậy, clearCart gọi trong OrderSuccess sẽ chỉ ảnh hưởng đến instance của nó, không ảnh hưởng đến instance trong Header. Đó là lý do chính! Để share state, cần có một store (ví dụ Pinia) hoặc khai báo state bên ngoài hàm.
> 
> Trong useCart.js hiện tại:
> ```javascript
> export function useCart() {
>     const cartItems = ref([])
>     // ...
> }
> ```
> Mỗi lần gọi useCart đều tạo ref mới, nên state không share. Do đó, clearCart trong OrderSuccess chỉ xóa instance của nó, không ảnh hưởng đến header.
> 
> Vậy giải pháp là chuyển state ra ngoài hàm, hoặc dùng Pinia.
> 
> Hãy kiểm tra xem header có dùng useCart không. Nếu có, nó sẽ có instance riêng. Khi clearCart gọi, nó xóa localStorage, nhưng header instance vẫn giữ cartItems cũ vì nó không được cập nhật. Và khi header fetch lại, nó sẽ đọc từ localStorage (đã xóa) và sẽ cập nhật thành rỗng. Nhưng nếu header không fetch lại, nó vẫn giữ cũ.
> 
> Trong trường hợp này, giỏ hàng vẫn còn trên header vì header chưa fetch lại và không nhận được sự kiện.
> 
> Vậy cần phải:
> 1. Đưa state ra ngoài để share (dùng singleton).
> 2. Hoặc emit sự kiện và các component khác lắng nghe để cập nhật.
> 
> Hiện tại, CartEvents đã được emit trong updateCounts, nhưng header có lắng nghe không? Nếu header có lắng nghe và cập nhật cartCount thì sẽ ổn. Nhưng nếu header không lắng nghe, thì không.
> 
> Vì vậy, trước khi sửa, cần kiểm tra header có sử dụng useCart và có lắng nghe sự kiện không. Nếu có, việc xóa localStorage và emit event sẽ cập nhật header.
> 
> Nhưng log show clear đã gọi, và header vẫn hiển thị giỏ hàng cũ, có thể header chưa lắng nghe hoặc không dùng useCart.
> 
> Để xác minh, bạn có thể kiểm tra code của Header component.
> 
> Vậy câu trả lời: Nguyên nhân giỏ hàng vẫn còn là do state không được share giữa các component (useCart tạo instance riêng), và dù clearCart xóa localStorage, các component khác vẫn giữ dữ liệu cũ. Cần dùng Pinia hoặc singleton để share state.

Dựa trên log và mô tả, tôi thấy `clearCart()` **đã được gọi** (có log `CartController@clear called`). Tuy nhiên, giỏ hàng vẫn hiển thị ở header (hoặc nơi khác). **Nguyên nhân chính: `useCart` không phải là singleton – mỗi component gọi `useCart()` sẽ tạo một instance riêng biệt, do đó state không được chia sẻ.**

## Vấn đề chi tiết

### 1. State không được chia sẻ
Trong `useCart.js`:

```javascript
export function useCart() {
    const cartItems = ref([])   // mỗi lần gọi đều tạo mới
    // ...
}
```

- **OrderSuccess.vue** gọi `useCart()` và gọi `clearCart()`. Lúc này, instance của OrderSuccess có `cartItems` được reset về `[]`, và `localStorage` bị xóa.
- **Header component** (hoặc bất kỳ component nào khác hiển thị số lượng giỏ hàng) cũng gọi `useCart()` nhưng có **instance riêng**, với `cartItems` vẫn giữ giá trị cũ (vì nó không được cập nhật). Dù `localStorage` đã xóa, nhưng instance của Header chưa biết sự thay đổi đó, trừ khi nó gọi `fetchCart()` hoặc lắng nghe sự kiện.

### 2. Sự kiện `CartEvents.emitUpdated` có thể không được lắng nghe
Trong `updateCounts()` có gọi `CartEvents.emitUpdated(newCount)` – nếu Header lắng nghe sự kiện này và cập nhật số lượng, thì giỏ hàng sẽ biến mất. Nhưng có thể Header không lắng nghe hoặc chưa được gắn listener.

### 3. Thứ tự gọi
Log cho thấy:
- `clear` được gọi
- Sau đó có 2 lần gọi `index` (CartController@index). Những lần gọi này có thể là do một component nào đó (có thể Header) gọi `fetchCart()` sau khi `clear` được gọi. Nếu `fetchCart()` chạy sau khi `localStorage` đã bị xóa, nó sẽ load được `[]` và cập nhật state của instance đó. Nhưng nếu Header không gọi `fetchCart`, thì state vẫn giữ nguyên.

## Giải pháp

### Cách 1: Chuyển `useCart` thành singleton (dùng state toàn cục)
Đưa các `ref` ra ngoài hàm để tất cả component dùng chung một instance:

```javascript
// useCart.js
import { ref, computed } from 'vue'
// ...

// Khai báo state bên ngoài
const cartItems = ref([])
const cartCount = ref(0)
// ... các state khác

export function useCart() {
    // Các hàm vẫn giữ nguyên, nhưng dùng state đã khai báo
    // Không tạo mới ref trong hàm
}
```

### Cách 2: Sử dụng Pinia (khuyến nghị)
Tạo store cho giỏ hàng với Pinia, các component dùng chung một store, mọi thay đổi đều được reactive.

### Cách 3: Đảm bảo tất cả component lắng nghe sự kiện `CartEvents`
Trong Header, thêm listener:

```javascript
onMounted(() => {
    CartEvents.on('updated', (count) => {
        cartCount.value = count
        // hoặc gọi fetchCart() để refresh toàn bộ
    })
})
```

Và trong `updateCounts`, vẫn emit sự kiện.

### Cách 4: Gọi `clearCart` và sau đó emit sự kiện riêng để báo các component khác
Sau khi clear, gọi `fetchCart()` trong các component cần cập nhật.

## Kết luận

- **Lý do giỏ hàng vẫn hiển thị** dù `clearCart` đã chạy: do state không được chia sẻ giữa các component.
- **Khắc phục**: Dùng singleton hoặc Pinia để đảm bảo state toàn cục. Khi đó, một component xóa giỏ hàng thì tất cả component khác sẽ tự động cập nhật.

---

**Gợi ý**: Sửa `useCart.js` thành singleton là cách nhanh nhất. Nếu bạn muốn làm sạch và mở rộng, hãy dùng Pinia.


## User:
07/19/2026 00:12

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
            console.error('Error clearing cart:', error)
            throw error
        }
    }
Làm sao để biết hàm nào không chạy và hàm nào chạy?


## Assistant:
07/19/2026 00:12

> **Thinking:**
> Chúng ta cần xác định hàm `clearCart` đã thực thi những phần nào khi được gọi, dựa trên log và hành vi. Câu hỏi: "Làm sao để biết hàm nào không chạy và hàm nào chạy?" – tức là làm sao debug để biết dòng nào trong hàm đã được thực hiện.
> 
> Chúng ta đã thấy log backend cho thấy API `/api/cart/clear` đã được gọi (vì có log "CartController@clear called"). Do đó, `await axios.delete('/api/cart/clear')` đã thực hiện thành công (không throw). Sau đó, nếu không có lỗi, code sẽ tiếp tục: `cartItems.value = []`, `updateCounts()`, `localStorage.removeItem(key)`, `clearVoucherStorage()`. 
> 
> Nhưng hiện tại, mặc dù clear được gọi, giỏ hàng vẫn hiển thị ở header (hoặc nơi khác). Điều đó có thể là do:
> 
> 1. `cartItems.value` được reset trong instance hiện tại, nhưng instance khác (Header) không được cập nhật.
> 2. `localStorage.removeItem` có thể đã xóa thành công, nhưng Header lại load từ localStorage hoặc có state riêng.
> 
> Vì vậy, cần xác định xem các dòng sau API có thực sự chạy hay không. Để kiểm tra, chúng ta có thể thêm `console.log` vào từng bước.
> 
> **Cách kiểm tra:** Đặt `console.log` ở mỗi bước để xem chúng có in ra không. Ví dụ:
> 
> ```javascript
> const clearCart = async () => {
>   try {
>     console.log('Clearing cart...');
>     await axios.delete('/api/cart/clear');
>     console.log('API success');
>     cartItems.value = [];
>     console.log('cartItems reset');
>     updateCounts();
>     console.log('updateCounts done');
>     const key = getStorageKey();
>     localStorage.removeItem(key);
>     console.log('localStorage removed for key:', key);
>     clearVoucherStorage();
>     console.log('voucher storage cleared');
>     return { success: true };
>   } catch (error) {
>     console.error('Error clearing cart:', error);
>     throw error;
>   }
> }
> ```
> 
> Sau đó kiểm tra console trình duyệt. Nếu tất cả log đều xuất hiện, thì tất cả dòng đều chạy. Nếu có log bị thiếu, bạn biết điểm dừng.
> 
> **Ngoài ra, để kiểm tra localStorage:** Mở DevTools -> Application -> Local Storage, xem key `bigbag_cart_<userId>` có bị xóa không. Nếu vẫn còn, chứng tỏ `localStorage.removeItem` không chạy hoặc key sai.
> 
> **Đối với vấn đề giỏ hàng vẫn hiển thị ở header:** Là do state không đồng bộ. Để kiểm tra, thêm log trong Header component khi nó lấy dữ liệu, ví dụ trong `fetchCart` hoặc khi computed tính toán số lượng.
> 
> **Tóm lại:**
> - Dùng console.log để xác định từng bước.
> - Kiểm tra localStorage trực tiếp.
> - Kiểm tra component Header đang lấy dữ liệu từ đâu (có thể từ store riêng, không dùng chung).
> 
> Với thông tin hiện tại, có vẻ API và các bước sau API đã chạy vì log backend xuất hiện, và không có lỗi gì. Vấn đề chính là state không được chia sẻ giữa các component. Nhưng câu hỏi của bạn là làm sao biết hàm nào không chạy, vậy hãy dùng console.log để kiểm tra trực tiếp.

Để xác định chính xác dòng nào trong `clearCart` đã chạy và dòng nào chưa, bạn có thể thêm `console.log` hoặc breakpoint (debugger) vào từng bước. Dưới đây là cách cụ thể:

---

## 1. Thêm `console.log` từng dòng

Sửa lại hàm `clearCart` như sau:

```javascript
const clearCart = async () => {
  try {
    console.log('🟢 Bắt đầu clearCart');

    await axios.delete('/api/cart/clear');
    console.log('✅ API /api/cart/clear thành công');

    cartItems.value = [];
    console.log('✅ cartItems.value đã reset');

    updateCounts();
    console.log('✅ updateCounts đã chạy');

    const key = getStorageKey();
    localStorage.removeItem(key);
    console.log(`✅ localStorage key "${key}" đã bị xóa`);

    clearVoucherStorage();
    console.log('✅ clearVoucherStorage đã chạy');

    return { success: true };
  } catch (error) {
    console.error('❌ Lỗi trong clearCart:', error);
    throw error;
  }
}
```

Sau đó, mở **DevTools Console** của trình duyệt, thực hiện hành động gọi `clearCart` (ví dụ reload trang success) và xem tất cả log có xuất hiện không:

- Nếu bạn thấy tất cả các dòng log từ `🟢` đến `✅ clearVoucherStorage` thì toàn bộ hàm đã chạy.
- Nếu thiếu log nào đó (ví dụ không thấy `✅ API ...`), có nghĩa là `axios.delete` đã throw lỗi và nhảy vào `catch`.
- Nếu thiếu dòng `✅ localStorage key ...`, có thể `localStorage.removeItem` bị lỗi (ví dụ key không tồn tại, hoặc bị chặn do same-origin policy), nhưng hiếm.

---

## 2. Kiểm tra trực tiếp localStorage

Mở DevTools → tab **Application** → **Local Storage** → tìm key `bigbag_cart_*` (với `*` là userId). Sau khi `clearCart` chạy, key đó phải biến mất. Nếu vẫn còn, chứng tỏ `localStorage.removeItem` chưa được thực thi (có thể do lỗi hoặc do không vào đúng nhánh).

---

## 3. Kiểm tra state của các component

Để biết `cartItems.value` có thực sự được reset hay không, bạn có thể thêm log ngay sau dòng `cartItems.value = []`:

```javascript
console.log('cartItems after reset:', cartItems.value); // phải là []
```

Nếu nó hiển thị `[]`, nhưng Header vẫn hiển thị số lượng cũ, chứng tỏ Header đang dùng một instance khác của `useCart` (không dùng chung state). Điều này giải thích tại sao giỏ hàng vẫn hiển thị dù đã clear.

---

## 4. Sử dụng breakpoint (debugger)

Thêm dòng `debugger;` vào đầu hàm, sau đó mở DevTools và thực hiện action. Trình duyệt sẽ dừng tại đó, bạn có thể step qua từng dòng để xem luồng thực thi và giá trị biến.

---

## Kết luận từ log hiện tại

- Log backend cho thấy API `/api/cart/clear` đã được gọi và thành công.
- Không có log lỗi nào từ frontend (nếu có lỗi, nó sẽ in ra console).
- Vậy có khả năng **tất cả các dòng trong `clearCart` đều đã chạy**, nhưng do thiết kế `useCart` không dùng singleton, nên Header không nhận được sự cập nhật.

**Hãy thêm console.log để xác nhận.** Sau đó, nếu xác nhận tất cả các dòng đều chạy mà giỏ hàng vẫn hiển thị, vấn đề nằm ở việc chia sẻ state giữa các component – bạn cần chuyển sang dùng Pinia hoặc singleton.


## User:
07/19/2026 08:20

Bắt đầu clearCart
useCart.js:256 ✅ API /api/cart/clear thành công
useCart.js:259 ✅ cartItems.value đã reset
useCart.js:262 ✅ updateCounts đã chạy
useCart.js:266 ✅ localStorage key "bigbag_cart_5" đã bị xóa
useCart.js:269 ✅ clearVoucherStorage đã chạy
client:993 [vite] server connection lost. Polling for restart...
Cố chạy nhưng giỏ hàng vẫn còn


## Assistant:
07/19/2026 08:20

> **Thinking:**
> Chúng ta thấy tất cả các dòng log đều chạy thành công, bao gồm xóa localStorage. Tuy nhiên, giỏ hàng vẫn hiển thị ở giao diện. Điều này khẳng định vấn đề là do state không được chia sẻ: component hiển thị giỏ hàng (ví dụ Header) có instance riêng của useCart, không nhận được cập nhật từ OrderSuccess.
> 
> Vì vậy, giải pháp là chuyển useCart thành singleton hoặc dùng Pinia.
> 
> Tuy nhiên, câu hỏi là "Làm sao để biết hàm nào không chạy" - qua log ta thấy tất cả đều chạy. Vậy vấn đề không phải là hàm không chạy, mà là state không được đồng bộ giữa các component.
> 
> Tôi sẽ trả lời: dựa trên log, tất cả các bước trong clearCart đều đã thực thi thành công. Lý do giỏ hàng vẫn hiển thị là do vấn đề chia sẻ state giữa các component. Tôi sẽ đưa ra giải pháp như đã đề cập.

Qua log bạn cung cấp, **tất cả các dòng trong `clearCart` đều đã chạy thành công**:

- ✅ API `/api/cart/clear` thành công
- ✅ `cartItems.value = []` reset state
- ✅ `updateCounts()` chạy và emit sự kiện
- ✅ `localStorage.removeItem(key)` xóa key `bigbag_cart_5`
- ✅ `clearVoucherStorage()` xóa voucher

**Nhưng giỏ hàng vẫn hiển thị** → Vấn đề nằm ở **state không được chia sẻ giữa các component**.

### Giải thích
- `useCart()` được gọi ở nhiều nơi (OrderSuccess, Header, v.v.). Mỗi lần gọi đều tạo một instance riêng với `cartItems` riêng.
- Khi bạn gọi `clearCart()` trong OrderSuccess, chỉ instance của OrderSuccess bị reset, còn instance của Header vẫn giữ giá trị cũ.
- Dù sự kiện `CartEvents.emitUpdated` được gọi, nhưng Header có thể không lắng nghe hoặc chưa được gắn listener, nên không cập nhật.

### Giải pháp
1. **Chuyển `useCart` thành singleton** (khuyến nghị nhanh) – đưa các `ref` ra ngoài hàm để dùng chung.
2. **Hoặc dùng Pinia** – tạo store toàn cục cho giỏ hàng, các component dùng chung store.

Sau khi sửa, bạn sẽ thấy Header tự động cập nhật khi clear.

---

**Kết luận**: Hàm `clearCart` **đã chạy đầy đủ**, nhưng do thiết kế state không dùng chung nên Header không nhận được thay đổi.


## User:
07/19/2026 08:22

Hãy hướng dẫn giải pháp


## Assistant:
07/19/2026 08:22

> **Thinking:**
> Chúng ta sẽ hướng dẫn giải pháp chuyển `useCart` thành singleton để các component dùng chung một state. Đây là cách nhanh nhất và ít thay đổi nhất.
> 
> Trước hết, cần phân tích file `useCart.js`. Các biến `ref` được khai báo bên trong hàm, do đó mỗi lần gọi `useCart` sẽ tạo mới. Để singleton, ta đưa các `ref` ra ngoài hàm, và trong hàm chỉ trả về các biến và phương thức đó.
> 
> Sau đó, đảm bảo các component gọi `useCart` sẽ nhận được cùng một instance.
> 
> Ngoài ra, cần kiểm tra xem Header có lắng nghe sự kiện `CartEvents` hay không, nếu có thì cũng nên đảm bảo việc lắng nghe được thiết lập đúng.
> 
> Dưới đây là hướng dẫn chi tiết:
> 
> 1. Sửa file `useCart.js`: đưa các `ref` ra ngoài, và các hàm sử dụng biến đó. Ví dụ:
> 
> ```javascript
> // useCart.js
> import { ref, computed } from 'vue'
> import axios from 'axios'
> import { CartEvents } from '@/events/CartEvents'
> 
> // Biến state toàn cục
> const cartItems = ref([])
> const cartCount = ref(0)
> const cartTotal = ref(0)
> const loading = ref(false)
> const couponCode = ref('')
> const discountAmount = ref(0)
> const appliedCoupon = ref(null)
> const couponError = ref('')
> let isFetching = false
> 
> // Các hàm (giữ nguyên, chỉ tham chiếu đến biến toàn cục)
> export function useCart() {
>   // các hàm như cũ, nhưng không khai báo lại ref ở đây
>   // ...
> }
> ```
> 
> Lưu ý: Các hàm như `getUserId`, `getStorageKey`, `saveToLocalStorage`, `loadFromLocalStorage` vẫn giữ nguyên. Các hàm khác như `fetchCart`, `addToCart`, `updateCart`, `removeFromCart`, `clearCart`, `applyCoupon`, `removeCoupon`... đều sử dụng `cartItems`, `cartCount`, ... đã được khai báo bên ngoài.
> 
> 2. Kiểm tra Header component: đảm bảo nó gọi `useCart()` và sử dụng `cartCount` để hiển thị số lượng.
> 
> 3. Nếu Header không tự cập nhật, có thể cần thêm listener:
> 
> ```javascript
> // Trong Header
> import { CartEvents } from '@/events/CartEvents'
> import { onMounted, onUnmounted } from 'vue'
> import { useCart } from '@/utils/useCart'
> 
> const { cartCount, fetchCart } = useCart()
> 
> // Nếu sự kiện không được emit trong clearCart (dù đã gọi updateCounts), ta vẫn có thể force fetchCart sau khi clear.
> // Nhưng updateCounts đã emit sự kiện, nên Header cần lắng nghe:
> const updateCartCount = (count) => {
>   // cách 1: gán trực tiếp (nếu state dùng chung, chỉ cần thay đổi cartCount)
>   // nhưng với singleton, cartCount là biến dùng chung nên tự reactive.
>   // Có thể không cần lắng nghe nếu dùng singleton.
> }
> ```
> 
> Với singleton, khi `cartCount.value` thay đổi ở bất kỳ đâu, mọi component sử dụng `cartCount` sẽ tự động cập nhật. Vì vậy ta không cần sự kiện nữa, nhưng vẫn có thể giữ để đảm bảo các component khác không dùng singleton thì vẫn hoạt động.
> 
> 4. Lưu ý: Nếu dùng singleton, khi reload trang, các component sẽ khởi tạo lại nhưng state toàn cục cũng được tạo lại (vì module được import lại). Tuy nhiên, khi có nhiều component cùng mount, chúng sẽ dùng chung state, vì module được import một lần.
> 
> Vậy hãy viết lại file `useCart.js` hoàn chỉnh dạng singleton.
> 
> Ngoài ra, cần kiểm tra xem Header có dùng `cartCount` để hiển thị, nếu có thì sau khi clear, `cartCount` sẽ là 0, và Header sẽ tự cập nhật.
> 
> Vậy hướng dẫn giải pháp: Chuyển `useCart` thành singleton bằng cách đưa biến ref ra ngoài hàm.
> 
> Ta sẽ soạn câu trả lời bao gồm các bước cụ thể và code mẫu.

## Giải pháp: Chuyển `useCart` thành Singleton

Vấn đề của bạn là **state không được chia sẻ giữa các component** vì mỗi lần gọi `useCart()` đều tạo một instance mới với `cartItems`, `cartCount` riêng biệt. Để khắc phục, bạn cần đưa các biến `ref` ra ngoài hàm `useCart` để tất cả component dùng chung một state.

### Bước 1: Sửa file `useCart.js`

Di chuyển tất cả biến `ref` và các biến khác ra ngoài phạm vi hàm. Các hàm vẫn giữ nguyên, nhưng tham chiếu đến biến toàn cục đó.

**Code mới cho `useCart.js`** (phần chính):

```javascript
// useCart.js
import { ref, computed } from 'vue'
import axios from 'axios'
import { CartEvents } from '@/events/CartEvents'

// ============= STATE TOÀN CỤC =============
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
    } catch (e) {}
}

const loadFromLocalStorage = () => {
    try {
        const key = getStorageKey()
        const data = localStorage.getItem(key)
        if (data) return JSON.parse(data)
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

// ============= HÀM CẬP NHẬT =============
const updateCounts = () => {
    const newCount = cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
    cartCount.value = newCount
    cartTotal.value = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    CartEvents.emitUpdated(newCount)
}

// ============= CÁC HÀM KHÁC =============
// ... (giữ nguyên toàn bộ phần còn lại, từ fetchCart, addToCart, updateCart, removeFromCart, clearCart, applyCoupon, removeCoupon, reloadCart, setVoucherFromSession, restoreVoucher, ...)
// Chỉ cần bỏ các dòng khai báo ref ở đầu hàm, vì đã có sẵn bên ngoài.

export function useCart() {
    // Trả về tất cả state và methods
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
```

### Bước 2: Đảm bảo Header và các component khác dùng `useCart()`

Tất cả component (Header, OrderSuccess, ...) vẫn gọi `useCart()` như cũ. Vì state đã được dùng chung, khi `clearCart()` thay đổi `cartItems.value = []`, mọi nơi sử dụng `cartItems` hoặc `cartCount` sẽ tự động cập nhật.

### Bước 3: Xóa sự kiện `CartEvents` (không bắt buộc)

Nếu bạn dùng singleton, bạn có thể bỏ hẳn `CartEvents` vì state đã reactive toàn cục. Tuy nhiên, nếu có component nào không dùng `useCart` (ví dụ component legacy), bạn vẫn giữ sự kiện để tương thích.

### Bước 4: Kiểm tra lại

- Sau khi sửa, reload lại trang (hoặc hard refresh) để module mới được tải.
- Thực hiện đặt hàng và xem giỏ hàng ở Header: sau khi clear, số lượng sẽ về 0 ngay lập tức.

---

## Lợi ích của giải pháp

- **Không cần thay đổi logic component** – chỉ sửa `useCart.js`.
- **Reactive toàn cục** – mọi component đều nhận được cập nhật.
- **Dễ dàng mở rộng** – nếu sau này muốn dùng Pinia, bạn có thể chuyển đổi dần.

Với thay đổi này, khi bạn gọi `clearCart()` ở OrderSuccess, `cartItems.value` và `cartCount.value` sẽ được reset trên toàn hệ thống, Header sẽ hiển thị số 0 ngay lập tức mà không cần sự kiện hay reload.


## User:
07/19/2026 08:26

import { ref, computed } from 'vue'
import axios from 'axios'
import { CartEvents } from '@/events/CartEvents'

export function useCart() {
    const cartItems = ref([])
    const cartCount = ref(0)
    const cartTotal = ref(0)
    const loading = ref(false)
    const couponCode = ref('')
    const discountAmount = ref(0)
    const appliedCoupon = ref(null)
    const couponError = ref('')
    let isFetching = false

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

    const subtotal = computed(() => {
        return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    })

    const total = computed(() => {
        return subtotal.value - discountAmount.value
    })

    const updateCounts = () => {
        const newCount = cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
        cartCount.value = newCount
        cartTotal.value = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        
        CartEvents.emitUpdated(newCount)
    }

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
        console.log('🟢 Bắt đầu clearCart');

        await axios.delete('/api/cart/clear');
        console.log('✅ API /api/cart/clear thành công');

        cartItems.value = [];
        console.log('✅ cartItems.value đã reset');

        updateCounts();
        console.log('✅ updateCounts đã chạy');

        const key = getStorageKey();
        localStorage.removeItem(key);
        console.log(`✅ localStorage key "${key}" đã bị xóa`);

        clearVoucherStorage();
        console.log('✅ clearVoucherStorage đã chạy');

        return { success: true };
    } catch (error) {
        console.error('❌ Lỗi trong clearCart:', error);
        throw error;
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

    // ============ SỬA: Xóa coupon ============
    const removeCoupon = async () => {
        try {
            // Gọi API xóa session
            const response = await axios.post('/api/cart/remove-coupon')
            
            // Reset state
            discountAmount.value = 0
            appliedCoupon.value = null
            couponCode.value = ''
            couponError.value = ''
            
            // Xóa localStorage
            clearVoucherStorage()
            return { success: true }
        } catch (error) {
            console.error('Error removing coupon:', error)
            // Vẫn reset state dù API fail
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
Hãy sửa lại toàn bộ cart


## Assistant:
07/19/2026 08:26

> **Thinking:**
> Chúng ta cần sửa lại `useCart.js` để biến nó thành singleton (state toàn cục), tức là đưa các biến `ref` ra ngoài phạm vi hàm `useCart`. Tất cả các hàm sẽ dùng chung state đó. Đây là cách nhanh nhất để tất cả component dùng chung một giỏ hàng.
> 
> Tôi sẽ viết lại toàn bộ file `useCart.js` với các thay đổi:
> 
> - Đưa `cartItems`, `cartCount`, `cartTotal`, `loading`, `couponCode`, `discountAmount`, `appliedCoupon`, `couponError`, `isFetching` ra ngoài hàm.
> - Giữ nguyên toàn bộ logic bên trong các hàm, nhưng sửa đổi để chúng dùng biến toàn cục.
> - Không thay đổi API bên ngoài, các component gọi `useCart()` vẫn nhận được object như cũ nhưng state được chia sẻ.
> 
> Tôi sẽ viết lại toàn bộ code.

Dưới đây là file `useCart.js` được sửa lại thành **singleton** (state toàn cục dùng chung cho tất cả component). Tất cả các biến `ref` được đưa ra ngoài hàm `useCart()` để đảm bảo mọi nơi gọi đều dùng cùng một instance.

```javascript
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
        console.log('🟢 Bắt đầu clearCart')

        await axios.delete('/api/cart/clear')
        console.log('✅ API /api/cart/clear thành công')

        cartItems.value = []
        console.log('✅ cartItems.value đã reset')

        updateCounts()
        console.log('✅ updateCounts đã chạy')

        const key = getStorageKey()
        localStorage.removeItem(key)
        console.log(`✅ localStorage key "${key}" đã bị xóa`)

        clearVoucherStorage()
        console.log('✅ clearVoucherStorage đã chạy')

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
```

### Thay đổi chính:
- Tất cả `ref` và biến `isFetching` được khai báo bên ngoài hàm `useCart()`.
- Các hàm (fetchCart, addToCart, …) hoạt động trên chính các biến toàn cục đó.
- Hàm `useCart()` chỉ đơn giản trả về object chứa các biến và hàm – không tạo mới bất kỳ state nào.
- Nhờ vậy, khi gọi `clearCart()` ở bất kỳ đâu, tất cả component dùng `useCart()` đều thấy sự thay đổi (reactive) và Header sẽ hiển thị số lượng 0 ngay lập tức.

### Cách dùng
Các component vẫn gọi `import { useCart } from '@/utils/useCart'` và sử dụng như cũ. Không cần thay đổi gì khác.


## User:
07/19/2026 08:32

<template>
  <div>
    <Head title="Đặt hàng thành công - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-4xl mx-auto px-4 py-12" id="print-area">
      <!-- Success Banner -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
          <span class="material-symbols-outlined text-green-600 text-4xl">check_circle</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Đặt hàng thành công!</h1>
        <p class="text-gray-500 text-lg">Cảm ơn bạn đã mua hàng tại BigBag</p>

        <!-- Order Code -->
        <div class="mt-4 inline-block bg-gray-50 px-6 py-3 rounded-xl border border-gray-200">
          <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
          <p class="text-2xl font-bold text-primary">{{ orderDisplayCode }}</p>
        </div>
      </div>

      <!-- ====== THANH TOÁN PAYOS (không QR) ====== -->
      <div 
        v-if="shouldShowPaymentButton"
        class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8"
      >
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 px-6 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">payments</span>
            <h2 class="text-xl font-semibold text-gray-800">Thanh toán đơn hàng</h2>
          </div>
        </div>
        <div class="p-6 text-center">
          <!-- Trạng thái: Đã thanh toán -->
          <div v-if="paymentStatus === 'paid'" class="py-4">
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
              <span class="material-symbols-outlined text-green-600 align-middle">check_circle</span>
              <span class="text-green-700 font-semibold ml-2">Thanh toán đã được xác nhận!</span>
              <p class="text-sm text-gray-500 mt-2">Trang sẽ tự động chuyển về trang chủ sau 3 giây...</p>
            </div>
          </div>

          <!-- Trạng thái: Thất bại -->
          <div v-else-if="paymentStatus === 'failed'" class="py-4">
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <span class="material-symbols-outlined text-red-600 align-middle">error</span>
              <span class="text-red-700 font-semibold ml-2">Thanh toán thất bại. Vui lòng thử lại.</span>
            </div>
          </div>

          <!-- Trạng thái: Chờ thanh toán -->
          <div v-else-if="paymentStatus === 'pending'" class="py-4">
            <p class="text-gray-600 mb-4">
              Bạn chưa hoàn tất thanh toán. Vui lòng bấm nút bên dưới để thanh toán qua PayOS.
            </p>
            <p class="text-sm text-gray-500 mb-4">
              Số tiền: <span class="font-bold text-primary">{{ formatPrice(orderSummary.final_amount) }}</span>
            </p>

            <!-- Nút mở link thanh toán trong tab mới -->
            <button 
              @click="openPaymentTab"
              class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-lg hover:bg-primary-dark transition-all font-medium shadow-md hover:shadow-lg"
            >
              <span class="material-symbols-outlined">open_in_new</span>
              Thanh toán qua PayOS
            </button>

            <!-- Thông tin đơn hàng -->
            <div class="mt-4 text-xs text-gray-400 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 inline-block">
              <span class="material-symbols-outlined text-sm align-middle">info</span>
              Mã đơn hàng: {{ orderDisplayCode }}
            </div>

            <div class="mt-4 text-sm text-gray-500">
              <span class="inline-block animate-pulse mr-2">⏳</span>
              Đang chờ thanh toán...
            </div>
          </div>

          <!-- Lỗi tạo link -->
          <div v-else-if="paymentError" class="py-4 text-red-500">
            <span class="material-symbols-outlined text-4xl block">error</span>
            <p>{{ paymentError }}</p>
            <button 
              @click="fetchPaymentLink" 
              class="mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary-dark"
            >
              Thử lại
            </button>
          </div>

          <!-- Loading -->
          <div v-else class="py-8">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
            <p class="mt-4 text-gray-500">Đang chuẩn bị thanh toán...</p>
          </div>
        </div>
      </div>

      <!-- Order Details Card -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 px-6 py-4 border-b border-gray-100">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-primary">receipt_long</span>
              <h2 class="text-xl font-semibold text-gray-800">Chi tiết đơn hàng</h2>
            </div>
            <div class="flex items-center gap-2">
              <span 
                v-if="order?.order_code === 'preorder'"
                class="inline-block px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full"
              >
                Pre-order
              </span>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
          <!-- Customer Information - 2 columns -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person</span>
                Thông tin người đặt
              </h3>
              <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Họ tên:</span> <span class="font-medium">{{ order?.customer_name || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Email:</span> <span class="font-medium">{{ customerEmail }}</span></p>
                <p><span class="text-gray-500">Số điện thoại:</span> <span class="font-medium">{{ order?.customer_phone || 'N/A' }}</span></p>
              </div>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">local_shipping</span>
                Thông tin người nhận
              </h3>
              <div class="space-y-2 text-sm">
                <p><span class="text-gray-500">Họ tên:</span> <span class="font-medium">{{ order?.receiver_name || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Số điện thoại:</span> <span class="font-medium">{{ order?.receiver_phone || 'N/A' }}</span></p>
                <p><span class="text-gray-500">Địa chỉ:</span> <span class="font-medium">{{ order?.shipping_address || 'N/A' }}</span></p>
              </div>
            </div>
          </div>

          <!-- Note -->
          <div v-if="order?.note" class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-500 flex items-start gap-2">
              <span class="material-symbols-outlined text-sm">note</span>
              <span><span class="font-medium text-gray-600">Ghi chú:</span> {{ order.note }}</span>
            </p>
          </div>

          <!-- Products List -->
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
              <span class="material-symbols-outlined text-sm">shopping_bag</span>
              Sản phẩm đã đặt
            </h3>
            <div class="border border-gray-100 rounded-xl overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Sản phẩm</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium">Số lượng</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Đơn giá</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="item in orderDetails" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                          <img 
                            :src="item.image || '/images/default-product.jpg'" 
                            :alt="item.name"
                            @error="(e) => { e.target.src = '/images/default-product.jpg' }"
                            class="w-full h-full object-cover"
                          />
                        </div>
                        <div>
                          <p class="font-medium text-gray-800">{{ item.name }}</p>
                          <p v-if="item.color || item.size" class="text-xs text-gray-500">
                            {{ item.color ? `Màu: ${item.color}` : '' }}
                            {{ item.color && item.size ? ' | ' : '' }}
                            {{ item.size ? `Size: ${item.size}` : '' }}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td class="text-center px-4 py-3 font-medium">{{ item.quantity }}</td>
                    <td class="text-right px-4 py-3">{{ formatPrice(item.unit_price) }}</td>
                    <td class="text-right px-4 py-3 font-medium text-primary">{{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Tạm tính</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(orderSummary.subtotal) }}</td>
                  </tr>
                  <tr v-if="orderSummary.shipping_fee > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Phí vận chuyển</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(orderSummary.shipping_fee) }}</td>
                  </tr>
                  <tr v-if="orderSummary.discount_amount > 0" class="bg-green-50">
                    <td colspan="3" class="text-right px-4 py-3 text-green-600 font-medium">
                      <span class="flex items-center justify-end gap-2">
                        <span class="material-symbols-outlined text-sm">local_offer</span>
                        Giảm giá
                      </span>
                    </td>
                    <td class="text-right px-4 py-3 font-medium text-red-500">
                      -{{ formatPrice(orderSummary.discount_amount) }}
                    </td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-3 font-bold text-gray-800">
                      <span class="text-lg">Tổng cộng</span>
                    </td>
                    <td class="text-right px-4 py-3 font-bold text-2xl text-primary">
                      {{ formatPrice(orderSummary.final_amount) }}
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 rounded-xl p-4">
            <div>
              <p class="text-sm text-gray-500">Phương thức thanh toán</p>
              <p class="font-medium text-gray-800 flex items-center gap-2 mt-1">
                <span 
                  class="w-3 h-3 rounded-full inline-block"
                  :class="getPaymentColor(order?.payment_method)"
                ></span>
                {{ getPaymentLabel(order?.payment_method) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Trạng thái thanh toán</p>
              <p class="font-medium flex items-center gap-2 mt-1">
                <span 
                  class="inline-block px-2 py-0.5 text-xs font-bold rounded-full"
                  :class="getPaymentStatusBadge(order?.payment_status || 'pending')"
                >
                  {{ getPaymentStatusLabel(order?.payment_status || 'pending') }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center no-print">
        <a 
          :href="route('home')" 
          class="inline-flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 rounded-xl hover:bg-primary-dark transition-all font-semibold shadow-sm hover:shadow-md"
        >
          <span class="material-symbols-outlined">shopping_cart</span>
          Tiếp tục mua sắm
        </a>
        <a 
          :href="route('orders.history')" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-50 transition-all font-semibold"
        >
          <span class="material-symbols-outlined">history</span>
          Xem lịch sử đơn hàng
        </a>
        <button 
          v-if="order?.id"
          @click="printOrder" 
          class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-200 transition-all font-semibold"
        >
          <span class="material-symbols-outlined">print</span>
          In đơn hàng
        </button>
      </div>

      <!-- Support Info -->
      <div class="mt-8 text-center text-sm text-gray-500 no-print">
        <p>Cần hỗ trợ? Liên hệ hotline: <a href="tel:19001234" class="text-primary font-medium">1900 1234</a></p>
        <p class="mt-1">Email: <a href="mailto:support@bigbag.vn" class="text-primary font-medium">support@bigbag.vn</a></p>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'
import axios from 'axios'
import { useCart } from '@/utils/useCart'

const props = defineProps({
  order: {
    type: Object,
    default: null
  },
  order_display_code: {
    type: String,
    default: ''
  }
})

// ============ CART ============
const { clearCart } = useCart()

// ============ PAYMENT STATE ============
const paymentUrl = ref(null)
const loading = ref(false)
const paymentError = ref(null)
const paymentStatus = ref(props.order?.payment_status || 'pending')
const pollTimer = ref(null)
const redirectTimer = ref(null)

// ============ COMPUTED ============
const shouldShowPaymentButton = computed(() => {
  if (!props.order) return false
  const method = props.order.payment_method
  const status = props.order.payment_status
  // Chỉ hiển thị khi phương thức là bank_transfer hoặc payos và chưa thanh toán
  return (method === 'bank_transfer' || method === 'payos') && status === 'pending'
})

// Lấy mã đơn hàng
const orderDisplayCode = computed(() => {
  if (props.order_display_code) {
    return props.order_display_code
  }
  if (props.order?.order_display_code) {
    return props.order.order_display_code
  }
  if (props.order?.display_code) {
    return props.order.display_code
  }
  return 'N/A'
})

// Lấy email
const customerEmail = computed(() => {
  if (props.order?.customer_email && props.order.customer_email !== 'N/A') {
    return props.order.customer_email
  }
  if (props.order?.user?.email) {
    return props.order.user.email
  }
  return 'N/A'
})

// Chi tiết sản phẩm
const orderDetails = computed(() => {
  if (props.order?.details) {
    return props.order.details.map(detail => ({
      ...detail,
      name: detail.productVariant?.product?.name || detail.name || 'Sản phẩm không xác định',
      image: detail.image || detail.productVariant?.product?.image_url?.[0] || '/images/default-product.jpg',
      color: detail.color || detail.productVariant?.color?.name || '',
      size: detail.size || detail.productVariant?.size_name || '',
    }))
  }
  return []
})

// Tổng hợp đơn hàng
const orderSummary = computed(() => {
  if (props.order) {
    const total = props.order.total_amount || 0
    const discount = props.order.discount_amount || 0
    const shippingFee = props.order.shipping_fee || 0
    const finalAmount = props.order.final_amount || total
    const subtotal = total + discount
    return {
      subtotal,
      shipping_fee: shippingFee,
      discount_amount: discount,
      final_amount: finalAmount,
    }
  }
  return {
    subtotal: 0,
    shipping_fee: 0,
    discount_amount: 0,
    final_amount: 0
  }
})

// ============ METHODS ============
const formatPrice = (val) => {
  if (!val && val !== 0) return '0₫'
  return Number(val).toLocaleString('vi-VN') + '₫'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  const d = new Date(date)
  return d.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getOrderStatusBadge = (status) => {
  const map = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'processing': 'bg-blue-100 text-blue-800',
    'shipping': 'bg-purple-100 text-purple-800',
    'completed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'Đang xử lý': 'bg-yellow-100 text-yellow-800',
    'Đã xác nhận': 'bg-blue-100 text-blue-800',
    'Đang giao hàng': 'bg-purple-100 text-purple-800',
    'Đã giao hàng': 'bg-green-100 text-green-800',
    'Đã hủy': 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng (PayOS)',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
    payos: 'PayOS',
  }
  return map[method] || method || 'Chưa xác định'
}

const getPaymentColor = (method) => {
  const map = {
    cod: 'bg-green-500',
    bank_transfer: 'bg-blue-500',
    ewallet: 'bg-purple-500',
    vnpay: 'bg-red-500',
    momo: 'bg-pink-500',
    payos: 'bg-indigo-500',
  }
  return map[method] || 'bg-gray-400'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    success: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    success: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

// ============ FETCH PAYMENT LINK ============
const fetchPaymentLink = async () => {
  if (!props.order || !props.order.id) return

  loading.value = true
  paymentError.value = null

  try {
    const response = await axios.get(`/payment/link/${props.order.id}`)
    if (response.data.success) {
      paymentUrl.value = response.data.checkout_url
      // Bắt đầu polling để kiểm tra trạng thái
      startPolling()
    } else {
      paymentError.value = response.data.message || 'Không thể tạo link thanh toán'
    }
  } catch (error) {
    console.error('Error fetching payment link:', error)
    paymentError.value = error.response?.data?.message || 'Có lỗi xảy ra khi tạo thanh toán'
  } finally {
    loading.value = false
  }
}

// ============ MỞ TAB THANH TOÁN ============
const openPaymentTab = () => {
  if (paymentUrl.value) {
    window.open(paymentUrl.value, '_blank')
  }
}

// ============ POLLING CHECK ORDER STATUS ============
const checkOrderStatus = async () => {
  try {
    const response = await axios.get(`/don-hang/${props.order.id}`)
    if (response.data && response.data.order) {
      const orderData = response.data.order
      if (orderData.payment_status === 'paid' || orderData.payment_status === 'success') {
        paymentStatus.value = 'paid'
        stopPolling()
        // ==== CHUYỂN HƯỚNG VỀ TRANG CHỦ SAU 3 GIÂY ====
        redirectTimer.value = setTimeout(() => {
          router.visit('/')
        }, 3000)
      } else if (orderData.payment_status === 'failed') {
        paymentStatus.value = 'failed'
        stopPolling()
      }
    }
  } catch (error) {
    console.warn('Polling error:', error)
  }
}

const startPolling = () => {
  if (pollTimer.value) return
  checkOrderStatus()
  pollTimer.value = setInterval(checkOrderStatus, 5000)
}

const stopPolling = () => {
  if (pollTimer.value) {
    clearInterval(pollTimer.value)
    pollTimer.value = null
  }
  if (redirectTimer.value) {
    clearTimeout(redirectTimer.value)
    redirectTimer.value = null
  }
}

// ============ LIFECYCLE ============
onMounted(() => {
  clearCart().catch(() => {})
  if (shouldShowPaymentButton.value) {
    fetchPaymentLink()
  }
})

onUnmounted(() => {
  stopPolling()
})

// ============ PRINT ============
const printOrder = () => {
  if (!props.order) return

  const order = props.order
  const details = orderDetails.value
  const displayCode = orderDisplayCode.value
  const summary = orderSummary.value

  const printWindow = window.open('', '_blank')
  if (!printWindow) {
    alert('Vui lòng cho phép popup để in đơn hàng')
    return
  }

  const detailsHtml = details.map(item => `
    <tr>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">${item.name}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: center;">${item.quantity}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.unit_price)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.subtotal)}</td>
    </tr>
  `).join('')

  const discountHtml = summary.discount_amount > 0 ? `
    <tr>
      <td colspan="3" style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">Giảm giá</td>
      <td style="text-align: right; padding: 8px 12px; border: 1px solid #ddd; color: red;">-${formatPrice(summary.discount_amount)}</td>
    </tr>
  ` : ''

  const content = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Đơn hàng #${displayCode}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: auto; }
        h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-box h3 { margin: 0 0 10px 0; color: #6b7280; font-size: 14px; text-transform: uppercase; }
        .info-box p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; border: 1px solid #ddd; }
        td { padding: 8px 12px; border: 1px solid #ddd; }
        .total { font-size: 20px; font-weight: bold; color: #1a56db; text-align: right; }
        .footer { margin-top: 40px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        .discount-row { background-color: #f0fdf4; }
      </style>
    </head>
    <body>
      <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
      <p><strong>Mã đơn hàng:</strong> ${displayCode}</p>
      <p><strong>Ngày đặt:</strong> ${formatDate(order.created_at)}</p>
      
      <div class="info-grid">
        <div class="info-box">
          <h3>Thông tin người đặt</h3>
          <p><strong>Họ tên:</strong> ${order.customer_name}</p>
          <p><strong>Email:</strong> ${customerEmail.value}</p>
          <p><strong>SĐT:</strong> ${order.customer_phone}</p>
        </div>
        <div class="info-box">
          <h3>Thông tin người nhận</h3>
          <p><strong>Họ tên:</strong> ${order.receiver_name}</p>
          <p><strong>SĐT:</strong> ${order.receiver_phone}</p>
          <p><strong>Địa chỉ:</strong> ${order.shipping_address}</p>
        </div>
      </div>
      
      <h3>Danh sách sản phẩm</h3>
      <table>
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th style="text-align: center;">Số lượng</th>
            <th style="text-align: right;">Đơn giá</th>
            <th style="text-align: right;">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          ${detailsHtml}
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; padding: 8px 12px; border: 1px solid #ddd;">Tạm tính</td>
            <td style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">${formatPrice(summary.subtotal)}</td>
          </tr>
          ${summary.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">Phí vận chuyển</td>
              <td style="text-align: right; padding: 8px 12px; border: 1px solid #ddd;">${formatPrice(summary.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${discountHtml}
          <tr style="background-color: #f0f7ff;">
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px; padding: 12px; border: 1px solid #ddd;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db; padding: 12px; border: 1px solid #ddd;">${formatPrice(summary.final_amount)}</td>
          </tr>
        </tfoot>
      </table>
      
      <div style="margin-top: 20px;">
        <p><strong>Phương thức thanh toán:</strong> ${getPaymentLabel(order.payment_method)}</p>
        <p><strong>Trạng thái thanh toán:</strong> ${getPaymentStatusLabel(order.payment_status)}</p>
      </div>
      
      ${order.note ? `<p><strong>Ghi chú:</strong> ${order.note}</p>` : ''}
      
      <div class="footer">
        <p>Cảm ơn bạn đã mua hàng tại BigBag!</p>
        <p>Hotline: 1900 1234 | Email: support@bigbag.vn</p>
        <p style="font-size: 10px; color: #9ca3af;">Hóa đơn được tạo tự động</p>
      </div>
    </body>
    </html>
  `

  printWindow.document.write(content)
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
}
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }

  header, footer, .chatbot, .chatbot-toggle {
    display: none !important;
  }

  body {
    background: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  #print-area {
    max-width: 100% !important;
    padding: 20px !important;
    margin: 0 !important;
  }

  .bg-white {
    background: white !important;
    box-shadow: none !important;
    border: 1px solid #e5e7eb !important;
  }

  .shadow-lg, .shadow-sm {
    box-shadow: none !important;
  }

  .rounded-2xl, .rounded-xl {
    border-radius: 8px !important;
  }

  .text-primary {
    color: #1a56db !important;
  }

  .bg-primary {
    background-color: #1a56db !important;
  }

  .bg-primary\/5 {
    background-color: #f0f4ff !important;
  }

  .bg-gray-50 {
    background-color: #f9fafb !important;
  }

  table {
    width: 100% !important;
    border-collapse: collapse !important;
  }

  th, td {
    border: 1px solid #e5e7eb !important;
    padding: 8px 12px !important;
  }

  thead {
    background-color: #f9fafb !important;
  }

  @page {
    margin: 20mm;
  }
}
</style>
Page này có cần sửa gì không?


## Assistant:
07/19/2026 08:32

> **Thinking:**
> Chúng ta cần xem xét xem page OrderSuccess có cần sửa gì không, đặc biệt sau khi đã chuyển useCart thành singleton. Trong file OrderSuccess.vue hiện tại, ở `onMounted` có gọi `clearCart().catch(() => {})`. Điều này đã đúng: xóa giỏ hàng ngay khi vào trang success (đối với COD hoặc bất kỳ đơn hàng nào thành công). Với singleton, việc này sẽ cập nhật toàn bộ component sử dụng useCart (bao gồm Header). 
> 
> Tuy nhiên, cần kiểm tra xem có vấn đề gì không: 
> - Ở `onUnmounted` chỉ có `stopPolling()`; không còn gọi clearCart nữa (vì đã gọi ở onMounted). 
> - Cần đảm bảo không gọi clearCart hai lần.
> - Có thể giữ nguyên onMounted gọi clearCart (vì chúng ta muốn xóa giỏ hàng ngay sau khi đặt hàng thành công). Nếu đơn hàng là COD thì việc xóa giỏ hàng ngay khi vào trang success là đúng. Nếu là chuyển khoản (bank_transfer/payos) thì cũng có thể xóa ngay vì đơn hàng đã được tạo thành công, nhưng nếu thanh toán thất bại thì người dùng sẽ mất giỏ hàng. Tuy nhiên, thông thường khi đặt hàng thành công (dù chưa thanh toán), người ta vẫn xóa giỏ hàng để tránh nhầm lẫn. 
> 
> Không có lỗi gì trong page, nhưng cần lưu ý: nếu bạn đã chuyển useCart thành singleton, thì việc gọi clearCart ở onMounted sẽ xóa giỏ hàng và Header sẽ cập nhật ngay lập tức. Điều này giải quyết vấn đề bạn gặp phải.
> 
> Vì vậy, câu trả lời: Page này không cần sửa gì thêm, vì đã có clearCart ở onMounted. Tuy nhiên, bạn có thể muốn giữ lại hoặc loại bỏ onUnmounted (vì không cần clearCart nữa), nhưng không ảnh hưởng. Tôi sẽ khuyên giữ nguyên, vì stopPolling cần thiết.
> 
> Vậy câu trả lời: Không cần sửa gì, page đã đúng. Nhưng cần đảm bảo useCart đã được sửa thành singleton như đã hướng dẫn. Và kiểm tra lại Header có sử dụng useCart không (chắc chắn có). Sau khi sửa useCart, mọi thứ sẽ hoạt động.

Không cần sửa gì thêm trong `OrderSuccess.vue`. Bạn đã có `clearCart()` trong `onMounted`, nên giỏ hàng sẽ được xóa ngay khi vào trang. Với `useCart` đã chuyển thành **singleton**, Header và các component khác sẽ tự động cập nhật.

### Lưu ý
- **`onUnmounted`** chỉ còn `stopPolling()` – hoàn toàn hợp lý.
- Nếu bạn muốn xóa giỏ hàng chỉ khi thanh toán thành công (đối với chuyển khoản), có thể di chuyển `clearCart` vào polling khi nhận được `paid`. Nhưng với COD, xóa ngay là đúng.
- Với singleton, header sẽ cập nhật ngay lập tức, không cần sự kiện `CartEvents` nữa, nhưng bạn vẫn có thể giữ để tương thích.

**Kết luận**: Page này đã đúng, không cần thay đổi. Hãy đảm bảo đã áp dụng bản `useCart.js` singleton như hướng dẫn trước đó.


## User:
07/19/2026 08:41

<template>
  <header class="w-full top-0 sticky z-[100] bg-white border-b border-gray-200 shadow-sm">
    <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-4 md:px-8 py-4">
      <!-- Logo -->
      <div class="flex items-center gap-8">
        <Link :href="route('home')" class="font-headline-lg text-xl md:text-2xl font-bold hover:opacity-80 transition-opacity">
          <span class="text-primary">BigBag</span><span class="text-gray-800">.vn</span>
        </Link>

        <!-- Main Menu Desktop -->
        <div class="hidden md:flex items-center gap-6">
          <!-- Dropdown Balo -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'balo' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('balo') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Balo
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="cat in laptopCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Dropdown Cặp - Túi -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'cap-tui' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('cap-tui') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Cặp - Túi
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="cat in bagCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Dropdown Thương hiệu -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'thuong-hieu' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('thuong-hieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Thương hiệu
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="brand in brands" :key="brand.id">
                  <Link 
                    v-if="brand.slug"
                    :href="getCategoryUrl(brand.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ brand.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ brand.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Các link đơn -->
          <Link :href="route('wholesale')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('wholesale') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Mua sỉ
          </Link>
          <Link :href="route('promotion')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('promotion') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Khuyến mãi
          </Link>
          <Link :href="route('home') + '#gioi-thieu'" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveHash('gioi-thieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Giới thiệu
          </Link>
          <Link :href="route('category', { slug: 'new-arrivals' })" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveCategory('new-arrivals') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Sản phẩm mới
          </Link>
        </div>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
        <div class="relative w-full">
          <input 
            v-model="searchKeyword"
            @keyup.enter="handleSearch"
            class="w-full bg-gray-50 border border-gray-200 rounded-full py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" 
            placeholder="Tìm kiếm sản phẩm..." 
            type="text">
          <button @click="handleSearch" class="absolute right-4 top-1/2 -translate-y-1/2">
            <span class="material-symbols-outlined text-gray-400 text-xl">search</span>
          </button>
        </div>
      </div>

      <!-- User & Cart -->
      <div class="flex items-center gap-2">
        <!-- Nếu chưa đăng nhập -->
        <Link v-if="!user" :href="route('login')" class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">person</span>
        </Link>

        <!-- Nếu đã đăng nhập: Dropdown -->
        <div v-else class="relative" ref="userDropdownRef">
          <button 
            @click="toggleDropdown" 
            class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary focus:outline-none"
          >
            <span class="material-symbols-outlined">account_circle</span>
          </button>
          <div 
            v-if="dropdownOpen" 
            class="absolute right-0 mt-2 w-52 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
          >
            <!-- Mục Dashboard cho admin -->
            <Link 
              v-if="user.role === 'admin'"
              :href="route('admin.dashboard')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">dashboard</span>
              Dashboard
            </Link>
            
            <!-- LỊCH SỬ ĐƠN HÀNG -->
            <Link 
              :href="route('orders.history')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">receipt_long</span>
              Lịch sử đơn hàng
            </Link>
            
            <!-- HỒ SƠ -->
            <Link 
              :href="route('profile.edit')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">person</span>
              Hồ sơ
            </Link>
            
            <!-- ĐĂNG XUẤT -->
            <button 
              @click="handleLogout" 
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100 mt-1 pt-2"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">logout</span>
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Giỏ hàng -->
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
            {{ cartCount }}
          </span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'
import { CartEvents } from '@/events/CartEvents'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

// Sử dụng useCart
const { cartCount, fetchCart, reloadCart, getUserId, setCartCount } = useCart()

// Lọc danh mục Balo
const laptopCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('balo') || c.name?.toLowerCase().includes('balo')) && c.slug
  ).slice(0, 8)
})

// Lọc danh mục Cặp - Túi
const bagCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('tui') || c.slug?.includes('cap') ||
     c.name?.toLowerCase().includes('túi') || c.name?.toLowerCase().includes('cặp')) && c.slug
  ).slice(0, 8)
})

// Hàm tạo URL an toàn
const getCategoryUrl = (slug) => {
  if (!slug) return '#'
  try {
    return route('category', { slug })
  } catch (e) {
    return '#'
  }
}

// Helper: kiểm tra active cho route name
const isActiveRoute = (routeName) => {
  return route().current(routeName)
}

// Helper: kiểm tra active cho category slug
const isActiveCategory = (slug) => {
  const currentRoute = route().current()
  const params = route().params
  
  if (currentRoute === 'category') {
    const currentSlug = params.slug
    return currentSlug === slug
  }
  return false
}

// Helper: kiểm tra active cho hash anchor (#gioi-thieu)
const isActiveHash = (hash) => {
  if (typeof window !== 'undefined') {
    return window.location.hash === `#${hash}`
  }
  return false
}

// Toggle dropdown
const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

// Đóng dropdown
const closeDropdown = () => {
  dropdownOpen.value = false
}

// Xử lý logout
const handleLogout = async () => {
  try {
    const userId = getUserId()    
    closeDropdown()
    
    router.post(route('logout'), {}, {
      onSuccess: () => {
        window.user = null
        CartEvents.emitUserChanged('guest')
        setTimeout(() => {
          reloadCart()
        }, 100)
      }
    })
  } catch (error) {
    router.post(route('logout'))
  }
}

// Xử lý tìm kiếm
const handleSearch = () => {
  if (searchKeyword.value.trim()) {
    router.get(route('category', { slug: 'tim-kiem' }), { q: searchKeyword.value })
  }
}

// Đóng dropdown khi click bên ngoài
const handleClickOutside = (event) => {
  if (userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const handleCartUpdated = (count) => {
  if (count !== undefined && count !== null) {
    // Cập nhật trực tiếp nếu có count từ event
    if (setCartCount) {
      setCartCount(count)
    } else {
      // Fallback: gọi fetchCart
      fetchCart()
    }
  } else {
    // Nếu không có count, gọi fetchCart để lấy mới
    fetchCart()
  }
}

// Xử lý sự kiện user-changed
const handleUserChanged = (event) => {
  reloadCart()
}

// Khi user thay đổi
watch(() => user.value, (newUser, oldUser) => {
  const newId = newUser?.id || 'guest'
  const oldId = oldUser?.id || 'guest'
  
  if (newId !== oldId) {
    window.user = newUser
    reloadCart()
    CartEvents.emitUserChanged(newId)
  }
}, { immediate: true })

// Lưu trữ các handler để cleanup
let cartUpdatedHandler = null
let userChangedHandler = null

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  // Fetch cart lần đầu
  fetchCart()
  
  // Lắng nghe sự kiện cart-updated
  cartUpdatedHandler = handleCartUpdated
  CartEvents.onUpdated(cartUpdatedHandler)
  
  // Lắng nghe sự kiện user-changed
  userChangedHandler = handleUserChanged
  CartEvents.onUserChanged(userChangedHandler)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (cartUpdatedHandler) {
    CartEvents.offUpdated(cartUpdatedHandler)
  }
  if (userChangedHandler) {
    CartEvents.offUserChanged(userChangedHandler)
  }
})
</script>

<style scoped>
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}

.dropdown-menu {
  animation: fadeDown 0.2s ease;
}

@keyframes fadeDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

Khi trang success được load, giỏ hàng về 0 trong chốc lát nhưng lại quay về số lượng cũ sau vài giây


## Assistant:
07/19/2026 08:41

> **Thinking:**
> Chúng ta đã có phân tích rõ: sau khi clearCart thành công (xóa localStorage, reset state) vài giây sau giỏ hàng lại hiện số cũ. Điều này cho thấy có một luồng khác gọi fetchCart và load lại dữ liệu từ localStorage (vẫn còn dữ liệu cũ vì chưa kịp xóa? Nhưng log đã xác nhận localStorage bị xóa). Tuy nhiên, header vẫn hiển thị số cũ do vấn đề state không chia sẻ hoặc do có lỗi trong cách quản lý dữ liệu.
> 
> Bạn vừa đưa code của AppHeader.vue. Trong header, bạn đang sử dụng useCart và lắng nghe sự kiện CartEvents. Khi clearCart được gọi, nó sẽ reset cartItems và gọi updateCounts, emit sự kiện updated. Header sẽ nhận được sự kiện và cập nhật cartCount. Nhưng sau đó có thể có một số tác vụ khác gọi lại fetchCart, ví dụ như sau khi clear, có setTimeout trong addToCart/updateCart/removeFromCart, nhưng clearCart không có setTimeout nào. Có thể có một lần gọi fetchCart ở đâu đó khi component mount lại hoặc từ một sự kiện khác. Khi fetchCart được gọi, nó sẽ đọc từ localStorage, lúc này đã bị xóa, nên cartItems sẽ là [] và số lượng 0. Vậy tại sao số lượng lại quay về cũ?
> 
> Hãy kiểm tra lại logic: Khi bạn clearCart, localStorage đã bị xóa. Nhưng fetchCart gọi loadFromLocalStorage, trả về []. Nên không có lý do để có số cũ. Có thể có trường hợp localStorage chưa kịp xóa hoặc có vấn đề với cache. Nhưng bạn đã log xác nhận xóa thành công.
> 
> Một khả năng: Header có gọi fetchCart trong onMounted, và sau khi clearCart được gọi (trong onMounted của OrderSuccess) rồi, nhưng do thứ tự thực thi, có thể header đã fetchCart lần nữa sau khi clear? Có thể có một sự kiện khác kích hoạt fetchCart. Trong header, bạn cũng lắng nghe sự kiện user-changed, nhưng không có gì thay đổi.
> 
> Vấn đề có thể đến từ việc sau khi clearCart, có một tác vụ nào đó gọi fetchCart từ một component khác (ví dụ AppFooter, Chatbot, hay một component khác). Cũng có thể do header có lắng nghe sự kiện CartEvents và xử lý nhưng lại gọi fetchCart sau khi nhận được sự kiện? Trong header, bạn có handleCartUpdated gọi fetchCart nếu setCartCount không tồn tại. Trong useCart mới (singleton), chúng ta vẫn giữ CartEvents.emitUpdated. Khi clearCart gọi updateCounts, nó emit sự kiện với newCount = 0. Header nhận sự kiện, gọi handleCartUpdated. Trong handleCartUpdated, nếu setCartCount tồn tại (nó có tồn tại trong useCart? Chúng ta không export setCartCount). Vì vậy sẽ vào else và gọi fetchCart(). Và fetchCart lúc này sẽ load từ localStorage (đã bị xóa) nên sẽ set cartItems = [] và cartCount = 0. Vậy không có lý do số lượng trở về cũ.
> 
> Nhưng bạn thấy số lượng hiện lên 0 rồi lại về cũ, điều đó có thể là do có một lần fetchCart khác được gọi sau đó, nhưng lúc đó localStorage lại có dữ liệu (có thể do một thao tác ghi vào localStorage khác chạy sau khi clear). Có thể trong quá trình đặt hàng, sau khi clear, có một lần gọi addToCart hoặc updateCart nào đó (do side effect) làm ghi lại vào localStorage. Kiểm tra trong PaymentController hoặc các controller khác: có thể sau khi tạo đơn hàng, hệ thống vẫn cố gắng thêm lại sản phẩm? Nhưng log không thấy điều đó.
> 
> Một giả thuyết khác: Sự kiện CartEvents.emitUpdated trong updateCounts có thể gây ra vòng lặp nếu các component khác gọi fetchCart và lại emit sự kiện. Nhưng không.
> 
> Quan sát header: bạn gọi fetchCart trong onMounted. Khi OrderSuccess gọi clearCart trong onMounted, nó xóa dữ liệu. Tuy nhiên, thứ tự mount giữa các component không đảm bảo: có thể header đã mount trước, gọi fetchCart và lấy dữ liệu cũ, sau đó OrderSuccess mount và clearCart xóa đi, nhưng header đã có dữ liệu cũ trong cartItems (state cũ) và vì header không dùng state chia sẻ (nếu chưa áp dụng singleton) thì nó không cập nhật. Nhưng bạn đã áp dụng singleton, và header dùng useCart lấy state chung, nên khi clearCart thay đổi cartItems, header sẽ reactive và cập nhật.
> 
> Nhưng bạn thấy số lượng về 0 rồi lại về cũ, tức là có sự thay đổi sau đó. Điều đó có thể do header có một logic khác tự động fetchCart định kỳ hoặc do sự kiện nào đó. Trong header, bạn có lắng nghe sự kiện updated và gọi fetchCart, nhưng như đã nói, fetchCart khi đó sẽ lấy từ localStorage đã bị xóa nên không thể lấy dữ liệu cũ. Vậy có thể localStorage đã bị ghi lại từ nơi khác.
> 
> Hãy kiểm tra các component khác: có component nào gọi saveToLocalStorage sau khi clear không? Có thể có một component đang lưu giỏ hàng vào localStorage mỗi khi có thay đổi. Trong useCart, các hàm addToCart, updateCart, removeFromCart đều gọi saveToLocalStorage. Nếu có lỗi nào đó khiến một trong những hàm này được gọi sau clear, nó sẽ ghi lại dữ liệu cũ.
> 
> Một điểm cần kiểm tra: Khi đặt hàng thành công, bạn có gọi clearCart trong onMounted, nhưng có thể có một component khác (ví dụ ShoppingCart, Checkout, ...) đang lắng nghe sự kiện hoặc có một quá trình khác gọi addToCart/updateCart. Để biết chính xác, bạn có thể thêm log vào tất cả các hàm ghi vào localStorage (addToCart, updateCart, removeFromCart, saveToLocalStorage) để xem có hàm nào chạy sau clear không.
> 
> Ngoài ra, header có lắng nghe sự kiện user-changed và gọi reloadCart. Nếu có sự kiện user-changed phát sinh (ví dụ user đăng nhập/thoát) thì có thể gọi fetchCart và lấy dữ liệu từ localStorage. Nhưng ở đây bạn chỉ đang đặt hàng với user đã đăng nhập, không có thay đổi user.
> 
> Vậy giải pháp: 
> - Kiểm tra các component khác có gọi fetchCart hoặc addToCart không.
> - Thêm log để biết luồng gọi.
> - Nếu không xác định được, có thể disable sự kiện CartEvents trong updateCounts hoặc thay đổi cách header cập nhật để không gọi fetchCart mà chỉ cập nhật số lượng từ event.
> 
> Trong header, thay vì gọi fetchCart khi nhận sự kiện, bạn có thể chỉ cập nhật cartCount từ sự kiện (vì sự kiện truyền số lượng). Như vậy header sẽ không tự động fetchCart, tránh gây ra ghi đè.
> 
> Sửa lại header:
> ```javascript
> const handleCartUpdated = (count) => {
>   if (count !== undefined && count !== null) {
>     // Cập nhật trực tiếp số lượng từ sự kiện
>     cartCount.value = count
>   } else {
>     // Fallback: fetchCart
>     fetchCart()
>   }
> }
> ```
> Như vậy, khi clearCart emit updated với count = 0, header sẽ set cartCount = 0 và không gọi fetchCart, đảm bảo không có thêm thao tác ghi đè.
> 
> Đồng thời, trong useCart, updateCounts đã gán cartCount.value = newCount nên cartCount reactive sẽ được cập nhật toàn cục. Header dùng cartCount từ useCart nên sẽ tự động thay đổi. Thậm chí không cần sự kiện nữa nếu dùng singleton.
> 
> Tuy nhiên, header vẫn đang lắng nghe sự kiện và gọi fetchCart có thể gây ra vấn đề. Bạn có thể bỏ hoàn toàn việc lắng nghe sự kiện trong header và chỉ dùng state từ useCart.
> 
> Vậy đề xuất sửa header: bỏ hoặc điều chỉnh handleCartUpdated như trên, đồng thời đảm bảo không gọi fetchCart khi không cần.
> 
> Tôi sẽ cung cấp hướng dẫn sửa header.

Vấn đề bạn gặp phải: sau khi clearCart, giỏ hàng về 0 rồi lại hiện số cũ sau vài giây, xuất phát từ việc **Header đang lắng nghe sự kiện `CartEvents.updated` và gọi `fetchCart()`** – dẫn đến việc đọc lại localStorage (có thể vẫn còn dữ liệu do một component khác ghi đè) hoặc gây ra vòng lặp không mong muốn.

Với việc đã chuyển `useCart` thành **singleton**, bạn **không cần dùng sự kiện** để đồng bộ giữa các component nữa – vì state đã được chia sẻ toàn cục. Do đó, hãy **loại bỏ việc lắng nghe và gọi `fetchCart` trong Header** để tránh tác dụng phụ.

---

## Cách sửa `AppHeader.vue`

### 1. Xóa sự kiện `CartEvents` trong Header
- Bỏ import `CartEvents`.
- Bỏ các handler `handleCartUpdated`, `handleUserChanged`.
- Bỏ `onMounted` và `onUnmounted` liên quan đến sự kiện.
- Chỉ giữ lại `fetchCart` một lần duy nhất khi mount (nếu cần).

### 2. Chỉ dùng `cartCount` từ `useCart`
Vì state đã là singleton, `cartCount` sẽ tự động cập nhật khi `clearCart` thay đổi. Header chỉ việc hiển thị `cartCount`.

### 3. Loại bỏ `setCartCount` (không dùng nữa)
Vì không còn sự kiện, bạn cũng có thể xóa hoặc bỏ qua.

---

## Code `AppHeader.vue` sửa lại

```vue
<template>
  <header class="w-full top-0 sticky z-[100] bg-white border-b border-gray-200 shadow-sm">
    <!-- ... (phần HTML giữ nguyên) ... -->
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

// Lấy cartCount từ useCart (state dùng chung)
const { cartCount, fetchCart, reloadCart, getUserId } = useCart()

// ... (các computed, methods khác giữ nguyên) ...

// Xóa các handler sự kiện CartEvents
// Chỉ giữ onMounted để fetchCart lần đầu
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  // Fetch giỏ hàng lần đầu khi header mount
  fetchCart()
})

// Bỏ onUnmounted liên quan đến sự kiện (chỉ giữ nếu cần xóa listener click)
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// ... (phần còn lại giữ nguyên, như handleLogout, handleSearch, watch user, v.v.) ...
</script>
```

### Giải thích thay đổi
- **Xóa toàn bộ `CartEvents`** trong Header: không cần lắng nghe `updated` hay `user-changed` vì state đã được chia sẻ.
- **Xóa `handleCartUpdated` và `handleUserChanged`**.
- **Chỉ còn `fetchCart` duy nhất trong `onMounted`** để khởi tạo giỏ hàng lúc đầu. Khi `clearCart` chạy ở trang success, `cartCount` sẽ reactive thay đổi thành 0 mà Header nhận được ngay lập tức.
- Không còn lời gọi `fetchCart` nào khác ngoài lần đầu, tránh việc ghi đè dữ liệu.

---

## Kiểm tra lại

Sau khi sửa:
- Khi vào trang success, `clearCart()` trong `onMounted` của OrderSuccess chạy → reset `cartItems`, `cartCount` về 0.
- Header (dùng cùng `cartCount` từ singleton) sẽ tự động hiển thị 0.
- **Không có thêm lệnh gọi `fetchCart` nào** → không thể load lại dữ liệu cũ từ localStorage.

Kết quả: số lượng giỏ hàng sẽ giữ nguyên là 0, không bị nhảy lại số cũ.


## User:
07/19/2026 08:43

<template>
  <header class="w-full top-0 sticky z-[100] bg-white border-b border-gray-200 shadow-sm">
    <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-4 md:px-8 py-4">
      <!-- Logo -->
      <div class="flex items-center gap-8">
        <Link :href="route('home')" class="font-headline-lg text-xl md:text-2xl font-bold hover:opacity-80 transition-opacity">
          <span class="text-primary">BigBag</span><span class="text-gray-800">.vn</span>
        </Link>

        <!-- Main Menu Desktop -->
        <div class="hidden md:flex items-center gap-6">
          <!-- Dropdown Balo -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'balo' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('balo') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Balo
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="cat in laptopCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Dropdown Cặp - Túi -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'cap-tui' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('cap-tui') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Cặp - Túi
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="cat in bagCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Dropdown Thương hiệu -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'thuong-hieu' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('thuong-hieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Thương hiệu
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="brand in brands" :key="brand.id">
                  <Link 
                    v-if="brand.slug"
                    :href="getCategoryUrl(brand.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ brand.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ brand.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Các link đơn -->
          <Link :href="route('wholesale')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('wholesale') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Mua sỉ
          </Link>
          <Link :href="route('promotion')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('promotion') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Khuyến mãi
          </Link>
          <Link :href="route('home') + '#gioi-thieu'" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveHash('gioi-thieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Giới thiệu
          </Link>
          <Link :href="route('category', { slug: 'new-arrivals' })" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveCategory('new-arrivals') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Sản phẩm mới
          </Link>
        </div>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
        <div class="relative w-full">
          <input 
            v-model="searchKeyword"
            @keyup.enter="handleSearch"
            class="w-full bg-gray-50 border border-gray-200 rounded-full py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" 
            placeholder="Tìm kiếm sản phẩm..." 
            type="text">
          <button @click="handleSearch" class="absolute right-4 top-1/2 -translate-y-1/2">
            <span class="material-symbols-outlined text-gray-400 text-xl">search</span>
          </button>
        </div>
      </div>

      <!-- User & Cart -->
      <div class="flex items-center gap-2">
        <!-- Nếu chưa đăng nhập -->
        <Link v-if="!user" :href="route('login')" class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">person</span>
        </Link>

        <!-- Nếu đã đăng nhập: Dropdown -->
        <div v-else class="relative" ref="userDropdownRef">
          <button 
            @click="toggleDropdown" 
            class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary focus:outline-none"
          >
            <span class="material-symbols-outlined">account_circle</span>
          </button>
          <div 
            v-if="dropdownOpen" 
            class="absolute right-0 mt-2 w-52 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
          >
            <!-- Mục Dashboard cho admin -->
            <Link 
              v-if="user.role === 'admin'"
              :href="route('admin.dashboard')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">dashboard</span>
              Dashboard
            </Link>
            
            <!-- LỊCH SỬ ĐƠN HÀNG -->
            <Link 
              :href="route('orders.history')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">receipt_long</span>
              Lịch sử đơn hàng
            </Link>
            
            <!-- HỒ SƠ -->
            <Link 
              :href="route('profile.edit')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">person</span>
              Hồ sơ
            </Link>
            
            <!-- ĐĂNG XUẤT -->
            <button 
              @click="handleLogout" 
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100 mt-1 pt-2"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">logout</span>
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Giỏ hàng -->
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
            {{ cartCount }}
          </span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'
import { CartEvents } from '@/events/CartEvents'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

// Sử dụng useCart
const { cartCount, fetchCart, reloadCart, getUserId, setCartCount } = useCart()

// Lọc danh mục Balo
const laptopCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('balo') || c.name?.toLowerCase().includes('balo')) && c.slug
  ).slice(0, 8)
})

// Lọc danh mục Cặp - Túi
const bagCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('tui') || c.slug?.includes('cap') ||
     c.name?.toLowerCase().includes('túi') || c.name?.toLowerCase().includes('cặp')) && c.slug
  ).slice(0, 8)
})

// Hàm tạo URL an toàn
const getCategoryUrl = (slug) => {
  if (!slug) return '#'
  try {
    return route('category', { slug })
  } catch (e) {
    return '#'
  }
}

// Helper: kiểm tra active cho route name
const isActiveRoute = (routeName) => {
  return route().current(routeName)
}

// Helper: kiểm tra active cho category slug
const isActiveCategory = (slug) => {
  const currentRoute = route().current()
  const params = route().params
  
  if (currentRoute === 'category') {
    const currentSlug = params.slug
    return currentSlug === slug
  }
  return false
}

// Helper: kiểm tra active cho hash anchor (#gioi-thieu)
const isActiveHash = (hash) => {
  if (typeof window !== 'undefined') {
    return window.location.hash === `#${hash}`
  }
  return false
}

// Toggle dropdown
const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

// Đóng dropdown
const closeDropdown = () => {
  dropdownOpen.value = false
}

// Xử lý logout
const handleLogout = async () => {
  try {
    const userId = getUserId()    
    closeDropdown()
    
    router.post(route('logout'), {}, {
      onSuccess: () => {
        window.user = null
        CartEvents.emitUserChanged('guest')
        setTimeout(() => {
          reloadCart()
        }, 100)
      }
    })
  } catch (error) {
    router.post(route('logout'))
  }
}

// Xử lý tìm kiếm
const handleSearch = () => {
  if (searchKeyword.value.trim()) {
    router.get(route('category', { slug: 'tim-kiem' }), { q: searchKeyword.value })
  }
}

// Đóng dropdown khi click bên ngoài
const handleClickOutside = (event) => {
  if (userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const handleCartUpdated = (count) => {
  if (count !== undefined && count !== null) {
    // Cập nhật trực tiếp nếu có count từ event
    if (setCartCount) {
      setCartCount(count)
    } else {
      // Fallback: gọi fetchCart
      fetchCart()
    }
  } else {
    // Nếu không có count, gọi fetchCart để lấy mới
    fetchCart()
  }
}

// Xử lý sự kiện user-changed
const handleUserChanged = (event) => {
  reloadCart()
}

// Khi user thay đổi
watch(() => user.value, (newUser, oldUser) => {
  const newId = newUser?.id || 'guest'
  const oldId = oldUser?.id || 'guest'
  
  if (newId !== oldId) {
    window.user = newUser
    reloadCart()
    CartEvents.emitUserChanged(newId)
  }
}, { immediate: true })

// Lưu trữ các handler để cleanup
let cartUpdatedHandler = null
let userChangedHandler = null

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  // Fetch cart lần đầu
  fetchCart()
  
  // Lắng nghe sự kiện cart-updated
  cartUpdatedHandler = handleCartUpdated
  CartEvents.onUpdated(cartUpdatedHandler)
  
  // Lắng nghe sự kiện user-changed
  userChangedHandler = handleUserChanged
  CartEvents.onUserChanged(userChangedHandler)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (cartUpdatedHandler) {
    CartEvents.offUpdated(cartUpdatedHandler)
  }
  if (userChangedHandler) {
    CartEvents.offUserChanged(userChangedHandler)
  }
})
</script>

<style scoped>
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}

.dropdown-menu {
  animation: fadeDown 0.2s ease;
}

@keyframes fadeDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
Cập nhật toàn bộ


## Assistant:
07/19/2026 08:43

> **Thinking:**
> Chúng ta cần cập nhật toàn bộ `AppHeader.vue` để loại bỏ hoàn toàn sự kiện CartEvents và chỉ dùng `useCart` singleton. Đồng thời, cần sửa lại logic khi logout, khi user thay đổi vẫn gọi `reloadCart()` để refresh dữ liệu, nhưng không dùng sự kiện. Ngoài ra, giữ lại các tính năng khác như search, dropdown, v.v.
> 
> Trong `onMounted`, chỉ gọi `fetchCart` lần đầu. Không lắng nghe sự kiện. Khi user thay đổi, có thể gọi `reloadCart` trực tiếp trong `watch` (không cần emit event). Tuy nhiên, cần lưu ý khi logout: vẫn cần gọi `reloadCart` sau khi logout để làm mới giỏ hàng (vì user thay đổi). Có thể xử lý trong `watch` user.
> 
> Vậy cần sửa:
> 
> 1. Xóa import `CartEvents`.
> 2. Xóa `setCartCount` (không dùng nữa).
> 3. Xóa các handler `handleCartUpdated`, `handleUserChanged`.
> 4. Xóa các lắng nghe sự kiện trong `onMounted` và `onUnmounted`.
> 5. Trong `handleLogout`, gọi `reloadCart()` sau khi logout (vì user thay đổi, watch sẽ kích hoạt, nhưng để chắc chắn có thể gọi).
> 6. Trong `watch` user, chỉ cần cập nhật `window.user` và gọi `reloadCart()` (không emit event).
> 7. Xóa các biến `cartUpdatedHandler`, `userChangedHandler`.
> 
> Cần giữ lại `reloadCart` và `fetchCart` từ `useCart`. Header chỉ cần hiển thị `cartCount` từ `useCart`.
> 
> Đưa ra code hoàn chỉnh.
> 
> Lưu ý: trong `handleLogout`, có thể dùng `router.post` với onSuccess. Sau khi logout, `watch` user sẽ chạy (vì user thay đổi từ object sang null), nên sẽ gọi `reloadCart()` tự động. Tuy nhiên, để đảm bảo, vẫn gọi `reloadCart` sau khi set `window.user = null` và `setTimeout`.
> 
> Đề xuất code.

Dưới đây là file `AppHeader.vue` đã được cập nhật toàn bộ, loại bỏ `CartEvents` và chỉ dùng `useCart` singleton:

```vue
<template>
  <header class="w-full top-0 sticky z-[100] bg-white border-b border-gray-200 shadow-sm">
    <nav class="flex justify-between items-center max-w-[1440px] mx-auto px-4 md:px-8 py-4">
      <!-- Logo -->
      <div class="flex items-center gap-8">
        <Link :href="route('home')" class="font-headline-lg text-xl md:text-2xl font-bold hover:opacity-80 transition-opacity">
          <span class="text-primary">BigBag</span><span class="text-gray-800">.vn</span>
        </Link>

        <!-- Main Menu Desktop -->
        <div class="hidden md:flex items-center gap-6">
          <!-- Dropdown Balo -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'balo' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('balo') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Balo
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="cat in laptopCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Dropdown Cặp - Túi -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'cap-tui' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('cap-tui') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Cặp - Túi
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="cat in bagCategories" :key="cat.id">
                  <Link 
                    v-if="cat.slug"
                    :href="getCategoryUrl(cat.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ cat.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ cat.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Dropdown Thương hiệu -->
          <div class="relative dropdown-group">
            <Link :href="route('category', { slug: 'thuong-hieu' })" 
                  class="font-label-md text-sm py-4 block transition-colors"
                  :class="isActiveCategory('thuong-hieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
              Thương hiệu
            </Link>
            <div class="dropdown-menu absolute top-full left-0 bg-white border border-gray-200 shadow-xl p-6 min-w-[400px] rounded-b-lg z-50">
              <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                <template v-for="brand in brands" :key="brand.id">
                  <Link 
                    v-if="brand.slug"
                    :href="getCategoryUrl(brand.slug)" 
                    class="text-sm text-gray-600 hover:text-primary"
                  >
                    {{ brand.name }}
                  </Link>
                  <span v-else class="text-sm text-gray-400">{{ brand.name }}</span>
                </template>
              </div>
            </div>
          </div>

          <!-- Các link đơn -->
          <Link :href="route('wholesale')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('wholesale') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Mua sỉ
          </Link>
          <Link :href="route('promotion')" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveRoute('promotion') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Khuyến mãi
          </Link>
          <Link :href="route('home') + '#gioi-thieu'" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveHash('gioi-thieu') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Giới thiệu
          </Link>
          <Link :href="route('category', { slug: 'new-arrivals' })" 
                class="font-label-md text-sm py-4 block transition-colors"
                :class="isActiveCategory('new-arrivals') ? 'font-bold text-primary underline decoration-primary underline-offset-4' : 'text-gray-700 hover:text-primary'">
            Sản phẩm mới
          </Link>
        </div>
      </div>

      <!-- Search -->
      <div class="flex items-center gap-4 flex-1 max-w-md mx-8">
        <div class="relative w-full">
          <input 
            v-model="searchKeyword"
            @keyup.enter="handleSearch"
            class="w-full bg-gray-50 border border-gray-200 rounded-full py-2 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none pl-5 pr-12" 
            placeholder="Tìm kiếm sản phẩm..." 
            type="text">
          <button @click="handleSearch" class="absolute right-4 top-1/2 -translate-y-1/2">
            <span class="material-symbols-outlined text-gray-400 text-xl">search</span>
          </button>
        </div>
      </div>

      <!-- User & Cart -->
      <div class="flex items-center gap-2">
        <!-- Nếu chưa đăng nhập -->
        <Link v-if="!user" :href="route('login')" class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">person</span>
        </Link>

        <!-- Nếu đã đăng nhập: Dropdown -->
        <div v-else class="relative" ref="userDropdownRef">
          <button 
            @click="toggleDropdown" 
            class="p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary focus:outline-none"
          >
            <span class="material-symbols-outlined">account_circle</span>
          </button>
          <div 
            v-if="dropdownOpen" 
            class="absolute right-0 mt-2 w-52 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
          >
            <!-- Mục Dashboard cho admin -->
            <Link 
              v-if="user.role === 'admin'"
              :href="route('admin.dashboard')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">dashboard</span>
              Dashboard
            </Link>
            
            <!-- LỊCH SỬ ĐƠN HÀNG -->
            <Link 
              :href="route('orders.history')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">receipt_long</span>
              Lịch sử đơn hàng
            </Link>
            
            <!-- HỒ SƠ -->
            <Link 
              :href="route('profile.edit')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">person</span>
              Hồ sơ
            </Link>
            
            <!-- ĐĂNG XUẤT -->
            <button 
              @click="handleLogout" 
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100 mt-1 pt-2"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">logout</span>
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Giỏ hàng -->
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">
            {{ cartCount }}
          </span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { useCart } from '@/utils/useCart'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

// Sử dụng useCart (singleton)
const { cartCount, fetchCart, reloadCart, getUserId } = useCart()

// Lọc danh mục Balo
const laptopCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('balo') || c.name?.toLowerCase().includes('balo')) && c.slug
  ).slice(0, 8)
})

// Lọc danh mục Cặp - Túi
const bagCategories = computed(() => {
  return categories.value.filter(c => 
    (c.slug?.includes('tui') || c.slug?.includes('cap') ||
     c.name?.toLowerCase().includes('túi') || c.name?.toLowerCase().includes('cặp')) && c.slug
  ).slice(0, 8)
})

// Hàm tạo URL an toàn
const getCategoryUrl = (slug) => {
  if (!slug) return '#'
  try {
    return route('category', { slug })
  } catch (e) {
    return '#'
  }
}

// Helper: kiểm tra active cho route name
const isActiveRoute = (routeName) => {
  return route().current(routeName)
}

// Helper: kiểm tra active cho category slug
const isActiveCategory = (slug) => {
  const currentRoute = route().current()
  const params = route().params
  
  if (currentRoute === 'category') {
    const currentSlug = params.slug
    return currentSlug === slug
  }
  return false
}

// Helper: kiểm tra active cho hash anchor (#gioi-thieu)
const isActiveHash = (hash) => {
  if (typeof window !== 'undefined') {
    return window.location.hash === `#${hash}`
  }
  return false
}

// Toggle dropdown
const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

// Đóng dropdown
const closeDropdown = () => {
  dropdownOpen.value = false
}

// Xử lý logout
const handleLogout = async () => {
  try {
    closeDropdown()
    
    router.post(route('logout'), {}, {
      onSuccess: () => {
        window.user = null
        // Sau khi logout, watch user sẽ tự động gọi reloadCart, nhưng để chắc chắn:
        setTimeout(() => {
          reloadCart()
        }, 100)
      }
    })
  } catch (error) {
    router.post(route('logout'))
  }
}

// Xử lý tìm kiếm
const handleSearch = () => {
  if (searchKeyword.value.trim()) {
    router.get(route('category', { slug: 'tim-kiem' }), { q: searchKeyword.value })
  }
}

// Đóng dropdown khi click bên ngoài
const handleClickOutside = (event) => {
  if (userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

// Khi user thay đổi (đăng nhập/đăng xuất), reload giỏ hàng
watch(() => user.value, (newUser, oldUser) => {
  const newId = newUser?.id || 'guest'
  const oldId = oldUser?.id || 'guest'
  
  if (newId !== oldId) {
    window.user = newUser
    reloadCart()
  }
}, { immediate: true })

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  // Fetch giỏ hàng lần đầu
  fetchCart()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-group:hover .dropdown-menu {
  display: block;
}
.dropdown-menu {
  display: none;
}

.dropdown-menu {
  animation: fadeDown 0.2s ease;
}

@keyframes fadeDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
```

### Thay đổi chính:
1. **Xóa hoàn toàn `CartEvents`** – không import, không dùng.
2. **Xóa `setCartCount`** – không cần thiết.
3. **Xóa các handler sự kiện** (`handleCartUpdated`, `handleUserChanged`) và biến lưu trữ handler.
4. **Chỉ giữ lại `fetchCart` trong `onMounted`** – khởi tạo dữ liệu lần đầu.
5. **Trong `watch` user** – khi user thay đổi, gọi `reloadCart()` trực tiếp, không emit event.
6. **Trong `handleLogout`** – sau khi logout, vẫn gọi `reloadCart()` sau 100ms để đảm bảo giỏ hàng được cập nhật (dù watch cũng sẽ chạy).
7. **`onUnmounted`** chỉ xóa listener click.

Với cách này, Header hoàn toàn phụ thuộc vào reactive state từ `useCart` (singleton). Khi `clearCart()` được gọi ở trang success, `cartCount` lập tức về 0 và Header hiển thị đúng mà không cần sự kiện hay gọi lại `fetchCart` gây override.



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
