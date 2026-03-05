<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AIService;
use App\Services\EventTracker;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private EventTracker $eventTracker,
        private AIService $aiService
    ) {}

    public function index()
    {
        $cartItems = \Cart::getContent()->sortBy('id');
        $total     = \Cart::getTotal();
        $subtotal  = \Cart::getSubTotal();

        $abandonment = ['risk_level' => 'unknown'];
        try {
            $abandonment = $this->aiService->predictCartAbandonment(session()->getId());
        } catch (\Exception $e) {}

        $showDiscount = $abandonment['risk_level'] === 'high';

        $upsells = Product::where('is_active', true)
            ->whereNotIn('id', $cartItems->pluck('id')->toArray())
            ->orderByDesc('rating')->limit(4)->get();

        return view('cart.index', compact('cartItems', 'total', 'subtotal', 'showDiscount', 'upsells'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        \Cart::add([
            'id'         => $product->id,
            'name'       => $product->name,
            'price'      => $product->price,
            'quantity'   => $validated['quantity'],
            'attributes' => [
                'image' => $product->image,
                'slug'  => $product->slug,
                'brand' => $product->brand,
                'stock' => $product->stock,
            ],
        ]);

        try {
            $this->eventTracker->track('add_to_cart', [
                'product_id' => $product->id,
                'quantity'   => $validated['quantity'],
                'price'      => $product->price,
                'cart_total' => \Cart::getTotal(),
            ]);
        } catch (\Exception $e) {}

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'cart_count' => \Cart::getTotalQuantity(),
                'message'    => $product->name . ' added to cart!',
            ]);
        }

        return back()->with('success', $product->name . ' added to cart!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'rowId'    => 'required',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        \Cart::update($request->rowId, ['quantity' => ['relative' => false, 'value' => $request->quantity]]);

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'cart_count' => \Cart::getTotalQuantity(),
                'total'      => \Cart::getTotal(),
            ]);
        }
        return back();
    }

    public function remove(Request $request)
    {
        $request->validate(['rowId' => 'required']);
        \Cart::remove($request->rowId);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'cart_count' => \Cart::getTotalQuantity()]);
        }
        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        \Cart::clear();
        return back()->with('success', 'Cart cleared.');
    }
}
