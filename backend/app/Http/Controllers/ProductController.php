<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\AIService;
use App\Services\EventTracker;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private EventTracker $eventTracker,
        private AIService $aiService
    ) {}

    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $query = Product::where('is_active', true)->with('category');

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        if ($request->brand) {
            $query->where('brand', $request->brand);
        }
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->rating) {
            $query->where('rating', '>=', $request->rating);
        }

        $sort = $request->get('sort', 'newest');
        match($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'rating'     => $query->orderByDesc('rating'),
            'popular'    => $query->orderByDesc('reviews_count'),
            default      => $query->latest(),
        };

        $products = $query->paginate(20)->withQueryString();
        $brands = Product::where('is_active', true)->distinct()->pluck('brand')->filter()->sort()->values();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)
            ->with(['category', 'images', 'reviews.user'])
            ->firstOrFail();

        try {
            $this->eventTracker->track('product_view', [
                'product_id'   => $product->id,
                'category_id'  => $product->category_id,
                'price'        => $product->price,
                'product_name' => $product->name,
            ]);
        } catch (\Exception $e) {}

        $similar = collect();
        try {
            $recs = $this->aiService->getSimilarProducts($product->id, 6);
            if (!empty($recs)) {
                $ids = array_column($recs, 'product_id');
                $similar = Product::whereIn('id', $ids)->where('is_active', true)->get();
            }
        } catch (\Exception $e) {}

        if ($similar->isEmpty()) {
            $similar = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)->limit(6)->get();
        }

        $inWishlist = auth()->check() && auth()->user()->hasInWishlist($product->id);

        return view('products.show', compact('product', 'similar', 'inWishlist'));
    }

    public function byCategory(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $products = Product::where('category_id', $category->id)
            ->where('is_active', true)->with('category')->paginate(20);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $brands = Product::where('category_id', $category->id)->distinct()->pluck('brand')->filter()->sort()->values();

        return view('products.index', compact('products', 'categories', 'brands', 'category'));
    }
}
