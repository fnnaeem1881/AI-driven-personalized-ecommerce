<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $products   = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'brand'             => 'required|string|max:100',
            'price'             => 'required|numeric|min:0',
            'compare_price'     => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'sku'               => 'required|string|unique:products,sku',
            'short_description' => 'required|string|max:500',
            'description'       => 'required|string',
            'image'             => 'required|string',
            'rating'            => 'nullable|numeric|min:0|max:5',
            'is_featured'       => 'boolean',
            'is_active'         => 'boolean',
        ]);

        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active', true);
        $data['rating']      = $data['rating'] ?? 0;
        $data['specs']       = ['Warranty' => '1 Year', 'In the Box' => 'Device, Accessories'];

        // Ensure unique slug
        $slug = $data['slug'];
        $i    = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $data['slug'] . '-' . $i++;
        }
        $data['slug'] = $slug;

        $product = Product::create($data);

        // Handle extra images
        if ($request->has('extra_images')) {
            foreach (array_filter($request->input('extra_images', [])) as $idx => $imgUrl) {
                if ($imgUrl) {
                    $product->images()->create([
                        'image_path' => $imgUrl,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product "' . $data['name'] . '" created successfully.');
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,id',
            'brand'               => 'required|string|max:100',
            'price'               => 'required|numeric|min:0',
            'compare_price'       => 'nullable|numeric|min:0',
            'stock'               => 'required|integer|min:0',
            'sku'                 => 'required|string|unique:products,sku,' . $product->id,
            'short_description'   => 'required|string|max:500',
            'description'         => 'required|string',
            'image'               => 'required|string',
            'rating'              => 'nullable|numeric|min:0|max:5',
            'is_featured'         => 'boolean',
            'is_active'           => 'boolean',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');

        $product->update($data);

        // Handle extra images - sync them
        if ($request->has('extra_images')) {
            // Delete existing and recreate
            $product->images()->delete();
            foreach (array_filter($request->input('extra_images', [])) as $idx => $imgUrl) {
                if ($imgUrl) {
                    $product->images()->create([
                        'image_path' => $imgUrl,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deactivated successfully.');
    }
}
