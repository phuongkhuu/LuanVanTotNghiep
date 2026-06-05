<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'todayRevenue' => 12500000,
                'totalOrders' => 284,
                'totalCustomers' => 156,
                'lowStockProducts' => 12,
            ]
        ]);
    }
}