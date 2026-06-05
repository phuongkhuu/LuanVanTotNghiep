<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Products');
    }
    
    public function retail()
    {
        return Inertia::render('Admin/Products', ['type' => 'retail']);
    }
    
    public function wholesale()
    {
        return Inertia::render('Admin/Products', ['type' => 'wholesale']);
    }
    
    public function preorder()
    {
        return Inertia::render('Admin/Products', ['type' => 'preorder']);
    }
}