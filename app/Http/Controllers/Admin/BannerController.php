<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::with('campaign')->orderBy('order', 'asc')->get()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::with('campaign')->orderBy('order', 'asc')->get());
    }

    public function store(Request $request)
    {
        try {
            Log::info('Banner store request:', $request->all());

            $rules = [
                'title' => 'nullable|string|max:255',
                'campaign_id' => 'nullable|exists:campaigns,id',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? 'Banner ' . now()->format('d/m/Y'),
                'campaign_id' => $validated['campaign_id'] ?? null,
                'link' => $validated['link'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? true,
            ];
            
            // Xử lý ảnh
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
                $data['image'] = $request->image;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ảnh hoặc nhập URL'
                ], 422);
            }

            // Xác định order mới
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
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
            
            $rules = [
                'title' => 'nullable|string|max:255',
                'campaign_id' => 'nullable|exists:campaigns,id',
                'image' => 'nullable|url',
                'link' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'boolean',
                'order' => 'nullable|integer|min:0'
            ];

            if ($request->hasFile('image_file')) {
                $rules['image_file'] = 'image|max:2048';
            }

            $validated = $request->validate($rules);

            $data = [
                'title' => $validated['title'] ?? $banner->title,
                'campaign_id' => $validated['campaign_id'] ?? $banner->campaign_id,
                'link' => $validated['link'] ?? $banner->link,
                'description' => $validated['description'] ?? $banner->description,
                'status' => $validated['status'] ?? $banner->status,
            ];
            
            // Xử lý ảnh
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
                $data['image'] = $request->image;
            }

            // Xử lý thay đổi order
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
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
        try {
            $banner = Banner::findOrFail($id);
            $request->validate(['status' => 'required|boolean']);
            $banner->update(['status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
}