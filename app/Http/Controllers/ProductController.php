<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand', 'variants'])->paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:products,slug',
            'material' => 'nullable|string',
            'image_url' => 'nullable|url',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|url',
            'is_featured' => 'boolean',
            'is_preorder' => 'boolean',
            'status' => 'required|integer|in:0,1',
        ]);

        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'variants.color', 'variants.size']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:products,slug,' . $product->id,
            'material' => 'nullable|string',
            'image_url' => 'nullable|url',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|url',
            'is_featured' => 'boolean',
            'is_preorder' => 'boolean',
            'status' => 'required|integer|in:0,1',
        ]);

        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}