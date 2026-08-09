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
                
                // Tạo dữ liệu cho QR code
                $qrData = $this->generateQrData($order);
                
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
                    'qr_data' => $qrData,
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
     * Tạo dữ liệu cho QR code
     */
    private function generateQrData($order)
    {
        $orderData = [
            'order_id' => $order->id,
            'display_code' => $this->generateDisplayCode($order),
            'order_code' => $order->order_code,
            'total_amount' => (int) $order->total_amount,
            'final_amount' => (int) $order->final_amount,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email ?? $order->user?->email ?? 'N/A',
            'order_status' => $order->order_status,
            'created_at' => $order->created_at->toISOString(),
        ];
        
        return json_encode($orderData);
    }

    /**
     * Tạo mã hiển thị cho đơn hàng
     */
    private function generateDisplayCode($order)
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

    /**
     * API lấy thông tin đơn hàng từ mã QR
     */
    public function getOrderByQr(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập'
                ], 401);
            }

            $request->validate([
                'qr_data' => 'required|string'
            ]);

            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['order_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu QR không hợp lệ'
                ], 400);
            }

            $order = Order::where('id', $qrData['order_id'])
                ->where('user_id', $user->id)
                ->with([
                    'details.productVariant.product',
                    'details.productVariant.color',
                    'payment',
                    'user'
                ])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng'
                ], 404);
            }

            $displayCode = $this->generateDisplayCode($order);
            $customerEmail = $order->customer_email ?? $order->user?->email ?? 'N/A';

            $formattedOrder = [
                'id' => $order->id,
                'display_code' => $displayCode,
                'order_code' => $order->order_code,
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

            return response()->json([
                'success' => true,
                'order' => $formattedOrder
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting order by QR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}