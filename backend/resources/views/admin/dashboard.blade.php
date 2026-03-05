@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview of your store')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-2xl">💰</span>
            <span class="badge badge-green">Revenue</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">${{ number_format($stats['total_revenue'], 2) }}</div>
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
                        <td class="px-5 py-3 text-right font-semibold text-gray-800">${{ number_format($order->total, 2) }}</td>
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
                    <div class="text-sm font-semibold text-gray-800">${{ number_format($p->revenue, 0) }}</div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-2">No sales yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
