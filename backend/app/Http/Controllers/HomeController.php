<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FlashDeal;
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

        // Flash deals: use active FlashDeal entity first, fallback to compare_price products
        $activeDeal   = FlashDeal::active()->with(['products' => fn ($q) => $q->where('is_active', true)])->latest('starts_at')->first();
        $flashSale    = $activeDeal && $activeDeal->products->count()
            ? $activeDeal->products->take(6)
            : Product::where('is_active', true)->whereNotNull('compare_price')->orderByDesc('rating')->limit(6)->get();
        $flashSaleEnds = $activeDeal ? $activeDeal->ends_at->timestamp : now()->endOfDay()->timestamp;
        $activeDealDiscount = $activeDeal?->discount_percent ?? 0;

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
            'flashSale', 'recommendations', 'flashSaleEnds', 'activeDeal', 'activeDealDiscount'
        ));
    }
}
