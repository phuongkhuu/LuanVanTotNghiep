<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    public function index()
    {
        // Lấy campaigns - chỉ lấy active và scheduled cho dropdown
        $campaigns = Campaign::whereIn('status', ['active', 'scheduled'])
            ->whereNotIn('type', ['voucher', 'preorder'])
            ->orderByRaw("FIELD(status, 'active', 'scheduled')")
            ->orderBy('start_time', 'asc')
            ->get();

        // Lấy tất cả banners và tự động xét trạng thái
        $banners = Banner::with('campaign')
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($banner) {
                // Tự động xét trạng thái dựa trên campaign
                if ($banner->campaign_id && $banner->campaign) {
                    // Nếu campaign đang diễn ra -> Hoạt động (1)
                    if ($banner->campaign->status === 'active') {
                        $banner->status = Banner::STATUS_ACTIVE; // 1
                    } 
                    // Nếu campaign sắp diễn ra -> Đang chờ (0)
                    elseif ($banner->campaign->status === 'scheduled') {
                        $banner->status = Banner::STATUS_PENDING; // 0
                    }
                    // Nếu campaign đã kết thúc -> Đã khóa (-1)
                    elseif ($banner->campaign->status === 'ended') {
                        $banner->status = Banner::STATUS_INACTIVE; // -1
                    }
                    // Lưu lại để đồng bộ database
                    $banner->save();
                }
                return $banner;
            });

        return Inertia::render('Admin/Banners', [
            'banners' => $banners,
            'campaigns' => $campaigns
        ]);
    }

    public function getBanners()
    {
        $banners = Banner::with('campaign')
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($banner) {
                if ($banner->campaign_id && $banner->campaign) {
                    if ($banner->campaign->status === 'active') {
                        $banner->status = Banner::STATUS_ACTIVE; // 1
                    } elseif ($banner->campaign->status === 'scheduled') {
                        $banner->status = Banner::STATUS_PENDING; // 0
                    } elseif ($banner->campaign->status === 'ended') {
                        $banner->status = Banner::STATUS_INACTIVE; // -1
                    }
                    $banner->save();
                }
                return $banner;
            });
            
        return response()->json($banners);
    }

    public function getCampaigns()
    {
        $campaigns = Campaign::whereIn('status', ['active', 'scheduled'])
            ->whereNotIn('type', ['voucher', 'preorder'])
            ->orderByRaw("FIELD(status, 'active', 'scheduled')")
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'type' => $campaign->type,
                    'start_time' => $campaign->start_time,
                    'end_time' => $campaign->end_time,
                ];
            });
        
        return response()->json($campaigns);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store request:', $request->all());

            $rules = [
                'title' => 'required|string|max:255',
                'campaign_id' => 'required|exists:campaigns,id',
                'link' => 'nullable|url|max:255',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'required|image|mimes:jpeg,png,gif,svg,webp|max:2048';
            } else {
                $rules['image'] = 'required|string';
            }

            $validated = $request->validate($rules);

            $campaign = Campaign::find($validated['campaign_id']);
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chiến dịch'
                ], 404);
            }

            if (!in_array($campaign->status, ['active', 'scheduled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch đã kết thúc hoặc không khả dụng. Chỉ chọn chiến dịch đang diễn ra hoặc sắp diễn ra.'
                ], 422);
            }

            if (in_array($campaign->type, ['voucher', 'preorder'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch không hợp lệ. Chỉ chọn campaign thông thường.'
                ], 422);
            }

            // Tự động xét trạng thái dựa trên campaign
            // active -> 1 (Hoạt động), scheduled -> 0 (Đang chờ)
            $status = $campaign->status === 'active' 
                ? Banner::STATUS_ACTIVE   // 1
                : Banner::STATUS_PENDING; // 0

            $data = [
                'title' => $validated['title'],
                'campaign_id' => $validated['campaign_id'],
                'link' => $validated['link'] ?? null,
                'status' => $status,
            ];
            
            if ($request->hasFile('image_file')) {
                try {
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $imageUrl = trim($request->image);
                if (!preg_match('/^(https?:\/\/|\/)/', $imageUrl)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'URL ảnh không hợp lệ. Vui lòng nhập đúng định dạng'
                    ], 422);
                }
                $data['image'] = $imageUrl;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng cung cấp ảnh banner (URL hoặc file)'
                ], 422);
            }

            $totalBanners = Banner::count();
            $newOrder = $request->input('order', $totalBanners);
            
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            if ($newOrder < $totalBanners) {
                Banner::where('order', '>=', $newOrder)->increment('order');
            }

            $data['order'] = $newOrder;
            $banner = Banner::create($data);

            Log::info('Banner created successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner->load('campaign')
            ]);
            
        } catch (ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi tạo banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Banner update request:', ['id' => $id, 'data' => $request->all()]);

            $banner = Banner::findOrFail($id);
            
            // Kiểm tra campaign hiện tại
            $currentCampaign = Campaign::find($banner->campaign_id);
            
            // Nếu campaign đã kết thúc -> KHÔNG cho sửa
            if ($currentCampaign && $currentCampaign->status === 'ended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch đã kết thúc, không thể sửa banner này. Bạn chỉ có thể xóa.'
                ], 422);
            }
            
            $rules = [
                'title' => 'required|string|max:255',
                'campaign_id' => 'required|exists:campaigns,id',
                'link' => 'nullable|url|max:255',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'required|image|mimes:jpeg,png,gif,svg,webp|max:2048';
            } else {
                $rules['image'] = 'nullable|string';
            }

            $validated = $request->validate($rules);

            $campaign = Campaign::find($validated['campaign_id']);
            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chiến dịch'
                ], 404);
            }

            if (!in_array($campaign->status, ['active', 'scheduled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch đã kết thúc. Chỉ chọn chiến dịch đang diễn ra hoặc sắp diễn ra.'
                ], 422);
            }

            if (in_array($campaign->type, ['voucher', 'preorder'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chiến dịch không hợp lệ. Chỉ chọn campaign thông thường.'
                ], 422);
            }

            // Tự động xét trạng thái dựa trên campaign mới
            $status = $campaign->status === 'active' 
                ? Banner::STATUS_ACTIVE   // 1
                : Banner::STATUS_PENDING; // 0

            $data = [
                'title' => $validated['title'],
                'campaign_id' => $validated['campaign_id'],
                'link' => $validated['link'] ?? $banner->link,
                'status' => $status,
            ];
            
            if ($request->hasFile('image_file')) {
                try {
                    if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                        Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
                    }
                    
                    $path = $request->file('image_file')->store('banners', 'public');
                    $data['image'] = '/storage/' . $path;
                } catch (\Exception $e) {
                    Log::error('Lỗi upload ảnh: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi upload ảnh: ' . $e->getMessage()
                    ], 500);
                }
            } elseif ($request->filled('image')) {
                $imageUrl = trim($request->image);
                if (!preg_match('/^(https?:\/\/|\/)/', $imageUrl)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'URL ảnh không hợp lệ. Vui lòng nhập đúng định dạng'
                    ], 422);
                }
                $data['image'] = $imageUrl;
            } else {
                $data['image'] = $banner->image;
            }

            $newOrder = $request->input('order');
            if (!is_null($newOrder) && $newOrder != $banner->order) {
                $oldOrder = $banner->order;
                $totalBanners = Banner::count();

                if ($newOrder < 0) $newOrder = 0;
                if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

                if ($oldOrder < $newOrder) {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>', $oldOrder)
                        ->where('order', '<=', $newOrder)
                        ->decrement('order');
                } else {
                    Banner::where('id', '!=', $id)
                        ->where('order', '>=', $newOrder)
                        ->where('order', '<', $oldOrder)
                        ->increment('order');
                }

                $data['order'] = $newOrder;
            }

            $banner->update($data);

            Log::info('Banner updated successfully:', ['id' => $banner->id]);

            return response()->json([
                'success' => true, 
                'data' => $banner->load('campaign')
            ]);
            
        } catch (ValidationException $e) {
            Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật banner: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            
            $deletedOrder = $banner->order;
            $banner->delete();

            Banner::where('order', '>', $deletedOrder)->decrement('order');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Lỗi xóa banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // Không cho phép thay đổi trạng thái thủ công
        return response()->json([
            'success' => false,
            'message' => 'Trạng thái banner được tự động xét dựa trên chiến dịch.'
        ], 422);
    }

    /**
     * Lấy banner hoạt động để hiển thị lên trang chủ (CHỈ LẤY STATUS = 1)
     */
    public function getActiveBanners()
    {
        $banners = Banner::where('status', Banner::STATUS_ACTIVE) // Chỉ lấy status = 1
            ->with('campaign')
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'image' => $banner->image,
                    'link' => $banner->link,
                    'campaign' => $banner->campaign?->name,
                    'status' => $banner->status,
                    'status_label' => $banner->status_label,
                ];
            });

        return response()->json($banners);
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['order' => 'required|integer|min:0']);

            $newOrder = $request->order;
            $oldOrder = $banner->order;

            if ($oldOrder == $newOrder) {
                return response()->json(['success' => true]);
            }

            $totalBanners = Banner::count();
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder >= $totalBanners) $newOrder = $totalBanners - 1;

            if ($oldOrder < $newOrder) {
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $banner->update(['order' => $newOrder]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function checkAndUpdateStatus()
    {
        try {
            $banners = Banner::with('campaign')
                ->whereNotNull('campaign_id')
                ->get();

            $updated = 0;
            foreach ($banners as $banner) {
                if ($banner->campaign) {
                    // Cập nhật status dựa trên campaign
                    $newStatus = match($banner->campaign->status) {
                        'active' => Banner::STATUS_ACTIVE,    // 1
                        'scheduled' => Banner::STATUS_PENDING, // 0
                        'ended' => Banner::STATUS_INACTIVE,    // -1
                        default => $banner->status,
                    };
                    
                    if ($banner->status != $newStatus) {
                        $banner->update(['status' => $newStatus]);
                        $updated++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Đã cập nhật {$updated} banner",
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}