<?php

namespace App\Http\Controllers;

use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashDealController extends Controller
{
    public function index(Request $request)
    {
        // Active deals with their products (eager load)
        $activeDeals = FlashDeal::active()
            ->with(['products' => function ($q) {
                $q->where('is_active', true)->with('images');
            }])
            ->orderBy('ends_at')
            ->get();

        // Fallback: if no active deals, show all flash-deal products
        $fallbackProducts = collect();
        if ($activeDeals->isEmpty()) {
            $fallbackProducts = Product::where('is_active', true)
                ->where('is_flash_deal', true)
                ->where(function ($q) {
                    $q->whereNull('flash_deal_ends_at')
                      ->orWhere('flash_deal_ends_at', '>=', now());
                })
                ->orderByDesc('flash_deal_discount')
                ->get();
        }

        // Nearest ending deal for page-level countdown
        $closestEnd = $activeDeals->first()?->ends_at ?? now()->addDay();

        return view('flash-deals.index', compact('activeDeals', 'fallbackProducts', 'closestEnd'));
    }
}
