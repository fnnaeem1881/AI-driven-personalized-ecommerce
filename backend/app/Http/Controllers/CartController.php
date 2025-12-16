<?php

namespace App\Http\Controllers;

use App\Services\EventTracker;
use App\Services\AIService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private EventTracker $eventTracker,
        private AIService $aiService
    ) {}

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = \App\Models\Product::find($validated['product_id']);
        
        // Add to cart (your existing logic)
        \Cart::add($product->id, $product->name, $validated['quantity'], $product->price);

        // Track event
        $this->eventTracker->track('add_to_cart', [
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'price' => $product->price,
            'cart_total' => \Cart::total(),
        ]);

        return response()->json(['success' => true, 'cart_count' => \Cart::count()]);
    }

    public function index()
    {
        $cartItems = \Cart::content();
        
        // Check abandonment risk
        $abandonment = $this->aiService->predictCartAbandonment(session()->getId());
        
        // Show incentive if high risk
        $showDiscount = $abandonment['risk_level'] === 'high';

        return view('cart.index', compact('cartItems', 'showDiscount'));
    }

    public function checkout()
    {
        // Track purchase
        $this->eventTracker->track('purchase', [
            'cart_total' => \Cart::total(),
            'item_count' => \Cart::count(),
            'products' => \Cart::content()->pluck('id')->toArray(),
        ]);

        // Your checkout logic...
    }
}