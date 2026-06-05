<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CustomizeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Customize');
    }
    
    public function updateStatus($id)
    {
        return back()->with('success', 'Cập nhật trạng thái thành công');
    }
    
    public function approve($id)
    {
        return back()->with('success', 'Đã duyệt yêu cầu');
    }
    
    public function sendQuote()
    {
        return back()->with('success', 'Đã gửi báo giá');
    }
}