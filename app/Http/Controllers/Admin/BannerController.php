<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Banners', [
            'banners' => Banner::with('campaign')->orderBy('order')->get(),
            'campaigns' => Campaign::all()
        ]);
    }

    public function getBanners()
    {
        return response()->json(Banner::with('campaign')->orderBy('order')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'image' => 'nullable|url',
            'image_file' => 'nullable|image|max:2048',
            'link' => 'nullable|string|max:255',
            'status' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        $data = $request->only(['campaign_id', 'link', 'status']);
        
        // Xử lý ảnh
        if ($request->filled('image')) {
            $data['image'] = $request->image;
        }
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('banners', 'public');
            $data['image'] = '/storage/' . $path;
        }

        // Xác định order mới
        $newOrder = $request->input('order');
        $totalBanners = Banner::count();

        // Nếu không có order, thêm vào cuối
        if (is_null($newOrder) || $newOrder > $totalBanners) {
            $newOrder = $totalBanners;
        }

        // Nếu order < 0 thì set về 0
        if ($newOrder < 0) {
            $newOrder = 0;
        }

        // Dịch chuyển các banner có order >= newOrder lên 1
        Banner::where('order', '>=', $newOrder)->increment('order');

        // Tạo banner mới với order đã chọn
        $data['order'] = $newOrder;
        $banner = Banner::create($data);

        return response()->json(['success' => true, 'data' => $banner->load('campaign')]);
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'image' => 'nullable|url',
            'image_file' => 'nullable|image|max:2048',
            'link' => 'nullable|string|max:255',
            'status' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        $data = $request->only(['campaign_id', 'link', 'status']);
        
        // Xử lý ảnh
        if ($request->filled('image')) {
            $data['image'] = $request->image;
        }
        if ($request->hasFile('image_file')) {
            // Xóa ảnh cũ
            if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
            }
            $path = $request->file('image_file')->store('banners', 'public');
            $data['image'] = '/storage/' . $path;
        }

        // Xử lý thay đổi order (nếu có)
        $newOrder = $request->input('order');
        if (!is_null($newOrder) && $newOrder != $banner->order) {
            $oldOrder = $banner->order;
            $totalBanners = Banner::count() - 1; // không tính banner hiện tại

            // Giới hạn order trong khoảng hợp lệ
            if ($newOrder < 0) $newOrder = 0;
            if ($newOrder > $totalBanners) $newOrder = $totalBanners;

            // Dịch chuyển các banner khác
            if ($oldOrder < $newOrder) {
                // Di chuyển xuống: các banner có order > oldOrder và <= newOrder giảm 1
                Banner::where('id', '!=', $id)
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->decrement('order');
            } else {
                // Di chuyển lên: các banner có order >= newOrder và < oldOrder tăng 1
                Banner::where('id', '!=', $id)
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->increment('order');
            }

            $data['order'] = $newOrder;
        }

        $banner->update($data);

        return response()->json(['success' => true, 'data' => $banner->load('campaign')]);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        // Xóa ảnh
        if ($banner->image && Storage::disk('public')->exists(str_replace('/storage/', '', $banner->image))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $banner->image));
        }
        
        // Lưu order để dịch chuyển các banner sau
        $deletedOrder = $banner->order;
        $banner->delete();

        // Dịch chuyển các banner có order > deletedOrder giảm 1
        Banner::where('order', '>', $deletedOrder)->decrement('order');

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $request->validate(['status' => 'required|boolean']);
        $banner->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function updateOrder(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        
        $request->validate([
            'order' => 'required|integer|min:0'
        ]);

        $newOrder = $request->order;
        $oldOrder = $banner->order;

        // Không thay đổi
        if ($oldOrder == $newOrder) {
            return response()->json(['success' => true]);
        }

        $totalBanners = Banner::count() - 1;
        if ($newOrder < 0) $newOrder = 0;
        if ($newOrder > $totalBanners) $newOrder = $totalBanners;

        // Dịch chuyển
        if ($oldOrder < $newOrder) {
            // Di chuyển xuống: các banner có order > oldOrder và <= newOrder giảm 1
            Banner::where('id', '!=', $id)
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $newOrder)
                ->decrement('order');
        } else {
            // Di chuyển lên: các banner có order >= newOrder và < oldOrder tăng 1
            Banner::where('id', '!=', $id)
                ->where('order', '>=', $newOrder)
                ->where('order', '<', $oldOrder)
                ->increment('order');
        }

        $banner->update(['order' => $newOrder]);

        return response()->json(['success' => true]);
    }
}