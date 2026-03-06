@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview of your store')

@section('content')
{{-- Stat Cards --}}
{{-- ── ClickHouse Live Analytics Bar ──────────────────────────────────────── --}}
@if($eventServiceUp || $aiServiceUp)
<div class="mb-6 rounded-xl border border-cyan-200 bg-gradient-to-r from-cyan-50 to-blue-50 p-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-700 bg-cyan-100 border border-cyan-200 rounded-full px-3 py-1">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                ClickHouse Live Analytics
            </span>
            <span class="text-xs text-gray-500">Real-time behavioral data from your store</span>
        </div>
        <div class="flex items-center gap-4 text-sm">
            @if($eventServiceUp)
            <span class="text-green-600 font-medium">✓ Event Service</span>
            @else
            <span class="text-red-500 font-medium">✗ Event Service Down</span>
            @endif
            @if($aiServiceUp)
            <span class="text-green-600 font-medium">✓ AI Service</span>
            @else
            <span class="text-red-500 font-medium">✗ AI Service Down</span>
            @endif
            <a href="{{ route('admin.ai-health') }}" class="text-xs text-blue-600 hover:underline font-medium">Full Health Report →</a>
        </div>
    </div>

    {{-- Event Funnel --}}
    @if($eventServiceUp && $totalEvents > 0)
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-lg p-3 border border-cyan-100 text-center">
            <div class="text-xl font-bold text-gray-800">{{ number_format($totalEvents) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Events</div>
            <div class="text-xs text-cyan-600 font-medium mt-1">📊 ClickHouse</div>
        </div>
        <div class="bg-white rounded-lg p-3 border border-blue-100 text-center">
            <div class="text-xl font-bold text-blue-700">{{ number_format($totalViews) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Product Views</div>
            <div class="text-xs text-blue-500 font-medium mt-1">👁 view_product</div>
        </div>
        <div class="bg-white rounded-lg p-3 border border-purple-100 text-center">
            <div class="text-xl font-bold text-purple-700">{{ number_format($totalCartAdds) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Cart Adds</div>
            <div class="text-xs text-purple-500 font-medium mt-1">🛒 add_to_cart</div>
        </div>
        <div class="bg-white rounded-lg p-3 border border-green-100 text-center">
            <div class="text-xl font-bold text-green-700">{{ number_format($totalPurchases) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Purchases</div>
            <div class="text-xs text-green-500 font-medium mt-1">✅ purchase</div>
        </div>
    </div>

    {{-- Conversion Funnel Visual --}}
    @if($totalViews > 0)
    <div class="mt-4 bg-white rounded-lg p-4 border border-gray-100">
        <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">Conversion Funnel</div>
        <div class="space-y-2">
            @php
                $cartRate     = $totalViews > 0 ? round(($totalCartAdds / $totalViews) * 100, 1) : 0;
                $purchaseRate = $totalCartAdds > 0 ? round(($totalPurchases / $totalCartAdds) * 100, 1) : 0;
                $overallRate  = $totalViews > 0 ? round(($totalPurchases / $totalViews) * 100, 1) : 0;
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">👁 Views → 🛒 Cart ({{ $cartRate }}%)</span>
                    <span class="font-semibold text-blue-700">{{ number_format($totalViews) }} → {{ number_format($totalCartAdds) }}</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-blue-600" style="width:{{ min($cartRate, 100) }}%;"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">🛒 Cart → ✅ Purchase ({{ $purchaseRate }}%)</span>
                    <span class="font-semibold text-green-700">{{ number_format($totalCartAdds) }} → {{ number_format($totalPurchases) }}</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-green-400 to-green-600" style="width:{{ min($purchaseRate, 100) }}%;"></div>
                </div>
            </div>
            <div class="pt-1 border-t border-gray-100 flex justify-between text-xs">
                <span class="text-gray-500 font-medium">Overall conversion (Views → Purchase)</span>
                <span class="font-bold text-gray-800">{{ $overallRate }}%</span>
            </div>
        </div>
    </div>
    @endif

    {{-- All Event Types --}}
    @if(count($liveStats) > 0)
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($liveStats as $evType => $evCount)
        <span class="inline-flex items-center gap-1 text-xs bg-white border border-gray-200 rounded-full px-2.5 py-1 text-gray-600">
            <span class="font-mono">{{ $evType }}</span>
            <span class="font-bold text-gray-800">{{ number_format($evCount) }}</span>
        </span>
        @endforeach
    </div>
    @endif
    @elseif($eventServiceUp)
    <div class="mt-3 text-xs text-gray-400 italic">No events tracked yet — visit the store to start generating data!</div>
    @endif
</div>
@else
<div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 p-3 flex items-center justify-between flex-wrap gap-2">
    <div class="flex items-center gap-2 text-orange-700 text-sm">
        <span>⚠️</span>
        <span class="font-medium">AI & Event Services are offline.</span>
        <span class="text-orange-600">Live analytics unavailable.</span>
    </div>
    <a href="{{ route('admin.ai-health') }}" class="text-xs text-orange-700 border border-orange-300 rounded px-3 py-1 hover:bg-orange-100">View Health Report →</a>
</div>
@endif

{{-- ── ClickHouse Popular Products (AI-driven) ────────────────────────────── --}}
@if($aiServiceUp && $livePopular->isNotEmpty())
<div class="mb-6 bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <h2 class="font-semibold text-gray-800">🤖 AI-Detected Popular Products</h2>
            <span class="text-xs text-cyan-600 bg-cyan-50 border border-cyan-200 rounded-full px-2 py-0.5 font-medium">ClickHouse Live</span>
        </div>
        <a href="{{ route('admin.products.index') }}" class="text-xs text-blue-600 hover:underline">Manage Products →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach($livePopular as $i => $p)
        <a href="{{ route('admin.products.edit', $p) }}" class="group flex flex-col items-center gap-2 p-3 rounded-lg border border-gray-100 hover:border-blue-300 hover:bg-blue-50 transition-all">
            <div class="relative">
                <img src="{{ $p->image }}" alt="{{ $p->name }}" class="w-16 h-16 object-cover rounded-lg">
                <div class="absolute -top-1.5 -left-1.5 w-5 h-5 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white text-xs flex items-center justify-center font-bold">{{ $i+1 }}</div>
            </div>
            <div class="text-center">
                <div class="text-xs font-medium text-gray-800 line-clamp-2 group-hover:text-blue-700">{{ $p->name }}</div>
                <div class="text-xs text-blue-600 font-semibold mt-0.5">{{ format_currency($p->price) }}</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">💰</span>
            <span class="badge badge-green">Revenue</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ format_currency($stats['total_revenue']) }}</div>
        <div class="text-xs text-gray-400 mt-1">Total paid orders</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">📋</span>
            <span class="badge badge-blue">Orders</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</div>
        <div class="text-xs text-gray-400 mt-1">All time orders</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">📦</span>
            <span class="badge badge-purple">Products</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</div>
        <div class="text-xs text-gray-400 mt-1">Active products</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">👥</span>
            <span class="badge badge-yellow">Users</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</div>
        <div class="text-xs text-gray-400 mt-1">Registered customers</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Recent Orders --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recent Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs text-blue-600 hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-5 py-3 text-left font-medium">Order</th>
                        <th class="px-5 py-3 text-left font-medium">Customer</th>
                        <th class="px-5 py-3 text-left font-medium">Items</th>
                        <th class="px-5 py-3 text-right font-medium">Total</th>
                        <th class="px-5 py-3 text-left font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recent_orders as $order)
                    <tr class="table-row">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                #{{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-700">{{ $order->user?->name ?? 'Guest' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $order->items->count() }} item(s)</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ format_currency($order->total) }}</td>
                        <td class="px-5 py-3">
                            @php
                                $cls = match($order->status) {
                                    'pending'    => 'badge-yellow',
                                    'processing' => 'badge-blue',
                                    'shipped'    => 'badge-purple',
                                    'delivered'  => 'badge-green',
                                    'cancelled'  => 'badge-red',
                                    default      => 'badge-gray',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ ucfirst($order->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right column --}}
    <div class="space-y-6">
        {{-- Orders by Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Orders by Status</h2>
            @php
                $statusColors = ['pending'=>'#F59E0B','processing'=>'#3B82F6','shipped'=>'#8B5CF6','delivered'=>'#10B981','cancelled'=>'#EF4444'];
                $total = array_sum($orders_by_status) ?: 1;
            @endphp
            <div class="space-y-3">
                @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                @php $cnt = $orders_by_status[$s] ?? 0; $pct = round($cnt / $total * 100); @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 capitalize">{{ $s }}</span>
                        <span class="font-semibold text-gray-800">{{ $cnt }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ $pct }}%; background:{{ $statusColors[$s] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Top Products --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Top Products</h2>
            <div class="space-y-3">
                @forelse($top_products as $i => $p)
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white text-xs flex items-center justify-center font-bold flex-shrink-0">{{ $i+1 }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ $p->product_name }}</div>
                        <div class="text-xs text-gray-400">{{ $p->units }} units sold</div>
                    </div>
                    <div class="text-sm font-semibold text-gray-800">{{ format_currency($p->revenue) }}</div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-2">No sales yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
