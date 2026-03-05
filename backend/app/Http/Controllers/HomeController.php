<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Services\AIService;

class HomeController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $heroSlides = HeroSlide::active()->get();

        $featured = Product::where('is_featured', true)->where('is_active', true)
            ->with('category')->limit(8)->get();

        $newArrivals = Product::where('is_active', true)->latest()->limit(8)->get();

        $trending = Product::where('is_active', true)
            ->orderByDesc('reviews_count')->limit(8)->get();

        // Flash deals: DB-marked flash deals first, fallback to discounted products
        $flashSale = Product::where('is_active', true)
            ->where('is_flash_deal', true)
            ->where(function ($q) {
                $q->whereNull('flash_deal_ends_at')
                  ->orWhere('flash_deal_ends_at', '>', now());
            })
            ->orderByDesc('rating')->limit(6)->get();

        if ($flashSale->isEmpty()) {
            $flashSale = Product::where('is_active', true)
                ->whereNotNull('compare_price')
                ->orderByDesc('rating')->limit(6)->get();
        }

        // Flash sale end time: earliest active deal end, or end of today
        $flashSaleEnds = $flashSale->whereNotNull('flash_deal_ends_at')
            ->min('flash_deal_ends_at');
        $flashSaleEnds = $flashSaleEnds ? $flashSaleEnds->timestamp : now()->endOfDay()->timestamp;

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

        return view('home.index', compact(
            'categories', 'heroSlides', 'featured', 'newArrivals', 'trending',
            'flashSale', 'recommendations', 'flashSaleEnds'
        ));
    }
}
