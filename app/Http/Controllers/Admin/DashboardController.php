<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [ //Inertia sẽ tìm đến resources/js/Pages/ + đường dẫn
            'stats' => [
                'todayRevenue' => 12500000,
                'totalOrders' => 284,
                'totalCustomers' => 156,
                'lowStockProducts' => 12,
            ]
            //Tạo giá trị mặc định cho biến tên là stats, có các thuộc tính todayRevenue, totalOrders, totalCustomers, lowStockProducts.
        ]);
    }
}