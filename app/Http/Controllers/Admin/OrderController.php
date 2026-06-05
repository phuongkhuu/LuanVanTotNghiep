<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Orders');
    }
    
    public function retail()
    {
        return Inertia::render('Admin/Orders', ['type' => 'retail']);
    }
    
    public function wholesale()
    {
        return Inertia::render('Admin/Orders', ['type' => 'wholesale']);
    }
    
    public function preorder()
    {
        return Inertia::render('Admin/Orders', ['type' => 'preorder']);
    }
    
    public function show($id)
    {
        return Inertia::render('Admin/Orders', ['id' => $id]);
    }
    
    public function updateStatus($id)
    {
        return back()->with('success', 'Cập nhật trạng thái thành công');
    }
    
    public function export()
    {
        return back()->with('success', 'Xuất file thành công');
    }
}