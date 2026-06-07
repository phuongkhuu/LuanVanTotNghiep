<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index($type = 'retail')
{
    $validTypes = ['retail', 'wholesale', 'preorder'];
    $type = in_array($type, $validTypes) ? $type : 'retail';

    // Lấy tất cả đơn hàng (không lọc theo type)
    $allOrders = Order::latest()->get()->map(function ($order) {
        $statusMap = [
            0 => 'pending',
            1 => 'processing',
            2 => 'shipping',
            3 => 'completed',
            4 => 'cancelled',
        ];
        $statusText = $statusMap[$order->order_status] ?? 'pending';

        $payment = 'COD';
        $paymentClass = 'bg-green-100 text-green-800';
        if ($order->order_code === 'wholesale') {
            $payment = 'Chuyển khoản';
            $paymentClass = 'bg-blue-100 text-blue-800';
        }

        return [
            'id' => $order->id,
            'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
            'customer' => $order->receiver_name,
            'phone' => $order->receiver_phone,
            'date' => $order->created_at->format('d/m/Y'),
            'amount' => (int) $order->final_amount,
            'payment' => $payment,
            'paymentClass' => $paymentClass,
            'status' => $statusText,
            'type' => $order->order_code,
            'address' => $order->shipping_address,
            'products' => [], // có thể lấy từ order_details sau
        ];
    });

    return Inertia::render('Admin/Orders', [
        'type' => $type,
        'initialOrders' => $allOrders, // trả về tất cả
    ]);
}

    public function show($id)
    {
        $order = Order::with('user', 'discount', 'campaign')->findOrFail($id);
        // Nếu bạn có trang chi tiết riêng, hãy tạo Inertia page 'Admin/Orders/Show'
        // Hoặc có thể trả về cùng view với modal, nhưng hiện tại giữ nguyên
        return Inertia::render('Admin/Orders/Show', ['order' => $order]);
    }

    public function updateStatus($id)
    {
        $order = Order::findOrFail($id);
        $order->order_status = request()->status;
        $order->save();

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    public function export()
    {
        // Logic export Excel
        return back()->with('success', 'Xuất file thành công');
    }
}