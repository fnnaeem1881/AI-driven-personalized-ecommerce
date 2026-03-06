@extends('layouts.admin')

@section('title', 'User AI Profile — ' . $user->name)
@section('page-title', 'User AI Profile')
@section('breadcrumb', $user->name . ' · ' . $user->email)

@section('header-actions')
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">← Back to Users</a>
@endsection

@section('content')

{{-- ── Top summary ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">

    {{-- User Card --}}
    <div class="lg:col-span-1 bg-white rounded-xl border border-gray-200 p-5 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500
                    flex items-center justify-center text-white text-2xl font-bold mb-3">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="font-bold text-gray-900 text-base">{{ $user->name }}</div>
        <div class="text-xs text-gray-400 mb-3">{{ $user->email }}</div>
        <span class="badge {{ $user->role === 'admin' ? 'badge-purple' : 'badge-gray' }} mb-3">
            {{ $user->role === 'admin' ? '👑 Admin' : '👤 Customer' }}
        </span>
        <div class="w-full border-t border-gray-100 pt-3 space-y-1 text-xs text-gray-500">
            <div class="flex justify-between"><span>Joined</span><span>{{ $user->created_at->format('d M Y') }}</span></div>
            <div class="flex justify-between"><span>Orders</span><span class="font-semibold">{{ $user->orders_count }}</span></div>
            <div class="flex justify-between"><span>Total Spent</span><span class="font-semibold text-green-600">{{ format_currency($user->orders_sum_total ?? 0) }}</span></div>
        </div>
        <div class="mt-4 w-full">
            <a href="{{ route('admin.orders.index', ['search' => $user->email]) }}"
               class="btn-secondary w-full justify-center text-xs py-1.5">📋 View Orders</a>
        </div>
    </div>

    {{-- AI Segment --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">🤖 AI Segment</div>
        @if(!empty($aiProfile['segment']))
            @php
                $seg = $aiProfile['segment'];
                $segColors = [
                    'VIP Customers'      => ['bg' => 'bg-yellow-50 border-yellow-200', 'badge' => 'badge-yellow', 'icon' => '👑'],
                    'High Spenders'      => ['bg' => 'bg-green-50 border-green-200',  'badge' => 'badge-green',  'icon' => '💰'],
                    'At Risk'            => ['bg' => 'bg-red-50 border-red-200',       'badge' => 'badge-red',    'icon' => '⚠️'],
                    'Frequent Browsers'  => ['bg' => 'bg-blue-50 border-blue-200',    'badge' => 'badge-blue',   'icon' => '👁️'],
                    'Casual Shoppers'    => ['bg' => 'bg-gray-50 border-gray-200',    'badge' => 'badge-gray',   'icon' => '🛍️'],
                ];
                $sc = $segColors[$seg['name']] ?? ['bg' => 'bg-blue-50 border-blue-200', 'badge' => 'badge-blue', 'icon' => '🤖'];
            @endphp
            <div class="rounded-lg border {{ $sc['bg'] }} p-4 text-center">
                <div class="text-3xl mb-1">{{ $sc['icon'] }}</div>
                <div class="font-bold text-gray-800 text-base">{{ $seg['name'] }}</div>
                <div class="text-xs text-gray-500 mt-1">Segment #{{ $seg['id'] }}</div>
            </div>
        @else
            <div class="text-center text-gray-300 py-4">
                <div class="text-2xl mb-1">—</div>
                <div class="text-xs">Not yet segmented</div>
                <div class="text-xs mt-1">(needs more activity)</div>
            </div>
        @endif
    </div>

    {{-- Event Summary --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">📊 Activity Summary</div>
        @if(!empty($aiProfile['event_summary']))
            @php
                $esum = $aiProfile['event_summary'];
                $icons = ['product_view' => '👁️', 'add_to_cart' => '🛒', 'purchase' => '💳'];
            @endphp
            <div class="space-y-2">
                @foreach($esum as $type => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">{{ $icons[$type] ?? '📌' }}</span>
                        <span class="text-xs text-gray-600">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                    </div>
                    <span class="font-bold text-gray-800 text-sm">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-xs text-gray-500">
                <span>Total events</span>
                <span class="font-bold">{{ array_sum($esum) }}</span>
            </div>
        @else
            <div class="text-center text-gray-300 py-4">
                <div class="text-2xl mb-1">📭</div>
                <div class="text-xs">No tracked events yet</div>
            </div>
        @endif
    </div>

    {{-- Quick Links --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">⚡ Quick Actions</div>
        <div class="space-y-2">
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.role', $user) }}">
                @csrf @method('PATCH')
                <button type="submit"
                    class="w-full btn-secondary text-xs py-2 justify-center {{ $user->role === 'admin' ? 'text-red-600 border-red-200' : '' }}"
                    onclick="return confirm('Change role for {{ addslashes($user->name) }}?')">
                    {{ $user->role === 'admin' ? '↓ Demote to Customer' : '↑ Make Admin' }}
                </button>
            </form>
            @endif
            <a href="{{ route('admin.orders.index', ['search' => $user->email]) }}"
               class="btn-secondary w-full justify-center text-xs py-2">📋 View All Orders</a>
            <a href="{{ route('admin.ai-health') }}"
               class="btn-secondary w-full justify-center text-xs py-2">🩺 AI Service Health</a>
        </div>
    </div>
</div>

{{-- ── Recommendations being shown ────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-800">🎯 AI Recommendations</h2>
                <p class="text-xs text-gray-400 mt-0.5">Products currently being recommended to this user</p>
            </div>
            <span class="badge badge-blue text-xs">{{ $recommendedProducts->count() }} products</span>
        </div>

        @if($recommendedProducts->isEmpty())
            <div class="p-8 text-center text-gray-300">
                <div class="text-4xl mb-2">🤖</div>
                <div class="text-sm">No recommendations yet</div>
                <div class="text-xs mt-1 text-gray-400">Recommendations appear after the user has browsed or purchased</div>
            </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($recommendedProducts as $i => $product)
            <div class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                <span class="text-lg font-bold text-gray-200 w-6 text-center">{{ $i + 1 }}</span>
                <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                    @if($product->image)
                        <img src="{{ $product->image }}" alt="{{ $product->name }}"
                             class="w-full h-full object-cover" onerror="this.style.display='none'">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">📦</div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('admin.products.edit', $product->id) }}"
                       class="font-medium text-sm text-gray-800 hover:text-blue-600 truncate block">
                        {{ $product->name }}
                    </a>
                    <div class="text-xs text-gray-400">
                        {{ $product->category->name ?? '—' }} · {{ format_currency($product->price) }}
                    </div>
                </div>
                <div class="text-right shrink-0">
                    @if($product->ai_score)
                        <span class="text-xs font-semibold text-blue-600">{{ number_format($product->ai_score, 1) }}</span>
                        <div class="text-xs text-gray-300">score</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Top Interacted Products ────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-gray-800">🔥 Top Interacted Products</h2>
                <p class="text-xs text-gray-400 mt-0.5">Products user viewed, carted, or purchased</p>
            </div>
            <span class="badge badge-purple text-xs">{{ $interactedProducts->count() }} products</span>
        </div>

        @if($interactedProducts->isEmpty())
            <div class="p-8 text-center text-gray-300">
                <div class="text-4xl mb-2">📭</div>
                <div class="text-sm">No interaction data</div>
                <div class="text-xs mt-1 text-gray-400">Data appears after user browses or shops on site</div>
            </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($interactedProducts as $i => $product)
            @php
                $score = $product->interaction_score ?? 0;
                $maxScore = $interactedProducts->max('interaction_score') ?: 1;
                $pct = round($score / $maxScore * 100);
            @endphp
            <div class="px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-cover" onerror="this.style.display='none'">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">📦</div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('admin.products.edit', $product->id) }}"
                           class="font-medium text-sm text-gray-800 hover:text-blue-600 truncate block">
                            {{ $product->name }}
                        </a>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                <div class="bg-gradient-to-r from-blue-400 to-purple-500 h-1.5 rounded-full"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-600 shrink-0">{{ $score }}pts</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ── Recent Activity Timeline ────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-gray-800">📅 Recent Activity (ClickHouse Events)</h2>
            <p class="text-xs text-gray-400 mt-0.5">Real-time behavioral events tracked from e-commerce site</p>
        </div>
        <span class="badge badge-green text-xs">{{ count($aiProfile['recent_events'] ?? []) }} events</span>
    </div>

    @if(empty($aiProfile['recent_events']))
        <div class="p-10 text-center text-gray-300">
            <div class="text-4xl mb-2">📭</div>
            <div class="text-sm font-medium">No activity recorded yet</div>
            <div class="text-xs mt-2 text-gray-400 max-w-xs mx-auto">
                Events will appear here as the user browses products, adds items to cart, and makes purchases.
            </div>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-3 text-left font-medium">Event</th>
                    <th class="px-5 py-3 text-left font-medium">Product</th>
                    <th class="px-5 py-3 text-right font-medium">Price</th>
                    <th class="px-5 py-3 text-right font-medium">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($aiProfile['recent_events'] as $event)
                @php
                    $evIcons = ['product_view' => ['icon' => '👁️', 'class' => 'badge-gray'],
                                'add_to_cart'  => ['icon' => '🛒', 'class' => 'badge-blue'],
                                'purchase'     => ['icon' => '💳', 'class' => 'badge-green']];
                    $ei = $evIcons[$event['event_type']] ?? ['icon' => '📌', 'class' => 'badge-gray'];
                @endphp
                <tr class="table-row">
                    <td class="px-5 py-3">
                        <span class="badge {{ $ei['class'] }} text-xs gap-1">
                            {{ $ei['icon'] }} {{ ucwords(str_replace('_', ' ', $event['event_type'])) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-700 text-xs">
                            {{ $event['product_name'] ?: '—' }}
                        </div>
                        @if($event['product_id'])
                            <div class="text-xs text-gray-400">ID: {{ $event['product_id'] }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right text-xs text-gray-600">
                        {{ $event['price'] ? format_currency($event['price']) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($event['timestamp'])->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
