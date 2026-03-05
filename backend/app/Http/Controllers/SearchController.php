<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $categorySlug = $request->get('category');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sort = $request->get('sort', 'relevance');
        $brand = $request->get('brand');

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $products = Product::where('is_active', true)
            ->when($query, fn($q) => $q->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            }))
            ->when($categorySlug, fn($q) => $q->whereHas('category', fn($q) => $q->where('slug', $categorySlug)))
            ->when($minPrice, fn($q) => $q->where('price', '>=', $minPrice))
            ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
            ->when($brand, fn($q) => $q->where('brand', $brand))
            ->when($sort === 'price_asc', fn($q) => $q->orderBy('price'))
            ->when($sort === 'price_desc', fn($q) => $q->orderByDesc('price'))
            ->when($sort === 'rating', fn($q) => $q->orderByDesc('rating'))
            ->when($sort === 'newest', fn($q) => $q->latest())
            ->when($sort === 'relevance' || !$sort, fn($q) => $q->orderByDesc('reviews_count'))
            ->with('category')
            ->paginate(20)->withQueryString();

        $brands = Product::where('is_active', true)->distinct()->pluck('brand')->filter()->sort()->values();

        return view('search.index', compact('products', 'categories', 'query', 'brands'));
    }
}
