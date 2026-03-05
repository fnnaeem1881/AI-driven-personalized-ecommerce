<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_revenue'  => Order::where('payment_status', 'paid')->sum('total'),
            'total_orders'   => Order::count(),
            'total_products' => Product::where('is_active', true)->count(),
            'total_users'    => User::where('role', 'user')->count(),
        ];

        $recent_orders = Order::with(['user', 'items'])
            ->latest()->limit(10)->get();

        $orders_by_status = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status')->toArray();

        $top_products = DB::table('order_items')
            ->select('product_name', DB::raw('SUM(total) as revenue'), DB::raw('SUM(quantity) as units'))
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(5)->get();

        $revenue_last_7_days = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')->orderBy('date')->get()
            ->pluck('total', 'date')->toArray();

        return view('admin.dashboard', compact(
            'stats', 'recent_orders', 'orders_by_status', 'top_products', 'revenue_last_7_days'
        ));
    }
}
