<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\ProductVariant;
use App\Models\User;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

    public function getNews()
    {
        return response()->json(News::with(['productVariant', 'author'])->get());
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:news,slug',
                'content' => 'required|string',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'author_id' => 'nullable|exists:users,id',
                'status' => 'boolean',
                'thumbnail' => 'nullable|url',
                'thumbnail_file' => 'nullable|image|max:2048'
            ]);

            $data = $request->only(['title', 'slug', 'content', 'product_variant_id', 'author_id', 'status']);
            
            // Xử lý ảnh
            if ($request->hasFile('thumbnail_file')) {
                $path = $request->file('thumbnail_file')->store('news', 'public');
                $data['thumbnail'] = '/storage/' . $path;
            } elseif ($request->filled('thumbnail')) {
                $data['thumbnail'] = $request->thumbnail;
            }

            // Tự động tạo slug nếu chưa có
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            }

            $news = News::create($data);

            return response()->json([
                'success' => true,
                'data' => $news->load(['productVariant', 'author'])
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo tin tức: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);
            
            $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:news,slug,' . $id,
                'content' => 'required|string',
                'product_variant_id' => 'nullable|exists:product_variants,id',
                'author_id' => 'nullable|exists:users,id',
                'status' => 'boolean',
                'thumbnail' => 'nullable|url',
                'thumbnail_file' => 'nullable|image|max:2048'
            ]);

            $data = $request->only(['title', 'slug', 'content', 'product_variant_id', 'author_id', 'status']);
            
            // Xử lý ảnh
            if ($request->hasFile('thumbnail_file')) {
                // Xóa ảnh cũ
                if ($news->thumbnail && Storage::disk('public')->exists(str_replace('/storage/', '', $news->thumbnail))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $news->thumbnail));
                }
                $path = $request->file('thumbnail_file')->store('news', 'public');
                $data['thumbnail'] = '/storage/' . $path;
            } elseif ($request->filled('thumbnail')) {
                $data['thumbnail'] = $request->thumbnail;
            }

            // Tự động tạo slug nếu chưa có
            if (empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['title']);
            }

            $news->update($data);

            return response()->json([
                'success' => true,
                'data' => $news->load(['productVariant', 'author'])
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật tin tức: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $news = News::findOrFail($id);
            
            // Xóa ảnh
            if ($news->thumbnail && Storage::disk('public')->exists(str_replace('/storage/', '', $news->thumbnail))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $news->thumbnail));
            }
            
            $news->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa tin tức: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $news->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generateSlug($title)
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}