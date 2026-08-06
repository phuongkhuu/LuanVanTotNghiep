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
    public function index(Request $request)
    {
        $period = $request->input('period', 'day');
        $reportData = $this->getReportData($period);

        return Inertia::render('Admin/Reports', [
            'reportData' => $reportData,
            'currentPeriod' => $period,
        ]);
    }

    public function getData(Request $request)
    {
        $period = $request->input('period', 'week');
        $reportData = $this->getReportData($period);
        return response()->json($reportData);
    }

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

        $summary = $this->getSummary($period);
        $chartData = $this->getChartData($period);
        $topProducts = $this->getTopProducts($startDate, $endDate, 5);
        $topCustomers = $this->getTopCustomers($startDate, $endDate, 5);
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
            case 'day':    return Carbon::now()->subDay();
            case 'week':   return Carbon::now()->subWeek();
            case 'month':  return Carbon::now()->subMonth();
            case 'year':   return Carbon::now()->subYear();
            default:       return Carbon::now()->subWeek();
        }
    }

    private function getSummary($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'day':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDay()->startOfDay();
                $prevEnd = $now->copy()->subDay()->endOfDay();
                break;
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $prevStart = $now->copy()->subWeek()->startOfWeek();
                $prevEnd = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $prevStart = $now->copy()->subMonth()->startOfMonth();
                $prevEnd = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $prevStart = $now->copy()->subYear()->startOfYear();
                $prevEnd = $now->copy()->subYear()->endOfYear();
                break;
            default:
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $prevStart = $now->copy()->subWeek()->startOfWeek();
                $prevEnd = $now->copy()->subWeek()->endOfWeek();
        }

        $types = ['retail', 'wholesale', 'preorder'];
        $summary = [];

        foreach ($types as $type) {
            $revenueCurrent = Order::where('order_code', $type)
                ->whereBetween('created_at', [$start, $end])
                ->sum('final_amount');

            $revenuePrevious = Order::where('order_code', $type)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->sum('final_amount');

            $growth = 0;
            if ($revenuePrevious > 0) {
                $growth = round(($revenueCurrent - $revenuePrevious) / $revenuePrevious * 100, 1);
            } elseif ($revenueCurrent > 0) {
                $growth = 100;
            }

            $summary[$type] = [
                'revenue' => $revenueCurrent,
                'growth' => $growth,
            ];
        }

        return $summary;
    }

    private function getChartData($period)
    {
        $labels = [];
        $retail = [];
        $wholesale = [];
        $preorder = [];

        if ($period === 'day') {
            // 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $labels[] = $date->format('d/m');
                $retail[] = (int) Order::where('order_code', 'retail')->whereDate('created_at', $date)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereDate('created_at', $date)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereDate('created_at', $date)->sum('final_amount');
            }
        } elseif ($period === 'week') {
            // 4 tuần gần nhất
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::today()->subWeeks($i)->startOfWeek();
                $end = Carbon::today()->subWeeks($i)->endOfWeek();
                $labels[] = $start->format('d/m') . ' - ' . $end->format('d/m');
                $retail[] = (int) Order::where('order_code', 'retail')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereBetween('created_at', [$start, $end])->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereBetween('created_at', [$start, $end])->sum('final_amount');
            }
        } elseif ($period === 'month') {
            // 12 tháng gần nhất
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::today()->subMonths($i);
                $labels[] = $month->format('m/Y');
                $retail[] = (int) Order::where('order_code', 'retail')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('final_amount');
            }
        } else { // year
            // 5 năm gần nhất
            for ($i = 4; $i >= 0; $i--) {
                $year = Carbon::today()->subYears($i)->year;
                $labels[] = (string) $year;
                $retail[] = (int) Order::where('order_code', 'retail')->whereYear('created_at', $year)->sum('final_amount');
                $wholesale[] = (int) Order::where('order_code', 'wholesale')->whereYear('created_at', $year)->sum('final_amount');
                $preorder[] = (int) Order::where('order_code', 'preorder')->whereYear('created_at', $year)->sum('final_amount');
            }
        }

        return [
            'labels' => $labels,
            'retail' => $retail,
            'wholesale' => $wholesale,
            'preorder' => $preorder,
        ];
    }

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

    private function getTopCustomers($startDate, $endDate, $limit = 5)
    {
        return Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->select(
                'customer_phone as phone',
                \DB::raw('MAX(customer_name) as name'),
                \DB::raw('COUNT(id) as orders'),
                \DB::raw('SUM(final_amount) as total')
            )
            ->groupBy('customer_phone')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Khách lẻ',
                    'phone' => $item->phone,
                    'orders' => (int) $item->orders,
                    'total' => (int) $item->total,
                ];
            });
    }

    /**
     * Phân bố doanh thu theo danh mục (tính %)
     * Đảm bảo tổng luôn = 100% sau khi làm tròn
     */
    private function getCategoryDistribution($startDate, $endDate)
    {
        $total = Order::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
        if ($total == 0) {
            return [['label' => 'Chưa có dữ liệu', 'value' => 100]];
        }

        $categories = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get();

        if ($categories->count() <= 3) {
            return $categories->map(function ($cat) use ($total) {
                return [
                    'label' => $cat->name,
                    'value' => round(($cat->revenue / $total) * 100),
                ];
            })->toArray();
        }

        $top = $categories->take(3);
        $others = $categories->slice(3);

        $items = $top->map(function ($cat) use ($total) {
            return [
                'label' => $cat->name,
                'raw' => ($cat->revenue / $total) * 100,
            ];
        })->toArray();

        $othersRaw = $others->sum('revenue') / $total * 100;
        if ($othersRaw > 0.5) {
            $items[] = [
                'label' => 'Khác',
                'raw' => $othersRaw,
            ];
        }

        $rounded = array_map(function ($item) {
            return [
                'label' => $item['label'],
                'value' => round($item['raw']),
            ];
        }, $items);

        $totalRounded = array_sum(array_column($rounded, 'value'));
        $diff = 100 - $totalRounded;

        if ($diff != 0 && count($rounded) > 0) {
            $maxIndex = array_search(max(array_column($rounded, 'value')), array_column($rounded, 'value'));
            $rounded[$maxIndex]['value'] += $diff;
            $rounded[$maxIndex]['value'] = max(0, $rounded[$maxIndex]['value']);
        }

        $rounded = array_map(function ($item) {
            $item['value'] = max(0, $item['value']);
            return $item;
        }, $rounded);

        return $rounded;
    }

    private function getVietnameseDayOfWeek($dayNumber)
    {
        $days = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
        return $days[$dayNumber] ?? 'T' . ($dayNumber + 1);
    }
}