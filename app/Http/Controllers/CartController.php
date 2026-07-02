<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index(Request $request)
    {
        Log::info('CartController@index called');
        $cart = $request->session()->get('cart', []);
        Log::info('Cart data:', $cart);
        
        $items = [];
        $total = 0;
        $count = 0;

        foreach ($cart as $variantId => $item) {
            $variant = ProductVariant::with('product', 'color')->find($variantId);
            if ($variant) {
                $items[] = [
                    'id' => $variantId,
                    'product_variant_id' => $variantId,
                    'name' => $variant->product->name ?? 'Sản phẩm',
                    'price' => $item['price'] ?? $variant->price,
                    'quantity' => $item['quantity'],
                    'image' => $variant->product->image ?? '/images/default-product.jpg',
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                ];
                $total += ($item['price'] ?? $variant->price) * $item['quantity'];
                $count += $item['quantity'];
            }
        }

        return response()->json([
            'success' => true,
            'items' => $items,
            'total' => $total,
            'count' => $count
        ]);
    }

    public function add(Request $request)
    {
        Log::info('CartController@add called', $request->all());
        
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $variantId = $request->variant_id;
        $quantity = $request->quantity ?? 1;

        Log::info("Adding variant_id: {$variantId}, quantity: {$quantity}");

        $variant = ProductVariant::with('product', 'color')->find($variantId);
        if (!$variant) {
            Log::error("Variant not found: {$variantId}");
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        Log::info('Variant found:', ['id' => $variant->id, 'stock' => $variant->stock, 'price' => $variant->price]);

        if ($variant->stock < $quantity) {
            Log::warning("Not enough stock. Requested: {$quantity}, Available: {$variant->stock}");
            return response()->json([
                'success' => false,
                'message' => "Sản phẩm chỉ còn {$variant->stock} sản phẩm"
            ], 400);
        }

        // Lấy giỏ hàng từ session
        $cart = $request->session()->get('cart', []);
        Log::info('Cart before add:', $cart);

        if (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] += $quantity;
            Log::info("Updated existing item. New quantity: {$cart[$variantId]['quantity']}");
        } else {
            $cart[$variantId] = [
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $variant->price,
            ];
            Log::info("Added new item");
        }

        // Lưu vào session
        $request->session()->put('cart', $cart);
        // Force save session
        $request->session()->save();
        
        Log::info('Cart after add:', $cart);

        $totalCount = 0;
        foreach ($cart as $item) {
            $totalCount += $item['quantity'];
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'cart_count' => $totalCount,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = $request->session()->get('cart', []);
        $variantId = $request->variant_id;

        if (!isset($cart[$variantId])) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không có trong giỏ hàng'
            ], 404);
        }

        if ($request->quantity <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId]['quantity'] = $request->quantity;
        }

        $request->session()->put('cart', $cart);
        $request->session()->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật giỏ hàng'
        ]);
    }

    public function remove($variantId, Request $request)
    {
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$variantId])) {
            unset($cart[$variantId]);
            $request->session()->put('cart', $cart);
            $request->session()->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Sản phẩm không có trong giỏ hàng'
        ], 404);
    }

    public function clear(Request $request)
    {
        $request->session()->forget('cart');
        $request->session()->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng'
        ]);
    }
}