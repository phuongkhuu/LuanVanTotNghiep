<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CleanCartMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('cart')) {
            $cart = $request->session()->get('cart', []);
            $hasChanges = false;
            
            foreach ($cart as $variantId => $item) {
                $variant = ProductVariant::with('product')->find($variantId);
                if (!$variant || ($variant->product->is_pre_order ?? false)) {
                    unset($cart[$variantId]);
                    $hasChanges = true;
                }
            }
            
            if ($hasChanges) {
                $request->session()->put('cart', $cart);
                $request->session()->save();
            }
        }
        
        return $next($request);
    }
}