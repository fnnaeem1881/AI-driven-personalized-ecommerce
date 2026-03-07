<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // ── ClickHouse Live Analytics (from Event Service) ──────────────────────
        $eventServiceUrl  = config('services.events.url', 'http://localhost:8000');
        $aiServiceUrl     = config('services.ai.url', 'http://localhost:8001');
        $liveStats        = [];  // event_type => count
        $livePopular      = [];  // top popular products from ClickHouse
        $eventServiceUp   = false;
        $aiServiceUp      = false;

        try {
            $r = Http::connectTimeout(2)->timeout(4)->get("{$eventServiceUrl}/stats");
            if ($r->successful()) {
                $rawStats = $r->json()['stats'] ?? [];
                foreach ($rawStats as $row) {
                    $liveStats[$row['event_type']] = $row['count'] ?? 0;
                }
                $eventServiceUp = true;
            }
        } catch (\Exception $e) {
            Log::debug('Admin dashboard event stats: ' . $e->getMessage());
        }

        try {
            $r = Http::connectTimeout(2)->timeout(4)->get("{$aiServiceUrl}/health");
            if ($r->successful()) $aiServiceUp = true;
        } catch (\Exception $e) {}

        try {
            if ($aiServiceUp) {
                $r = Http::connectTimeout(2)->timeout(5)->post("{$aiServiceUrl}/recommendations/popular", ['limit' => 5]);
                if ($r->successful()) {
                    $popularIds = array_column($r->json()['popular_products'] ?? [], 'product_id');
                    if (!empty($popularIds)) {
                        $livePopular = Product::whereIn('id', $popularIds)->where('is_active', true)
                            ->get()->sortBy(fn($p) => array_search($p->id, $popularIds))->values();
                    }
                }
            }
        } catch (\Exception $e) {}

        // Build event funnel
        $totalViews    = $liveStats['product_view'] ?? 0;
        $totalCartAdds = $liveStats['add_to_cart']  ?? 0;
        $totalPurchases= $liveStats['purchase']      ?? 0;
        $totalEvents   = array_sum($liveStats);

        return view('admin.dashboard', compact(
            'stats', 'recent_orders', 'orders_by_status', 'top_products', 'revenue_last_7_days',
            'liveStats', 'livePopular', 'eventServiceUp', 'aiServiceUp',
            'totalViews', 'totalCartAdds', 'totalPurchases', 'totalEvents'
        ));
    }

    /**
     * AI Services Health Dashboard — shows live status of all services.
     */
    public function aiHealth()
    {
        $aiServiceUrl    = config('services.ai.url',     'http://localhost:8001');
        $eventServiceUrl = config('services.events.url', 'http://localhost:8000');

        // ── AI Service ─────────────────────────────────────────────────────────
        $aiHealth  = ['status' => 'down', 'services' => ['clickhouse' => false, 'redis' => false], 'models' => []];
        $aiModels  = [];
        $aiOnline  = false;

        try {
            $r = Http::connectTimeout(2)->timeout(4)->get("{$aiServiceUrl}/health");
            if ($r->successful()) {
                $aiHealth = $r->json() ?? $aiHealth;
                $aiOnline = true;
            }
        } catch (\Exception $e) {}

        if ($aiOnline) {
            try {
                $r = Http::connectTimeout(2)->timeout(4)->get("{$aiServiceUrl}/analytics/model-performance");
                if ($r->successful()) $aiModels = $r->json() ?? [];
            } catch (\Exception $e) {}
        }

        // ── Event Service ───────────────────────────────────────────────────────
        $eventHealth = ['status' => 'down', 'redis' => false, 'clickhouse' => false];
        $eventStats  = [];
        $eventOnline = false;

        try {
            $r = Http::connectTimeout(2)->timeout(4)->get("{$eventServiceUrl}/health");
            if ($r->successful()) {
                $eventHealth = $r->json() ?? $eventHealth;
                $eventOnline = true;
            }
        } catch (\Exception $e) {}

        if ($eventOnline) {
            try {
                $r = Http::connectTimeout(2)->timeout(4)->get("{$eventServiceUrl}/stats");
                if ($r->successful()) $eventStats = $r->json()['stats'] ?? [];
            } catch (\Exception $e) {}
        }

        // ── Laravel itself ──────────────────────────────────────────────────────
        $dbOk = true;
        try { DB::select('SELECT 1'); } catch (\Exception $e) { $dbOk = false; }

        $totalEvents = collect($eventStats)->sum('count');

        return view('admin.ai-health', compact(
            'aiHealth', 'aiModels', 'aiOnline',
            'eventHealth', 'eventStats', 'eventOnline',
            'dbOk', 'totalEvents',
            'aiServiceUrl', 'eventServiceUrl'
        ));
    }

    /**
     * Trigger model retraining on the AI service (background task).
     */
    public function aiRetrain()
    {
        $aiServiceUrl = config('services.ai.url', 'http://localhost:8001');
        $message      = 'Retraining started in background. Models will update in ~30 seconds.';
        $type         = 'success';

        try {
            $r = Http::connectTimeout(2)->timeout(5)->post("{$aiServiceUrl}/train");
            if (!$r->successful()) {
                $message = 'AI service returned error: ' . $r->status();
                $type    = 'error';
            }
        } catch (\Exception $e) {
            $message = 'Could not reach AI service: ' . $e->getMessage();
            $type    = 'error';
        }

        return redirect()->route('admin.ai-health')->with($type, $message);
    }

    /**
     * ClickHouse Full Analytics Dashboard.
     */
    public function analytics(Request $request)
    {
        $eventServiceUrl = config('services.events.url', 'http://localhost:8000');
        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 14, 30, 90]) ? $days : 30;

        $overview   = [];
        $timeline   = [];
        $products   = [];
        $users      = [];
        $eventTypes = [];
        $serviceUp  = false;

        try {
            // Overview totals
            $r = Http::connectTimeout(2)->timeout(6)->get("{$eventServiceUrl}/analytics/overview", ['days' => $days]);
            if ($r->successful()) {
                $overview  = $r->json() ?? [];
                $serviceUp = true;
            }

            // Timeline (events per day)
            $r = Http::connectTimeout(2)->timeout(8)->get("{$eventServiceUrl}/analytics/timeline", ['days' => $days]);
            if ($r->successful()) $timeline = $r->json()['rows'] ?? [];

            // Top products
            $r = Http::connectTimeout(2)->timeout(8)->get("{$eventServiceUrl}/analytics/products", ['days' => $days, 'limit' => 20]);
            if ($r->successful()) $products = $r->json()['products'] ?? [];

            // Top users
            $r = Http::connectTimeout(2)->timeout(8)->get("{$eventServiceUrl}/analytics/users", ['days' => $days, 'limit' => 20]);
            if ($r->successful()) $users = $r->json()['users'] ?? [];

            // All event types (all-time)
            $r = Http::connectTimeout(2)->timeout(6)->get("{$eventServiceUrl}/analytics/event-types");
            if ($r->successful()) $eventTypes = $r->json()['event_types'] ?? [];

        } catch (\Exception $e) {
            Log::debug('Analytics page error: ' . $e->getMessage());
        }

        // Attach MySQL product names to ClickHouse product rows
        if (!empty($products)) {
            $productIds  = array_column($products, 'product_id');
            $mysqlProds  = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $products    = array_map(function ($p) use ($mysqlProds) {
                $mp = $mysqlProds[$p['product_id']] ?? null;
                $p['name']  = $mp?->name  ?? $p['product_name'];
                $p['image'] = $mp?->image ?? null;
                $p['slug']  = $mp?->slug  ?? null;
                $p['price'] = $mp?->price ?? null;
                return $p;
            }, $products);
        }

        // Attach MySQL user names to ClickHouse user rows
        if (!empty($users)) {
            $userIds   = array_column($users, 'user_id');
            $mysqlUsrs = User::whereIn('id', $userIds)->get()->keyBy('id');
            $users     = array_map(function ($u) use ($mysqlUsrs) {
                $mu = $mysqlUsrs[$u['user_id']] ?? null;
                $u['name']  = $mu?->name  ?? 'Guest';
                $u['email'] = $mu?->email ?? '';
                return $u;
            }, $users);
        }

        // Build timeline for Chart.js: dates array + per-event-type series
        $timelineDates  = [];
        $timelineSeries = [];
        foreach ($timeline as $row) {
            if (!in_array($row['day'], $timelineDates)) {
                $timelineDates[] = $row['day'];
            }
            $timelineSeries[$row['event_type']][$row['day']] = $row['count'];
        }

        return view('admin.analytics', compact(
            'overview', 'timeline', 'products', 'users', 'eventTypes',
            'serviceUp', 'days', 'timelineDates', 'timelineSeries'
        ));
    }

    /**
     * AJAX endpoint: paginated raw ClickHouse events.
     */
    public function analyticsEvents(Request $request)
    {
        $eventServiceUrl = config('services.events.url', 'http://localhost:8000');
        $limit     = min((int) $request->get('limit', 50), 100);
        $offset    = (int) $request->get('offset', 0);
        $eventType = $request->get('event_type', '');

        try {
            $r = Http::connectTimeout(2)->timeout(8)->get("{$eventServiceUrl}/analytics/recent", [
                'limit'      => $limit,
                'offset'     => $offset,
                'event_type' => $eventType,
            ]);
            if ($r->successful()) {
                return response()->json($r->json());
            }
        } catch (\Exception $e) {}

        return response()->json(['total' => 0, 'events' => []]);
    }
}
