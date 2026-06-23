<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index($type = 'retail')
    {
        $validTypes = ['retail', 'wholesale', 'preorder'];
        $type = in_array($type, $validTypes) ? $type : 'retail';

        $orders = Order::with(['details.productVariant.product', 'payment'])
            ->latest()
            ->get()
            ->map(function ($order) {
                $products = $order->details->map(function ($detail) {
                    $variant = $detail->productVariant;
                    $product = $variant ? $variant->product : null;
                    return [
                        'name'     => $product ? $product->name : 'Sản phẩm không xác định',
                        'quantity' => $detail->quantity,
                        'price'    => (int) $detail->unit_price,      // giá 1 sản phẩm
                        'subtotal' => (int) $detail->subtotal,       // thành tiền = price * quantity
                        'image'    => $product ? $product->image : null,
                    ];
                });

                $subtotal = $products->sum('subtotal');
                $shipping = (int) ($order->shipping_fee ?? 0);
                $discount = (int) ($order->discount_amount ?? 0);
                $final = $subtotal + $shipping - $discount; // luôn tính lại

                $payment = 'COD';
                $paymentClass = 'bg-green-100 text-green-800';
                if ($order->payment && $order->payment->method === 'bank_transfer') {
                    $payment = 'Chuyển khoản';
                    $paymentClass = 'bg-blue-100 text-blue-800';
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
                'image'    => $product ? $product->image : null,
            ];
        });

        $subtotal = $products->sum('subtotal');
        $shipping = (int) ($order->shipping_fee ?? 0);
        $discount = (int) ($order->discount_amount ?? 0);
        $final = $subtotal + $shipping - $discount;

        $payment = 'COD';
        if ($order->payment && $order->payment->method === 'bank_transfer') {
            $payment = 'Chuyển khoản';
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

    public function updateStatus($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->status;
        $statusMap = $this->getStatusMapForOrder($order);
        $statusInt = $statusMap[$newStatus] ?? 0;

        $order->order_status = $statusInt;
        $order->save();

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    public function export()
    {

        return back()->with('success', 'Xuất file thành công');
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