<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\ProductVariant;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{
    public function index()
{
    return Inertia::render('Admin/News', [
        'news' => News::with(['productVariant', 'author'])->get(),
        'productVariants' => ProductVariant::all(),
        'authors' => User::all()
    ]);
}
}
