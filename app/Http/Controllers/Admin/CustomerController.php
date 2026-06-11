<?php
// app/Http/Controllers/Admin/CustomerController.php

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
        $search = $request->get('search', '');

        // Xác định order_code dựa vào type
        $orderCodes = [];
        if ($type === 'retail') {
            $orderCodes = ['retail', 'preorder'];
        } elseif ($type === 'wholesale') {
            $orderCodes = ['wholesale'];
        } else {
            $orderCodes = ['retail', 'wholesale', 'preorder'];
        }

        $query = Order::select(
                'receiver_phone',
                DB::raw('MAX(receiver_name) as name'),
                DB::raw('MAX(shipping_address) as address'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(final_amount) as total_spent'),
                DB::raw('MIN(created_at) as join_date')
            )
            ->whereNotNull('receiver_phone')
            ->whereIn('order_code', $orderCodes)
            ->when($search, function($q) use ($search) {
                return $q->where(function($sq) use ($search) {
                    $sq->where('receiver_phone', 'like', "%{$search}%")
                       ->orWhere('receiver_name', 'like', "%{$search}%");
                });
            })
            ->groupBy('receiver_phone')
            ->orderByDesc('last_order_date');

        $customers = $query->paginate(15);

        $customers->getCollection()->transform(function ($item) {
            return [
                'phone'           => $item->receiver_phone ?? '',
                'name'            => $item->name ?? 'Khách hàng',
                'address'         => $item->address ?? '',
                'last_order_date' => $item->last_order_date ? Carbon::parse($item->last_order_date)->format('d/m/Y') : null,
                'orders_count'    => (int) ($item->orders_count ?? 0),
                'total_spent'     => (float) ($item->total_spent ?? 0),
                'join_date'       => $item->join_date ? Carbon::parse($item->join_date)->format('d/m/Y') : null,
            ];
        });

        return Inertia::render('Admin/Customers', [
            'customers' => $customers,
            'type'      => $type,
        ]);
    }

    public function show($phone)
    {
        $orders = Order::where('receiver_phone', $phone)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($order) {
                $displayType = $order->order_code;
                if ($displayType === 'retail') $displayType = 'Bán lẻ';
                elseif ($displayType === 'wholesale') $displayType = 'Bán sỉ';
                elseif ($displayType === 'preorder') $displayType = 'Pre-order';
                
                return [
                    'id'               => $order->id,
                    'order_code'       => $displayType,
                    'total_amount'     => (float) $order->final_amount,
                    'status'           => (int) $order->order_status,
                    'created_at'       => Carbon::parse($order->created_at)->format('d/m/Y H:i'),
                    'receiver_name'    => $order->receiver_name,
                    'receiver_phone'   => $order->receiver_phone,
                    'shipping_address' => $order->shipping_address,
                ];
            });

        $customer = Order::where('receiver_phone', $phone)
            ->select(
                'receiver_phone as phone',
                DB::raw('MAX(receiver_name) as name'),
                DB::raw('MAX(shipping_address) as address'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(final_amount) as total_spent'),
                DB::raw('MIN(created_at) as join_date')
            )
            ->groupBy('receiver_phone')
            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Không tìm thấy khách hàng'], 404);
        }

        return response()->json([
            'phone'           => $customer->phone ?? '',
            'name'            => $customer->name ?? 'Khách hàng',
            'address'         => $customer->address ?? '',
            'last_order_date' => $customer->last_order_date ? Carbon::parse($customer->last_order_date)->format('d/m/Y') : null,
            'orders_count'    => (int) ($customer->orders_count ?? 0),
            'total_spent'     => (float) ($customer->total_spent ?? 0),
            'join_date'       => $customer->join_date ? Carbon::parse($customer->join_date)->format('d/m/Y') : null,
            'orders'          => $orders,
        ]);
    }

    public function export(Request $request)
    {
        return back()->with('error', 'Tính năng đang phát triển');
    }
}