# Sửa CheckoutSuccess

**Created:** 07/13/2026 06:22  
**Updated:** 07/13/2026 08:13  
**Exported:** 07/13/2026 08:38  
**Link:** [https://chat.deepseek.com/a/chat/s/6055b25d-509a-4998-a061-226762198e34](https://chat.deepseek.com/a/chat/s/6055b25d-509a-4998-a061-226762198e34)  

## User:
07/13/2026 07:34

Mã đơn hàng đang hiển thị không đúng ý tôi , nó vẫn đang hiện ORD-030, tôi muốn hiện L1307202600030.Chỉnh lại CheckoutSuccess.vue và đưa tôi code hoàn chỉnh.
CheckoutSuccess.vue thì sai 
Bên OrderHistory.vue thì đã hiển thị đúng rồi 

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderHistoryController extends Controller
{
    /**
     * Hiển thị trang lịch sử đơn hàng
     */
    public function index()
    {
        return Inertia::render('Web/OrderHistory');
    }

    /**
     * API lấy danh sách đơn hàng của user
     */
    public function getOrders(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('OrderHistoryController@getOrders called', [
                'user_id' => $user ? $user->id : null,
                'user_email' => $user ? $user->email : null,
                'is_authenticated' => $user ? true : false
            ]);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập'
                ], 401);
            }

            $orders = Order::where('user_id', $user->id)
                ->with([
                    'details.productVariant.product',
                    'details.productVariant.color',
                    'payment',
                    'user'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Orders found: ' . $orders->count());

            // Format lại dữ liệu
            $formattedOrders = $orders->map(function ($order) {
                $orderCode = $order->order_code ?? 'retail';
                $displayCode = $this->generateDisplayCode($order);
                
                // Lấy email từ order hoặc từ user
                $customerEmail = $order->customer_email;
                if (empty($customerEmail) || $customerEmail === 'N/A') {
                    $customerEmail = $order->user?->email ?? 'N/A';
                }
                
                return [
                    'id' => $order->id,
                    'display_code' => $displayCode,
                    'order_code' => $orderCode,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'customer_email' => $customerEmail,
                    'receiver_name' => $order->receiver_name,
                    'receiver_phone' => $order->receiver_phone,
                    'shipping_address' => $order->shipping_address,
                    'note' => $order->note,
                    'total_amount' => (int) $order->total_amount,
                    'shipping_fee' => (int) $order->shipping_fee,
                    'discount_amount' => (int) $order->discount_amount,
                    'final_amount' => (int) $order->final_amount,
                    'order_status' => $order->order_status,
                    'created_at' => $order->created_at,
                    'details' => $order->details->map(function ($detail) {
                        $variant = $detail->productVariant;
                        $product = $variant ? $variant->product : null;
                        
                        return [
                            'id' => $detail->id,
                            'quantity' => (int) $detail->quantity,
                            'unit_price' => (int) $detail->unit_price,
                            'subtotal' => (int) $detail->subtotal,
                            'product_name' => $product ? $product->name : null,
                            'product' => $product ? [
                                'name' => $product->name,
                                'image_url' => $product->image_url,
                                'thumbnail' => $product->thumbnail,
                            ] : null,
                            'color_name' => $variant && $variant->color ? $variant->color->name : null,
                            'color' => $variant && $variant->color ? [
                                'name' => $variant->color->name,
                            ] : null,
                            'size_name' => $variant ? $variant->size_name : null,
                            'size' => $variant ? $variant->size_name : null,
                            'image' => $product ? $this->getProductImage($product) : null,
                        ];
                    }),
                    'payment' => $order->payment ? [
                        'payment_method' => $order->payment->payment_method,
                        'status' => $order->payment->status,
                        'transaction_code' => $order->payment->transaction_code,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders,
                'total' => $formattedOrders->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mã hiển thị cho đơn hàng
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600016 (L + 13072026 + 00016)
     */
    private function generateDisplayCode($order)
    {
        // Nếu truyền vào là ID
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // FIX: Dùng now() thay vì created_at để lấy ngày hiện tại
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Lấy hình ảnh sản phẩm
     */
    private function getProductImage($product)
    {
        if (!$product) {
            return '/images/default-product.jpg';
        }
        
        $imageUrls = $product->image_url;
        if (is_array($imageUrls) && !empty($imageUrls)) {
            return $imageUrls[0];
        }
        if (is_string($imageUrls) && !empty($imageUrls)) {
            return $imageUrls;
        }
        if ($product->thumbnail) {
            return $product->thumbnail;
        }
        
        return '/images/default-product.jpg';
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function index(Request $request)
    {
        $isPreOrder = $request->session()->get('pre_order_checkout', false);
        $preOrderVariantId = $request->session()->get('pre_order_variant_id', null);
        
        $products = [];
        $subtotal = 0;
        $orderType = 'retail';

        if ($isPreOrder && $preOrderVariantId) {
            Log::info('Processing pre-order checkout for variant: ' . $preOrderVariantId);
            
            $variant = ProductVariant::with('product', 'color')->find($preOrderVariantId);
            if ($variant && ($variant->product->is_preorder ?? false)) {
                $quantity = $request->session()->get('pre_order_quantity', 1);
                $price = $variant->price;
                $total = $price * $quantity;
                $subtotal = $total;
                
                $images = $variant->product->image_url ?? [];
                if (!is_array($images)) {
                    $images = [];
                }
                if (empty($images) && $variant->product->thumbnail) {
                    $images = [$variant->product->thumbnail];
                }
                
                $products[] = [
                    'id'          => $variant->id,
                    'name'        => $variant->product->name,
                    'variant_name'=> $variant->name ?? '',
                    'price'       => $price,
                    'quantity'    => $quantity,
                    'total'       => $total,
                    'image'       => $images[0] ?? '/images/default-product.jpg',
                    'color'       => $variant->color->name ?? 'Đen',
                    'size'        => $variant->size_name ?? 'M',
                    'is_pre_order' => true,
                ];
                
                $orderType = 'preorder';
            } else {
                Log::warning('Pre-order variant not found or invalid: ' . $preOrderVariantId);
                $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                return redirect()->route('cart')->with('error', 'Sản phẩm Pre-order không hợp lệ');
            }
        } else {
            Log::info('Processing retail checkout from cart');
            $cartItems = Session::get('cart', []);
            
            $filteredCart = [];
            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::with('product')->find($variantId);
                if ($variant && !($variant->product->is_preorder ?? false)) {
                    $filteredCart[$variantId] = $item;
                }
            }
            
            foreach ($filteredCart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if ($variant) {
                    $price = $item['price'] ?? $variant->price;
                    $quantity = $item['quantity'];
                    $total = $price * $quantity;
                    $subtotal += $total;
                    
                    $images = $variant->product->image_url ?? [];
                    if (!is_array($images)) {
                        $images = [];
                    }
                    if (empty($images) && $variant->product->thumbnail) {
                        $images = [$variant->product->thumbnail];
                    }
                    
                    $products[] = [
                        'id'          => $variant->id,
                        'name'        => $variant->product->name,
                        'variant_name'=> $variant->name ?? '',
                        'price'       => $price,
                        'quantity'    => $quantity,
                        'total'       => $total,
                        'image'       => $images[0] ?? '/images/default-product.jpg',
                        'color'       => $variant->color->name ?? 'Đen',
                        'size'        => $variant->size_name ?? 'M',
                        'is_pre_order' => false,
                    ];
                }
            }
            
            if (empty($products)) {
                return redirect()->route('cart')->with('error', 'Giỏ hàng trống');
            }
            
            $orderType = 'retail';
        }

        $shippingFee = 0;
        $discount = 0;
        $finalTotal = $subtotal + $shippingFee - $discount;

        $user = Auth::user();
        $userData = $user ? [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ] : null;

        return Inertia::render('Web/Checkout', [
            'user' => $userData,
            'products' => $products,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'order_type' => $orderType,
            'is_pre_order' => $isPreOrder,
        ]);
    }

    /**
     * Xử lý tạo đơn hàng
     */
    public function store(Request $request)
    {
        Log::info('PaymentController@store called', $request->all());
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'required|in:retail,preorder',
        ]);

        $orderType = $validated['order_type'];
        Log::info('Creating order with type: ' . $orderType);

        $orderRequest = new Request([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'shipping_address' => $validated['shipping_address'],
            'note' => $validated['note'] ?? null,
            'payment_method' => $validated['payment_method'],
            'items' => $validated['items'],
            'total_amount' => $validated['total_amount'],
            'order_type' => $orderType,
        ]);

        try {
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                if ($orderType === 'retail') {
                    $request->session()->forget('cart');
                    Log::info('Cart cleared after retail order');
                } else {
                    $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                    Log::info('Pre-order session cleared');
                }
                
                session(['last_order_id' => $responseData->order->id]);
                session(['last_order_display_code' => $responseData->order_display_code ?? '']);

                return redirect()->route('checkout.success');
            }

            return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success()
    {
        $orderId = session('last_order_id');
        $displayCode = session('last_order_display_code');

        Log::info('Checkout success - order_id: ' . $orderId . ', display_code: ' . $displayCode);

        if (!$orderId) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
        }

        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment',
            'user'
        ])->find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
        }

        // Nếu không có displayCode trong session, tạo mới
        if (empty($displayCode)) {
            $displayCode = $this->generateOrderDisplayCode($order);
        }

        // Lấy email từ order, nếu không có thì lấy từ user
        $customerEmail = $order->customer_email;
        if (empty($customerEmail) || $customerEmail === 'N/A') {
            $customerEmail = $order->user?->email ?? 'N/A';
        }

        $orderDetails = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            
            $image = '/images/default-product.jpg';
            if ($product) {
                $imageUrls = $product->image_url;
                if (is_array($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls[0];
                } elseif (is_string($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls;
                } elseif ($product->thumbnail) {
                    $image = $product->thumbnail;
                }
            }
            
            return [
                'id' => $detail->id,
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'image' => $image,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'color' => $variant && $variant->color ? $variant->color->name : '',
                'size' => $variant ? $variant->size_name : '',
            ];
        });

        $payment = $order->payment;
        $paymentMethod = $payment ? $payment->payment_method : 'cod';
        $paymentStatus = 'pending';
        
        if ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        } elseif (in_array($paymentMethod, ['bank_transfer', 'ewallet', 'vnpay', 'momo'])) {
            $paymentStatus = 'paid';
        }

        if ($payment && $payment->status !== $paymentStatus) {
            $payment->status = $paymentStatus;
            $payment->save();
        }

        // Tạo dữ liệu order để gửi lên view
        $orderData = [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $customerEmail,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'note' => $order->note,
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) $order->discount_amount,
            'final_amount' => (int) $order->final_amount,
            'status' => $order->getStatusText(),
            'type' => $order->order_code ?? 'retail',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'transaction_code' => $payment ? $payment->transaction_code : null,
            'details' => $orderDetails,
            'order_display_code' => $displayCode,
            'created_at' => $order->created_at,
            'display_code' => $displayCode,
        ];

        Log::info('Order data sent to view:', [
            'customer_email' => $orderData['customer_email'],
            'order_display_code' => $displayCode,
            'display_code' => $displayCode,
            'created_at' => $orderData['created_at'],
            'details_count' => count($orderDetails),
        ]);

        // Xóa session sau khi đã lấy dữ liệu
        session()->forget(['last_order_id', 'last_order_display_code']);

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode,
        ]);
    }

    /**
     * Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
     * Format: [Loại đơn hàng][Ngày tạo dmY][STT 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     */
    private function generateOrderDisplayCode($order)
    {
        // Xác định prefix dựa trên loại đơn hàng
        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số (VD: 00019)
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Tạo đơn hàng mới từ giỏ hàng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'nullable|in:retail,wholesale,preorder',
        ]);

        $user = Auth::user();
        $orderType = $validated['order_type'] ?? 'retail';
        $totalAmount = $validated['total_amount'];
        $shippingFee = 0;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        try {
            DB::beginTransaction();

            // 1. Kiểm tra tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity']) {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
            }

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderType,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0,
            ]);

            // 3. Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            // 4. Tạo bản ghi thanh toán
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_code' => $this->generateTransactionCode(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            // 5. Xóa giỏ hàng
            Session::forget('cart');

            DB::commit();

            // Tạo mã đơn hàng hiển thị - Truyền order đã tạo
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
                'created_at' => $order->created_at->format('dmY H:i:s'),
                'current_time' => now()->format('dmY H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $displayCode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị cho khách
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600016 (L + 13072026 + 00016)
     * 
     * @param Order $order
     * @return string
     */
    public function generateOrderDisplayCode($order)
    {
        // Lấy order object hoặc order_id
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('dmY');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }

    /**
     * Xem chi tiết đơn hàng (cho người dùng)
     */
    public function show($id)
    {
        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment'
        ]);

        if (Auth::check()) {
            $order->where('user_id', Auth::id());
        }

        $order = $order->findOrFail($id);

        return response()->json([
            'order' => $order,
            'order_display_code' => $this->generateOrderDisplayCode($order),
            'status_text' => $order->status_text,
            'status_label' => $order->status_label,
        ]);
    }

    /**
     * Lịch sử đơn hàng của người dùng
     */
    public function history()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['details', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $orders->each(function ($order) {
            $order->display_code = $this->generateOrderDisplayCode($order);
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
<template>
  <div>
    <Head title="Lịch sử đơn hàng - BigBag Premium Utility Carry Gear" />
    <AppHeader />

    <main class="max-w-6xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
          <span class="material-symbols-outlined text-primary text-4xl">receipt_long</span>
          Lịch sử đơn hàng
        </h1>
        <p class="text-gray-500 mt-2">Quản lý tất cả đơn hàng của bạn tại BigBag</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <span class="material-symbols-outlined text-red-500 text-5xl block mb-3">error_outline</span>
        <p class="text-red-600">{{ error }}</p>
        <button @click="fetchOrders" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
          Thử lại
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="orders.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <span class="material-symbols-outlined text-gray-300 text-7xl block mb-4">shopping_bag</span>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Chưa có đơn hàng nào</h3>
        <p class="text-gray-500 mb-6">Bạn chưa đặt bất kỳ đơn hàng nào tại BigBag</p>
        <a :href="route('home')" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl hover:bg-primary-dark transition font-semibold">
          <span class="material-symbols-outlined text-sm">shopping_cart</span>
          Mua sắm ngay
        </a>
      </div>

      <!-- Orders List -->
      <div v-else class="space-y-6">
        <!-- Filter / Search -->
        <div class="flex flex-wrap gap-4 items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-100">
          <div class="flex flex-wrap gap-3">
            <button 
              v-for="tab in tabs" 
              :key="tab.value"
              @click="activeTab = tab.value"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
              :class="activeTab === tab.value 
                ? 'bg-primary text-white shadow-sm' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            >
              {{ tab.label }}
              <span class="ml-1 text-xs" :class="activeTab === tab.value ? 'text-white/70' : 'text-gray-400'">
                ({{ getOrderCountByStatus(tab.value) }})
              </span>
            </button>
          </div>
          
          <div class="flex items-center gap-3">
            <div class="relative">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
              <input 
                v-model="searchQuery"
                type="text"
                placeholder="Tìm kiếm đơn hàng..."
                class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:border-primary focus:ring-0 text-sm w-48 md:w-64"
              />
            </div>
            <button @click="fetchOrders" class="p-2 text-gray-500 hover:text-primary transition">
              <span class="material-symbols-outlined">refresh</span>
            </button>
          </div>
        </div>

        <!-- Order Cards -->
        <div 
          v-for="order in filteredOrders" 
          :key="order.id"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
        >
          <!-- Order Header -->
          <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100/50 border-b border-gray-100">
            <div class="flex items-center gap-4 flex-wrap">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
                <p class="font-bold text-gray-800">{{ order.display_code || order.order_code || 'N/A' }}</p>
              </div>
              <div class="hidden sm:block w-px h-8 bg-gray-300"></div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Ngày đặt</p>
                <p class="font-medium text-gray-700">{{ formatDate(order.created_at) }}</p>
              </div>
              <div class="hidden sm:block w-px h-8 bg-gray-300"></div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Tổng tiền</p>
                <p class="font-bold text-primary">{{ formatPrice(order.final_amount || order.total_amount) }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <!-- Badge Pre-order -->
              <span 
                v-if="order.order_code === 'preorder'"
                class="inline-block px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full"
              >
                Pre-order
              </span>
              
              <!-- Status Badge -->
              <span 
                class="inline-block px-4 py-1.5 text-xs font-bold rounded-full"
                :class="getStatusBadgeClass(order.order_status)"
              >
                {{ getStatusLabel(order.order_status) }}
              </span>
            </div>
          </div>

          <!-- Order Body -->
          <div class="px-6 py-4">
            <!-- Products -->
            <div class="space-y-3">
              <div 
                v-for="(item, index) in order.details" 
                :key="index"
                class="flex items-center gap-4 py-2 border-b border-gray-50 last:border-0"
              >
                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                  <img 
                    :src="getProductImage(item)" 
                    :alt="getProductName(item)"
                    @error="(e) => { e.target.src = '/images/default-product.jpg' }"
                    class="w-full h-full object-cover"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-gray-800 truncate">{{ getProductName(item) }}</p>
                  <p class="text-sm text-gray-500">
                    <span v-if="getProductColor(item)">Màu: {{ getProductColor(item) }}</span>
                    <span v-if="getProductColor(item) && getProductSize(item)"> | </span>
                    <span v-if="getProductSize(item)">Size: {{ getProductSize(item) }}</span>
                  </p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="text-sm text-gray-500">x{{ item.quantity }}</p>
                  <p class="font-semibold text-gray-800">{{ formatPrice(item.subtotal || item.unit_price * item.quantity) }}</p>
                </div>
              </div>
            </div>

            <!-- Order Footer -->
            <div class="flex flex-wrap items-center justify-between gap-4 mt-4 pt-4 border-t border-gray-100">
              <div class="text-sm text-gray-500">
                <span class="font-medium text-gray-700">Phương thức thanh toán:</span>
                {{ getPaymentLabel(order.payment?.payment_method) }}
              </div>
              
              <div class="flex gap-3">
                <button 
                  @click="viewOrderDetail(order.id)"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition"
                >
                  <span class="material-symbols-outlined text-sm">visibility</span>
                  Xem chi tiết
                </button>
                <button 
                  @click="printOrder(order)"
                  class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                >
                  <span class="material-symbols-outlined text-sm">print</span>
                  In
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex justify-center gap-2 mt-8">
          <button 
            v-for="page in pagination.last_page" 
            :key="page"
            @click="goToPage(page)"
            class="px-4 py-2 rounded-lg text-sm font-medium transition"
            :class="page === pagination.current_page 
              ? 'bg-primary text-white shadow-sm' 
              : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </main>

    <!-- Order Detail Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModal">
      <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto modal-scroll">
        <div class="sticky top-0 bg-white z-10 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800">Chi tiết đơn hàng</h3>
          <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        
        <div v-if="selectedOrder" class="p-6">
          <!-- Order Info -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div>
              <p class="text-xs text-gray-500 uppercase">Mã đơn hàng</p>
              <p class="font-bold text-gray-800">{{ selectedOrder.display_code }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Ngày đặt</p>
              <p class="font-medium text-gray-700">{{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Trạng thái</p>
              <span class="inline-block px-3 py-1 text-xs font-bold rounded-full" :class="getStatusBadgeClass(selectedOrder.order_status)">
                {{ getStatusLabel(selectedOrder.order_status) }}
              </span>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Tổng tiền</p>
              <p class="font-bold text-primary">{{ formatPrice(selectedOrder.final_amount) }}</p>
            </div>
          </div>

          <!-- Customer Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person</span>
                Thông tin người đặt
              </h4>
              <p class="text-sm"><span class="text-gray-500">Họ tên:</span> {{ selectedOrder.customer_name }}</p>
              <p class="text-sm"><span class="text-gray-500">Email:</span> {{ selectedOrder.customer_email || 'N/A' }}</p>
              <p class="text-sm"><span class="text-gray-500">SĐT:</span> {{ selectedOrder.customer_phone }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">local_shipping</span>
                Thông tin người nhận
              </h4>
              <p class="text-sm"><span class="text-gray-500">Họ tên:</span> {{ selectedOrder.receiver_name }}</p>
              <p class="text-sm"><span class="text-gray-500">SĐT:</span> {{ selectedOrder.receiver_phone }}</p>
              <p class="text-sm"><span class="text-gray-500">Địa chỉ:</span> {{ selectedOrder.shipping_address }}</p>
            </div>
          </div>

          <!-- Products -->
          <div class="mb-6">
            <h4 class="font-semibold text-gray-700 mb-3">Sản phẩm</h4>
            <div class="border border-gray-100 rounded-lg overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Sản phẩm</th>
                    <th class="text-center px-4 py-2 text-gray-500 font-medium">SL</th>
                    <th class="text-right px-4 py-2 text-gray-500 font-medium">Đơn giá</th>
                    <th class="text-right px-4 py-2 text-gray-500 font-medium">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="item in selectedOrder.details" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                          <img :src="getProductImage(item)" class="w-full h-full object-cover" :alt="getProductName(item)" />
                        </div>
                        <span class="font-medium">{{ getProductName(item) }}</span>
                      </div>
                    </td>
                    <td class="text-center px-4 py-3">{{ item.quantity }}</td>
                    <td class="text-right px-4 py-3">{{ formatPrice(item.unit_price) }}</td>
                    <td class="text-right px-4 py-3 font-medium">{{ formatPrice(item.subtotal) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                  <tr>
                    <td colspan="3" class="text-right px-4 py-2 text-gray-600">Tạm tính</td>
                    <td class="text-right px-4 py-2 font-medium">{{ formatPrice(selectedOrder.total_amount) }}</td>
                  </tr>
                  <tr v-if="selectedOrder.shipping_fee > 0">
                    <td colspan="3" class="text-right px-4 py-2 text-gray-600">Phí vận chuyển</td>
                    <td class="text-right px-4 py-2 font-medium">{{ formatPrice(selectedOrder.shipping_fee) }}</td>
                  </tr>
                  <tr v-if="selectedOrder.discount_amount > 0">
                    <td colspan="3" class="text-right px-4 py-2 text-gray-600">Giảm giá</td>
                    <td class="text-right px-4 py-2 font-medium text-red-500">-{{ formatPrice(selectedOrder.discount_amount) }}</td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-2 font-bold text-gray-800">Tổng cộng</td>
                    <td class="text-right px-4 py-2 font-bold text-primary">{{ formatPrice(selectedOrder.final_amount) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Payment -->
          <div class="grid grid-cols-2 gap-4 bg-gray-50 rounded-lg p-4">
            <div>
              <p class="text-xs text-gray-500 uppercase">Phương thức thanh toán</p>
              <p class="font-medium">{{ getPaymentLabel(selectedOrder.payment?.payment_method) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase">Trạng thái thanh toán</p>
              <span class="inline-block px-3 py-1 text-xs font-bold rounded-full" :class="getPaymentStatusBadge(selectedOrder.payment?.status)">
                {{ getPaymentStatusLabel(selectedOrder.payment?.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

// Lấy user từ page props
const page = usePage()
const userEmail = computed(() => page.props.auth?.user?.email || '')

// State
const orders = ref([])
const loading = ref(true)
const error = ref(null)
const activeTab = ref('all')
const searchQuery = ref('')
const pagination = ref(null)
const showModal = ref(false)
const selectedOrder = ref(null)

// Tabs filter
const tabs = [
  { value: 'all', label: 'Tất cả' },
  { value: 'pending', label: 'Chờ xử lý' },
  { value: 'processing', label: 'Đang xử lý' },
  { value: 'shipping', label: 'Đang giao' },
  { value: 'completed', label: 'Hoàn thành' },
  { value: 'cancelled', label: 'Đã hủy' },
]

// Fetch orders
const fetchOrders = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await fetch('/lich-su-don-hang/data', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin'
    })
    
    if (response.status === 401) {
      window.location.href = '/login'
      return
    }
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      throw new Error(errorData.message || 'Không thể tải dữ liệu đơn hàng')
    }
    
    const data = await response.json()
    console.log('📦 Orders data:', data)
    
    if (data.success) {
      // Thay thế email nếu bị N/A bằng email user
      orders.value = (data.orders || []).map(order => ({
        ...order,
        customer_email: order.customer_email && order.customer_email !== 'N/A' 
          ? order.customer_email 
          : userEmail.value || 'N/A'
      }))
      pagination.value = data.pagination || null
      console.log('✅ Loaded orders:', orders.value.length)
    } else {
      throw new Error(data.message || 'Có lỗi xảy ra')
    }
  } catch (err) {
    console.error('❌ Error fetching orders:', err)
    error.value = err.message || 'Có lỗi xảy ra khi tải đơn hàng'
  } finally {
    loading.value = false
  }
}

// Filtered orders
const filteredOrders = computed(() => {
  let filtered = orders.value
  
  if (activeTab.value !== 'all') {
    const statusMap = {
      pending: 0,
      processing: 1,
      shipping: 2,
      completed: 3,
      cancelled: 4
    }
    const statusValue = statusMap[activeTab.value]
    if (statusValue !== undefined) {
      filtered = filtered.filter(order => order.order_status === statusValue)
    }
  }
  
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.trim().toLowerCase()
    filtered = filtered.filter(order => 
      (order.display_code || '').toLowerCase().includes(query) ||
      (order.order_code || '').toLowerCase().includes(query) ||
      (order.customer_name || '').toLowerCase().includes(query) ||
      (order.customer_phone || '').includes(query)
    )
  }
  
  return filtered
})

const getOrderCountByStatus = (status) => {
  if (status === 'all') return orders.value.length
  
  const statusMap = {
    pending: 0,
    processing: 1,
    shipping: 2,
    completed: 3,
    cancelled: 4
  }
  const statusValue = statusMap[status]
  if (statusValue === undefined) return 0
  
  return orders.value.filter(order => order.order_status === statusValue).length
}

// Helper functions
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

const getStatusBadgeClass = (status) => {
  const map = {
    0: 'bg-yellow-100 text-yellow-800',
    1: 'bg-blue-100 text-blue-800',
    2: 'bg-purple-100 text-purple-800',
    3: 'bg-green-100 text-green-800',
    4: 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const map = {
    0: 'Chờ xử lý',
    1: 'Đang xử lý',
    2: 'Đang giao',
    3: 'Hoàn thành',
    4: 'Đã hủy',
  }
  return map[status] || 'Không xác định'
}

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
  }
  return map[method] || method || 'Chưa xác định'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

const getProductName = (item) => {
  return item.product?.name || item.product_name || 'Sản phẩm không xác định'
}

const getProductImage = (item) => {
  if (item.product?.image_url) {
    if (Array.isArray(item.product.image_url) && item.product.image_url.length > 0) {
      return item.product.image_url[0]
    }
    if (typeof item.product.image_url === 'string') {
      return item.product.image_url
    }
  }
  if (item.product?.thumbnail) {
    return item.product.thumbnail
  }
  if (item.image) {
    return item.image
  }
  return '/images/default-product.jpg'
}

const getProductColor = (item) => {
  return item.color?.name || item.color_name || ''
}

const getProductSize = (item) => {
  return item.size || item.size_name || ''
}

// View order detail
const viewOrderDetail = (orderId) => {
  const order = orders.value.find(o => o.id === orderId)
  if (order) {
    // Đảm bảo email được hiển thị
    const orderWithEmail = {
      ...order,
      customer_email: order.customer_email && order.customer_email !== 'N/A' 
        ? order.customer_email 
        : userEmail.value || 'N/A'
    }
    selectedOrder.value = orderWithEmail
    showModal.value = true
  }
}

const closeModal = () => {
  showModal.value = false
  selectedOrder.value = null
}

// Print order
const printOrder = (order) => {
  const printWindow = window.open('', '_blank')
  if (!printWindow) {
    alert('Vui lòng cho phép popup để in đơn hàng')
    return
  }
  
  const content = generatePrintContent(order)
  printWindow.document.write(content)
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
}

// Generate print content
const generatePrintContent = (order) => {
  // Lấy email từ order hoặc từ user
  const email = order.customer_email && order.customer_email !== 'N/A' 
    ? order.customer_email 
    : userEmail.value || 'N/A'

  const detailsHtml = order.details.map(item => `
    <tr>
      <td style="padding: 8px 12px; border: 1px solid #ddd;">${getProductName(item)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: center;">${item.quantity}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.unit_price)}</td>
      <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">${formatPrice(item.subtotal)}</td>
    </tr>
  `).join('')

  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Đơn hàng #${order.display_code || order.order_code}</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: auto; }
        h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .info { margin: 20px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-box h3 { margin: 0 0 10px 0; color: #6b7280; font-size: 14px; text-transform: uppercase; }
        .info-box p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; border: 1px solid #ddd; }
        td { padding: 8px 12px; border: 1px solid #ddd; }
        .total { font-size: 20px; font-weight: bold; color: #1a56db; text-align: right; }
        .footer { margin-top: 40px; text-align: center; color: #6b7280; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-processing { background: #dbeafe; color: #1e40af; }
        .badge-shipping { background: #f3e8ff; color: #6b21a8; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
      </style>
    </head>
    <body>
      <h1>HÓA ĐƠN ĐẶT HÀNG</h1>
      <p><strong>Mã đơn hàng:</strong> ${order.display_code || order.order_code}</p>
      <p><strong>Ngày đặt:</strong> ${formatDate(order.created_at)}</p>
      
      <div class="info-grid">
        <div class="info-box">
          <h3>Thông tin người đặt</h3>
          <p><strong>Họ tên:</strong> ${order.customer_name}</p>
          <p><strong>Email:</strong> ${email}</p>
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
            <td colspan="3" style="text-align: right; font-weight: bold;">Tạm tính</td>
            <td style="text-align: right;">${formatPrice(order.total_amount)}</td>
          </tr>
          ${order.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Phí vận chuyển</td>
              <td style="text-align: right;">${formatPrice(order.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${order.discount_amount > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Giảm giá</td>
              <td style="text-align: right; color: red;">-${formatPrice(order.discount_amount)}</td>
            </tr>
          ` : ''}
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db;">${formatPrice(order.final_amount)}</td>
          </tr>
        </tfoot>
      </table>
      
      <div style="margin-top: 20px;">
        <p><strong>Trạng thái:</strong> <span class="badge badge-${['pending','processing','shipping','completed','cancelled'][order.order_status] || 'pending'}">${getStatusLabel(order.order_status)}</span></p>
        <p><strong>Phương thức thanh toán:</strong> ${getPaymentLabel(order.payment?.payment_method)}</p>
        <p><strong>Trạng thái thanh toán:</strong> ${getPaymentStatusLabel(order.payment?.status)}</p>
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
}

// Pagination
const goToPage = (page) => {
  // Implement pagination logic
}

// Lifecycle
onMounted(() => {
  fetchOrders()
})
</script>

<style scoped>
/* Custom scrollbar cho modal */
.modal-scroll::-webkit-scrollbar {
  width: 6px;
}

.modal-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 8px;
}

.modal-scroll::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 8px;
}

.modal-scroll::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>

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
        <div v-if="orderDisplayCode" class="mt-4 inline-block bg-gray-50 px-6 py-3 rounded-xl border border-gray-200">
          <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
          <p class="text-2xl font-bold text-primary">{{ orderDisplayCode }}</p>
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
                v-if="order?.type === 'preorder'"
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
              <span class="material-symbols-outlined text-sm text-gray-400">note</span>
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
                  <tr v-if="orderSummary.discount_amount > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Giảm giá</td>
                    <td class="text-right px-4 py-3 font-medium text-red-500">-{{ formatPrice(orderSummary.discount_amount) }}</td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-3 font-bold text-gray-800">Tổng cộng</td>
                    <td class="text-right px-4 py-3 font-bold text-2xl text-primary">{{ formatPrice(orderSummary.final_amount) }}</td>
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
          <span class="material-symbols-outlined text-sm">shopping_cart</span>
          Tiếp tục mua sắm
        </a>
        <a 
          :href="route('orders.history')" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-50 transition-all font-semibold"
        >
          <span class="material-symbols-outlined text-sm">receipt_long</span>
          Xem lịch sử đơn hàng
        </a>
        <button 
          v-if="order?.id"
          @click="printOrder" 
          class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-200 transition-all font-semibold"
        >
          <span class="material-symbols-outlined text-sm">print</span>
          In đơn hàng
        </button>
      </div>

      <!-- Support Info -->
      <div class="mt-8 text-center text-sm text-gray-500 no-print">
        <p>📞 Cần hỗ trợ? Liên hệ hotline: <a href="tel:19001234" class="text-primary font-medium">1900 1234</a></p>
        <p class="mt-1">📧 Email: <a href="mailto:support@bigbag.vn" class="text-primary font-medium">support@bigbag.vn</a></p>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  order: {
    type: Object,
    default: null
  },
  order_display_code: {
    type: String,
    default: ''
  },
  order_details: {
    type: Array,
    default: () => []
  },
  order_summary: {
    type: Object,
    default: () => ({
      subtotal: 0,
      shipping_fee: 0,
      discount_amount: 0,
      final_amount: 0
    })
  }
})

// Debug: Log toàn bộ props để kiểm tra
onMounted(() => {
  console.log('📦 Full props:', props)
  console.log('📦 Order object:', props.order)
  console.log('📦 Customer email from order:', props.order?.customer_email)
  console.log('📦 Order display code from props:', props.order_display_code)
  console.log('📦 Order display code from order:', props.order?.order_display_code)
  console.log('📦 Order display_code from order:', props.order?.display_code)
  console.log('📦 Order created_at:', props.order?.created_at)
})

// Lấy email từ nhiều nguồn khác nhau
const customerEmail = computed(() => {
  if (props.order?.customer_email && props.order.customer_email !== 'N/A') {
    return props.order.customer_email
  }
  if (props.order?.user?.email) {
    return props.order.user.email
  }
  return 'N/A'
})

// Compute order display code - GIỐNG VỚI ORDERHISTORY
const orderDisplayCode = computed(() => {
  // Ưu tiên lấy từ props.order_display_code
  if (props.order_display_code) {
    return props.order_display_code
  }
  // Lấy từ order.order_display_code
  if (props.order?.order_display_code) {
    return props.order.order_display_code
  }
  // Lấy từ order.display_code (giống OrderHistory)
  if (props.order?.display_code) {
    return props.order.display_code
  }
  // Nếu không có, hiển thị N/A
  return 'N/A'
})

// Compute order details from order or props
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
  return props.order_details || []
})

// Compute order summary
const orderSummary = computed(() => {
  if (props.order) {
    return {
      subtotal: props.order.total_amount || 0,
      shipping_fee: props.order.shipping_fee || 0,
      discount_amount: props.order.discount_amount || 0,
      final_amount: props.order.final_amount || props.order.total_amount || 0,
    }
  }
  return props.order_summary || {
    subtotal: 0,
    shipping_fee: 0,
    discount_amount: 0,
    final_amount: 0
  }
})

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

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
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
  }
  return map[method] || 'bg-gray-400'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

// Hàm in đơn hàng
const printOrder = () => {
  if (!props.order) return
  
  const order = props.order
  const details = orderDetails.value
  const displayCode = orderDisplayCode.value
  
  // Tạo nội dung in
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
            <td colspan="3" style="text-align: right; font-weight: bold;">Tạm tính</td>
            <td style="text-align: right;">${formatPrice(order.total_amount)}</td>
          </tr>
          ${order.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Phí vận chuyển</td>
              <td style="text-align: right;">${formatPrice(order.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${order.discount_amount > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Giảm giá</td>
              <td style="text-align: right; color: red;">-${formatPrice(order.discount_amount)}</td>
            </tr>
          ` : ''}
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db;">${formatPrice(order.final_amount)}</td>
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
  
  .bg-primary\/10 {
    background-color: #e8edf8 !important;
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


## Assistant:
07/13/2026 07:34

Tôi thấy vấn đề: Mã đơn hàng trong `CheckoutSuccess.vue` vẫn hiển thị `ORD-030` thay vì format mới `L1307202600030`. Mặc dù `OrderHistory.vue` đã hiển thị đúng.

Vấn đề nằm ở chỗ `CheckoutSuccess.vue` không nhận được `display_code` từ backend. Hãy sửa file `CheckoutSuccess.vue` như sau:

## CheckoutSuccess.vue (Sửa hoàn chỉnh)

```vue
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
        
        <!-- Order Code - QUAN TRỌNG: Hiển thị mã đúng format -->
        <div class="mt-4 inline-block bg-gray-50 px-6 py-3 rounded-xl border border-gray-200">
          <p class="text-xs text-gray-500 uppercase tracking-wider">Mã đơn hàng</p>
          <p class="text-2xl font-bold text-primary">{{ orderDisplayCode }}</p>
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
              <span class="material-symbols-outlined text-sm text-gray-400">note</span>
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
                  <tr v-if="orderSummary.discount_amount > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Giảm giá</td>
                    <td class="text-right px-4 py-3 font-medium text-red-500">-{{ formatPrice(orderSummary.discount_amount) }}</td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-3 font-bold text-gray-800">Tổng cộng</td>
                    <td class="text-right px-4 py-3 font-bold text-2xl text-primary">{{ formatPrice(orderSummary.final_amount) }}</td>
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
          <span class="material-symbols-outlined text-sm">shopping_cart</span>
          Tiếp tục mua sắm
        </a>
        <a 
          :href="route('orders.history')" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-50 transition-all font-semibold"
        >
          <span class="material-symbols-outlined text-sm">receipt_long</span>
          Xem lịch sử đơn hàng
        </a>
        <button 
          v-if="order?.id"
          @click="printOrder" 
          class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-200 transition-all font-semibold"
        >
          <span class="material-symbols-outlined text-sm">print</span>
          In đơn hàng
        </button>
      </div>

      <!-- Support Info -->
      <div class="mt-8 text-center text-sm text-gray-500 no-print">
        <p>📞 Cần hỗ trợ? Liên hệ hotline: <a href="tel:19001234" class="text-primary font-medium">1900 1234</a></p>
        <p class="mt-1">📧 Email: <a href="mailto:support@bigbag.vn" class="text-primary font-medium">support@bigbag.vn</a></p>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

const props = defineProps({
  order: {
    type: Object,
    default: null
  },
  order_display_code: {
    type: String,
    default: ''
  },
  order_details: {
    type: Array,
    default: () => []
  },
  order_summary: {
    type: Object,
    default: () => ({
      subtotal: 0,
      shipping_fee: 0,
      discount_amount: 0,
      final_amount: 0
    })
  }
})

// Debug: Log toàn bộ props để kiểm tra
onMounted(() => {
  console.log('📦 Full props:', props)
  console.log('📦 Order object:', props.order)
  console.log('📦 Customer email from order:', props.order?.customer_email)
  console.log('📦 Order display code from props.order_display_code:', props.order_display_code)
  console.log('📦 Order display code from order.order_display_code:', props.order?.order_display_code)
  console.log('📦 Order display_code from order.display_code:', props.order?.display_code)
  console.log('📦 Order created_at:', props.order?.created_at)
})

// QUAN TRỌNG: Lấy mã đơn hàng hiển thị
const orderDisplayCode = computed(() => {
  // Ưu tiên 1: Lấy từ props.order_display_code (được truyền từ backend)
  if (props.order_display_code) {
    console.log('✅ Using order_display_code from props:', props.order_display_code)
    return props.order_display_code
  }
  
  // Ưu tiên 2: Lấy từ order.order_display_code
  if (props.order?.order_display_code) {
    console.log('✅ Using order.order_display_code:', props.order.order_display_code)
    return props.order.order_display_code
  }
  
  // Ưu tiên 3: Lấy từ order.display_code (giống OrderHistory)
  if (props.order?.display_code) {
    console.log('✅ Using order.display_code:', props.order.display_code)
    return props.order.display_code
  }
  
  // Nếu không có, hiển thị N/A
  console.warn('⚠️ No display code found!')
  return 'N/A'
})

// Lấy email từ nhiều nguồn khác nhau
const customerEmail = computed(() => {
  if (props.order?.customer_email && props.order.customer_email !== 'N/A') {
    return props.order.customer_email
  }
  if (props.order?.user?.email) {
    return props.order.user.email
  }
  return 'N/A'
})

// Compute order details from order or props
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
  return props.order_details || []
})

// Compute order summary
const orderSummary = computed(() => {
  if (props.order) {
    return {
      subtotal: props.order.total_amount || 0,
      shipping_fee: props.order.shipping_fee || 0,
      discount_amount: props.order.discount_amount || 0,
      final_amount: props.order.final_amount || props.order.total_amount || 0,
    }
  }
  return props.order_summary || {
    subtotal: 0,
    shipping_fee: 0,
    discount_amount: 0,
    final_amount: 0
  }
})

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

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
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
  }
  return map[method] || 'bg-gray-400'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

// Hàm in đơn hàng
const printOrder = () => {
  if (!props.order) return
  
  const order = props.order
  const details = orderDetails.value
  const displayCode = orderDisplayCode.value
  
  // Tạo nội dung in
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
            <td colspan="3" style="text-align: right; font-weight: bold;">Tạm tính</td>
            <td style="text-align: right;">${formatPrice(order.total_amount)}</td>
          </tr>
          ${order.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Phí vận chuyển</td>
              <td style="text-align: right;">${formatPrice(order.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${order.discount_amount > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Giảm giá</td>
              <td style="text-align: right; color: red;">-${formatPrice(order.discount_amount)}</td>
            </tr>
          ` : ''}
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db;">${formatPrice(order.final_amount)}</td>
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
  
  .bg-primary\/10 {
    background-color: #e8edf8 !important;
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
```

## Lưu ý quan trọng:

1. **Phần `orderDisplayCode` computed** đã được sửa để ưu tiên lấy mã từ các nguồn theo thứ tự:
   - `props.order_display_code`
   - `props.order?.order_display_code` 
   - `props.order?.display_code` (giống OrderHistory)

2. **Thêm console.log** để debug xem mã đang được lấy từ đâu

3. **Giữ nguyên format** giống với OrderHistory: `[Prefix][Ngày dmY][ID 5 số]`


## User:
07/13/2026 07:37

![image.png](data:image/webp;base64,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)

vẫn hiển thị sai


## Assistant:
07/13/2026 07:37

Tôi thấy vấn đề: Mã đơn hàng vẫn hiển thị `ORD-033` thay vì format mới. Điều này có nghĩa là dữ liệu từ backend không truyền đúng `display_code` xuống view.

Vấn đề nằm ở **Controller** - cụ thể là phương thức `success()` trong `PaymentController`. Dữ liệu `order_display_code` không được truyền đúng cách sang view.

## Sửa file `PaymentController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function index(Request $request)
    {
        // ... (giữ nguyên code cũ)
    }

    /**
     * Xử lý tạo đơn hàng
     */
    public function store(Request $request)
    {
        // ... (giữ nguyên code cũ)
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success()
    {
        $orderId = session('last_order_id');
        $displayCode = session('last_order_display_code');

        Log::info('Checkout success - order_id: ' . $orderId . ', display_code: ' . $displayCode);

        if (!$orderId) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
        }

        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment',
            'user'
        ])->find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
        }

        // QUAN TRỌNG: Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
        // Nếu không có displayCode trong session, tạo mới
        if (empty($displayCode)) {
            $displayCode = $this->generateOrderDisplayCode($order);
            Log::info('Generated new display code: ' . $displayCode);
        }

        // Lấy email từ order, nếu không có thì lấy từ user
        $customerEmail = $order->customer_email;
        if (empty($customerEmail) || $customerEmail === 'N/A') {
            $customerEmail = $order->user?->email ?? 'N/A';
        }

        $orderDetails = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            
            $image = '/images/default-product.jpg';
            if ($product) {
                $imageUrls = $product->image_url;
                if (is_array($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls[0];
                } elseif (is_string($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls;
                } elseif ($product->thumbnail) {
                    $image = $product->thumbnail;
                }
            }
            
            return [
                'id' => $detail->id,
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'image' => $image,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'color' => $variant && $variant->color ? $variant->color->name : '',
                'size' => $variant ? $variant->size_name : '',
            ];
        });

        $payment = $order->payment;
        $paymentMethod = $payment ? $payment->payment_method : 'cod';
        $paymentStatus = 'pending';
        
        if ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        } elseif (in_array($paymentMethod, ['bank_transfer', 'ewallet', 'vnpay', 'momo'])) {
            $paymentStatus = 'paid';
        }

        if ($payment && $payment->status !== $paymentStatus) {
            $payment->status = $paymentStatus;
            $payment->save();
        }

        // Tạo dữ liệu order để gửi lên view
        $orderData = [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $customerEmail,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'note' => $order->note,
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) $order->discount_amount,
            'final_amount' => (int) $order->final_amount,
            'status' => $order->getStatusText(),
            'order_code' => $order->order_code ?? 'retail', // THÊM DÒNG NÀY
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'transaction_code' => $payment ? $payment->transaction_code : null,
            'details' => $orderDetails,
            'created_at' => $order->created_at,
            // QUAN TRỌNG: Thêm display_code vào order data
            'display_code' => $displayCode,
            'order_display_code' => $displayCode,
        ];

        Log::info('Order data sent to view:', [
            'customer_email' => $orderData['customer_email'],
            'order_display_code' => $displayCode,
            'display_code' => $displayCode,
            'order_code' => $orderData['order_code'],
            'created_at' => $orderData['created_at'],
            'details_count' => count($orderDetails),
        ]);

        // Xóa session sau khi đã lấy dữ liệu
        session()->forget(['last_order_id', 'last_order_display_code']);

        // QUAN TRỌNG: Truyền cả order_display_code và order
        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode, // Truyền riêng
            'order_details' => $orderDetails,
            'order_summary' => [
                'subtotal' => (int) $order->total_amount,
                'shipping_fee' => (int) $order->shipping_fee,
                'discount_amount' => (int) $order->discount_amount,
                'final_amount' => (int) $order->final_amount,
            ]
        ]);
    }

    /**
     * Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
     * Format: [Loại đơn hàng][Ngày tạo dmY][STT 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     */
    private function generateOrderDisplayCode($order)
    {
        // Xác định prefix dựa trên loại đơn hàng
        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số (VD: 00019)
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }
}
```

## Sửa file `OrderController.php` (phương thức store):

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Tạo đơn hàng mới từ giỏ hàng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'nullable|in:retail,wholesale,preorder',
        ]);

        $user = Auth::user();
        $orderType = $validated['order_type'] ?? 'retail';
        $totalAmount = $validated['total_amount'];
        $shippingFee = 0;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        try {
            DB::beginTransaction();

            // 1. Kiểm tra tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity']) {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
            }

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderType,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0,
            ]);

            // 3. Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            // 4. Tạo bản ghi thanh toán
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_code' => $this->generateTransactionCode(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            // 5. Xóa giỏ hàng
            Session::forget('cart');

            DB::commit();

            // QUAN TRỌNG: Tạo mã đơn hàng hiển thị
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
                'created_at' => $order->created_at->format('dmY H:i:s'),
                'current_time' => now()->format('dmY H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $displayCode, // QUAN TRỌNG: Trả về display code
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị cho khách
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600016 (L + 13072026 + 00016)
     * 
     * @param Order $order
     * @return string
     */
    public function generateOrderDisplayCode($order)
    {
        // Lấy order object hoặc order_id
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('dmY');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }

    /**
     * Xem chi tiết đơn hàng (cho người dùng)
     */
    public function show($id)
    {
        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment'
        ]);

        if (Auth::check()) {
            $order->where('user_id', Auth::id());
        }

        $order = $order->findOrFail($id);

        return response()->json([
            'order' => $order,
            'order_display_code' => $this->generateOrderDisplayCode($order),
            'status_text' => $order->status_text,
            'status_label' => $order->status_label,
        ]);
    }

    /**
     * Lịch sử đơn hàng của người dùng
     */
    public function history()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['details', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $orders->each(function ($order) {
            $order->display_code = $this->generateOrderDisplayCode($order);
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
```

## Kiểm tra thêm ở `PaymentController` - phần `store()`:

Trong `PaymentController@store`, sau khi gọi `$this->orderController->store()` và nhận response, cần lấy `order_display_code` từ response:

```php
public function store(Request $request)
{
    // ... validation code ...

    try {
        $response = $this->orderController->store($orderRequest);
        $responseData = $response->getData();

        if ($responseData->success) {
            if ($orderType === 'retail') {
                $request->session()->forget('cart');
                Log::info('Cart cleared after retail order');
            } else {
                $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                Log::info('Pre-order session cleared');
            }
            
            // QUAN TRỌNG: Lưu cả order_id và display_code vào session
            session(['last_order_id' => $responseData->order->id]);
            
            // Lấy order_display_code từ response, nếu không có thì tự tạo
            if (isset($responseData->order_display_code) && !empty($responseData->order_display_code)) {
                session(['last_order_display_code' => $responseData->order_display_code]);
            } else {
                // Tạo display code từ order
                $displayCode = $this->generateOrderDisplayCode($responseData->order);
                session(['last_order_display_code' => $displayCode]);
            }

            return redirect()->route('checkout.success');
        }

        return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

    } catch (\Exception $e) {
        Log::error('Payment store error: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}
```

## Tóm tắt các thay đổi quan trọng:

1. **OrderController@store**: Thêm `order_display_code` vào response JSON
2. **PaymentController@store**: Lấy `order_display_code` từ response và lưu vào session
3. **PaymentController@success**: 
   - Đảm bảo lấy `display_code` từ session
   - Tạo mới nếu chưa có
   - Truyền cả `order_display_code` và `display_code` vào view
   - Thêm `order_code` vào `$orderData`

Sau khi sửa các file này, mã đơn hàng sẽ hiển thị đúng format `L1307202600030` thay vì `ORD-033`.


## User:
07/13/2026 07:41

đưa code hoàn chỉnh cho tôi


## Assistant:
07/13/2026 07:41

Tôi sẽ đưa bạn code hoàn chỉnh cho tất cả các file cần sửa:

## 1. PaymentController.php (Hoàn chỉnh)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function index(Request $request)
    {
        $isPreOrder = $request->session()->get('pre_order_checkout', false);
        $preOrderVariantId = $request->session()->get('pre_order_variant_id', null);
        
        $products = [];
        $subtotal = 0;
        $orderType = 'retail';

        if ($isPreOrder && $preOrderVariantId) {
            Log::info('Processing pre-order checkout for variant: ' . $preOrderVariantId);
            
            $variant = ProductVariant::with('product', 'color')->find($preOrderVariantId);
            if ($variant && ($variant->product->is_preorder ?? false)) {
                $quantity = $request->session()->get('pre_order_quantity', 1);
                $price = $variant->price;
                $total = $price * $quantity;
                $subtotal = $total;
                
                $images = $variant->product->image_url ?? [];
                if (!is_array($images)) {
                    $images = [];
                }
                if (empty($images) && $variant->product->thumbnail) {
                    $images = [$variant->product->thumbnail];
                }
                
                $products[] = [
                    'id'          => $variant->id,
                    'name'        => $variant->product->name,
                    'variant_name'=> $variant->name ?? '',
                    'price'       => $price,
                    'quantity'    => $quantity,
                    'total'       => $total,
                    'image'       => $images[0] ?? '/images/default-product.jpg',
                    'color'       => $variant->color->name ?? 'Đen',
                    'size'        => $variant->size_name ?? 'M',
                    'is_pre_order' => true,
                ];
                
                $orderType = 'preorder';
            } else {
                Log::warning('Pre-order variant not found or invalid: ' . $preOrderVariantId);
                $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                return redirect()->route('cart')->with('error', 'Sản phẩm Pre-order không hợp lệ');
            }
        } else {
            Log::info('Processing retail checkout from cart');
            $cartItems = Session::get('cart', []);
            
            $filteredCart = [];
            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::with('product')->find($variantId);
                if ($variant && !($variant->product->is_preorder ?? false)) {
                    $filteredCart[$variantId] = $item;
                }
            }
            
            foreach ($filteredCart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if ($variant) {
                    $price = $item['price'] ?? $variant->price;
                    $quantity = $item['quantity'];
                    $total = $price * $quantity;
                    $subtotal += $total;
                    
                    $images = $variant->product->image_url ?? [];
                    if (!is_array($images)) {
                        $images = [];
                    }
                    if (empty($images) && $variant->product->thumbnail) {
                        $images = [$variant->product->thumbnail];
                    }
                    
                    $products[] = [
                        'id'          => $variant->id,
                        'name'        => $variant->product->name,
                        'variant_name'=> $variant->name ?? '',
                        'price'       => $price,
                        'quantity'    => $quantity,
                        'total'       => $total,
                        'image'       => $images[0] ?? '/images/default-product.jpg',
                        'color'       => $variant->color->name ?? 'Đen',
                        'size'        => $variant->size_name ?? 'M',
                        'is_pre_order' => false,
                    ];
                }
            }
            
            if (empty($products)) {
                return redirect()->route('cart')->with('error', 'Giỏ hàng trống');
            }
            
            $orderType = 'retail';
        }

        $shippingFee = 0;
        $discount = 0;
        $finalTotal = $subtotal + $shippingFee - $discount;

        $user = Auth::user();
        $userData = $user ? [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ] : null;

        return Inertia::render('Web/Checkout', [
            'user' => $userData,
            'products' => $products,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'order_type' => $orderType,
            'is_pre_order' => $isPreOrder,
        ]);
    }

    /**
     * Xử lý tạo đơn hàng
     */
    public function store(Request $request)
    {
        Log::info('PaymentController@store called', $request->all());
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'required|in:retail,preorder',
        ]);

        $orderType = $validated['order_type'];
        Log::info('Creating order with type: ' . $orderType);

        $orderRequest = new Request([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'shipping_address' => $validated['shipping_address'],
            'note' => $validated['note'] ?? null,
            'payment_method' => $validated['payment_method'],
            'items' => $validated['items'],
            'total_amount' => $validated['total_amount'],
            'order_type' => $orderType,
        ]);

        try {
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            if ($responseData->success) {
                if ($orderType === 'retail') {
                    $request->session()->forget('cart');
                    Log::info('Cart cleared after retail order');
                } else {
                    $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                    Log::info('Pre-order session cleared');
                }
                
                // Lưu order_id vào session
                session(['last_order_id' => $responseData->order->id]);
                
                // Lấy order_display_code từ response, nếu không có thì tự tạo
                if (isset($responseData->order_display_code) && !empty($responseData->order_display_code)) {
                    session(['last_order_display_code' => $responseData->order_display_code]);
                    Log::info('Display code from response: ' . $responseData->order_display_code);
                } else {
                    // Tạo display code từ order
                    $displayCode = $this->generateOrderDisplayCode($responseData->order);
                    session(['last_order_display_code' => $displayCode]);
                    Log::info('Generated display code: ' . $displayCode);
                }

                return redirect()->route('checkout.success');
            }

            return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

        } catch (\Exception $e) {
            Log::error('Payment store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success()
    {
        $orderId = session('last_order_id');
        $displayCode = session('last_order_display_code');

        Log::info('Checkout success - order_id: ' . $orderId . ', display_code: ' . $displayCode);

        if (!$orderId) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
        }

        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment',
            'user'
        ])->find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
        }

        // Nếu không có displayCode trong session, tạo mới
        if (empty($displayCode)) {
            $displayCode = $this->generateOrderDisplayCode($order);
            Log::info('Generated new display code: ' . $displayCode);
        }

        // Lấy email từ order, nếu không có thì lấy từ user
        $customerEmail = $order->customer_email;
        if (empty($customerEmail) || $customerEmail === 'N/A') {
            $customerEmail = $order->user?->email ?? 'N/A';
        }

        $orderDetails = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            
            $image = '/images/default-product.jpg';
            if ($product) {
                $imageUrls = $product->image_url;
                if (is_array($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls[0];
                } elseif (is_string($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls;
                } elseif ($product->thumbnail) {
                    $image = $product->thumbnail;
                }
            }
            
            return [
                'id' => $detail->id,
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'image' => $image,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'color' => $variant && $variant->color ? $variant->color->name : '',
                'size' => $variant ? $variant->size_name : '',
            ];
        });

        $payment = $order->payment;
        $paymentMethod = $payment ? $payment->payment_method : 'cod';
        $paymentStatus = 'pending';
        
        if ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        } elseif (in_array($paymentMethod, ['bank_transfer', 'ewallet', 'vnpay', 'momo'])) {
            $paymentStatus = 'paid';
        }

        if ($payment && $payment->status !== $paymentStatus) {
            $payment->status = $paymentStatus;
            $payment->save();
        }

        // Tạo dữ liệu order để gửi lên view
        $orderData = [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $customerEmail,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'note' => $order->note,
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) $order->discount_amount,
            'final_amount' => (int) $order->final_amount,
            'status' => $order->getStatusText(),
            'order_code' => $order->order_code ?? 'retail',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'transaction_code' => $payment ? $payment->transaction_code : null,
            'details' => $orderDetails,
            'created_at' => $order->created_at,
            'display_code' => $displayCode,
            'order_display_code' => $displayCode,
        ];

        Log::info('Order data sent to view:', [
            'customer_email' => $orderData['customer_email'],
            'order_display_code' => $displayCode,
            'display_code' => $displayCode,
            'created_at' => $orderData['created_at'],
            'details_count' => count($orderDetails),
        ]);

        // Xóa session sau khi đã lấy dữ liệu
        session()->forget(['last_order_id', 'last_order_display_code']);

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode,
        ]);
    }

    /**
     * Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
     * Format: [Loại đơn hàng][Ngày tạo dmY][STT 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     */
    private function generateOrderDisplayCode($order)
    {
        // Xác định prefix dựa trên loại đơn hàng
        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số (VD: 00019)
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }
}
```

## 2. OrderController.php (Hoàn chỉnh)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Tạo đơn hàng mới từ giỏ hàng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'nullable|in:retail,wholesale,preorder',
        ]);

        $user = Auth::user();
        $orderType = $validated['order_type'] ?? 'retail';
        $totalAmount = $validated['total_amount'];
        $shippingFee = 0;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        try {
            DB::beginTransaction();

            // 1. Kiểm tra tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity']) {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
            }

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderType,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0,
            ]);

            // 3. Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            // 4. Tạo bản ghi thanh toán
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_code' => $this->generateTransactionCode(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            // 5. Xóa giỏ hàng
            Session::forget('cart');

            DB::commit();

            // Tạo mã đơn hàng hiển thị
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
                'created_at' => $order->created_at->format('dmY H:i:s'),
                'current_time' => now()->format('dmY H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $displayCode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị cho khách
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600016 (L + 13072026 + 00016)
     * 
     * @param Order $order
     * @return string
     */
    public function generateOrderDisplayCode($order)
    {
        // Lấy order object hoặc order_id
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // 13072026
        
        // Dùng ID của order làm sequence, format 5 số
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('dmY');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }

    /**
     * Xem chi tiết đơn hàng (cho người dùng)
     */
    public function show($id)
    {
        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment'
        ]);

        if (Auth::check()) {
            $order->where('user_id', Auth::id());
        }

        $order = $order->findOrFail($id);

        return response()->json([
            'order' => $order,
            'order_display_code' => $this->generateOrderDisplayCode($order),
            'status_text' => $order->status_text,
            'status_label' => $order->status_label,
        ]);
    }

    /**
     * Lịch sử đơn hàng của người dùng
     */
    public function history()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['details', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $orders->each(function ($order) {
            $order->display_code = $this->generateOrderDisplayCode($order);
        });

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}
```

## 3. CheckoutSuccess.vue (Hoàn chỉnh)

```vue
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
              <span class="material-symbols-outlined text-sm text-gray-400">note</span>
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
                  <tr v-if="orderSummary.discount_amount > 0">
                    <td colspan="3" class="text-right px-4 py-3 text-gray-600">Giảm giá</td>
                    <td class="text-right px-4 py-3 font-medium text-red-500">-{{ formatPrice(orderSummary.discount_amount) }}</td>
                  </tr>
                  <tr class="bg-primary/5">
                    <td colspan="3" class="text-right px-4 py-3 font-bold text-gray-800">Tổng cộng</td>
                    <td class="text-right px-4 py-3 font-bold text-2xl text-primary">{{ formatPrice(orderSummary.final_amount) }}</td>
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
          <span class="material-symbols-outlined text-sm">shopping_cart</span>
          Tiếp tục mua sắm
        </a>
        <a 
          :href="route('orders.history')" 
          class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-50 transition-all font-semibold"
        >
          <span class="material-symbols-outlined text-sm">receipt_long</span>
          Xem lịch sử đơn hàng
        </a>
        <button 
          v-if="order?.id"
          @click="printOrder" 
          class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-200 transition-all font-semibold"
        >
          <span class="material-symbols-outlined text-sm">print</span>
          In đơn hàng
        </button>
      </div>

      <!-- Support Info -->
      <div class="mt-8 text-center text-sm text-gray-500 no-print">
        <p>📞 Cần hỗ trợ? Liên hệ hotline: <a href="tel:19001234" class="text-primary font-medium">1900 1234</a></p>
        <p class="mt-1">📧 Email: <a href="mailto:support@bigbag.vn" class="text-primary font-medium">support@bigbag.vn</a></p>
      </div>
    </main>

    <Chatbot />
    <AppFooter />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

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

// Debug: Log toàn bộ props để kiểm tra
onMounted(() => {
  console.log('📦 Full props:', props)
  console.log('📦 Order object:', props.order)
  console.log('📦 Order display code from props.order_display_code:', props.order_display_code)
  console.log('📦 Order display code from order.order_display_code:', props.order?.order_display_code)
  console.log('📦 Order display_code from order.display_code:', props.order?.display_code)
})

// QUAN TRỌNG: Lấy mã đơn hàng hiển thị
const orderDisplayCode = computed(() => {
  // Ưu tiên 1: Lấy từ props.order_display_code (được truyền từ backend)
  if (props.order_display_code) {
    console.log('✅ Using order_display_code from props:', props.order_display_code)
    return props.order_display_code
  }
  
  // Ưu tiên 2: Lấy từ order.order_display_code
  if (props.order?.order_display_code) {
    console.log('✅ Using order.order_display_code:', props.order.order_display_code)
    return props.order.order_display_code
  }
  
  // Ưu tiên 3: Lấy từ order.display_code (giống OrderHistory)
  if (props.order?.display_code) {
    console.log('✅ Using order.display_code:', props.order.display_code)
    return props.order.display_code
  }
  
  // Nếu không có, hiển thị N/A
  console.warn('⚠️ No display code found!')
  return 'N/A'
})

// Lấy email từ nhiều nguồn khác nhau
const customerEmail = computed(() => {
  if (props.order?.customer_email && props.order.customer_email !== 'N/A') {
    return props.order.customer_email
  }
  if (props.order?.user?.email) {
    return props.order.user.email
  }
  return 'N/A'
})

// Compute order details from order
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

// Compute order summary
const orderSummary = computed(() => {
  if (props.order) {
    return {
      subtotal: props.order.total_amount || 0,
      shipping_fee: props.order.shipping_fee || 0,
      discount_amount: props.order.discount_amount || 0,
      final_amount: props.order.final_amount || props.order.total_amount || 0,
    }
  }
  return {
    subtotal: 0,
    shipping_fee: 0,
    discount_amount: 0,
    final_amount: 0
  }
})

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

const getPaymentLabel = (method) => {
  const map = {
    cod: 'Thanh toán khi nhận hàng (COD)',
    bank_transfer: 'Chuyển khoản ngân hàng',
    ewallet: 'Ví điện tử',
    vnpay: 'VNPay',
    momo: 'MoMo',
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
  }
  return map[method] || 'bg-gray-400'
}

const getPaymentStatusBadge = (status) => {
  const map = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const map = {
    pending: 'Chờ thanh toán',
    paid: 'Đã thanh toán',
    failed: 'Thanh toán thất bại',
    refunded: 'Đã hoàn tiền',
  }
  return map[status] || status || 'Chưa xác định'
}

// Hàm in đơn hàng
const printOrder = () => {
  if (!props.order) return
  
  const order = props.order
  const details = orderDetails.value
  const displayCode = orderDisplayCode.value
  
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
            <td colspan="3" style="text-align: right; font-weight: bold;">Tạm tính</td>
            <td style="text-align: right;">${formatPrice(order.total_amount)}</td>
          </tr>
          ${order.shipping_fee > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Phí vận chuyển</td>
              <td style="text-align: right;">${formatPrice(order.shipping_fee)}</td>
            </tr>
          ` : ''}
          ${order.discount_amount > 0 ? `
            <tr>
              <td colspan="3" style="text-align: right;">Giảm giá</td>
              <td style="text-align: right; color: red;">-${formatPrice(order.discount_amount)}</td>
            </tr>
          ` : ''}
          <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 18px;">Tổng cộng</td>
            <td style="text-align: right; font-weight: bold; font-size: 18px; color: #1a56db;">${formatPrice(order.final_amount)}</td>
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
  
  .bg-primary\/10 {
    background-color: #e8edf8 !important;
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
```

## 4. OrderHistoryController.php (Giữ nguyên - đã đúng)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderHistoryController extends Controller
{
    /**
     * Hiển thị trang lịch sử đơn hàng
     */
    public function index()
    {
        return Inertia::render('Web/OrderHistory');
    }

    /**
     * API lấy danh sách đơn hàng của user
     */
    public function getOrders(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('OrderHistoryController@getOrders called', [
                'user_id' => $user ? $user->id : null,
                'user_email' => $user ? $user->email : null,
                'is_authenticated' => $user ? true : false
            ]);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập'
                ], 401);
            }

            $orders = Order::where('user_id', $user->id)
                ->with([
                    'details.productVariant.product',
                    'details.productVariant.color',
                    'payment',
                    'user'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Orders found: ' . $orders->count());

            // Format lại dữ liệu
            $formattedOrders = $orders->map(function ($order) {
                $orderCode = $order->order_code ?? 'retail';
                $displayCode = $this->generateDisplayCode($order);
                
                // Lấy email từ order hoặc từ user
                $customerEmail = $order->customer_email;
                if (empty($customerEmail) || $customerEmail === 'N/A') {
                    $customerEmail = $order->user?->email ?? 'N/A';
                }
                
                return [
                    'id' => $order->id,
                    'display_code' => $displayCode,
                    'order_code' => $orderCode,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'customer_email' => $customerEmail,
                    'receiver_name' => $order->receiver_name,
                    'receiver_phone' => $order->receiver_phone,
                    'shipping_address' => $order->shipping_address,
                    'note' => $order->note,
                    'total_amount' => (int) $order->total_amount,
                    'shipping_fee' => (int) $order->shipping_fee,
                    'discount_amount' => (int) $order->discount_amount,
                    'final_amount' => (int) $order->final_amount,
                    'order_status' => $order->order_status,
                    'created_at' => $order->created_at,
                    'details' => $order->details->map(function ($detail) {
                        $variant = $detail->productVariant;
                        $product = $variant ? $variant->product : null;
                        
                        return [
                            'id' => $detail->id,
                            'quantity' => (int) $detail->quantity,
                            'unit_price' => (int) $detail->unit_price,
                            'subtotal' => (int) $detail->subtotal,
                            'product_name' => $product ? $product->name : null,
                            'product' => $product ? [
                                'name' => $product->name,
                                'image_url' => $product->image_url,
                                'thumbnail' => $product->thumbnail,
                            ] : null,
                            'color_name' => $variant && $variant->color ? $variant->color->name : null,
                            'color' => $variant && $variant->color ? [
                                'name' => $variant->color->name,
                            ] : null,
                            'size_name' => $variant ? $variant->size_name : null,
                            'size' => $variant ? $variant->size_name : null,
                            'image' => $product ? $this->getProductImage($product) : null,
                        ];
                    }),
                    'payment' => $order->payment ? [
                        'payment_method' => $order->payment->payment_method,
                        'status' => $order->payment->status,
                        'transaction_code' => $order->payment->transaction_code,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders,
                'total' => $formattedOrders->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mã hiển thị cho đơn hàng
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600016 (L + 13072026 + 00016)
     */
    private function generateDisplayCode($order)
    {
        // Nếu truyền vào là ID
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY');
        
        // Dùng ID của order làm sequence, format 5 số
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Lấy hình ảnh sản phẩm
     */
    private function getProductImage($product)
    {
        if (!$product) {
            return '/images/default-product.jpg';
        }
        
        $imageUrls = $product->image_url;
        if (is_array($imageUrls) && !empty($imageUrls)) {
            return $imageUrls[0];
        }
        if (is_string($imageUrls) && !empty($imageUrls)) {
            return $imageUrls;
        }
        if ($product->thumbnail) {
            return $product->thumbnail;
        }
        
        return '/images/default-product.jpg';
    }
}
```

## Các bước thực hiện:

1. **Thay thế** file `PaymentController.php` bằng code hoàn chỉnh ở trên
2. **Thay thế** file `OrderController.php` bằng code hoàn chỉnh ở trên
3. **Thay thế** file `CheckoutSuccess.vue` bằng code hoàn chỉnh ở trên
4. **Giữ nguyên** file `OrderHistoryController.php` (đã đúng)

Sau khi thay thế, mã đơn hàng sẽ hiển thị đúng format `L1307202600030` thay vì `ORD-033`.


## User:
07/13/2026 07:50

![image.png](data:image/webp;base64,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)

Thay thế file PaymentController.php bằng code hoàn chỉnh ở trên

Thay thế file OrderController.php bằng code hoàn chỉnh ở trên

Thay thế file CheckoutSuccess.vue bằng code hoàn chỉnh ở trên

Đã làm rồi nhưng vẫn sai ý tôi


## Assistant:
07/13/2026 07:50

Vấn đề vẫn còn ở session. Tôi thấy mã vẫn là `ORD-034` nghĩa là dữ liệu từ session không được lưu đúng. Hãy kiểm tra và sửa theo cách sau:

## 1. Kiểm tra file `Admin/OrderController.php` (Quan trọng!)

Đây có thể là file đang được gọi thay vì `OrderController.php` ở root. Hãy kiểm tra file:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Tạo đơn hàng mới từ giỏ hàng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'nullable|in:retail,wholesale,preorder',
        ]);

        $user = Auth::user();
        $orderType = $validated['order_type'] ?? 'retail';
        $totalAmount = $validated['total_amount'];
        $shippingFee = 0;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        try {
            DB::beginTransaction();

            // 1. Kiểm tra tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity']) {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
            }

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderType,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0,
            ]);

            // 3. Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            // 4. Tạo bản ghi thanh toán
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_code' => $this->generateTransactionCode(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            // 5. Xóa giỏ hàng
            Session::forget('cart');

            DB::commit();

            // Tạo mã đơn hàng hiển thị
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('Order created successfully in Admin/OrderController:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $displayCode, // QUAN TRỌNG: Phải có dòng này
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Order creation failed in Admin/OrderController:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     */
    public function generateOrderDisplayCode($order)
    {
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        $date = now()->format('dmY');
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('dmY');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }
}
```

## 2. Sửa lại `PaymentController.php` - Phần `store()`:

```php
/**
 * Xử lý tạo đơn hàng
 */
public function store(Request $request)
{
    Log::info('PaymentController@store called', $request->all());
    
    $validated = $request->validate([
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'customer_email' => 'required|email|max:255',
        'receiver_name' => 'required|string|max:255',
        'receiver_phone' => 'required|string|max:20',
        'shipping_address' => 'required|string|max:500',
        'note' => 'nullable|string|max:500',
        'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
        'items' => 'required|array|min:1',
        'items.*.id' => 'required|exists:product_variants,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'total_amount' => 'required|numeric|min:0',
        'order_type' => 'required|in:retail,preorder',
    ]);

    $orderType = $validated['order_type'];
    Log::info('Creating order with type: ' . $orderType);

    $orderRequest = new Request([
        'customer_name' => $validated['customer_name'],
        'customer_phone' => $validated['customer_phone'],
        'customer_email' => $validated['customer_email'],
        'receiver_name' => $validated['receiver_name'],
        'receiver_phone' => $validated['receiver_phone'],
        'shipping_address' => $validated['shipping_address'],
        'note' => $validated['note'] ?? null,
        'payment_method' => $validated['payment_method'],
        'items' => $validated['items'],
        'total_amount' => $validated['total_amount'],
        'order_type' => $orderType,
    ]);

    try {
        $response = $this->orderController->store($orderRequest);
        $responseData = $response->getData();

        Log::info('Response from OrderController:', [
            'success' => $responseData->success ?? false,
            'has_order' => isset($responseData->order),
            'order_display_code' => $responseData->order_display_code ?? 'NOT SET'
        ]);

        if ($responseData->success) {
            if ($orderType === 'retail') {
                $request->session()->forget('cart');
                Log::info('Cart cleared after retail order');
            } else {
                $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                Log::info('Pre-order session cleared');
            }
            
            // Lưu order_id vào session
            session(['last_order_id' => $responseData->order->id]);
            
            // QUAN TRỌNG: Lấy order_display_code từ response
            $displayCode = null;
            if (isset($responseData->order_display_code) && !empty($responseData->order_display_code)) {
                $displayCode = $responseData->order_display_code;
                Log::info('Display code from response: ' . $displayCode);
            } else {
                // Fallback: Tạo display code từ order
                $displayCode = $this->generateOrderDisplayCode($responseData->order);
                Log::info('Generated display code (fallback): ' . $displayCode);
            }
            
            // Lưu vào session
            session(['last_order_display_code' => $displayCode]);
            Log::info('Saved to session - last_order_display_code: ' . session('last_order_display_code'));

            return redirect()->route('checkout.success');
        }

        return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

    } catch (\Exception $e) {
        Log::error('Payment store error: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}
```

## 3. Sửa lại `PaymentController.php` - Phần `success()`:

```php
/**
 * Hiển thị trang thanh toán thành công
 */
public function success()
{
    $orderId = session('last_order_id');
    $displayCode = session('last_order_display_code');

    Log::info('=== CHECKOUT SUCCESS ===');
    Log::info('Session data:', [
        'last_order_id' => $orderId,
        'last_order_display_code' => $displayCode,
        'all_session' => session()->all()
    ]);

    if (!$orderId) {
        return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
    }

    $order = Order::with([
        'details.productVariant.product',
        'details.productVariant.color',
        'payment',
        'user'
    ])->find($orderId);

    if (!$order) {
        return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
    }

    // Nếu không có displayCode trong session, tạo mới
    if (empty($displayCode)) {
        $displayCode = $this->generateOrderDisplayCode($order);
        Log::info('Generated new display code (session was empty): ' . $displayCode);
    }

    Log::info('Final display code for view: ' . $displayCode);

    // Lấy email từ order
    $customerEmail = $order->customer_email;
    if (empty($customerEmail) || $customerEmail === 'N/A') {
        $customerEmail = $order->user?->email ?? 'N/A';
    }

    // ... (phần xử lý order details giữ nguyên) ...

    // Tạo dữ liệu order
    $orderData = [
        'id' => $order->id,
        'customer_name' => $order->customer_name,
        'customer_phone' => $order->customer_phone,
        'customer_email' => $customerEmail,
        'receiver_name' => $order->receiver_name,
        'receiver_phone' => $order->receiver_phone,
        'shipping_address' => $order->shipping_address,
        'note' => $order->note,
        'total_amount' => (int) $order->total_amount,
        'shipping_fee' => (int) $order->shipping_fee,
        'discount_amount' => (int) $order->discount_amount,
        'final_amount' => (int) $order->final_amount,
        'status' => $order->getStatusText(),
        'order_code' => $order->order_code ?? 'retail',
        'payment_method' => $paymentMethod,
        'payment_status' => $paymentStatus,
        'transaction_code' => $payment ? $payment->transaction_code : null,
        'details' => $orderDetails,
        'created_at' => $order->created_at,
        'display_code' => $displayCode, // THÊM DÒNG NÀY
        'order_display_code' => $displayCode, // THÊM DÒNG NÀY
    ];

    // Xóa session
    session()->forget(['last_order_id', 'last_order_display_code']);

    // Trả về view với cả 2 cách
    return Inertia::render('Web/CheckoutSuccess', [
        'order' => $orderData,
        'order_display_code' => $displayCode, // Truyền riêng
    ]);
}
```

## 4. Thêm debug trong `CheckoutSuccess.vue`:

```vue
<script setup>
import { computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import Chatbot from '@/Components/Chatbot.vue'

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

// Debug chi tiết
onMounted(() => {
  console.log('=== CHECKOUT SUCCESS DEBUG ===')
  console.log('1. props.order_display_code:', props.order_display_code)
  console.log('2. props.order?.display_code:', props.order?.display_code)
  console.log('3. props.order?.order_display_code:', props.order?.order_display_code)
  console.log('4. Full props.order:', props.order)
  console.log('5. All props:', props)
})

// QUAN TRỌNG: Lấy mã đơn hàng hiển thị
const orderDisplayCode = computed(() => {
  console.log('=== Computing orderDisplayCode ===')
  
  // Ưu tiên 1: Lấy từ props.order_display_code
  if (props.order_display_code) {
    console.log('✅ Using props.order_display_code:', props.order_display_code)
    return props.order_display_code
  }
  
  // Ưu tiên 2: Lấy từ order.order_display_code
  if (props.order?.order_display_code) {
    console.log('✅ Using order.order_display_code:', props.order.order_display_code)
    return props.order.order_display_code
  }
  
  // Ưu tiên 3: Lấy từ order.display_code
  if (props.order?.display_code) {
    console.log('✅ Using order.display_code:', props.order.display_code)
    return props.order.display_code
  }
  
  console.warn('⚠️ No display code found!')
  return 'N/A'
})

// ... phần còn lại giữ nguyên ...
</script>
```

## 5. Kiểm tra log sau khi đặt hàng:

Sau khi đặt hàng, kiểm tra file `storage/logs/laravel.log` để xem:

1. `Order created successfully in Admin/OrderController:` - Xem `display_code` có đúng không
2. `Response from OrderController:` - Xem `order_display_code` có trong response không
3. `Saved to session - last_order_display_code:` - Xem giá trị lưu vào session
4. `=== CHECKOUT SUCCESS ===` - Xem session data
5. `Final display code for view:` - Xem giá trị cuối cùng gửi xuống view

## 6. Nếu vẫn không được, hãy thử cách cuối cùng:

Trong `PaymentController@success`, thay vì lấy từ session, hãy tạo trực tiếp:

```php
public function success()
{
    $orderId = session('last_order_id');
    
    if (!$orderId) {
        return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
    }

    $order = Order::with([
        'details.productVariant.product',
        'details.productVariant.color',
        'payment',
        'user'
    ])->find($orderId);

    if (!$order) {
        return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
    }

    // TẠO TRỰC TIẾP TẠI ĐÂY, KHÔNG LẤY TỪ SESSION
    $displayCode = $this->generateOrderDisplayCode($order);
    
    // ... phần còn lại ...
}
```

Hãy thử các bước trên và kiểm tra log để xem dữ liệu được truyền đi như thế nào.


## User:
07/13/2026 07:59

đưa code hoàn chỉnh


## Assistant:
07/13/2026 07:59

Tôi sẽ đưa bạn code hoàn chỉnh cho tất cả các file cần sửa. Vấn đề là file `Admin/OrderController.php` đang được gọi thay vì file `OrderController.php` ở root.

## 1. `app/Http/Controllers/Admin/OrderController.php` (Hoàn chỉnh)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Tạo đơn hàng mới từ giỏ hàng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'nullable|in:retail,wholesale,preorder',
        ]);

        $user = Auth::user();
        $orderType = $validated['order_type'] ?? 'retail';
        $totalAmount = $validated['total_amount'];
        $shippingFee = 0;
        $discountAmount = 0;
        $finalAmount = $totalAmount;

        try {
            DB::beginTransaction();

            // 1. Kiểm tra tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity']) {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
            }

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => null,
                'order_code' => $orderType,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'order_status' => 0,
            ]);

            // 3. Tạo chi tiết đơn hàng và cập nhật tồn kho
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                $variant->decrement('stock', $item['quantity']);
            }

            // 4. Tạo bản ghi thanh toán
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_code' => $this->generateTransactionCode(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            // 5. Xóa giỏ hàng
            Session::forget('cart');

            DB::commit();

            // Tạo mã đơn hàng hiển thị
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('✅ Order created successfully in Admin/OrderController:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order,
                'payment' => $payment,
                'order_display_code' => $displayCode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Order creation failed in Admin/OrderController:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     */
    public function generateOrderDisplayCode($order)
    {
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        $date = now()->format('dmY');
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Tạo mã giao dịch
     */
    private function generateTransactionCode()
    {
        $prefix = 'PAY';
        $date = now()->format('dmY');
        $random = strtoupper(substr(uniqid(), -6));
        return $prefix . $date . $random;
    }
}
```

## 2. `app/Http/Controllers/PaymentController.php` (Hoàn chỉnh)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $orderController;

    public function __construct()
    {
        $this->orderController = app(\App\Http\Controllers\Admin\OrderController::class);
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function index(Request $request)
    {
        $isPreOrder = $request->session()->get('pre_order_checkout', false);
        $preOrderVariantId = $request->session()->get('pre_order_variant_id', null);
        
        $products = [];
        $subtotal = 0;
        $orderType = 'retail';

        if ($isPreOrder && $preOrderVariantId) {
            Log::info('Processing pre-order checkout for variant: ' . $preOrderVariantId);
            
            $variant = ProductVariant::with('product', 'color')->find($preOrderVariantId);
            if ($variant && ($variant->product->is_preorder ?? false)) {
                $quantity = $request->session()->get('pre_order_quantity', 1);
                $price = $variant->price;
                $total = $price * $quantity;
                $subtotal = $total;
                
                $images = $variant->product->image_url ?? [];
                if (!is_array($images)) {
                    $images = [];
                }
                if (empty($images) && $variant->product->thumbnail) {
                    $images = [$variant->product->thumbnail];
                }
                
                $products[] = [
                    'id'          => $variant->id,
                    'name'        => $variant->product->name,
                    'variant_name'=> $variant->name ?? '',
                    'price'       => $price,
                    'quantity'    => $quantity,
                    'total'       => $total,
                    'image'       => $images[0] ?? '/images/default-product.jpg',
                    'color'       => $variant->color->name ?? 'Đen',
                    'size'        => $variant->size_name ?? 'M',
                    'is_pre_order' => true,
                ];
                
                $orderType = 'preorder';
            } else {
                Log::warning('Pre-order variant not found or invalid: ' . $preOrderVariantId);
                $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                return redirect()->route('cart')->with('error', 'Sản phẩm Pre-order không hợp lệ');
            }
        } else {
            Log::info('Processing retail checkout from cart');
            $cartItems = Session::get('cart', []);
            
            $filteredCart = [];
            foreach ($cartItems as $variantId => $item) {
                $variant = ProductVariant::with('product')->find($variantId);
                if ($variant && !($variant->product->is_preorder ?? false)) {
                    $filteredCart[$variantId] = $item;
                }
            }
            
            foreach ($filteredCart as $variantId => $item) {
                $variant = ProductVariant::with('product', 'color')->find($variantId);
                if ($variant) {
                    $price = $item['price'] ?? $variant->price;
                    $quantity = $item['quantity'];
                    $total = $price * $quantity;
                    $subtotal += $total;
                    
                    $images = $variant->product->image_url ?? [];
                    if (!is_array($images)) {
                        $images = [];
                    }
                    if (empty($images) && $variant->product->thumbnail) {
                        $images = [$variant->product->thumbnail];
                    }
                    
                    $products[] = [
                        'id'          => $variant->id,
                        'name'        => $variant->product->name,
                        'variant_name'=> $variant->name ?? '',
                        'price'       => $price,
                        'quantity'    => $quantity,
                        'total'       => $total,
                        'image'       => $images[0] ?? '/images/default-product.jpg',
                        'color'       => $variant->color->name ?? 'Đen',
                        'size'        => $variant->size_name ?? 'M',
                        'is_pre_order' => false,
                    ];
                }
            }
            
            if (empty($products)) {
                return redirect()->route('cart')->with('error', 'Giỏ hàng trống');
            }
            
            $orderType = 'retail';
        }

        $shippingFee = 0;
        $discount = 0;
        $finalTotal = $subtotal + $shippingFee - $discount;

        $user = Auth::user();
        $userData = $user ? [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ] : null;

        return Inertia::render('Web/Checkout', [
            'user' => $userData,
            'products' => $products,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'order_type' => $orderType,
            'is_pre_order' => $isPreOrder,
        ]);
    }

    /**
     * Xử lý tạo đơn hàng
     */
    public function store(Request $request)
    {
        Log::info('🔵 PaymentController@store called', $request->all());
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'order_type' => 'required|in:retail,preorder',
        ]);

        $orderType = $validated['order_type'];
        Log::info('Creating order with type: ' . $orderType);

        $orderRequest = new Request([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'shipping_address' => $validated['shipping_address'],
            'note' => $validated['note'] ?? null,
            'payment_method' => $validated['payment_method'],
            'items' => $validated['items'],
            'total_amount' => $validated['total_amount'],
            'order_type' => $orderType,
        ]);

        try {
            $response = $this->orderController->store($orderRequest);
            $responseData = $response->getData();

            Log::info('📦 Response from OrderController:', [
                'success' => $responseData->success ?? false,
                'has_order' => isset($responseData->order),
                'order_id' => $responseData->order->id ?? null,
                'order_display_code' => $responseData->order_display_code ?? 'NOT SET'
            ]);

            if ($responseData->success) {
                if ($orderType === 'retail') {
                    $request->session()->forget('cart');
                    Log::info('Cart cleared after retail order');
                } else {
                    $request->session()->forget(['pre_order_checkout', 'pre_order_variant_id', 'pre_order_quantity']);
                    Log::info('Pre-order session cleared');
                }
                
                // Lưu order_id vào session
                session(['last_order_id' => $responseData->order->id]);
                
                // Lấy order_display_code từ response
                $displayCode = null;
                if (isset($responseData->order_display_code) && !empty($responseData->order_display_code)) {
                    $displayCode = $responseData->order_display_code;
                    Log::info('✅ Display code from response: ' . $displayCode);
                } else {
                    // Fallback: Tạo display code từ order
                    $displayCode = $this->generateOrderDisplayCode($responseData->order);
                    Log::info('⚠️ Generated display code (fallback): ' . $displayCode);
                }
                
                // Lưu vào session
                session(['last_order_display_code' => $displayCode]);
                Log::info('💾 Saved to session - last_order_display_code: ' . session('last_order_display_code'));

                return redirect()->route('checkout.success');
            }

            return back()->withErrors(['error' => $responseData->message ?? 'Có lỗi xảy ra khi đặt hàng.']);

        } catch (\Exception $e) {
            Log::error('❌ Payment store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success()
    {
        $orderId = session('last_order_id');
        $displayCode = session('last_order_display_code');

        Log::info('🟢 === CHECKOUT SUCCESS ===');
        Log::info('Session data:', [
            'last_order_id' => $orderId,
            'last_order_display_code' => $displayCode,
        ]);

        if (!$orderId) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng');
        }

        $order = Order::with([
            'details.productVariant.product',
            'details.productVariant.color',
            'payment',
            'user'
        ])->find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng');
        }

        // Nếu không có displayCode trong session, tạo mới
        if (empty($displayCode)) {
            $displayCode = $this->generateOrderDisplayCode($order);
            Log::info('🆕 Generated new display code (session was empty): ' . $displayCode);
        }

        Log::info('🎯 Final display code for view: ' . $displayCode);

        // Lấy email từ order
        $customerEmail = $order->customer_email;
        if (empty($customerEmail) || $customerEmail === 'N/A') {
            $customerEmail = $order->user?->email ?? 'N/A';
        }

        $orderDetails = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            
            $image = '/images/default-product.jpg';
            if ($product) {
                $imageUrls = $product->image_url;
                if (is_array($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls[0];
                } elseif (is_string($imageUrls) && !empty($imageUrls)) {
                    $image = $imageUrls;
                } elseif ($product->thumbnail) {
                    $image = $product->thumbnail;
                }
            }
            
            return [
                'id' => $detail->id,
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'image' => $image,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'color' => $variant && $variant->color ? $variant->color->name : '',
                'size' => $variant ? $variant->size_name : '',
            ];
        });

        $payment = $order->payment;
        $paymentMethod = $payment ? $payment->payment_method : 'cod';
        $paymentStatus = 'pending';
        
        if ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        } elseif (in_array($paymentMethod, ['bank_transfer', 'ewallet', 'vnpay', 'momo'])) {
            $paymentStatus = 'paid';
        }

        if ($payment && $payment->status !== $paymentStatus) {
            $payment->status = $paymentStatus;
            $payment->save();
        }

        // Tạo dữ liệu order
        $orderData = [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $customerEmail,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'note' => $order->note,
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) $order->discount_amount,
            'final_amount' => (int) $order->final_amount,
            'status' => $order->getStatusText(),
            'order_code' => $order->order_code ?? 'retail',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'transaction_code' => $payment ? $payment->transaction_code : null,
            'details' => $orderDetails,
            'created_at' => $order->created_at,
            'display_code' => $displayCode,
            'order_display_code' => $displayCode,
        ];

        // Xóa session
        session()->forget(['last_order_id', 'last_order_display_code']);

        return Inertia::render('Web/CheckoutSuccess', [
            'order' => $orderData,
            'order_display_code' => $displayCode,
        ]);
    }

    /**
     * Tạo mã đơn hàng hiển thị
     * Format: [Loại đơn hàng][Ngày tạo


## User:
07/13/2026 08:01

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\ProductVariant;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng theo loại
     */
    public function index($type = 'retail')
    {
        $validTypes = ['retail', 'wholesale', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'retail';

        $orders = Order::with(['details.productVariant.product', 'payment'])
            ->where('order_code', $type)
            ->latest()
            ->get()
            ->map(function ($order) {
                $products = $order->details->map(function ($detail) {
                    $variant = $detail->productVariant;
                    $product = $variant ? $variant->product : null;
                    return [
                        'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                        'quantity' => $detail->quantity,
                        'price'    => (int) $detail->unit_price,
                        'subtotal' => (int) $detail->subtotal,
                        'image'    => $product ? ($product->image_url[0] ?? '/images/default-product.jpg') : '/images/default-product.jpg',
                    ];
                });

                $subtotal = $products->sum('subtotal');
                $shipping = (int) ($order->shipping_fee ?? 0);
                $discount = (int) ($order->discount_amount ?? 0);
                $final = $subtotal + $shipping - $discount;

                $payment = 'COD';
                $paymentClass = 'bg-green-100 text-green-800';
                if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
                    $payment = 'Chuyển khoản';
                    $paymentClass = 'bg-blue-100 text-blue-800';
                } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
                    $payment = 'Ví điện tử';
                    $paymentClass = 'bg-purple-100 text-purple-800';
                }

                return [
                    'id'              => $order->id,
                    'code'            => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                    'customer'        => $order->customer_name ?? $order->receiver_name,
                    'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
                    'receiver'        => $order->receiver_name,
                    'receiver_phone'  => $order->receiver_phone,
                    'date'            => $order->created_at->format('d/m/Y'),
                    'subtotal'        => $subtotal,
                    'shipping_fee'    => $shipping,
                    'discount_amount' => $discount,
                    'final_amount'    => $final,
                    'amount'          => $final,
                    'payment'         => $payment,
                    'paymentClass'    => $paymentClass,
                    'status'          => $this->getStatusText($order),
                    'statusLabel'     => $this->getStatusLabel($order),
                    'type'            => $order->order_code ?? 'retail',
                    'address'         => $order->shipping_address,
                    'note'            => $order->note,
                    'products'        => $products,
                ];
            });

        return Inertia::render('Admin/Orders', [
            'type'          => $type,
            'initialOrders' => $orders->all(),
        ]);
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with(['details.productVariant.product', 'payment'])->findOrFail($id);

        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price'    => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'image'    => $product ? ($product->image_url[0] ?? '/images/default-product.jpg') : '/images/default-product.jpg',
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
            $payment = 'Ví điện tử';
        }

        $orderData = [
            'id'              => $order->id,
            'code'            => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'customer'        => $order->customer_name ?? $order->receiver_name,
            'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
            'receiver'        => $order->receiver_name,
            'receiver_phone'  => $order->receiver_phone,
            'date'            => $order->created_at->format('d/m/Y'),
            'subtotal'        => $subtotal,
            'shipping_fee'    => $shipping,
            'discount_amount' => $discount,
            'final_amount'    => $final,
            'amount'          => $final,
            'payment'         => $payment,
            'status'          => $this->getStatusText($order),
            'statusLabel'     => $this->getStatusLabel($order),
            'type'            => $order->order_code ?? 'retail',
            'address'         => $order->shipping_address,
            'note'            => $order->note,
            'products'        => $products,
        ];

        return Inertia::render('Admin/Orders/Show', ['order' => $orderData]);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $order = Order::findOrFail($id);
            $newStatus = $request->status;
            $statusMap = $this->getStatusMapForOrder($order);
            
            if (!isset($statusMap[$newStatus])) {
                return back()->with('error', 'Trạng thái không hợp lệ');
            }
            
            $statusInt = $statusMap[$newStatus];
            $order->order_status = $statusInt;
            $order->save();

            Log::info("Order #{$order->id} status updated to: {$newStatus}");

            return back()->with('success', 'Cập nhật trạng thái thành công');
        } catch (\Exception $e) {
            Log::error('Update order status error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    }

    /**
     * Tạo đơn hàng mới (từ PaymentController gọi)
     */
    public function store(Request $request)
    {
        Log::info('Admin\OrderController@store called', $request->all());
        
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'required|email|max:255',
                'receiver_name' => 'required|string|max:255',
                'receiver_phone' => 'required|string|max:20',
                'shipping_address' => 'required|string|max:500',
                'note' => 'nullable|string|max:500',
                'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'order_type' => 'required|in:retail,preorder',
            ]);

            $orderType = $validated['order_type'];
            $userId = Auth::id();
            $totalAmount = (int) $validated['total_amount'];
            $shippingFee = 0;
            $discountAmount = 0;

            Log::info('Creating order with type: ' . $orderType . ' for user: ' . $userId);

            // Bắt đầu transaction
            DB::beginTransaction();

            // Tạo đơn hàng với order_code đúng loại
            $order = Order::create([
                'user_id' => $userId,
                'order_code' => $orderType, // 'retail' hoặc 'preorder'
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'],
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $totalAmount + $shippingFee - $discountAmount,
                'order_status' => 0, // Pending
            ]);

            Log::info('Order created:', ['order_id' => $order->id, 'type' => $orderType]);

            // Tạo chi tiết đơn hàng và cập nhật stock
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (int) $item['price'];
                $subtotal = $price * $quantity;

                // Tạo order detail
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Cập nhật stock: CHỈ GIẢM STOCK CHO RETAIL
                // Pre-order KHÔNG giảm stock vì chưa có hàng
                if ($orderType === 'retail') {
                    if ($variant->stock < $quantity) {
                        throw new \Exception("Sản phẩm không đủ hàng. Còn {$variant->stock}, yêu cầu {$quantity}");
                    }
                    $variant->stock -= $quantity;
                    $variant->save();
                    Log::info("Stock updated for variant {$variant->id}: new stock {$variant->stock}");
                } else {
                    Log::info("Pre-order: Stock not reduced for variant {$variant->id}");
                }
            }

            // Tạo thanh toán
            Payment::create([
                'order_id' => $order->id,
                'transaction_code' => 'PAY-' . $order->id . '-' . time(),
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount + $shippingFee - $discountAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            DB::commit();

            // Tạo mã đơn hàng hiển thị
            $displayCode = 'ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order->load(['details', 'payment']),
                'order_display_code' => $displayCode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xuất tất cả đơn hàng (không phân biệt loại)
     */
    public function export(Request $request)
    {
        try {
            $orders = Order::with(['details.productVariant.product', 'payment'])
                ->latest()
                ->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            $export = new OrdersExport('all', $formattedOrders);
            $date = now()->format('Ymd');
            $filename = "{$date}_tat_ca_don_hang.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Export all orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Xuất đơn hàng theo bộ lọc
     */
    public function exportWithFilters(Request $request)
    {
        try {
            $type = $request->input('type', 'retail');
            $status = $request->input('status', 'all');
            $search = $request->input('search', '');
            
            $query = Order::with(['details.productVariant.product', 'payment'])
                ->where('order_code', $type);
            
            if ($status !== 'all') {
                $statusMap = [
                    'pending' => 0, 'processing' => 1, 'shipping' => 2,
                    'completed' => 3, 'cancelled' => 4, 'approved' => 1,
                    'production' => 2, 'confirmed' => 1, 'waiting' => 2,
                ];
                if (isset($statusMap[$status])) {
                    $query->where('order_status', $statusMap[$status]);
                }
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('customer_name', 'LIKE', "%{$search}%")
                      ->orWhere('receiver_name', 'LIKE', "%{$search}%")
                      ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                      ->orWhere('receiver_phone', 'LIKE', "%{$search}%");
                });
            }
            
            $orders = $query->latest()->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            $export = new OrdersExport($type, $formattedOrders);
            
            $typeLabels = ['retail' => 'ban_le', 'wholesale' => 'ban_si', 'preorder' => 'preorder'];
            $typeLabel = $typeLabels[$type] ?? 'don_hang';
            $statusLabel = $status !== 'all' ? "_" . $status : "";
            $date = now()->format('Ymd');
            $filename = "{$date}_don_hang_{$typeLabel}{$statusLabel}.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Export filtered orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Format order data for export
     */
    protected function formatOrderForExport($order)
    {
        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
            $payment = 'Ví điện tử';
        }

        $productList = $products->map(function ($item) {
            return $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ';
        })->implode('; ');

        return (object) [
            'id' => $order->id,
            'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'type' => $order->order_code ?? 'retail',
            'customer_name' => $order->customer_name ?? $order->receiver_name,
            'customer_phone' => $order->customer_phone ?? $order->receiver_phone,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'created_date' => $order->created_at->format('d/m/Y H:i'),
            'products' => $productList,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'payment_method' => $payment,
            'status' => $order->getStatusLabel(),
            'note' => $order->note ?? '',
        ];
    }

    protected function getStatusText($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        $maps = [
            'retail' => [0 => 'pending', 1 => 'processing', 2 => 'shipping', 3 => 'completed', 4 => 'cancelled'],
            'wholesale' => [0 => 'pending', 1 => 'approved', 2 => 'production', 3 => 'shipping', 4 => 'completed', 5 => 'cancelled'],
            'preorder' => [0 => 'pending', 1 => 'confirmed', 2 => 'waiting', 3 => 'shipping', 4 => 'completed', 5 => 'cancelled'],
        ];
        return $maps[$orderCode][$status] ?? 'pending';
    }

    protected function getStatusLabel($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        $maps = [
            'retail' => [0 => 'Chờ xử lý', 1 => 'Đang xử lý', 2 => 'Đang giao', 3 => 'Hoàn thành', 4 => 'Đã hủy'],
            'wholesale' => [0 => 'Chờ xác nhận', 1 => 'Đã duyệt', 2 => 'Đang sản xuất', 3 => 'Đang giao', 4 => 'Hoàn thành', 5 => 'Đã hủy'],
            'preorder' => [0 => 'Chờ xác nhận', 1 => 'Đã xác nhận', 2 => 'Chờ hàng', 3 => 'Đang giao', 4 => 'Hoàn thành', 5 => 'Đã hủy'],
        ];
        return $maps[$orderCode][$status] ?? 'Chờ xử lý';
    }

    protected function getStatusMapForOrder($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $maps = [
            'retail' => ['pending' => 0, 'processing' => 1, 'shipping' => 2, 'completed' => 3, 'cancelled' => 4],
            'wholesale' => ['pending' => 0, 'approved' => 1, 'production' => 2, 'shipping' => 3, 'completed' => 4, 'cancelled' => 5],
            'preorder' => ['pending' => 0, 'confirmed' => 1, 'waiting' => 2, 'shipping' => 3, 'completed' => 4, 'cancelled' => 5],
        ];
        return $maps[$orderCode] ?? [];
    }
}

Đây là code app/Http/Controllers/Admin/OrderController.php  của tôi 
Chỉnh và đưa code hoàn chỉnh


## Assistant:
07/13/2026 08:01

Tôi thấy vấn đề: Trong `Admin/OrderController.php`, mã đơn hàng đang được tạo là `ORD-` + ID (3 số) thay vì format mới `L1307202600030`.

Đây là file `Admin/OrderController.php` đã được chỉnh sửa hoàn chỉnh:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\ProductVariant;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Hiển thị danh sách đơn hàng theo loại
     */
    public function index($type = 'retail')
    {
        $validTypes = ['retail', 'wholesale', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'retail';

        $orders = Order::with(['details.productVariant.product', 'payment'])
            ->where('order_code', $type)
            ->latest()
            ->get()
            ->map(function ($order) {
                $products = $order->details->map(function ($detail) {
                    $variant = $detail->productVariant;
                    $product = $variant ? $variant->product : null;
                    return [
                        'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                        'quantity' => $detail->quantity,
                        'price'    => (int) $detail->unit_price,
                        'subtotal' => (int) $detail->subtotal,
                        'image'    => $product ? ($product->image_url[0] ?? '/images/default-product.jpg') : '/images/default-product.jpg',
                    ];
                });

                $subtotal = $products->sum('subtotal');
                $shipping = (int) ($order->shipping_fee ?? 0);
                $discount = (int) ($order->discount_amount ?? 0);
                $final = $subtotal + $shipping - $discount;

                $payment = 'COD';
                $paymentClass = 'bg-green-100 text-green-800';
                if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
                    $payment = 'Chuyển khoản';
                    $paymentClass = 'bg-blue-100 text-blue-800';
                } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
                    $payment = 'Ví điện tử';
                    $paymentClass = 'bg-purple-100 text-purple-800';
                }

                // Sử dụng mã hiển thị mới
                $displayCode = $this->generateOrderDisplayCode($order);

                return [
                    'id'              => $order->id,
                    'code'            => $displayCode, // Thay đổi từ #ORD-xxx thành mã mới
                    'display_code'    => $displayCode,
                    'customer'        => $order->customer_name ?? $order->receiver_name,
                    'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
                    'receiver'        => $order->receiver_name,
                    'receiver_phone'  => $order->receiver_phone,
                    'date'            => $order->created_at->format('d/m/Y'),
                    'subtotal'        => $subtotal,
                    'shipping_fee'    => $shipping,
                    'discount_amount' => $discount,
                    'final_amount'    => $final,
                    'amount'          => $final,
                    'payment'         => $payment,
                    'paymentClass'    => $paymentClass,
                    'status'          => $this->getStatusText($order),
                    'statusLabel'     => $this->getStatusLabel($order),
                    'type'            => $order->order_code ?? 'retail',
                    'address'         => $order->shipping_address,
                    'note'            => $order->note,
                    'products'        => $products,
                ];
            });

        return Inertia::render('Admin/Orders', [
            'type'          => $type,
            'initialOrders' => $orders->all(),
        ]);
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with(['details.productVariant.product', 'payment'])->findOrFail($id);

        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price'    => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
                'image'    => $product ? ($product->image_url[0] ?? '/images/default-product.jpg') : '/images/default-product.jpg',
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
            $payment = 'Ví điện tử';
        }

        // Sử dụng mã hiển thị mới
        $displayCode = $this->generateOrderDisplayCode($order);

        $orderData = [
            'id'              => $order->id,
            'code'            => $displayCode, // Thay đổi từ #ORD-xxx thành mã mới
            'display_code'    => $displayCode,
            'customer'        => $order->customer_name ?? $order->receiver_name,
            'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
            'receiver'        => $order->receiver_name,
            'receiver_phone'  => $order->receiver_phone,
            'date'            => $order->created_at->format('d/m/Y'),
            'subtotal'        => $subtotal,
            'shipping_fee'    => $shipping,
            'discount_amount' => $discount,
            'final_amount'    => $final,
            'amount'          => $final,
            'payment'         => $payment,
            'status'          => $this->getStatusText($order),
            'statusLabel'     => $this->getStatusLabel($order),
            'type'            => $order->order_code ?? 'retail',
            'address'         => $order->shipping_address,
            'note'            => $order->note,
            'products'        => $products,
        ];

        return Inertia::render('Admin/Orders/Show', ['order' => $orderData]);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $order = Order::findOrFail($id);
            $newStatus = $request->status;
            $statusMap = $this->getStatusMapForOrder($order);
            
            if (!isset($statusMap[$newStatus])) {
                return back()->with('error', 'Trạng thái không hợp lệ');
            }
            
            $statusInt = $statusMap[$newStatus];
            $order->order_status = $statusInt;
            $order->save();

            Log::info("Order #{$order->id} status updated to: {$newStatus}");

            return back()->with('success', 'Cập nhật trạng thái thành công');
        } catch (\Exception $e) {
            Log::error('Update order status error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    }

    /**
     * Tạo đơn hàng mới (từ PaymentController gọi)
     */
    public function store(Request $request)
    {
        Log::info('Admin\OrderController@store called', $request->all());
        
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'required|email|max:255',
                'receiver_name' => 'required|string|max:255',
                'receiver_phone' => 'required|string|max:20',
                'shipping_address' => 'required|string|max:500',
                'note' => 'nullable|string|max:500',
                'payment_method' => 'required|in:cod,ewallet,bank_transfer,vnpay,momo',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'order_type' => 'required|in:retail,preorder',
            ]);

            $orderType = $validated['order_type'];
            $userId = Auth::id();
            $totalAmount = (int) $validated['total_amount'];
            $shippingFee = 0;
            $discountAmount = 0;

            Log::info('Creating order with type: ' . $orderType . ' for user: ' . $userId);

            // Bắt đầu transaction
            DB::beginTransaction();

            // Tạo đơn hàng với order_code đúng loại
            $order = Order::create([
                'user_id' => $userId,
                'order_code' => $orderType, // 'retail' hoặc 'preorder'
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'],
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $totalAmount + $shippingFee - $discountAmount,
                'order_status' => 0, // Pending
            ]);

            Log::info('Order created:', ['order_id' => $order->id, 'type' => $orderType]);

            // Tạo chi tiết đơn hàng và cập nhật stock
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::find($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (int) $item['price'];
                $subtotal = $price * $quantity;

                // Tạo order detail
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Cập nhật stock: CHỈ GIẢM STOCK CHO RETAIL
                // Pre-order KHÔNG giảm stock vì chưa có hàng
                if ($orderType === 'retail') {
                    if ($variant->stock < $quantity) {
                        throw new \Exception("Sản phẩm không đủ hàng. Còn {$variant->stock}, yêu cầu {$quantity}");
                    }
                    $variant->stock -= $quantity;
                    $variant->save();
                    Log::info("Stock updated for variant {$variant->id}: new stock {$variant->stock}");
                } else {
                    Log::info("Pre-order: Stock not reduced for variant {$variant->id}");
                }
            }

            // Tạo thanh toán
            Payment::create([
                'order_id' => $order->id,
                'transaction_code' => 'PAY-' . $order->id . '-' . time(),
                'payment_method' => $validated['payment_method'],
                'amount' => $totalAmount + $shippingFee - $discountAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            DB::commit();

            // QUAN TRỌNG: Tạo mã đơn hàng hiển thị theo format mới
            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('✅ Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order->load(['details', 'payment']),
                'order_display_code' => $displayCode, // QUAN TRỌNG: Trả về mã mới
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mã đơn hàng hiển thị - GIỐNG VỚI ORDERHISTORY
     * Format: [Loại đơn hàng][Ngày tạo dmY][ID 5 số]
     * Ví dụ: L1307202600019 (L + 13072026 + 00019)
     * 
     * @param Order $order
     * @return string
     */
    public function generateOrderDisplayCode($order)
    {
        // Nếu truyền vào là ID
        if (is_numeric($order)) {
            $order = Order::find($order);
            if (!$order) {
                return 'DH' . now()->format('dmY') . '00001';
            }
        }

        // Xác định prefix dựa trên loại đơn hàng
        $prefix = match($order->order_code) {
            'retail' => 'L',
            'wholesale' => 'S',
            'preorder' => 'P',
            default => 'DH'
        };

        // Dùng ngày hiện tại format dmY (ngày-tháng-năm)
        $date = now()->format('dmY'); // Ví dụ: 13072026
        
        // Dùng ID của order làm sequence, format 5 số (VD: 00019)
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);

        return $prefix . $date . $sequence;
    }

    /**
     * Xuất tất cả đơn hàng (không phân biệt loại)
     */
    public function export(Request $request)
    {
        try {
            $orders = Order::with(['details.productVariant.product', 'payment'])
                ->latest()
                ->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            $export = new OrdersExport('all', $formattedOrders);
            $date = now()->format('Ymd');
            $filename = "{$date}_tat_ca_don_hang.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Export all orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Xuất đơn hàng theo bộ lọc
     */
    public function exportWithFilters(Request $request)
    {
        try {
            $type = $request->input('type', 'retail');
            $status = $request->input('status', 'all');
            $search = $request->input('search', '');
            
            $query = Order::with(['details.productVariant.product', 'payment'])
                ->where('order_code', $type);
            
            if ($status !== 'all') {
                $statusMap = [
                    'pending' => 0, 'processing' => 1, 'shipping' => 2,
                    'completed' => 3, 'cancelled' => 4, 'approved' => 1,
                    'production' => 2, 'confirmed' => 1, 'waiting' => 2,
                ];
                if (isset($statusMap[$status])) {
                    $query->where('order_status', $statusMap[$status]);
                }
            }
            
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                      ->orWhere('customer_name', 'LIKE', "%{$search}%")
                      ->orWhere('receiver_name', 'LIKE', "%{$search}%")
                      ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                      ->orWhere('receiver_phone', 'LIKE', "%{$search}%");
                });
            }
            
            $orders = $query->latest()->get();
            
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }
            
            $formattedOrders = $orders->map(function ($order) {
                return $this->formatOrderForExport($order);
            });
            
            $export = new OrdersExport($type, $formattedOrders);
            
            $typeLabels = ['retail' => 'ban_le', 'wholesale' => 'ban_si', 'preorder' => 'preorder'];
            $typeLabel = $typeLabels[$type] ?? 'don_hang';
            $statusLabel = $status !== 'all' ? "_" . $status : "";
            $date = now()->format('Ymd');
            $filename = "{$date}_don_hang_{$typeLabel}{$statusLabel}.xlsx";
            
            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Export filtered orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    /**
     * Format order data for export
     */
    protected function formatOrderForExport($order)
    {
        $products = $order->details->map(function ($detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            return [
                'name' => $product ? $product->name : 'Sản phẩm không xác định',
                'quantity' => $detail->quantity,
                'price' => (int) $detail->unit_price,
                'subtotal' => (int) $detail->subtotal,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->payment_method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
        } elseif ($order->payment && $order->payment->payment_method === 'ewallet') {
            $payment = 'Ví điện tử';
        }

        $productList = $products->map(function ($item) {
            return $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ';
        })->implode('; ');

        // Sử dụng mã hiển thị mới
        $displayCode = $this->generateOrderDisplayCode($order);

        return (object) [
            'id' => $order->id,
            'code' => $displayCode, // Thay đổi từ #ORD-xxx thành mã mới
            'display_code' => $displayCode,
            'type' => $order->order_code ?? 'retail',
            'customer_name' => $order->customer_name ?? $order->receiver_name,
            'customer_phone' => $order->customer_phone ?? $order->receiver_phone,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'created_date' => $order->created_at->format('d/m/Y H:i'),
            'products' => $productList,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'payment_method' => $payment,
            'status' => $order->getStatusLabel(),
            'note' => $order->note ?? '',
        ];
    }

    protected function getStatusText($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        $maps = [
            'retail' => [0 => 'pending', 1 => 'processing', 2 => 'shipping', 3 => 'completed', 4 => 'cancelled'],
            'wholesale' => [0 => 'pending', 1 => 'approved', 2 => 'production', 3 => 'shipping', 4 => 'completed', 5 => 'cancelled'],
            'preorder' => [0 => 'pending', 1 => 'confirmed', 2 => 'waiting', 3 => 'shipping', 4 => 'completed', 5 => 'cancelled'],
        ];
        return $maps[$orderCode][$status] ?? 'pending';
    }

    protected function getStatusLabel($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $status = $order->order_status;
        $maps = [
            'retail' => [0 => 'Chờ xử lý', 1 => 'Đang xử lý', 2 => 'Đang giao', 3 => 'Hoàn thành', 4 => 'Đã hủy'],
            'wholesale' => [0 => 'Chờ xác nhận', 1 => 'Đã duyệt', 2 => 'Đang sản xuất', 3 => 'Đang giao', 4 => 'Hoàn thành', 5 => 'Đã hủy'],
            'preorder' => [0 => 'Chờ xác nhận', 1 => 'Đã xác nhận', 2 => 'Chờ hàng', 3 => 'Đang giao', 4 => 'Hoàn thành', 5 => 'Đã hủy'],
        ];
        return $maps[$orderCode][$status] ?? 'Chờ xử lý';
    }

    protected function getStatusMapForOrder($order)
    {
        $orderCode = $order->order_code ?? 'retail';
        $maps = [
            'retail' => ['pending' => 0, 'processing' => 1, 'shipping' => 2, 'completed' => 3, 'cancelled' => 4],
            'wholesale' => ['pending' => 0, 'approved' => 1, 'production' => 2, 'shipping' => 3, 'completed' => 4, 'cancelled' => 5],
            'preorder' => ['pending' => 0, 'confirmed' => 1, 'waiting' => 2, 'shipping' => 3, 'completed' => 4, 'cancelled' => 5],
        ];
        return $maps[$orderCode] ?? [];
    }
}
```

## Các thay đổi chính:

1. **Thêm phương thức `generateOrderDisplayCode()`** - Tạo mã đơn hàng theo format mới:
   - Prefix: `L` cho retail, `S` cho wholesale, `P` cho preorder
   - Ngày: `dmY` (ví dụ: 13072026)
   - Sequence: ID 5 số (ví dụ: 00019)
   - Kết quả: `L1307202600019`

2. **Sửa phương thức `store()`**:
   - Gọi `generateOrderDisplayCode()` để tạo mã mới
   - Trả về `order_display_code` trong response

3. **Sửa phương thức `index()` và `show()`**:
   - Sử dụng mã mới thay vì `#ORD-xxx`

4. **Sửa phương thức `formatOrderForExport()`**:
   - Sử dụng mã mới cho export

Sau khi thay thế file này, mã đơn hàng sẽ hiển thị đúng format `L1307202600030` thay vì `ORD-033`.


## User:
07/13/2026 08:12

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
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-200 z-50"
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
            <Link 
              :href="route('profile.edit')" 
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              @click="closeDropdown"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">person</span>
              Hồ sơ
            </Link>
            <button 
              @click="handleLogout" 
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
              <span class="material-symbols-outlined text-base mr-2 align-middle">logout</span>
              Đăng xuất
            </button>
          </div>
        </div>

        <!-- Giỏ hàng -->
        <Link :href="route('cart')" class="relative p-2 hover:scale-95 duration-200 text-gray-600 hover:text-primary">
          <span class="material-symbols-outlined">shopping_bag</span>
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartCount }}</span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const cartCount = ref(3)

// Dropdown state
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

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
    console.warn(`Invalid slug: ${slug}`, e)
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
const handleLogout = () => {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      window.location.href = route('home')
    }
  })
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
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
</style>

Thêm cho tôi nút xem lịch sử đơn hàng đi


## Assistant:
07/13/2026 08:12

Tôi sẽ thêm nút "Lịch sử đơn hàng" vào dropdown user menu trong file `AppHeader.vue`:

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
            
            <!-- LỊCH SỬ ĐƠN HÀNG - THÊM MỚI -->
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
          <span v-if="cartCount > 0" class="absolute top-1 right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ cartCount }}</span>
        </Link>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth?.user || null)
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])

