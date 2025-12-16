<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\EventTracker;
use App\Services\AIService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private EventTracker $eventTracker,
        private AIService $aiService
    ) {}

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        
        // Track product view
        $this->eventTracker->track('product_view', [
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'product_name' => $product->name,
        ]);

        // Get similar products from AI
        $similarProducts = $this->aiService->getSimilarProducts($product->id, 6);
        $similarProductIds = array_column($similarProducts, 'product_id');
        $similar = Product::whereIn('id', $similarProductIds)->get();

        return view('products.show', compact('product', 'similar'));
    }

    public function index()
    {
        $products = Product::paginate(20);
        
        // Get personalized recommendations for logged-in users
        $recommendations = [];
        if (auth()->check()) {
            $recs = $this->aiService->getRecommendations(auth()->id(), 8);
            $recIds = array_column($recs, 'product_id');
            $recommendations = Product::whereIn('id', $recIds)->get();
        }

        return view('products.index', compact('products', 'recommendations'));
    }
}