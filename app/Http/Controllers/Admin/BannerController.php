<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Campaign; 
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    public function index()
{
    return Inertia::render('Admin/Banners', [
        'banners' => Banner::with('campaign')->get(),
        'campaigns' => Campaign::all()
    ]);
}
}
