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

    protected function imageDir(): string
    {
        return base_path('image');
    }


    protected function ensureImageDir(): void
    {
        $dir = $this->imageDir();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

  
    protected function saveContentToImage(string $contents, string $ext): string
    {
        $this->ensureImageDir();

        $filename = uniqid() . '.' . $ext;
        $path = $this->imageDir() . '/' . $filename;
        file_put_contents($path, $contents);

        return '/image/' . $filename;
    }


    protected function deleteImageIfExists(?string $imageUrl): void
    {
        if (!$imageUrl) return;

        $parsed = parse_url($imageUrl);
        $path = ltrim($parsed['path'] ?? $imageUrl, '/');

        if (!str_starts_with($path, 'image/')) return;

        $fullPath = base_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    public function index()
    {
        $categories = Category::orderBy('name')->get();
        return Inertia::render('Admin/Categories', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image'       => 'nullable|url|max:2048',      
            'image_file'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' 
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if ($request->hasFile('image_file')) {
            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
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
        $base = $validated['slug'];
        $i = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if ($request->hasFile('image_file')) {
            $this->deleteImageIfExists($category->image);

            $this->ensureImageDir();
            $file = $request->file('image_file');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = uniqid() . '.' . $ext;
            $file->move($this->imageDir(), $filename);
            $validated['image'] = '/image/' . $filename;
            unset($validated['image_file']);
        }
        elseif (!empty($validated['image'])) {
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