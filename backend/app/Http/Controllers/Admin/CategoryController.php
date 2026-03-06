<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $slug = $data['slug'];
        $i    = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $data['slug'] . '-' . $i++;
        }
        $data['slug'] = $slug;

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with products. Deactivate it instead.');
        }
        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }
}
