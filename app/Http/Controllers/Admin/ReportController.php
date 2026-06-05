<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reports');
    }
    
    public function getData()
    {
        // Logic lấy dữ liệu báo cáo theo period
        $data = [
            'revenue' => [
                'retail' => [8, 10, 12, 15, 18, 22, 20],
                'wholesale' => [15, 18, 22, 28, 35, 42, 38],
                'preorder' => [3, 4, 5, 7, 9, 12, 10]
            ],
            'category' => [58, 28, 14],
            'summary' => [
                'retail' => ['revenue' => 45200000, 'growth' => 12.5],
                'wholesale' => ['revenue' => 128500000, 'growth' => 23.8],
                'preorder' => ['revenue' => 18300000, 'growth' => 5.2]
            ],
            'topProducts' => [
                ['name' => 'Balo Doanh Nhân Elite', 'sold' => 145, 'revenue' => 464000000],
                ['name' => 'Túi Du Lịch Nomad', 'sold' => 98, 'revenue' => 181300000],
                ['name' => 'Balo Công Sở Commuter', 'sold' => 87, 'revenue' => 138330000]
            ],
            'topCustomers' => [
                ['name' => 'Công ty ABC', 'total' => 156000000, 'orders' => 8],
                ['name' => 'Nguyễn Văn A', 'total' => 28500000, 'orders' => 12],
                ['name' => 'TechPro', 'total' => 45200000, 'orders' => 5]
            ]
        ];
        
        return response()->json($data);
    }
    
    public function export()
    {
        // Logic export báo cáo
        return back()->with('success', 'Xuất báo cáo thành công!');
    }
}