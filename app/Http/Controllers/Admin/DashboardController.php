<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // 'week' or 'month'

        // Thống kê tổng quan
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Doanh thu theo loại hình hôm nay
        $todayRevenue = [
            'retail' => $this->getRevenueByType('retail', $today),
            'wholesale' => $this->getRevenueByType('wholesale', $today),
            'preorder' => $this->getRevenueByType('preorder', $today),
        ];

        // Tăng trưởng so với hôm qua
        $growth = [
            'retail' => $this->calcGrowth('retail', $today, $yesterday),
            'wholesale' => $this->calcGrowth('wholesale', $today, $yesterday),
            'preorder' => $this->calcGrowth('preorder', $today, $yesterday),
        ];

        // Tổng số đơn hàng, khách hàng, sản phẩm tồn kho thấp
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        $lowStockProducts = ProductVariant::where('stock', '<', 10)->count();

        // Đơn hàng gần đây (5 đơn)
        $recentOrders = Order::with(['details.productVariant.product'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'code' => '#ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT),
                    'customer' => $order->customer_name ?? $order->receiver_name,
                    'type' => $this->getTypeLabel($order->order_code),
                    'amount' => number_format($order->final_amount, 0, ',', '.') . '₫',
                    'status' => $this->getStatusLabel($order->order_status),
                    'statusClass' => $this->getStatusClass($order->order_status),
                ];
            });

        // Top sản phẩm bán chạy theo loại hình (số lượng bán)
        $topRetail = $this->getTopProducts('retail', 3);
        $topWholesale = $this->getTopProducts('wholesale', 3);
        $topPreorder = $this->getTopProducts('preorder', 3);

        // Dữ liệu biểu đồ theo period
        if ($period === 'week') {
            $chartData = $this->getRevenueChartData('week');
        } else {
            $chartData = $this->getRevenueChartData('month');
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'todayRevenue' => $todayRevenue,
                'totalOrders' => $totalOrders,
                'totalCustomers' => $totalCustomers,
                'lowStockProducts' => $lowStockProducts,
            ],
            'growth' => $growth,
            'recentOrders' => $recentOrders,
            'topRetail' => $topRetail,
            'topWholesale' => $topWholesale,
            'topPreorder' => $topPreorder,
            'chartWeek' => $period === 'week' ? $chartData : null,
            'chartMonth' => $period === 'month' ? $chartData : null,
            'currentPeriod' => $period,
        ]);
    }

    // ==================== Helper Methods ====================

    /**
     * Lấy tổng doanh thu (final_amount) theo loại hình và ngày
     */
    private function getRevenueByType($type, $date)
    {
        return Order::where('order_code', $type)
            ->whereDate('created_at', $date)
            ->sum('final_amount') ?: 0;
    }

    /**
     * Tính phần trăm tăng trưởng so với hôm qua
     */
    private function calcGrowth($type, $today, $yesterday)
    {
        $todayRevenue = $this->getRevenueByType($type, $today);
        $yesterdayRevenue = $this->getRevenueByType($type, $yesterday);

        if ($yesterdayRevenue == 0) {
            return $todayRevenue > 0 ? 100 : 0;
        }

        return round(($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue * 100, 1);
    }

    /**
     * Top sản phẩm bán chạy theo loại hình (dựa trên số lượng bán)
     */
    private function getTopProducts($type, $limit)
    {
        return Order::where('order_code', $type)
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(order_details.quantity) as sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                ];
            });
    }

    /**
     * Dữ liệu biểu đồ doanh thu theo tuần hoặc tháng
     */
    private function getRevenueChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'week') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $this->getVietnameseDayOfWeek($date->dayOfWeek);
                $retail[] = $this->getRevenueByType('retail', $date) / 1000000; // triệu
                $wholesale[] = $this->getRevenueByType('wholesale', $date) / 1000000;
                $preorder[] = $this->getRevenueByType('preorder', $date) / 1000000;
            }
        } else {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = Order::where('order_code', 'retail')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('final_amount') / 1000000;
                $wholesale[] = Order::where('order_code', 'wholesale')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('final_amount') / 1000000;
                $preorder[] = Order::where('order_code', 'preorder')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('final_amount') / 1000000;
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

    /**
     * Chuyển đổi số thứ tự ngày trong tuần sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [
            0 => 'CN',
            1 => 'T2',
            2 => 'T3',
            3 => 'T4',
            4 => 'T5',
            5 => 'T6',
            6 => 'T7',
        ];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }

    /**
     * Nhãn cho loại đơn hàng
     */
    private function getTypeLabel($type)
    {
        $map = [
            'retail' => 'Bán lẻ',
            'wholesale' => 'Bán sỉ',
            'preorder' => 'Pre-order',
        ];
        return $map[$type] ?? $type;
    }

    /**
     * Nhãn trạng thái đơn hàng
     */
    private function getStatusLabel($status)
    {
        $map = [
            0 => 'Chờ xác nhận',
            1 => 'Đang xử lý',
            2 => 'Đang giao',
            3 => 'Hoàn thành',
            4 => 'Đã hủy',
        ];
        return $map[$status] ?? 'Không xác định';
    }

    /**
     * Class CSS cho trạng thái
     */
    private function getStatusClass($status)
    {
        $map = [
            0 => 'bg-yellow-100 text-yellow-700',
            1 => 'bg-blue-100 text-blue-700',
            2 => 'bg-purple-100 text-purple-700',
            3 => 'bg-green-100 text-green-700',
            4 => 'bg-red-100 text-red-700',
        ];
        return $map[$status] ?? 'bg-gray-100 text-gray-700';
    }
}