@extends('layouts.admin')

@section('title', 'ClickHouse Analytics')
@section('page-title', '📊 ClickHouse Live Analytics')
@section('breadcrumb', 'Real-time behavioral data from your store')

@section('content')

{{-- Period Selector --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-700 bg-cyan-100 border border-cyan-200 rounded-full px-3 py-1.5">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full {{ $serviceUp ? 'animate-pulse' : '' }}"></span>
            {{ $serviceUp ? 'ClickHouse Live' : 'Service Offline' }}
        </span>
        @if(!$serviceUp)
        <span class="text-sm text-red-500">Event service is not running. Start it with <code class="bg-gray-100 px-1 rounded">start-all.bat</code></span>
        @endif
    </div>
    <div class="flex items-center gap-2">
        <span class="text-sm text-gray-500 font-medium">Period:</span>
        @foreach([7 => '7 days', 14 => '14 days', 30 => '30 days', 90 => '90 days'] as $d => $label)
        <a href="{{ route('admin.analytics', ['days' => $d]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-all
                  {{ $days == $d ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400 hover:text-blue-600' }}">
            {{ $label }}
        </a>
        @endforeach
        <a href="{{ route('admin.ai-health') }}" class="ml-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 bg-white text-gray-600 hover:border-purple-400 hover:text-purple-600 transition-all">
            🩺 Health →
        </a>
    </div>
</div>

@if(!$serviceUp)
<div class="bg-orange-50 border border-orange-200 rounded-xl p-8 text-center">
    <div class="text-4xl mb-3">⚠️</div>
    <h3 class="text-lg font-semibold text-orange-800 mb-2">Event Service Offline</h3>
    <p class="text-orange-600 mb-4">The event service must be running to view ClickHouse analytics.</p>
    <a href="{{ route('admin.ai-health') }}" class="inline-flex items-center gap-2 bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-700">View Service Health →</a>
</div>
@else

{{-- ══════════════════════════════ OVERVIEW STAT CARDS ══════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4 mb-6">
    @php
        $overviewCards = [
            ['icon'=>'📊','label'=>'Total Events',    'value'=>number_format($overview['total_events'] ?? 0),   'sub'=>'All behavioral events',       'color'=>'cyan'],
            ['icon'=>'👥','label'=>'Unique Users',     'value'=>number_format($overview['unique_users'] ?? 0),   'sub'=>'Distinct logged-in users',    'color'=>'blue'],
            ['icon'=>'🔗','label'=>'Unique Sessions',  'value'=>number_format($overview['unique_sessions'] ?? 0),'sub'=>'Browser sessions tracked',   'color'=>'purple'],
            ['icon'=>'👁','label'=>'Product Views',    'value'=>number_format($overview['views'] ?? 0),          'sub'=>'view_product events',         'color'=>'indigo'],
            ['icon'=>'🛒','label'=>'Cart Adds',        'value'=>number_format($overview['cart_adds'] ?? 0),      'sub'=>'add_to_cart events',          'color'=>'violet'],
            ['icon'=>'✅','label'=>'Purchases',        'value'=>number_format($overview['purchases'] ?? 0),      'sub'=>'purchase events',             'color'=>'green'],
            ['icon'=>'🔍','label'=>'Searches',         'value'=>number_format($overview['searches'] ?? 0),       'sub'=>'search events',               'color'=>'amber'],
            ['icon'=>'❤️','label'=>'Wishlists',        'value'=>number_format($overview['wishlists'] ?? 0),      'sub'=>'wishlist_toggle events',      'color'=>'pink'],
        ];
        $colorMap = [
            'cyan'  =>['bg'=>'bg-cyan-50','border'=>'border-cyan-200','val'=>'text-cyan-700','icon'=>'bg-cyan-100'],
            'blue'  =>['bg'=>'bg-blue-50','border'=>'border-blue-200','val'=>'text-blue-700','icon'=>'bg-blue-100'],
            'purple'=>['bg'=>'bg-purple-50','border'=>'border-purple-200','val'=>'text-purple-700','icon'=>'bg-purple-100'],
            'indigo'=>['bg'=>'bg-indigo-50','border'=>'border-indigo-200','val'=>'text-indigo-700','icon'=>'bg-indigo-100'],
            'violet'=>['bg'=>'bg-violet-50','border'=>'border-violet-200','val'=>'text-violet-700','icon'=>'bg-violet-100'],
            'green' =>['bg'=>'bg-green-50','border'=>'border-green-200','val'=>'text-green-700','icon'=>'bg-green-100'],
            'amber' =>['bg'=>'bg-amber-50','border'=>'border-amber-200','val'=>'text-amber-700','icon'=>'bg-amber-100'],
            'pink'  =>['bg'=>'bg-pink-50','border'=>'border-pink-200','val'=>'text-pink-700','icon'=>'bg-pink-100'],
        ];
    @endphp
    @foreach($overviewCards as $card)
    @php $c = $colorMap[$card['color']]; @endphp
    <div class="rounded-xl border {{ $c['border'] }} {{ $c['bg'] }} p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xl {{ $c['icon'] }} w-8 h-8 rounded-lg flex items-center justify-center">{{ $card['icon'] }}</span>
            <span class="text-xs text-gray-400 font-medium">{{ $days }}d</span>
        </div>
        <div class="text-2xl font-bold {{ $c['val'] }}">{{ $card['value'] }}</div>
        <div class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</div>
        <div class="text-xs text-gray-400 mt-0.5">{{ $card['sub'] }}</div>
    </div>
    @endforeach
</div>

{{-- Conversion rates --}}
@php
    $views    = $overview['views'] ?? 0;
    $cartAdds = $overview['cart_adds'] ?? 0;
    $purchases= $overview['purchases'] ?? 0;
    $cartRate    = $views    > 0 ? round($cartAdds  / $views    * 100, 1) : 0;
    $purchRate   = $cartAdds > 0 ? round($purchases / $cartAdds * 100, 1) : 0;
    $overallRate = $views    > 0 ? round($purchases / $views    * 100, 2) : 0;
@endphp
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <span>🔄</span> Conversion Funnel
        <span class="text-xs text-gray-400 font-normal">(last {{ $days }} days)</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
            <div class="text-3xl font-bold text-blue-700">{{ $cartRate }}%</div>
            <div class="text-sm text-gray-600 mt-1">Views → Cart</div>
            <div class="text-xs text-gray-400">{{ number_format($views) }} → {{ number_format($cartAdds) }}</div>
            <div class="mt-2 h-1.5 bg-blue-100 rounded-full"><div class="h-full bg-blue-500 rounded-full" style="width:{{ min($cartRate*3,100) }}%"></div></div>
        </div>
        <div class="text-center p-4 bg-purple-50 rounded-xl border border-purple-100">
            <div class="text-3xl font-bold text-purple-700">{{ $purchRate }}%</div>
            <div class="text-sm text-gray-600 mt-1">Cart → Purchase</div>
            <div class="text-xs text-gray-400">{{ number_format($cartAdds) }} → {{ number_format($purchases) }}</div>
            <div class="mt-2 h-1.5 bg-purple-100 rounded-full"><div class="h-full bg-purple-500 rounded-full" style="width:{{ min($purchRate*3,100) }}%"></div></div>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-xl border border-green-100">
            <div class="text-3xl font-bold text-green-700">{{ $overallRate }}%</div>
            <div class="text-sm text-gray-600 mt-1">Overall (View → Buy)</div>
            <div class="text-xs text-gray-400">{{ number_format($views) }} → {{ number_format($purchases) }}</div>
            <div class="mt-2 h-1.5 bg-green-100 rounded-full"><div class="h-full bg-green-500 rounded-full" style="width:{{ min($overallRate*10,100) }}%"></div></div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════ TIMELINE CHART ══════════════════════════════ --}}
@if(!empty($timelineDates))
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <span>📈</span> Event Timeline
        <span class="text-xs text-gray-400 font-normal">(last {{ $days }} days — from ClickHouse)</span>
    </h3>
    <canvas id="timelineChart" height="80"></canvas>
</div>
@endif

{{-- ══════════════════════════════ EVENT TYPES ALL-TIME ══════════════════════════════ --}}
@if(!empty($eventTypes))
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <span>🏷️</span> All Event Types
        <span class="text-xs text-gray-400 font-normal">(all-time totals)</span>
    </h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-4 py-2.5 text-left font-medium">Event Type</th>
                    <th class="px-4 py-2.5 text-right font-medium">Total</th>
                    <th class="px-4 py-2.5 text-right font-medium">Unique Users</th>
                    <th class="px-4 py-2.5 text-right font-medium">Unique Sessions</th>
                    <th class="px-4 py-2.5 text-right font-medium">Unique Products</th>
                    <th class="px-4 py-2.5 text-left font-medium">First Seen</th>
                    <th class="px-4 py-2.5 text-left font-medium">Last Seen</th>
                    <th class="px-4 py-2.5 text-left font-medium">Distribution</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $maxCount = collect($eventTypes)->max('total') ?: 1; @endphp
                @foreach($eventTypes as $et)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-mono font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded">
                            {{ $et['event_type'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">{{ number_format($et['total']) }}</td>
                    <td class="px-4 py-3 text-right text-blue-700 font-medium">{{ number_format($et['unique_users']) }}</td>
                    <td class="px-4 py-3 text-right text-purple-700 font-medium">{{ number_format($et['unique_sessions']) }}</td>
                    <td class="px-4 py-3 text-right text-indigo-700 font-medium">{{ number_format($et['unique_products']) }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $et['first_seen'] ? \Carbon\Carbon::parse($et['first_seen'])->format('d M y H:i') : '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $et['last_seen'] ? \Carbon\Carbon::parse($et['last_seen'])->format('d M y H:i') : '—' }}</td>
                    <td class="px-4 py-3 w-32">
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-cyan-500" style="width:{{ round($et['total']/$maxCount*100) }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════ TOP PRODUCTS ══════════════════════════════ --}}
@if(!empty($products))
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <span>📦</span> Top Products by Behavior
            <span class="text-xs text-gray-400 font-normal">(last {{ $days }} days — ClickHouse)</span>
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-4 py-2.5 text-left font-medium">#</th>
                    <th class="px-4 py-2.5 text-left font-medium">Product</th>
                    <th class="px-4 py-2.5 text-right font-medium">👁 Views</th>
                    <th class="px-4 py-2.5 text-right font-medium">🛒 Cart Adds</th>
                    <th class="px-4 py-2.5 text-right font-medium">✅ Purchases</th>
                    <th class="px-4 py-2.5 text-right font-medium">👥 Users</th>
                    <th class="px-4 py-2.5 text-right font-medium">Cart Rate</th>
                    <th class="px-4 py-2.5 text-left font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($products as $i => $p)
                @php $pcartRate = $p['views'] > 0 ? round($p['cart_adds']/$p['views']*100, 1) : 0; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white text-xs flex items-center justify-center font-bold">{{ $i+1 }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($p['image'])
                            <img src="{{ $p['image'] }}" alt="" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs flex-shrink-0">📦</div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-800 text-sm">{{ Str::limit($p['name'], 40) }}</div>
                                <div class="text-xs text-gray-400">ID: {{ $p['product_id'] }}{{ $p['price'] ? ' · '.format_currency($p['price']) : '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">{{ number_format($p['views']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-violet-700">{{ number_format($p['cart_adds']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-green-700">{{ number_format($p['purchases']) }}</td>
                    <td class="px-4 py-3 text-right text-blue-700">{{ number_format($p['unique_users']) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-xs font-semibold {{ $pcartRate >= 10 ? 'text-green-600' : ($pcartRate >= 5 ? 'text-amber-600' : 'text-gray-500') }}">
                            {{ $pcartRate }}%
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            @if($p['slug'])
                            <a href="{{ route('products.show', $p['slug']) }}" target="_blank" class="text-xs text-blue-600 hover:underline">View</a>
                            @endif
                            @if($p['product_id'])
                            <a href="{{ route('admin.products.edit', $p['product_id']) }}" class="text-xs text-purple-600 hover:underline">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════ TOP USERS ══════════════════════════════ --}}
@if(!empty($users))
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <span>👥</span> Most Active Users
            <span class="text-xs text-gray-400 font-normal">(last {{ $days }} days — ClickHouse)</span>
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-4 py-2.5 text-left font-medium">#</th>
                    <th class="px-4 py-2.5 text-left font-medium">User</th>
                    <th class="px-4 py-2.5 text-right font-medium">Total Events</th>
                    <th class="px-4 py-2.5 text-right font-medium">👁 Views</th>
                    <th class="px-4 py-2.5 text-right font-medium">🛒 Cart</th>
                    <th class="px-4 py-2.5 text-right font-medium">✅ Purchases</th>
                    <th class="px-4 py-2.5 text-right font-medium">Sessions</th>
                    <th class="px-4 py-2.5 text-right font-medium">Products Seen</th>
                    <th class="px-4 py-2.5 text-left font-medium">Last Active</th>
                    <th class="px-4 py-2.5 text-left font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $i => $u)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-green-500 to-cyan-600 text-white text-xs flex items-center justify-center font-bold">{{ $i+1 }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $u['name'] }}</div>
                        <div class="text-xs text-gray-400">{{ $u['email'] }} · ID: {{ $u['user_id'] }}</div>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">{{ number_format($u['total_events']) }}</td>
                    <td class="px-4 py-3 text-right text-indigo-700">{{ number_format($u['views']) }}</td>
                    <td class="px-4 py-3 text-right text-violet-700">{{ number_format($u['cart_adds']) }}</td>
                    <td class="px-4 py-3 text-right text-green-700 font-semibold">{{ number_format($u['purchases']) }}</td>
                    <td class="px-4 py-3 text-right text-blue-700">{{ number_format($u['sessions']) }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format($u['unique_products']) }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $u['last_seen'] ? \Carbon\Carbon::parse($u['last_seen'])->diffForHumans() : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.users.show', $u['user_id']) }}" class="text-xs text-purple-600 hover:underline">AI Profile</a>
                            <a href="{{ route('admin.users.show', $u['user_id']) }}" class="text-xs text-blue-600 hover:underline">Details</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════ LIVE EVENTS TABLE ══════════════════════════════ --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <span>📋</span> Raw Events
            <span class="text-xs text-gray-400 font-normal">(all-time, newest first)</span>
            <span id="eventsTotal" class="text-xs text-cyan-600 bg-cyan-50 border border-cyan-200 rounded-full px-2 py-0.5 font-semibold"></span>
        </h3>
        <div class="flex items-center gap-2 flex-wrap">
            <select id="eventTypeFilter" class="text-xs border border-gray-300 rounded-lg px-2 py-1.5 bg-white text-gray-700 focus:border-blue-400 focus:outline-none">
                <option value="">All event types</option>
                @foreach($eventTypes as $et)
                <option value="{{ $et['event_type'] }}">{{ $et['event_type'] }} ({{ number_format($et['total']) }})</option>
                @endforeach
            </select>
            <button onclick="loadEvents(0)" class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">🔄 Refresh</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <th class="px-3 py-2.5 text-left font-medium">Timestamp</th>
                    <th class="px-3 py-2.5 text-left font-medium">Event Type</th>
                    <th class="px-3 py-2.5 text-left font-medium">User ID</th>
                    <th class="px-3 py-2.5 text-left font-medium">Session</th>
                    <th class="px-3 py-2.5 text-left font-medium">Product</th>
                    <th class="px-3 py-2.5 text-right font-medium">Price</th>
                    <th class="px-3 py-2.5 text-right font-medium">Qty</th>
                    <th class="px-3 py-2.5 text-left font-medium">IP</th>
                </tr>
            </thead>
            <tbody id="eventsTableBody" class="divide-y divide-gray-100">
                <tr><td colspan="8" class="px-3 py-8 text-center text-gray-400">Loading events…</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
        <button id="prevBtn" onclick="changePage(-1)" disabled class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-500 disabled:opacity-40 hover:border-blue-400 hover:text-blue-600">← Previous</button>
        <span id="pageInfo" class="text-xs text-gray-400">—</span>
        <button id="nextBtn" onclick="changePage(1)" class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-500 disabled:opacity-40 hover:border-blue-400 hover:text-blue-600">Next →</button>
    </div>
</div>

{{-- Info footer --}}
@if($overview['first_event'] ?? false)
<div class="text-xs text-gray-400 text-center pb-4">
    ClickHouse tracking since: {{ \Carbon\Carbon::parse($overview['first_event'])->format('d M Y H:i') }} ·
    Last event: {{ \Carbon\Carbon::parse($overview['last_event'])->diffForHumans() }}
</div>
@endif

@endif {{-- end if serviceUp --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ─── Timeline Chart ──────────────────────────────────────────────────────────
@if(!empty($timelineDates))
(function(){
    const dates   = @json($timelineDates);
    const series  = @json($timelineSeries);

    const colorMap = {
        'view_product':   {border:'#6366F1',bg:'rgba(99,102,241,0.08)'},
        'add_to_cart':    {border:'#8B5CF6',bg:'rgba(139,92,246,0.08)'},
        'purchase':       {border:'#10B981',bg:'rgba(16,185,129,0.08)'},
        'search':         {border:'#F59E0B',bg:'rgba(245,158,11,0.08)'},
        'wishlist_toggle':{border:'#EC4899',bg:'rgba(236,72,153,0.08)'},
    };

    const datasets = Object.keys(series).map((evType, i) => {
        const c = colorMap[evType] || {border:`hsl(${i*50},65%,55%)`,bg:`hsla(${i*50},65%,55%,0.08)`};
        return {
            label: evType,
            data:  dates.map(d => series[evType][d] ?? 0),
            borderColor: c.border,
            backgroundColor: c.bg,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5,
            fill: true,
            tension: 0.3,
        };
    });

    new Chart(document.getElementById('timelineChart'), {
        type: 'line',
        data: { labels: dates, datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` } }
            },
            scales: {
                x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, precision: 0 } }
            }
        }
    });
})();
@endif

// ─── Live Events Table ───────────────────────────────────────────────────────
let currentOffset = 0;
const PAGE_SIZE   = 50;
let totalEvents   = 0;

const eventColors = {
    'view_product':    'bg-indigo-100 text-indigo-700',
    'add_to_cart':     'bg-violet-100 text-violet-700',
    'purchase':        'bg-green-100 text-green-700',
    'search':          'bg-amber-100 text-amber-700',
    'wishlist_toggle': 'bg-pink-100 text-pink-700',
    'checkout_start':  'bg-blue-100 text-blue-700',
    'checkout_complete':'bg-emerald-100 text-emerald-700',
};

function getEventBadgeClass(type) {
    return eventColors[type] || 'bg-gray-100 text-gray-700';
}

async function loadEvents(offset) {
    currentOffset = offset;
    const eventType = document.getElementById('eventTypeFilter').value;
    const tbody = document.getElementById('eventsTableBody');
    tbody.innerHTML = '<tr><td colspan="8" class="px-3 py-6 text-center text-gray-400">Loading…</td></tr>';

    try {
        const params = new URLSearchParams({ limit: PAGE_SIZE, offset, event_type: eventType });
        const res  = await fetch(`{{ route('admin.analytics.events') }}?${params}`);
        const data = await res.json();
        totalEvents = data.total || 0;

        document.getElementById('eventsTotal').textContent = `${totalEvents.toLocaleString()} events`;
        document.getElementById('pageInfo').textContent =
            `Showing ${offset+1}–${Math.min(offset+PAGE_SIZE, totalEvents)} of ${totalEvents.toLocaleString()}`;
        document.getElementById('prevBtn').disabled = offset <= 0;
        document.getElementById('nextBtn').disabled = offset + PAGE_SIZE >= totalEvents;

        if (!data.events || !data.events.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-3 py-8 text-center text-gray-400">No events found</td></tr>';
            return;
        }

        tbody.innerHTML = data.events.map(e => `
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 text-gray-500 whitespace-nowrap font-mono">${e.timestamp ? e.timestamp.substring(0,19) : '—'}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold ${getEventBadgeClass(e.event_type)}">
                        ${e.event_type}
                    </span>
                </td>
                <td class="px-3 py-2 text-gray-600">${e.user_id ? `<a href="{{ url('/admin/users') }}/${e.user_id}" class="text-blue-600 hover:underline">#${e.user_id}</a>` : '<span class="text-gray-400">guest</span>'}</td>
                <td class="px-3 py-2 text-gray-400 font-mono">${e.session_id || '—'}</td>
                <td class="px-3 py-2">
                    ${e.product_id ? `<span class="text-gray-700">${e.product_name ? e.product_name.substring(0,28) + (e.product_name.length > 28 ? '…' : '') : ''}</span><br><span class="text-gray-400">ID:${e.product_id}</span>` : '<span class="text-gray-300">—</span>'}
                </td>
                <td class="px-3 py-2 text-right text-green-700 font-medium">${e.price ? '৳'+parseFloat(e.price).toLocaleString() : '—'}</td>
                <td class="px-3 py-2 text-right text-gray-600">${e.quantity || '—'}</td>
                <td class="px-3 py-2 text-gray-400 font-mono">${e.ip_address || '—'}</td>
            </tr>
        `).join('');
    } catch(err) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-3 py-6 text-center text-red-400">Error loading events: ${err.message}</td></tr>`;
    }
}

function changePage(dir) {
    const newOffset = currentOffset + (dir * PAGE_SIZE);
    if (newOffset >= 0 && newOffset < totalEvents) loadEvents(newOffset);
}

document.getElementById('eventTypeFilter').addEventListener('change', () => loadEvents(0));

// Auto-load on page ready
loadEvents(0);
</script>
@endpush
