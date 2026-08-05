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
    /**
     * Cập nhật giá khuyến mãi theo tier của pre-order
     */
    private function updatePreorderSalePrice($campaign)
    {
        try {
            $product = Product::find($campaign->product_id);
            if (!$product) {
                return;
            }

            $tiers = $campaign->tiers ?? [];
            $currentBuyers = $campaign->current_buyers ?? 0;

            usort($tiers, fn($a, $b) => ($a['from'] ?? 0) - ($b['from'] ?? 0));

            $discountPercent = 0;
            if (!empty($tiers)) {
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
                $salePrice = round($variant->price * (1 - $discountPercent / 100));
                $variant->update([
                    'sale_price' => $salePrice,
                    'is_on_sale' => $discountPercent > 0,
                    'sale_type' => 'preorder',
                    'sale_campaign_id' => $campaign->id,
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
        try {
            $validated = $request->validate([
                'customer_name'   => 'required|string|max:255',
                'customer_phone'  => 'required|string|max:20',
                'customer_email'  => 'required|email|max:255',
                'receiver_name'   => 'required|string|max:255',
                'receiver_phone'  => 'required|string|max:20',
                'shipping_address'=> 'required|string|max:500',
                'note'            => 'nullable|string|max:500',
                'payment_method'  => 'required|in:cod,ewallet,bank_transfer,vnpay,momo,payos',
                'items'           => 'required|array|min:1',
                'items.*.id'      => 'required|exists:product_variants,id',
                'items.*.quantity'=> 'required|integer|min:1',
                'items.*.price'   => 'required|numeric|min:0',
                'total_amount'    => 'required|numeric|min:0',
                'order_type'      => 'required|in:retail,preorder,wholesale',
                'promo_code'      => 'nullable|string',
                'discount_amount' => 'nullable|numeric|min:0',
                'deposit_amount'  => 'nullable|numeric|min:0',
                'remaining_amount'=> 'nullable|numeric|min:0',
                'payment_status'  => 'nullable|string|in:pending,deposit_paid,paid,failed',
            ]);

            $orderType = $validated['order_type'];
            $userId = Auth::id();

            $totalAmount = (int) $validated['total_amount'];
            $discountAmount = (int) ($validated['discount_amount'] ?? 0);
            $promoCode = $validated['promo_code'] ?? null;
            $finalAmount = $totalAmount;

            $campaignId = null;
            if ($orderType === 'preorder') {
                foreach ($validated['items'] as $item) {
                    $variant = ProductVariant::with('product')->find($item['id']);
                    if (!$variant || !$variant->product) continue;

                    $preorder = Campaign::where('type', 'preorder')
                        ->where('status', 'active')
                        ->where('product_id', $variant->product->id)
                        ->where(function($query) {
                            $query->where('end_time', '>=', now())
                                  ->orWhereNull('end_time');
                        })
                        ->first();

                    if ($preorder) {
                        $campaignId = $preorder->id;
                        break;
                    }
                }
            }

            DB::beginTransaction();

            $order = Order::create([
                'user_id'          => $userId,
                'order_code'       => $orderType,
                'campaign_id'      => $campaignId,
                'customer_name'    => $validated['customer_name'],
                'customer_phone'   => $validated['customer_phone'],
                'customer_email'   => $validated['customer_email'],
                'receiver_name'    => $validated['receiver_name'],
                'receiver_phone'   => $validated['receiver_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note'             => $validated['note'] ?? null,
                'shipping_fee'     => 0,
                'total_amount'     => $totalAmount,
                'discount_amount'  => $discountAmount,
                'promo_code'       => $promoCode,
                'final_amount'     => $finalAmount,
                'deposit_amount'   => $validated['deposit_amount'] ?? 0,
                'remaining_amount' => $validated['remaining_amount'] ?? 0,
                'payment_status'   => $validated['payment_status'] ?? 'pending',
                'order_status'     => 0,
            ]);

            $orderNumber = $this->generateOrderNumber($order);
            $order->order_number = $orderNumber;
            $order->save();

            if ($promoCode) {
                $voucher = Campaign::where('code', $promoCode)->where('type', 'voucher')->first();
                if ($voucher) {
                    $voucher->increment('used');
                }
            }

            $productIds = [];

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->find($item['id']);
                $quantity = (int) $item['quantity'];
                $price = (int) $item['price'];
                $subtotal = $price * $quantity;

                OrderDetail::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $quantity,
                    'unit_price'         => $price,
                    'subtotal'           => $subtotal,
                ]);

                if ($variant && $variant->product) {
                    $productIds[] = $variant->product->id;
                }

                if ($orderType === 'retail') {
                    if ($variant->stock < $quantity) {
                        throw new \Exception("Sản phẩm không đủ hàng. Còn {$variant->stock}, yêu cầu {$quantity}");
                    }
                    $variant->stock -= $quantity;
                    $variant->save();
                }
            }

            if ($orderType === 'preorder') {
                $productIds = array_unique($productIds);
                foreach ($productIds as $productId) {
                    $preorder = Campaign::where('type', 'preorder')
                        ->where('status', 'active')
                        ->where('product_id', $productId)
                        ->where(fn($q) => $q->where('end_time', '>=', now())->orWhereNull('end_time'))
                        ->first();

                    if ($preorder) {
                        $totalQuantity = 0;
                        foreach ($validated['items'] as $item) {
                            $variant = ProductVariant::find($item['id']);
                            if ($variant && $variant->product_id == $productId) {
                                $totalQuantity += (int) $item['quantity'];
                            }
                        }
                        $preorder->increment('current_buyers', $totalQuantity);
                        $preorder->refresh();
                        $this->updatePreorderSalePrice($preorder);
                    }
                }
            }

            Payment::create([
                'order_id'          => $order->id,
                'transaction_code'  => 'PAY-' . $order->id . '-' . time(),
                'payment_method'    => $validated['payment_method'],
                'amount'            => $finalAmount,
                'payment_date'      => now(),
                'status'            => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order' => $order->load(['details', 'payment']),
                'order_display_code' => $orderNumber,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateOrderNumber($order)
    {
        $prefix = match($order->order_code) {
            'retail'    => 'L',
            'wholesale' => 'S',
            'preorder'  => 'P',
            default     => 'DH',
        };
        $date = now()->format('dmY');
        $sequence = str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $code = $prefix . $date . $sequence;
        
        $existing = Order::where('order_number', $code)->exists();
        if ($existing) {
            $suffix = rand(10, 99);
            $code = $prefix . $date . $sequence . $suffix;
        }
        
        return $code;
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
                if ($order->payment) {
                    $method = $order->payment->payment_method;
                    if ($method === 'bank_transfer') {
                        $payment = 'Chuyển khoản';
                        $paymentClass = 'bg-blue-100 text-blue-800';
                    } elseif ($method === 'ewallet') {
                        $payment = 'Ví điện tử';
                        $paymentClass = 'bg-purple-100 text-purple-800';
                    } elseif ($method === 'payos') {
                        $payment = 'PayOS';
                        $paymentClass = 'bg-indigo-100 text-indigo-800';
                    }
                }

                return [
                    'id'              => $order->id,
                    'code'            => $order->order_number,
                    'display_code'    => $order->order_number,
                    'customer'        => $order->customer_name ?? $order->receiver_name,
                    'customer_phone'  => $order->customer_phone ?? $order->receiver_phone,
                    'customer_email'  => $order->customer_email ?? 'N/A',
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
                    'status'          => $order->status_text,
                    'statusLabel'     => $order->status_label,
                    'type'            => $order->order_code ?? 'retail',
                    'address'         => $order->shipping_address,
                    'note'            => $order->note,
                    'products'        => $products,
                ];
            });
         $counts = [
            'retail'    => Order::where('order_code', 'retail')->count(),
            'wholesale' => Order::where('order_code', 'wholesale')->count(),
            'preorder'  => Order::where('order_code', 'preorder')->count(),
        ];

        return Inertia::render('Admin/Orders', [
            'type'          => $type,
            'initialOrders' => $orders->all(),
            'counts'        => $counts,
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
        if ($order->payment) {
            $method = $order->payment->payment_method;
            if ($method === 'bank_transfer') $payment = 'Chuyển khoản';
            elseif ($method === 'ewallet') $payment = 'Ví điện tử';
            elseif ($method === 'payos') $payment = 'PayOS';
        }

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id'              => $order->id,
                'code'            => $order->order_number,
                'display_code'    => $order->order_number,
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
                'status'          => $order->status_text,
                'statusLabel'     => $order->status_label,
                'type'            => $order->order_code ?? 'retail',
                'address'         => $order->shipping_address,
                'note'            => $order->note,
                'products'        => $products,
            ]
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        try {
            $order = Order::findOrFail($id);
            $newStatus = $request->status;
            $statusMap = $order->getStatusMap();

            if (!isset($statusMap[$newStatus])) {
                return back()->with('error', 'Trạng thái không hợp lệ');
            }

            $order->order_status = $statusMap[$newStatus];
            $order->save();

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

        return $order->order_number ?? $this->fallbackOrderCode($order);
    }

    private function fallbackOrderCode($order)
    {
        $prefix = match($order->order_code) {
            'retail'    => 'L',
            'wholesale' => 'S',
            'preorder'  => 'P',
            default     => 'DH',
        };
        return $prefix . now()->format('dmY') . str_pad($order->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * ==========================================
     * CHỨC NĂNG IN ĐƠN HÀNG
     * ==========================================
     */
    public function printOrderHtml($id)
    {
        try {
            // Xóa tất cả output buffer
            if (ob_get_length()) {
                ob_clean();
            }
            
            $order = Order::with([
                'details.productVariant.product',
                'details.productVariant.color',
                'payment',
                'user'
            ])->findOrFail($id);

            $html = $this->generatePrintHtml($order);

            // Trả về response với content-type là text/html
            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Print order HTML error: ' . $e->getMessage(), [
                'order_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi in đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generatePrintHtml($order)
    {
        // Bắt đầu output buffer
        ob_start();
        
        $displayCode = $this->generateOrderDisplayCode($order);
        
        $email = $order->customer_email;
        if (empty($email) || $email === 'N/A') {
            $email = $order->user?->email ?? 'N/A';
        }

        $detailsHtml = '';
        foreach ($order->details as $detail) {
            $variant = $detail->productVariant;
            $product = $variant ? $variant->product : null;
            $productName = $product ? $product->name : 'Sản phẩm không xác định';
            
            $detailsHtml .= '<tr>
                <td style="padding: 8px 12px; border: 1px solid #ddd;">' . $productName . '</td>
                <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: center;">' . $detail->quantity . '</td>
                <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">' . number_format($detail->unit_price) . '₫</td>
                <td style="padding: 8px 12px; border: 1px solid #ddd; text-align: right;">' . number_format($detail->subtotal) . '₫</td>
            </tr>';
        }

        $statusBadge = [
            'pending' => 'badge-pending',
            'processing' => 'badge-processing',
            'shipping' => 'badge-shipping',
            'completed' => 'badge-completed',
            'cancelled' => 'badge-cancelled'
        ];
        $statusClass = $statusBadge[$order->status_text] ?? 'badge-pending';

        $paymentMethod = 'COD';
        if ($order->payment) {
            $method = $order->payment->payment_method;
            $paymentMethod = match($method) {
                'bank_transfer' => 'Chuyển khoản ngân hàng',
                'ewallet' => 'Ví điện tử',
                'vnpay' => 'VNPay',
                'momo' => 'MoMo',
                'payos' => 'PayOS',
                default => 'COD'
            };
        }

        $paymentStatus = 'Chờ thanh toán';
        if ($order->payment) {
            $status = $order->payment->status;
            $paymentStatus = match($status) {
                'paid' => 'Đã thanh toán',
                'failed' => 'Thanh toán thất bại',
                'refunded' => 'Đã hoàn tiền',
                'pending' => 'Chờ thanh toán',
                default => $status
            };
        }

        // Tạo HTML hoàn chỉnh, KHÔNG có khoảng trắng trước DOCTYPE
        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đơn hàng #' . $displayCode . '</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;padding:40px;max-width:800px;margin:0 auto;color:#333}
h1{color:#1a56db;border-bottom:2px solid #1a56db;padding-bottom:10px;margin-bottom:20px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0}
.info-box{background:#f9fafb;padding:15px;border-radius:8px}
.info-box h3{margin:0 0 10px 0;color:#6b7280;font-size:14px;text-transform:uppercase}
.info-box p{margin:5px 0;font-size:14px}
table{width:100%;border-collapse:collapse;margin:20px 0}
th{background:#f9fafb;text-align:left;padding:10px 12px;border:1px solid #ddd}
td{padding:8px 12px;border:1px solid #ddd}
.footer{margin-top:40px;text-align:center;color:#6b7280;font-size:12px;border-top:1px solid #ddd;padding-top:20px}
.badge{display:inline-block;padding:4px 12px;border-radius:9999px;font-size:12px;font-weight:bold}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-processing{background:#dbeafe;color:#1e40af}
.badge-shipping{background:#f3e8ff;color:#6b21a8}
.badge-completed{background:#d1fae5;color:#065f46}
.badge-cancelled{background:#fee2e2;color:#991b1b}
.text-center{text-align:center}
.text-right{text-align:right}
.font-bold{font-weight:bold}
.text-red{color:red}
</style>
</head>
<body>
<h1>HÓA ĐƠN ĐẶT HÀNG</h1>
<p><strong>Mã đơn hàng:</strong> ' . $displayCode . '</p>
<p><strong>Ngày đặt:</strong> ' . ($order->created_at ? date('d/m/Y H:i', strtotime($order->created_at)) : 'N/A') . '</p>
<div class="info-grid">
<div class="info-box">
<h3>Thông tin người đặt</h3>
<p><strong>Họ tên:</strong> ' . $order->customer_name . '</p>
<p><strong>Email:</strong> ' . $email . '</p>
<p><strong>SĐT:</strong> ' . $order->customer_phone . '</p>
</div>
<div class="info-box">
<h3>Thông tin người nhận</h3>
<p><strong>Họ tên:</strong> ' . $order->receiver_name . '</p>
<p><strong>SĐT:</strong> ' . $order->receiver_phone . '</p>
<p><strong>Địa chỉ:</strong> ' . $order->shipping_address . '</p>
</div>
</div>
<h3>Danh sách sản phẩm</h3>
<table>
<thead>
<tr>
<th>Sản phẩm</th>
<th style="text-align:center;">Số lượng</th>
<th style="text-align:right;">Đơn giá</th>
<th style="text-align:right;">Thành tiền</th>
</tr>
</thead>
<tbody>' . $detailsHtml . '</tbody>
<tfoot>
<tr>
<td colspan="3" style="text-align:right;font-weight:bold;">Tạm tính</td>
<td style="text-align:right;">' . number_format($order->total_amount) . '₫</td>
</tr>';

        if ($order->shipping_fee > 0) {
            $html .= '<tr>
<td colspan="3" style="text-align:right;">Phí vận chuyển</td>
<td style="text-align:right;">' . number_format($order->shipping_fee) . '₫</td>
</tr>';
        }

        if ($order->discount_amount > 0) {
            $html .= '<tr>
<td colspan="3" style="text-align:right;">Giảm giá</td>
<td style="text-align:right;color:red;">-' . number_format($order->discount_amount) . '₫</td>
</tr>';
        }

        $html .= '<tr>
<td colspan="3" style="text-align:right;font-weight:bold;font-size:18px;">Tổng cộng</td>
<td style="text-align:right;font-weight:bold;font-size:18px;color:#1a56db;">' . number_format($order->final_amount) . '₫</td>
</tr>
</tfoot>
</table>
<div style="margin-top:20px;">
<p><strong>Trạng thái:</strong> <span class="badge ' . $statusClass . '">' . $order->status_label . '</span></p>
<p><strong>Phương thức thanh toán:</strong> ' . $paymentMethod . '</p>
<p><strong>Trạng thái thanh toán:</strong> ' . $paymentStatus . '</p>
</div>';

        if ($order->note) {
            $html .= '<p><strong>Ghi chú:</strong> ' . $order->note . '</p>';
        }

        $html .= '<div class="footer">
<p>Cảm ơn bạn đã mua hàng tại BigBag!</p>
<p>Hotline: 1900 1234 | Email: support@bigbag.vn</p>
<p style="font-size:10px;color:#9ca3af;">Hóa đơn được tạo tự động</p>
</div>
</body>
</html>';

        // Xóa output buffer và trả về HTML
        ob_end_clean();
        
        return $html;
    }


    /* -------------------- EXPORT -------------------- */

    public function export(Request $request)
    {
        try {
            $orders = Order::with(['details.productVariant.product', 'payment'])->latest()->get();
            if ($orders->isEmpty()) {
                return back()->with('error', 'Không có đơn hàng nào để xuất');
            }

            $formatted = $orders->map(fn($o) => $this->formatOrderForExport($o));
            $export = new OrdersExport('all', $formatted);
            return Excel::download($export, now()->format('Ymd') . '_tat_ca_don_hang.xlsx');
        } catch (\Exception $e) {
            Log::error('Export all orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

    public function exportWithFilters(Request $request)
    {
        try {
            $type = $request->input('type', 'retail');
            $status = $request->input('status', 'all');
            $search = $request->input('search', '');

            $query = Order::with(['details.productVariant.product', 'payment'])->where('order_code', $type);

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

            $formatted = $orders->map(fn($o) => $this->formatOrderForExport($o));
            $export = new OrdersExport($type, $formatted);

            $typeLabels = ['retail' => 'ban_le', 'wholesale' => 'ban_si', 'preorder' => 'preorder'];
            $typeLabel = $typeLabels[$type] ?? 'don_hang';
            $statusLabel = $status !== 'all' ? "_" . $status : "";
            return Excel::download($export, now()->format('Ymd') . "_don_hang_{$typeLabel}{$statusLabel}.xlsx");
        } catch (\Exception $e) {
            Log::error('Export filtered orders error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }

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
        if ($order->payment) {
            $method = $order->payment->payment_method;
            if ($method === 'bank_transfer') $payment = 'Chuyển khoản';
            elseif ($method === 'ewallet') $payment = 'Ví điện tử';
            elseif ($method === 'payos') $payment = 'PayOS';
        }

        $productList = $products->map(fn($item) => $item['name'] . ' x' . $item['quantity'] . ' = ' . number_format($item['subtotal']) . 'đ')
            ->implode('; ');

        return (object) [
            'id' => $order->id,
            'code' => $order->order_number,
            'display_code' => $order->order_number,
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
            'status' => $order->status_label,
            'note' => $order->note ?? '',
        ];
    }
}