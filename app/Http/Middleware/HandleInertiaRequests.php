<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {

        $categories = Category::whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('name')
            ->get();


        $brands = Brand::whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('name')
            ->get();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'categories' => $categories,
            'brands' => $brands,
        ];
    }
}