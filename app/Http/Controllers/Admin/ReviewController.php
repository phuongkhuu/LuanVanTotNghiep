<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'productVariant.product', 'productVariant.color']);

        // Lọc theo danh mục (category_id)
        if ($request->filled('category_id')) {
            $query->whereHas('productVariant.product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Lọc theo rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Lọc theo trạng thái (nếu có)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo tên sản phẩm hoặc người dùng
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhereHas('productVariant.product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Lấy tất cả reviews (không phân trang)
        $reviews = $query->orderBy('created_at', 'desc')->get();

        // Danh sách danh mục cho dropdown filter
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Reviews', [
            'reviews' => $reviews,
            'pagination' => [
                'total' => $reviews->count(),
                'per_page' => 5,
                'current_page' => 1,
                'last_page' => 1,
            ],
            'categories' => $categories, // thay vì products
            'filters' => $request->only(['search', 'category_id', 'rating', 'status']),
        ]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Xóa đánh giá thành công!');
    }
}