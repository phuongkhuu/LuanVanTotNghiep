<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Exports\MultiSheetCustomersExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'retail');

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
            $orderTypes = Order::where('customer_phone', $item->customer_phone)
                ->distinct('order_code')
                ->pluck('order_code')
                ->toArray();
            
            $type = 'retail';
            if (in_array('preorder', $orderTypes)) {
                $type = 'preorder';
            } elseif (in_array('wholesale', $orderTypes)) {
                $type = 'wholesale';
            }
            
            return [
                'phone'           => $item->customer_phone ?? '',
                'name'            => $item->name ?? 'Khách hàng',
                'address'         => $item->address ?? '',
                'last_order_date' => $item->last_order_date ? Carbon::parse($item->last_order_date)->format('d/m/Y') : null,
                'orders_count'    => (int) ($item->orders_count ?? 0),
                'total_spent'     => (float) ($item->total_spent ?? 0),
                'join_date'       => $item->join_date ? Carbon::parse($item->join_date)->format('d/m/Y') : null,
                'type'            => $type,
            ];
        });

        $counts = [
            'all'       => Order::whereNotNull('customer_phone')->distinct('customer_phone')->count('customer_phone'),
            'retail'    => Order::whereNotNull('customer_phone')->where('order_code', 'retail')->distinct('customer_phone')->count('customer_phone'),
            'wholesale' => Order::whereNotNull('customer_phone')->where('order_code', 'wholesale')->distinct('customer_phone')->count('customer_phone'),
            'preorder'  => Order::whereNotNull('customer_phone')->where('order_code', 'preorder')->distinct('customer_phone')->count('customer_phone'),
        ];

        return Inertia::render('Admin/Customers', [
            'customers' => $customers,
            'type'      => $type,
            'counts'    => $counts,
        ]);
    }

    public function show($phone, Request $request)
    {
        $type = $request->input('type', 'all');

        $orderCodes = match ($type) {
            'retail'    => ['retail'],
            'wholesale' => ['wholesale'],
            'preorder'  => ['preorder'],
            'all'       => ['retail', 'wholesale', 'preorder'],
            default     => ['retail', 'wholesale', 'preorder'],
        };

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

    /**
     * EXPORT CUSTOMERS - LỌC ĐÚNG THEO TAB
     */
    public function export(Request $request)
    {
        try {
            // Lấy type từ query parameter (GET)
            $type = $request->query('type', 'all');

            // Xác định order codes dựa trên type
            $orderCodes = match ($type) {
                'retail'    => ['retail'],
                'wholesale' => ['wholesale'],
                'preorder'  => ['preorder'],
                'all'       => ['retail', 'wholesale', 'preorder'],
                default     => ['retail', 'wholesale', 'preorder'],
            };

            // Lấy danh sách khách hàng dựa trên order codes
            $customers = Order::select(
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
                ->groupBy('customer_phone')
                ->orderByDesc('total_spent')
                ->get();

            if ($customers->isEmpty()) {
                $typeLabel = match ($type) {
                    'retail' => 'bán lẻ',
                    'wholesale' => 'doanh nghiệp',
                    'preorder' => 'pre-order',
                    'all' => '',
                    default => '',
                };
                return back()->with('error', 'Không có khách hàng ' . $typeLabel . ' nào để xuất');
            }

            // Format dữ liệu khách hàng
            $formattedCustomers = $customers->map(function ($item) {
                // Lấy tất cả loại đơn hàng của khách hàng này
                $allOrderTypes = Order::where('customer_phone', $item->customer_phone)
                    ->distinct('order_code')
                    ->pluck('order_code')
                    ->toArray();
                
                // Xác định loại khách hàng chính
                $customerType = 'retail';
                if (in_array('preorder', $allOrderTypes)) {
                    $customerType = 'preorder';
                } elseif (in_array('wholesale', $allOrderTypes)) {
                    $customerType = 'wholesale';
                }

                return [
                    'phone' => $item->customer_phone ?? '',
                    'name' => $item->name ?? 'Khách hàng',
                    'address' => $item->address ?? '',
                    'last_order_date' => $item->last_order_date ? Carbon::parse($item->last_order_date)->format('d/m/Y') : '',
                    'orders_count' => (int) ($item->orders_count ?? 0),
                    'total_spent' => (float) ($item->total_spent ?? 0),
                    'join_date' => $item->join_date ? Carbon::parse($item->join_date)->format('d/m/Y') : '',
                    'type' => $customerType,
                ];
            });

            // Tạo export với dữ liệu đã format
            $export = new MultiSheetCustomersExport($type, $formattedCustomers);

            // Tạo tên file dựa trên type
            $typeLabels = [
                'all' => 'tat_ca',
                'retail' => 'khach_le',
                'wholesale' => 'khach_doanh_nghiep',
                'preorder' => 'preorder'
            ];
            $typeLabel = $typeLabels[$type] ?? 'khach_hang';

            // Trả về file download
            return Excel::download($export, now()->format('Ymd') . '_danh_sach_khach_hang_' . $typeLabel . '.xlsx');

        } catch (\Exception $e) {
            Log::error('Export customers error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Có lỗi xảy ra khi xuất file: ' . $e->getMessage());
        }
    }
}