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
        // Only get categories that have a non‑empty slug
        $categories = Category::whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('name')
            ->get();

        // Only get brands that have a non‑empty slug
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