<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\AIService;

class HomeController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $featured = Product::where('is_featured', true)->where('is_active', true)
            ->with('category')->limit(8)->get();

        $newArrivals = Product::where('is_active', true)->latest()->limit(8)->get();

        $trending = Product::where('is_active', true)
            ->orderByDesc('reviews_count')->limit(8)->get();

        $flashSale = Product::where('is_active', true)
            ->whereNotNull('compare_price')
            ->orderByDesc('rating')->limit(6)->get();

        // AI recommendations for logged-in users
        $recommendations = collect();
        if (auth()->check()) {
            try {
                $recs = $this->aiService->getRecommendations(auth()->id(), 8);
                if (!empty($recs)) {
                    $recIds = array_column($recs, 'product_id');
                    $recommendations = Product::whereIn('id', $recIds)->get();
                }
            } catch (\Exception $e) {
                // Fallback to featured
            }
        }
        if ($recommendations->isEmpty()) {
            $recommendations = $featured;
        }

        // Flash sale ends in 24h from midnight
        $flashSaleEnds = now()->endOfDay()->timestamp;

        return view('home.index', compact(
            'categories', 'featured', 'newArrivals', 'trending',
            'flashSale', 'recommendations', 'flashSaleEnds'
        ));
    }
}