const searchKeyword = ref('')
const cartCount = ref(3)

// Dropdown state
const dropdownOpen = ref(false)
const userDropdownRef = ref(null)

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
    console.warn(`Invalid slug: ${slug}`, e)
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
const handleLogout = () => {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      window.location.href = route('home')
    }
  })
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
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
</style>
```

## Các thay đổi chính:

1. **Thêm mục "Lịch sử đơn hàng"** vào dropdown menu:
```vue
<!-- LỊCH SỬ ĐƠN HÀNG - THÊM MỚI -->
<Link 
  :href="route('orders.history')" 
  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
  @click="closeDropdown"
>
  <span class="material-symbols-outlined text-base mr-2 align-middle">receipt_long</span>
  Lịch sử đơn hàng
</Link>
```

2. **Sắp xếp lại menu** theo thứ tự ưu tiên:
   - Dashboard (chỉ admin)
   - **Lịch sử đơn hàng** (thêm mới)
   - Hồ sơ
   - Đăng xuất (có border phân cách)

3. **Thay đổi kích thước dropdown** từ `w-48` thành `w-52` để chứa văn bản dài hơn.

Khi người dùng click vào "Lịch sử đơn hàng", họ sẽ được chuyển đến trang `/lich-su-don-hang` (route `orders.history`).



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
