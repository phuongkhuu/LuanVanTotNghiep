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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'week'); // week, month, year

        $reportData = $this->getReportData($period);

        return Inertia::render('Admin/Reports', [
            'reportData' => $reportData,
            'currentPeriod' => $period,
        ]);
    }

    /**
     * API trả về dữ liệu dạng JSON (cho Vue gọi khi đổi period)
     */
    public function getData(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);
        return response()->json($reportData);
    }

    /**
     * Xuất báo cáo Excel
     */
    public function export(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);

        $export = new ReportExport($period, $reportData);
        $fileName = $export->getFileName();

        return Excel::download($export, $fileName);
    }

    // ==================== Phương thức lấy dữ liệu ====================

    private function getReportData($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // 1. Tổng quan doanh thu và tăng trưởng theo loại hình
        $summary = $this->getSummary($period);

        // 2. Dữ liệu biểu đồ doanh thu
        $chartData = $this->getChartData($period);

        // 3. Top sản phẩm bán chạy (theo doanh thu)
        $topProducts = $this->getTopProducts($startDate, $endDate, 5);

        // 4. Top khách hàng (theo tổng chi tiêu)
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 5);

        // 5. Phân bố danh mục (tính theo % doanh thu)
        $categoryDistribution = $this->getCategoryDistribution($startDate, $endDate);

        return [
            'summary' => $summary,
            'chartData' => $chartData,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'categoryDistribution' => $categoryDistribution,
        ];
    }

    private function getStartDate($period)
    {
        switch ($period) {
            case 'week':
                return Carbon::now()->subWeek();
            case 'month':
                return Carbon::now()->subMonth();
            case 'year':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subWeek();
        }
    }

    // ==================== Các helper chi tiết ====================

    /**
     * Tổng doanh thu và tăng trưởng của 3 loại hình
     */
    private function getSummary($period)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Nếu period là week, month, year thì có thể lấy doanh thu trong kỳ thay vì hôm nay
        // Ở đây ta vẫn lấy hôm nay để hiển thị trên thẻ
        $types = ['retail', 'wholesale', 'preorder'];
        $summary = [];

        foreach ($types as $type) {
            $revenueToday = Order::where('order_code', $type)
                ->whereDate('created_at', $today)
                ->sum('final_amount');

            $revenueYesterday = Order::where('order_code', $type)
                ->whereDate('created_at', $yesterday)
                ->sum('final_amount');

            $growth = 0;
            if ($revenueYesterday > 0) {
                $growth = round(($revenueToday - $revenueYesterday) / $revenueYesterday * 100, 1);
            } elseif ($revenueToday > 0) {
                $growth = 100;
            }

            $summary[$type] = [
                'revenue' => $revenueToday,
                'growth' => $growth,
            ];
        }

        return $summary;
    }

    /**
     * Dữ liệu biểu đồ cột doanh thu theo tuần/tháng/năm
     */
    private function getChartData($period)
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
                $retail[] = (int) Order::where('order_code', 'retail')->whereDate('created_at', $date)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereDate('created_at', $date)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereDate('created_at', $date)->sum('final_amount');
            }
        } elseif ($period === 'month') {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = 'Tuần ' . (4 - $i);
                $retail[] = (int) Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            }
        } else { // year
            // 12 tháng gần nhất
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->format('m/Y');
                $retail[] = (int) Order::where('order_code', 'retail')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereMonth('created_at', $month->month)->whereYear('created_at', $month->year)->sum('final_amount');
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
     * Top sản phẩm theo doanh thu
     */
    private function getTopProducts($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.name',
                \DB::raw('SUM(order_details.quantity) as sold'),
                \DB::raw('SUM(order_details.subtotal) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'sold' => (int) $item->sold,
                    'revenue' => (int) $item->revenue,
                ];
            });
    }

    /**
     * Top khách hàng theo tổng chi tiêu
     */
    private function getTopCustomers($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'orders.customer_name as name',
                \DB::raw('COUNT(orders.id) as orders'),
                \DB::raw('SUM(orders.final_amount) as total')
            )
            ->groupBy('orders.customer_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Khách lẻ',
                    'orders' => (int) $item->orders,
                    'total' => (int) $item->total,
                ];
            });
    }

    /**
     * Phân bố doanh thu theo danh mục (tính %)
     */
    private function getCategoryDistribution($startDate, $endDate)
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
        if ($total == 0) {
            return [
                ['label' => 'Chưa có dữ liệu', 'value' => 100]
            ];
        }

        $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.name')
            ->get();

        $result = $categories->map(function ($item) use ($total) {
            return [
                'label' => $item->name,
                'value' => round($item->revenue / $total * 100)
            ];
        })->sortByDesc('value')->values()->take(3);

        return $result->isEmpty() ? [['label' => 'Chưa có dữ liệu', 'value' => 100]] : $result;
    }

    /**
     * Chuyển đổi số thứ tự ngày sang tiếng Việt
     */
    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }
}