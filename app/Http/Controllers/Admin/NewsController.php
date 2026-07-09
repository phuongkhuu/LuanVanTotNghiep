<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Banner;
use App\Models\Campaign;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        try {
            // Lấy danh sách news kèm campaign và banner
            $news = News::with(['campaign', 'banner'])->get();
            
            // Lấy danh sách campaigns - Lấy cả active và scheduled, không lấy ended
            $campaigns = Campaign::whereIn('status', ['active', 'scheduled'])
                ->whereNotIn('type', ['voucher', 'preorder'])
                ->orderByRaw("FIELD(status, 'active', 'scheduled')")
                ->orderBy('start_time', 'asc')
                ->get();
            
            // Lấy danh sách banners có campaign và status = true
            $banners = Banner::with('campaign')
                ->where('status', true)
                ->whereHas('campaign', function($query) {
                    $query->whereIn('status', ['active', 'scheduled'])
                        ->whereNotIn('type', ['voucher', 'preorder']);
                })
                ->orderBy('order', 'asc')
                ->get();

            // Lấy danh sách tác giả duy nhất từ news (cho filter)
            $authors = News::select('author_name')
                ->whereNotNull('author_name')
                ->where('author_name', '!=', '')
                ->distinct()
                ->pluck('author_name')
                ->toArray();

            // Lấy user hiện tại
            $currentUser = Auth::user();

            return Inertia::render('Admin/News', [
                'news' => $news,
                'campaigns' => $campaigns,
                'banners' => $banners,
                'authors' => $authors,
                'currentUser' => $currentUser ? [
                    'id' => $currentUser->id,
                    'name' => $currentUser->name,
                    'email' => $currentUser->email,
                ] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi load trang News: ' . $e->getMessage());
            return Inertia::render('Admin/News', [
                'news' => [],
                'campaigns' => [],
                'banners' => [],
                'authors' => [],
                'currentUser' => null,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function getNews()
    {
        try {
            $news = News::with(['campaign', 'banner'])->get();
            
            // Chuẩn hóa dữ liệu trạng thái
            $news = $news->map(function($item) {
                $item->status = $item->status === true || $item->status === 1 ? 1 : 0;
                return $item;
            });
            
            return response()->json($news);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy danh sách news: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function getCampaigns()
    {
        try {
            $campaigns = Campaign::whereIn('status', ['active', 'scheduled'])
                ->whereNotIn('type', ['voucher', 'preorder'])
                ->orderBy('name', 'asc')
                ->get();
            
            return response()->json($campaigns);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:news,slug',
                'content' => 'required|string',
                'status' => 'boolean',
                'campaign_id' => 'required|exists:campaigns,id',
                'banner_id' => 'required|exists:banners,id',
            ]);

            // Kiểm tra campaign hợp lệ
            $campaign = Campaign::find($validated['campaign_id']);
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chiến dịch'
                ], 404);
            }

            if (in_array($campaign->type, ['voucher', 'preorder'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch không hợp lệ. Chỉ chọn campaign thông thường.'
                ], 422);
            }

            if ($campaign->status === 'ended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch đã kết thúc. Vui lòng chọn chiến dịch khác.'
                ], 422);
            }

            $banner = Banner::find($validated['banner_id']);
            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy banner'
                ], 404);
            }

            if ($banner->campaign_id != $validated['campaign_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner không thuộc chiến dịch đã chọn'
                ], 422);
            }

            // Lấy tên user hiện tại làm tác giả
            $authorName = Auth::user() ? Auth::user()->name : 'Admin';

            $data = [
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? $this->generateSlug($validated['title']),
                'content' => $validated['content'],
                'status' => $validated['status'] ?? true,
                'author_name' => $authorName, // Tự động lấy từ user đăng nhập
                'campaign_id' => $validated['campaign_id'],
                'banner_id' => $validated['banner_id'],
                'thumbnail' => $banner->image,
            ];

            $news = News::create($data);
            $news->load(['campaign', 'banner']);
            
            // Chuẩn hóa status
            $news->status = $news->status ? 1 : 0;

            return response()->json([
                'success' => true,
                'data' => $news,
                'message' => 'Thêm tin tức thành công'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
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
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:news,slug,' . $id,
                'content' => 'required|string',
                'status' => 'boolean',
                'campaign_id' => 'required|exists:campaigns,id',
                'banner_id' => 'required|exists:banners,id',
            ]);

            $campaign = Campaign::find($validated['campaign_id']);
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chiến dịch'
                ], 404);
            }

            if (in_array($campaign->type, ['voucher', 'preorder'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch không hợp lệ. Chỉ chọn campaign thông thường.'
                ], 422);
            }

            if ($campaign->status === 'ended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch đã kết thúc. Vui lòng chọn chiến dịch khác.'
                ], 422);
            }

            $banner = Banner::find($validated['banner_id']);
            if (!$banner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy banner'
                ], 404);
            }

            if ($banner->campaign_id != $validated['campaign_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner không thuộc chiến dịch đã chọn'
                ], 422);
            }

            // Giữ nguyên tác giả cũ, không cho sửa
            $data = [
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? $this->generateSlug($validated['title']),
                'content' => $validated['content'],
                'status' => $validated['status'] ?? true,
                // Không cập nhật author_name
                'campaign_id' => $validated['campaign_id'],
                'banner_id' => $validated['banner_id'],
                'thumbnail' => $banner->image,
            ];

            $news->update($data);
            $news->load(['campaign', 'banner']);
            
            // Chuẩn hóa status
            $news->status = $news->status ? 1 : 0;

            return response()->json([
                'success' => true,
                'data' => $news,
                'message' => 'Cập nhật tin tức thành công'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
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
            $news->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa tin tức thành công'
            ]);
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
            
            // Chuẩn hóa status
            $news->status = $news->status ? 1 : 0;
            
            return response()->json([
                'success' => true,
                'data' => $news,
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật trạng thái: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateSlug($title)
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $count = News::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }
        
        return $slug;
    }
}