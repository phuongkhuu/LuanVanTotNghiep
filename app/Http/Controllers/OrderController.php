<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\Payment;
use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Cập nhật sale_price cho pre-order
     */
    private function updatePreorderSalePrice($campaign)
    {
        try {
            $product = Product::find($campaign->product_id);
            if (!$product) return;

            $tiers = $campaign->tiers ?? [];
            $currentBuyers = $campaign->current_buyers ?? 0;

            usort($tiers, function($a, $b) {
                return ($a['from'] ?? 0) - ($b['from'] ?? 0);
            });

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

            foreach ($product->variants as $variant) {
                $originalPrice = $variant->price;
                $salePrice = round($originalPrice * (1 - $discountPercent / 100));

                $variant->update([
                    'sale_price' => $salePrice,
                    'is_on_sale' => $discountPercent > 0,
                    'sale_type' => 'preorder',
                    'sale_campaign_id' => $campaign->id,
                ]);
            }

            Log::info('Pre-order sale price updated:', [
                'campaign_id' => $campaign->id,
                'current_buyers' => $currentBuyers,
                'discount_percent' => $discountPercent,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating pre-order sale price: ' . $e->getMessage());
        }
    }

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

            // 1. Kiểm tra tồn kho và lấy campaign_id cho preorder
            $productIds = [];
            $campaignIdForOrder = null; // Sẽ lưu campaign_id của sản phẩm preorder

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                if (!$variant) {
                    throw new \Exception('Sản phẩm không tồn tại');
                }
                if ($variant->stock < $item['quantity'] && $orderType !== 'preorder') {
                    $productName = $variant->product->name ?? 'Sản phẩm';
                    throw new \Exception("Sản phẩm {$productName} không đủ hàng. Còn {$variant->stock} sản phẩm");
                }
                if ($variant->product) {
                    $productIds[] = $variant->product->id;
                }

                // Nếu là preorder, tìm campaign và lưu campaign_id
                if ($orderType === 'preorder' && $variant->product) {
                    $productId = $variant->product->id;
                    $preorder = Campaign::where('type', 'preorder')
                        ->where('status', 'active')
                        ->where('product_id', $productId)
                        ->where(function($query) {
                            $query->where('end_time', '>=', now())
                                  ->orWhereNull('end_time');
                        })
                        ->first();

                    if ($preorder) {
                        // Lưu campaign_id (nếu có nhiều sản phẩm, ta lưu sản phẩm đầu tiên hoặc có thể xử lý riêng)
                        if (!$campaignIdForOrder) {
                            $campaignIdForOrder = $preorder->id;
                        }
                        // Log để debug
                        Log::info('Found preorder campaign for product', [
                            'product_id' => $productId,
                            'campaign_id' => $preorder->id,
                            'campaign_name' => $preorder->name,
                        ]);
                    } else {
                        Log::warning('No active preorder campaign found for product', ['product_id' => $productId]);
                    }
                }
            }

            // 2. Tạo đơn hàng (gán campaign_id)
            $order = Order::create([
                'user_id' => $user ? $user->id : null,
                'discount_id' => null,
                'campaign_id' => $campaignIdForOrder, // Gán campaign_id tìm được
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

            Log::info('Order created with campaign_id', [
                'order_id' => $order->id,
                'campaign_id' => $order->campaign_id,
                'order_type' => $orderType,
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

                // Chỉ cập nhật stock cho retail
                if ($orderType !== 'preorder') {
                    $variant->decrement('stock', $item['quantity']);
                }
            }

            // ============ CẬP NHẬT SỐ LƯỢT MUA PRE-ORDER ============
            if ($orderType === 'preorder') {
                $productIds = array_unique($productIds);
                $productCampaignIds = []; // để log

                foreach ($productIds as $productId) {
                    $preorder = Campaign::where('type', 'preorder')
                        ->where('status', 'active')
                        ->where('product_id', $productId)
                        ->where(function($query) {
                            $query->where('end_time', '>=', now())
                                  ->orWhereNull('end_time');
                        })
                        ->first();
                    
                    if ($preorder) {
                        // Tính tổng số lượng đã đặt
                        $totalQuantity = 0;
                        foreach ($validated['items'] as $item) {
                            $variant = ProductVariant::find($item['id']);
                            if ($variant && $variant->product_id == $productId) {
                                $totalQuantity += (int) $item['quantity'];
                            }
                        }
                        
                        $preorder->increment('current_buyers', $totalQuantity);
                        $preorder->refresh();
                        
                        // Cập nhật lại sale_price
                        $this->updatePreorderSalePrice($preorder);
                        
                        Log::info('Pre-order buyers updated (User):', [
                            'campaign_id' => $preorder->id,
                            'product_id' => $productId,
                            'increment' => $totalQuantity,
                            'new_total' => $preorder->current_buyers,
                        ]);
                    }
                }
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

            $displayCode = $this->generateOrderDisplayCode($order);

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'display_code' => $displayCode,
                'order_type' => $orderType,
                'campaign_id' => $order->campaign_id,
                'created_at' => $order->created_at->format('dmY H:i:s'),
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
     * Tạo mã đơn hàng hiển thị cho khách
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
        
        $order->payment_status = $order->payment->status ?? 'pending';

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