<?php
// app/Http/Controllers/Admin/NewsController.php

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

    /**
     * API lấy news cho frontend (trang chủ)
     */
    public function getFrontendNews()
    {
        try {
            $now = now();
            
            // CHỈ lấy news có status = 1 và campaign đang active
            $news = News::with(['campaign', 'banner'])
                ->where('status', 1)
                ->whereHas('campaign', function($query) use ($now) {
                    $query->where('status', 'active')
                        ->where(function($q) use ($now) {
                            $q->where(function($sub) use ($now) {
                                $sub->where('start_time', '<=', $now)
                                    ->where('end_time', '>=', $now);
                            })->orWhere(function($sub) {
                                $sub->whereNull('start_time')
                                    ->whereNull('end_time');
                            });
                        });
                })
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            $result = $news->map(function ($item) {
                $campaign = $item->campaign;
                $category = 'Tin tức';
                
                if ($campaign) {
                    $typeLabels = [
                        'seasonal' => 'Theo mùa',
                        'flash_sale' => 'Flash Sale',
                        'anniversary' => 'Kỷ niệm',
                        'holiday' => 'Ngày lễ',
                        'product_launch' => 'Ra mắt sản phẩm',
                        'campaign' => 'Chiến dịch',
                        'other' => 'Khuyến mãi',
                    ];
                    $category = $typeLabels[$campaign->type] ?? 'Khuyến mãi';
                }

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'excerpt' => $this->getExcerpt($item->content, 120),
                    'image' => $item->thumbnail ?? $item->banner?->image ?? $this->getDefaultNewsImage(),
                    'category' => $category,
                    'date' => $item->created_at ? $item->created_at->format('d/m/Y') : date('d/m/Y'),
                    'slug' => $item->slug,
                    'campaign_id' => $item->campaign_id,
                    'banner_id' => $item->banner_id,
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Lỗi lấy news frontend: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    private function getExcerpt($content, $length = 120)
    {
        if (empty($content)) {
            return '';
        }
        $text = strip_tags($content);
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }
        return $text;
    }

    private function getDefaultNewsImage()
    {
        return 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&h=500&fit=crop';
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
                'author_name' => $authorName,
                'campaign_id' => $validated['campaign_id'],
                'banner_id' => $validated['banner_id'],
                'thumbnail' => $banner->image,
            ];

            $news = News::create($data);
            $news->load(['campaign', 'banner']);
            
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

            $data = [
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? $this->generateSlug($validated['title']),
                'content' => $validated['content'],
                'status' => $validated['status'] ?? true,
                'campaign_id' => $validated['campaign_id'],
                'banner_id' => $validated['banner_id'],
                'thumbnail' => $banner->image,
            ];

            $news->update($data);
            $news->load(['campaign', 'banner']);
            
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

    /**
     * Cập nhật trạng thái tin tức
     * - Nếu campaign bị xóa hoặc hết hạn, chuyển về Nháp (0)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            
            // Kiểm tra nếu đang cố gắng active (1) nhưng campaign không hợp lệ
            if ($request->status == 1) {
                $campaign = $news->campaign;
                if (!$campaign || $campaign->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể kích hoạt tin tức vì chiến dịch không hợp lệ hoặc đã kết thúc'
                    ], 422);
                }
            }
            
            $news->update(['status' => $request->status]);
            
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

    /**
     * Xử lý khi campaign bị xóa - Cập nhật news liên quan thành Nháp
     */
    public function handleCampaignDeleted($campaignId)
    {
        try {
            // Cập nhật tất cả news thuộc campaign bị xóa thành Nháp (0)
            $updated = News::where('campaign_id', $campaignId)
                ->update(['status' => 0]);
            
            Log::info("Đã cập nhật {$updated} news thành Nháp khi campaign #{$campaignId} bị xóa");
            
            return $updated;
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật news khi campaign bị xóa: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Xử lý khi campaign hết hạn - Cập nhật news liên quan thành Nháp
     */
    public function handleCampaignEnded($campaignId)
    {
        try {
            // Cập nhật tất cả news thuộc campaign đã kết thúc thành Nháp (0)
            $updated = News::where('campaign_id', $campaignId)
                ->where('status', 1) // Chỉ cập nhật những news đang active
                ->update(['status' => 0]);
            
            Log::info("Đã cập nhật {$updated} news thành Nháp khi campaign #{$campaignId} kết thúc");
            
            return $updated;
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật news khi campaign kết thúc: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Đồng bộ tất cả news với trạng thái campaign
     */
    public function syncAllNewsWithCampaigns()
    {
        try {
            $now = now();
            $updated = 0;
            
            // Lấy tất cả news đang active
            $activeNews = News::where('status', 1)
                ->whereNotNull('campaign_id')
                ->with('campaign')
                ->get();
            
            foreach ($activeNews as $news) {
                $campaign = $news->campaign;
                
                // Nếu không có campaign hoặc campaign không active -> chuyển thành Nháp
                if (!$campaign || $campaign->status !== 'active') {
                    $news->update(['status' => 0]);
                    $updated++;
                    continue;
                }
                
                // Kiểm tra thời gian campaign
                $isActive = true;
                if ($campaign->start_time && $campaign->start_time > $now) {
                    $isActive = false;
                }
                if ($campaign->end_time && $campaign->end_time < $now) {
                    $isActive = false;
                }
                
                if (!$isActive) {
                    $news->update(['status' => 0]);
                    $updated++;
                }
            }
            
            Log::info("Đã đồng bộ {$updated} news với trạng thái campaign");
            
            return response()->json([
                'success' => true,
                'message' => "Đã cập nhật {$updated} tin tức",
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi đồng bộ news với campaign: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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