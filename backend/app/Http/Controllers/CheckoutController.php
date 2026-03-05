<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\EventTracker;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private EventTracker $eventTracker) {}

    public function index()
    {
        $cartItems = \Cart::getContent();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = \Cart::getSubTotal();
        $shipping = $subtotal >= 50 ? 0 : 9.99;
        $tax      = round($subtotal * 0.08, 2);
        $total    = $subtotal + $shipping + $tax;

        return view('checkout.index', compact('cartItems', 'subtotal', 'shipping', 'tax', 'total'));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string',
            'city'           => 'required|string',
            'state'          => 'required|string',
            'zip'            => 'required|string',
            'country'        => 'required|string',
            'payment_method' => 'required|in:cod,card',
        ]);

        $cartItems = \Cart::getContent();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $subtotal = \Cart::getSubTotal();
        $shipping = $subtotal >= 50 ? 0 : 9.99;
        $tax      = round($subtotal * 0.08, 2);
        $total    = $subtotal + $shipping + $tax;

        $order = Order::create([
            'user_id'          => auth()->id(),
            'order_number'     => Order::generateOrderNumber(),
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'shipping'         => $shipping,
            'total'            => $total,
            'payment_method'   => $validated['payment_method'],
            'payment_status'   => $validated['payment_method'] === 'cod' ? 'pending' : 'paid',
            'shipping_address' => [
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'],
                'address'    => $validated['address'],
                'city'       => $validated['city'],
                'state'      => $validated['state'],
                'zip'        => $validated['zip'],
                'country'    => $validated['country'],
            ],
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $item->id,
                'product_name'  => $item->name,
                'product_image' => $item->attributes->image ?? null,
                'price'         => $item->price,
                'quantity'      => $item->quantity,
                'total'         => $item->price * $item->quantity,
            ]);
        }

        try {
            $this->eventTracker->track('purchase', [
                'cart_total' => $total,
                'item_count' => $cartItems->count(),
                'products'   => $cartItems->pluck('id')->toArray(),
            ]);
        } catch (\Exception $e) {}

        \Cart::clear();

        return redirect()->route('checkout.success', $order->id);
    }

    public function success(int $order)
    {
        $order = Order::with('items')->where('id', $order)->where('user_id', auth()->id())->firstOrFail();
        return view('checkout.success', compact('order'));
    }
}
