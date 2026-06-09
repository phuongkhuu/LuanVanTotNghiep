<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Absolute path to htdocs/image (or base_path('image'))
     */
    protected function imageDir(): string
    {
        return base_path('image');
    }

    /**
     * Ensure image directory exists
     */
    protected function ensureImageDir(): void
    {
        $dir = $this->imageDir();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * Save raw image contents into image directory
     * Returns public URL path
     */
    protected function saveContentToImage(string $contents, string $ext): string
    {
        $this->ensureImageDir();

        $filename = uniqid() . '.' . $ext;
        $path = $this->imageDir() . '/' . $filename;
        file_put_contents($path, $contents);

        return '/image/' . $filename;
    }

    /**
     * Delete image file if it exists in image directory
     */
    protected function deleteImageIfExists(?string $imageUrl): void
    {
        if (!$imageUrl) return;

        $parsed = parse_url($imageUrl);
        $path = ltrim($parsed['path'] ?? $imageUrl, '/');

        // Only allow deletion inside image/
        if (!str_starts_with($path, 'image/')) return;

        $fullPath = base_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    public function index()
    {
        $categories = Category::orderBy('name')->get();
        // Không cần transform nếu đường dẫn đã là /image/...
        return Inertia::render('Admin/Categories', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',       // URL (nếu chọn chế độ nhập link)
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // file upload
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        // Đảm bảo slug không trùng
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        // Xử lý ảnh từ file upload
        if ($request->hasFile('image_file')) {
            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        // Xử lý ảnh từ URL (nếu không có file)
        elseif (!empty($validated['image'])) {
            try {
                $res = Http::timeout(15)->get($validated['image']);
                if (!$res->ok()) {
                    return redirect()->back()->withErrors(['image' => 'Không thể tải ảnh từ URL']);
                }
                $type = $res->header('Content-Type', '');
                if (!str_starts_with($type, 'image/')) {
                    return redirect()->back()->withErrors(['image' => 'URL không phải ảnh']);
                }
                $ext = explode('/', $type)[1] ?? 'jpg';
                $validated['image'] = $this->saveContentToImage($res->body(), $ext);
            } catch (\Exception $e) {
                Log::error('Category image fetch failed', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors(['image' => 'Lỗi tải ảnh từ URL']);
            }
        }

        Category::create($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục thành công');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        // Tránh trùng slug với danh mục khác
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        // Nếu có file upload mới
        if ($request->hasFile('image_file')) {
            // Xóa ảnh cũ nếu có
            $this->deleteImageIfExists($category->image);

            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        // Nếu có URL ảnh mới (và không có file)
        elseif (!empty($validated['image'])) {
            // Nếu URL khác với ảnh cũ thì tải mới và xóa cũ
            if ($validated['image'] !== $category->image) {
                try {
                    $res = Http::timeout(15)->get($validated['image']);
                    if (!$res->ok()) {
                        return redirect()->back()->withErrors(['image' => 'Không thể tải ảnh từ URL']);
                    }
                    $type = $res->header('Content-Type', '');
                    if (!str_starts_with($type, 'image/')) {
                        return redirect()->back()->withErrors(['image' => 'URL không phải ảnh']);
                    }

                    $this->deleteImageIfExists($category->image);
                    $ext = explode('/', $type)[1] ?? 'jpg';
                    $validated['image'] = $this->saveContentToImage($res->body(), $ext);
                } catch (\Exception $e) {
                    Log::error('Category image update failed', ['error' => $e->getMessage()]);
                    return redirect()->back()->withErrors(['image' => 'Lỗi tải ảnh từ URL']);
                }
            }
        } else {
            // Nếu không có ảnh mới, giữ nguyên ảnh cũ
            unset($validated['image']);
        }

        $category->update($validated);
        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công');
    }

    public function destroy(Category $category)
    {
        $this->deleteImageIfExists($category->image);
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Xóa danh mục thành công');
    }
}