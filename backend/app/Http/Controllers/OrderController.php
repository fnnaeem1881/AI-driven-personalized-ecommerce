<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->with('items')->paginate(10);
        return view('orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::with('items.product')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $steps = [
            ['key' => 'pending',    'label' => 'Order Placed',  'icon' => '📋'],
            ['key' => 'processing', 'label' => 'Processing',    'icon' => '⚙️'],
            ['key' => 'shipped',    'label' => 'Shipped',       'icon' => '🚚'],
            ['key' => 'delivered',  'label' => 'Delivered',     'icon' => '✅'],
        ];

        $statusIndex = match($order->status) {
            'pending'    => 0,
            'processing' => 1,
            'shipped'    => 2,
            'delivered'  => 3,
            default      => 0,
        };

        return view('orders.show', compact('order', 'steps', 'statusIndex'));
    }
}
