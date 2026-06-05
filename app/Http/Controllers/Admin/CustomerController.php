<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Customers');
    }
    
    public function retail()
    {
        return Inertia::render('Admin/Customers', ['type' => 'retail']);
    }
    
    public function business()
    {
        return Inertia::render('Admin/Customers', ['type' => 'business']);
    }
}