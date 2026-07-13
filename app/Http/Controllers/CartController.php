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
        Log::info('Cart data before filter:', $cart);
        
        // ✅ Lọc bỏ sản phẩm pre-order khỏi giỏ hàng
        $filteredCart = [];
        foreach ($cart as $variantId => $item) {
            $variant = ProductVariant::with('product')->find($variantId);
            if ($variant && !($variant->product->is_pre_order ?? false)) {
                $filteredCart[$variantId] = $item;
            } else {
                Log::info("Removed pre-order product from cart: {$variantId}");
            }
        }
        
        // ✅ Cập nhật lại session nếu có thay đổi
        if (count($filteredCart) !== count($cart)) {
            $request->session()->put('cart', $filteredCart);
            $request->session()->save();
            Log::info('Filtered cart saved:', $filteredCart);
        }
        
        $items = [];
        $total = 0;
        $count = 0;

        foreach ($filteredCart as $variantId => $item) {
            $variant = ProductVariant::with('product', 'color')->find($variantId);
            if ($variant) {
                $items[] = [
                    'id' => $variantId,
                    'product_id' => $variant->product->id,
                    'product_variant_id' => $variantId,
                    'name' => $variant->product->name ?? 'Sản phẩm', 
                    'price' => $item['price'] ?? $variant->price,
                    'quantity' => $item['quantity'],
                    'image' => $variant->product->image_url[0] ?? '/images/default-product.jpg',
                    'color' => $variant->color->name ?? 'Đen',
                    'size' => $variant->size_name ?? 'M',
                    'is_pre_order' => $variant->product->is_pre_order ?? false,
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

        // ✅ KIỂM TRA: Không cho thêm sản phẩm pre-order vào giỏ hàng
        if ($variant->product->is_pre_order ?? false) {
            Log::warning("Attempted to add pre-order product to cart: {$variant->product->name}");
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm Pre-order chỉ có thể mua ngay, không thể thêm vào giỏ hàng'
            ], 400);
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
        
        // ✅ Kiểm tra và xóa sản phẩm pre-order nếu có trong giỏ
        foreach ($cart as $id => $item) {
            $variantCheck = ProductVariant::with('product')->find($id);
            if ($variantCheck && ($variantCheck->product->is_pre_order ?? false)) {
                unset($cart[$id]);
                Log::info("Removed existing pre-order product from cart: {$id}");
            }
        }
        
        Log::info('Cart before add:', $cart);

        if (isset($cart[$variantId])) {
            $cart[$variantId]['quantity'] += $quantity;
            Log::info("Updated existing item. New quantity: {$cart[$variantId]['quantity']}");
        } else {
            $cart[$variantId] = [
                'quantity' => $quantity,
                'price' => $variant->price,
            ];
            Log::info("Added new item");
        }

        // Lưu vào session
        $request->session()->put('cart', $cart);
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
        Log::info('CartController@update called', $request->all());
        
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:0'
        ]);

        $cart = $request->session()->get('cart', []);
        $variantId = $request->variant_id;

        Log::info("Updating variant_id: {$variantId}, quantity: {$request->quantity}");

        if (!isset($cart[$variantId])) {
            Log::warning("Variant not found in cart: {$variantId}");
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không có trong giỏ hàng'
            ], 404);
        }

        // ✅ Kiểm tra sản phẩm pre-order khi cập nhật
        $variant = ProductVariant::with('product')->find($variantId);
        if ($variant && ($variant->product->is_pre_order ?? false)) {
            Log::warning("Attempted to update pre-order product in cart: {$variant->product->name}");
            // Xóa sản phẩm pre-order khỏi giỏ hàng
            unset($cart[$variantId]);
            $request->session()->put('cart', $cart);
            $request->session()->save();
            
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm Pre-order không thể ở trong giỏ hàng, đã xóa'
            ], 400);
        }

        if ($request->quantity <= 0) {
            unset($cart[$variantId]);
            Log::info("Removed item from cart");
        } else {
            $cart[$variantId]['quantity'] = $request->quantity;
            Log::info("Updated quantity to: {$request->quantity}");
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
        Log::info("CartController@remove called with variant_id: {$variantId}");
        
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$variantId])) {
            unset($cart[$variantId]);
            $request->session()->put('cart', $cart);
            $request->session()->save();

            Log::info("Removed variant_id: {$variantId} from cart");

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
            ]);
        }

        Log::warning("Variant not found in cart for removal: {$variantId}");

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