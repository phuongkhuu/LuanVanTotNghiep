<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Campaign;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private function updatePreorderSalePrice($campaign)
    {
        try {
            $product = Product::find($campaign->product_id);
            if (!$product) {
                Log::warning('Product not found for pre-order sale price update', [
                    'campaign_id' => $campaign->id,
                    'product_id' => $campaign->product_id
                ]);
                return;
            }

            $tiers = $campaign->tiers ?? [];
            $currentBuyers = $campaign->current_buyers ?? 0;

            // Sắp xếp tiers theo from
            usort($tiers, function($a, $b) {
                return ($a['from'] ?? 0) - ($b['from'] ?? 0);
            });

            // Tìm discount theo tier hiện tại
            $discountPercent = 0;
            if (!empty($tiers)) {
                $firstTier = $tiers[0];
                $discountPercent = $firstTier['discount'] ?? 0;
                
                foreach ($tiers as $tier) {
                    $from = $tier['from'] ?? 0;
                    $to = $tier['to'] ?? PHP_INT_MAX;
                    if ($currentBuyers >= $from && $currentBuyers <= $to) {
                        $discountPercent = $tier['discount'] ?? 0;
                        break;
                    }
                }
            }

            Log::info('Updating pre-order sale price:', [
                'campaign_id' => $campaign->id,
                'product_id' => $product->id,
                'current_buyers' => $currentBuyers,
                'discount_percent' => $discountPercent,
            ]);

            // Cập nhật sale_price cho từng variant
            foreach ($product->variants as $variant) {
                $originalPrice = $variant->price;
                $salePrice = $originalPrice * (1 - $discountPercent / 100);
                $salePrice = round($salePrice);

                $variant->update([
                    'sale_price' => $salePrice,
                    'is_on_sale' => $discountPercent > 0,
                    'sale_type' => 'preorder',
                    'sale_campaign_id' => $campaign->id,
                ]);

                Log::info('Variant sale price updated:', [
                    'variant_id' => $variant->id,
                    'original_price' => $originalPrice,
                    'sale_price' => $salePrice,
                    'discount_percent' => $discountPercent,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating pre-order sale price: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'trace' => $e->getTraceAsString()
            ]);
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
                'promo_code' => 'nullable|string',
                'discount_amount' => 'nullable|numeric|min:0',
            ]);

            $orderType = $validated['order_type'];
            $userId = Auth::id();
            
            $totalAmount = (int) $validated['total_amount'];
            $discountAmount = (int) ($validated['discount_amount'] ?? 0);
            $promoCode = $validated['promo_code'] ?? null;
            $shippingFee = 0;
            $finalAmount = $totalAmount;

            Log::info('Creating order with type: ' . $orderType, [
                'total_amount_from_request' => $totalAmount,
                'discount_amount' => $discountAmount,
                'promo_code' => $promoCode,
                'final_amount' => $finalAmount,
            ]);

            DB::beginTransaction();

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $userId,
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
                'promo_code' => $promoCode,
                'final_amount' => $finalAmount,
                'order_status' => 0, // Pending
            ]);

            Log::info('Order created:', [
                'order_id' => $order->id,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'promo_code' => $promoCode,
            ]);

            // Cập nhật số lượng đã sử dụng voucher
            if ($promoCode) {
                $voucher = Campaign::where('code', $promoCode)
                    ->where('type', 'voucher')
                    ->first();
                
                if ($voucher) {
                    $voucher->increment('used');
                    Log::info('Voucher used count updated:', [
                        'code' => $promoCode,
                        'used' => $voucher->used,
                    ]);
                } else {
                    Log::warning('Voucher not found for update:', ['code' => $promoCode]);
                }
            }

            // Lưu product_id để cập nhật pre-order
            $productIds = [];

            // Tạo chi tiết đơn hàng
            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (int) $item['price'];
                $subtotal = $price * $quantity;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Lưu product_id để cập nhật pre-order
                if ($variant && $variant->product) {
                    $productIds[] = $variant->product->id;
                }

                // Cập nhật stock cho retail
                if ($orderType === 'retail') {
                    if ($variant->stock < $quantity) {
                        throw new \Exception("Sản phẩm không đủ hàng. Còn {$variant->stock}, yêu cầu {$quantity}");
                    }
                    $variant->stock -= $quantity;
                    $variant->save();
                }
            }

            // ============ CẬP NHẬT SỐ LƯỢT MUA PRE-ORDER ============
            if ($orderType === 'preorder') {
                // Lấy danh sách product_id duy nhất
                $productIds = array_unique($productIds);
                
                foreach ($productIds as $productId) {
                    // Tìm pre-order active cho sản phẩm này
                    $preorder = Campaign::where('type', 'preorder')
                        ->where('status', 'active')
                        ->where('product_id', $productId)
                        ->where(function($query) {
                            $query->where('end_time', '>=', now())
                                  ->orWhereNull('end_time');
                        })
                        ->first();
                    
                    if ($preorder) {
                        // Tính tổng số lượng đã đặt trong đơn hàng này cho sản phẩm đó
                        $totalQuantity = 0;
                        foreach ($validated['items'] as $item) {
                            $variant = ProductVariant::find($item['id']);
                            if ($variant && $variant->product_id == $productId) {
                                $totalQuantity += (int) $item['quantity'];
                            }
                        }
                        
                        // Cập nhật current_buyers (mỗi sản phẩm đặt là 1 lượt)
                        $preorder->increment('current_buyers', $totalQuantity);
                        
                        // Lấy số lượng mới nhất sau khi increment
                        $preorder->refresh();
                        
                        // Cập nhật lại sale_price sau khi tăng current_buyers
                        $this->updatePreorderSalePrice($preorder);
                        
                        Log::info('✅ Pre-order buyers updated:', [
                            'campaign_id' => $preorder->id,
                            'product_id' => $productId,
                            'increment' => $totalQuantity,
                            'new_total' => $preorder->current_buyers,
                            'new_discount' => $preorder->current_discount ?? 0,
                        ]);
                    } else {
                        Log::warning('No active pre-order found for product:', ['product_id' => $productId]);
                    }
                }
            }

            // Tạo thanh toán
            Payment::create([
                'order_id' => $order->id,
                'transaction_code' => 'PAY-' . $order->id . '-' . time(),
                'payment_method' => $validated['payment_method'],
                'amount' => $finalAmount,
                'payment_date' => now(),
                'status' => 'pending',
            ]);

            DB::commit();

            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('✅ Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'promo_code' => $promoCode,
                'order_type' => $orderType,
            ]);

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

                $displayCode = $this->generateOrderDisplayCode($order);

                return [
                    'id'              => $order->id,
                    'code'            => $displayCode,
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

        $displayCode = $this->generateOrderDisplayCode($order);

        $orderData = [
            'id'              => $order->id,
            'code'            => $displayCode,
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
     * Xuất tất cả đơn hàng
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

        $displayCode = $this->generateOrderDisplayCode($order);

        return (object) [
            'id' => $order->id,
            'code' => $displayCode,
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