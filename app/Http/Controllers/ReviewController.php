<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Lấy danh sách review của sản phẩm
    public function index($productId)
    {
        $product = Product::with('reviews.user')->findOrFail($productId);
        return response()->json($product->reviews);
    }

    // Thêm review mới
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Kiểm tra người dùng đã đăng nhập
        if (!Auth::check()) {
            return response()->json(['message' => 'Vui lòng đăng nhập để đánh giá'], 401);
        }

        // Kiểm tra xem người dùng đã review cho variant này chưa (tùy chọn)
        $existing = Review::where('user_id', Auth::id())
                          ->where('product_variant_id', $request->product_variant_id)
                          ->first();
        if ($existing) {
            return response()->json(['message' => 'Bạn đã đánh giá sản phẩm này rồi'], 409);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'product_variant_id' => $request->product_variant_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công',
            'review' => $review->load('user')
        ], 201);
    }
}