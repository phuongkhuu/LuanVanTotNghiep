<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QrScanController extends Controller
{
    /**
     * Hiển thị thông tin đơn hàng từ QR
     */
    public function show(Request $request)
    {
        $qrData = $request->query('data');
        
        if (!$qrData) {
            return Inertia::render('Web/QrScanError', [
                'error' => 'Không có dữ liệu QR'
            ]);
        }

        try {
            $orderData = json_decode($qrData, true);
            
            if (!$orderData || !isset($orderData['order_id'])) {
                return Inertia::render('Web/QrScanError', [
                    'error' => 'Dữ liệu QR không hợp lệ'
                ]);
            }

            $order = Order::with([
                'details.productVariant.product',
                'details.productVariant.color',
                'payment',
                'user'
            ])->find($orderData['order_id']);

            if (!$order) {
                return Inertia::render('Web/QrScanError', [
                    'error' => 'Không tìm thấy đơn hàng'
                ]);
            }

            $isOwner = Auth::check() && $order->user_id === Auth::id();
            $formattedOrder = $this->formatOrder($order, $isOwner);

            return Inertia::render('Web/QrScanResult', [
                'order' => $formattedOrder,
                'is_owner' => $isOwner,
                'is_authenticated' => Auth::check()
            ]);

        } catch (\Exception $e) {
            Log::error('QR Scan Error: ' . $e->getMessage());
            return Inertia::render('Web/QrScanError', [
                'error' => 'Có lỗi xảy ra khi xử lý dữ liệu'
            ]);
        }
    }

    private function formatOrder($order, $isOwner)
    {
        $displayCode = $this->generateDisplayCode($order);
        
        $formatted = [
            'id' => $order->id,
            'display_code' => $displayCode,
            'order_code' => $order->order_code,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'receiver_name' => $order->receiver_name,
            'receiver_phone' => $order->receiver_phone,
            'shipping_address' => $order->shipping_address,
            'total_amount' => (int) $order->total_amount,
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
                    'product_name' => $product ? $product->name : 'Sản phẩm',
                    'color_name' => $variant && $variant->color ? $variant->color->name : null,
                    'size_name' => $variant ? $variant->size_name : null,
                    'image' => $product ? $this->getProductImage($product) : '/images/default-product.jpg',
                ];
            }),
        ];

        if ($isOwner) {
            $formatted['customer_email'] = $order->customer_email ?? $order->user->email ?? 'N/A';
            $formatted['payment'] = $order->payment ? [
                'payment_method' => $order->payment->payment_method,
                'status' => $order->payment->status,
                'transaction_code' => $order->payment->transaction_code,
            ] : null;
            $formatted['note'] = $order->note;
            $formatted['discount_amount'] = (int) $order->discount_amount;
            $formatted['shipping_fee'] = (int) $order->shipping_fee;
        }

        return $formatted;
    }

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