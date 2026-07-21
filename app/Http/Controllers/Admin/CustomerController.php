<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'retail');

        // Xác định danh sách order_code dựa trên type
        $orderCodes = match ($type) {
            'retail'    => ['retail'],
            'wholesale' => ['wholesale'],
            'preorder'  => ['preorder'],
            'all'       => ['retail', 'wholesale', 'preorder'],
            default     => ['retail', 'wholesale', 'preorder'],
        };

        $search = $request->get('search', '');

        $query = Order::select(
            'customer_phone',
            DB::raw('MAX(customer_name) as name'),
            DB::raw('MAX(shipping_address) as address'),
            DB::raw('MAX(created_at) as last_order_date'),
            DB::raw('COUNT(*) as orders_count'),
            DB::raw('SUM(
                COALESCE((SELECT SUM(subtotal) FROM order_details WHERE order_details.order_id = orders.id), 0)
                + COALESCE(shipping_fee, 0)
                - COALESCE(discount_amount, 0)
            ) as total_spent'),
            DB::raw('MIN(created_at) as join_date')
        )
            ->whereNotNull('customer_phone')
            ->whereIn('order_code', $orderCodes)
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($sq) use ($search) {
                    $sq->where('customer_phone', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%");
                });
            })
            ->groupBy('customer_phone')
            ->orderByDesc('last_order_date');

        $customers = $query->paginate(15);

        $customers->getCollection()->transform(function ($item) {
            return [
                'phone'           => $item->customer_phone ?? '',
                'name'            => $item->name ?? 'Khách hàng',
                'address'         => $item->address ?? '',
                'last_order_date' => $item->last_order_date ? Carbon::parse($item->last_order_date)->format('d/m/Y') : null,
                'orders_count'    => (int) ($item->orders_count ?? 0),
                'total_spent'     => (float) ($item->total_spent ?? 0),
                'join_date'       => $item->join_date ? Carbon::parse($item->join_date)->format('d/m/Y') : null,
            ];
        });

        // Đếm số lượng khách hàng theo từng loại
        $counts = [
            'all'       => Order::whereNotNull('customer_phone')->distinct('customer_phone')->count('customer_phone'),
            'retail'    => Order::whereNotNull('customer_phone')->where('order_code', 'retail')->distinct('customer_phone')->count('customer_phone'),
            'wholesale' => Order::whereNotNull('customer_phone')->where('order_code', 'wholesale')->distinct('customer_phone')->count('customer_phone'),
            'preorder'  => Order::whereNotNull('customer_phone')->where('order_code', 'preorder')->distinct('customer_phone')->count('customer_phone'),
        ];

        return Inertia::render('Admin/Customers', [
            'customers' => $customers,
            'type'      => $type,
            'counts'    => $counts, // Thêm counts
        ]);
    }

    public function show($phone, Request $request)
    {
        $type = $request->input('type', 'all');

        // Xác định danh sách order_code dựa trên type
        $orderCodes = match ($type) {
            'retail'    => ['retail'],
            'wholesale' => ['wholesale'],
            'preorder'  => ['preorder'],
            'all'       => ['retail', 'wholesale', 'preorder'],
            default     => ['retail', 'wholesale', 'preorder'],
        };

        // Lấy danh sách đơn hàng theo type
        $orders = Order::where('customer_phone', $phone)
            ->whereIn('order_code', $orderCodes)
            ->with('details')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                $subtotal = $order->details->sum('subtotal');
                $shipping = (float) ($order->shipping_fee ?? 0);
                $discount = (float) ($order->discount_amount ?? 0);
                $calculatedTotal = $subtotal + $shipping - $discount;

                $statusText = match ((int) $order->order_status) {
                    0 => 'Chờ xử lý',
                    1 => 'Đã xác nhận',
                    2 => 'Hoàn thành',
                    3 => 'Đã hủy',
                    default => 'Chờ xử lý',
                };

                $displayType = match ($order->order_code) {
                    'retail'    => 'Bán lẻ',
                    'wholesale' => 'Bán sỉ',
                    'preorder'  => 'Pre-order',
                    default     => $order->order_code,
                };

                return [
                    'id'               => $order->id,
                    'order_code'       => $displayType,
                    'total_amount'     => $calculatedTotal,
                    'status'           => (int) $order->order_status,
                    'status_text'      => $statusText,
                    'created_at'       => Carbon::parse($order->created_at)->format('d/m/Y H:i'),
                    'customer_name'    => $order->customer_name,
                    'customer_phone'   => $order->customer_phone,
                    'receiver_name'    => $order->receiver_name,
                    'receiver_phone'   => $order->receiver_phone,
                    'shipping_address' => $order->shipping_address,
                ];
            });

        $totalSpent = $orders->sum('total_amount');
        $ordersCount = $orders->count();

        // Lấy thông tin khách hàng (vẫn lấy từ tất cả đơn hàng, không phân biệt type)
        $customer = Order::where('customer_phone', $phone)
            ->select(
                'customer_phone as phone',
                DB::raw('MAX(customer_name) as name'),
                DB::raw('MAX(shipping_address) as address'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('MIN(created_at) as join_date')
            )
            ->groupBy('customer_phone')
            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 404);
        }

        return response()->json([
            'phone'           => $customer->phone ?? '',
            'name'            => $customer->name ?? 'Khách hàng',
            'address'         => $customer->address ?? '',
            'last_order_date' => $customer->last_order_date ? Carbon::parse($customer->last_order_date)->format('d/m/Y') : null,
            'orders_count'    => $ordersCount,
            'total_spent'     => $totalSpent,
            'join_date'       => $customer->join_date ? Carbon::parse($customer->join_date)->format('d/m/Y') : null,
            'orders'          => $orders,
        ]);
    }

    public function export(Request $request)
    {
        return back()->with('error', 'Tính năng đang phát triển');
    }
}