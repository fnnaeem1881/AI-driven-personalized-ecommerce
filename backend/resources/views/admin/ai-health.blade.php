@extends('layouts.admin')

@section('title', 'Services Health')
@section('page-title', 'Services Health')
@section('breadcrumb', 'Live status of all running services')

@section('header-actions')
    <button onclick="window.location.reload()" class="btn-primary">🔄 Refresh</button>
@endsection

@section('content')

{{-- ── Overall Status Bar ─────────────────────────────────────────────── --}}
@php
    $allUp = $aiOnline && $eventOnline && $dbOk
             && ($aiHealth['services']['clickhouse'] ?? false)
             && ($aiHealth['services']['redis'] ?? false);
@endphp
<div class="rounded-xl border p-5 mb-6 flex items-center gap-4
            {{ $allUp ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
    <div class="text-4xl">{{ $allUp ? '✅' : '⚠️' }}</div>
    <div>
        <div class="font-bold text-gray-800 text-lg">
            {{ $allUp ? 'All Systems Operational' : 'Some Services Need Attention' }}
        </div>
        <div class="text-xs text-gray-500 mt-0.5">
            Last checked: {{ now()->format('d M Y, H:i:s') }} (page load time)
        </div>
    </div>
    <div class="ml-auto text-right">
        <div class="text-xs text-gray-400">Events tracked</div>
        <div class="text-2xl font-bold text-gray-800">{{ number_format($totalEvents) }}</div>
        <div class="text-xs text-gray-400">in ClickHouse</div>
    </div>
</div>

{{-- ── Service Cards Grid ──────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

    {{-- Laravel --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🐘</span>
                <div>
                    <div class="font-semibold text-gray-800">Laravel Backend</div>
                    <div class="text-xs text-gray-400">PHP {{ PHP_VERSION }}</div>
                </div>
            </div>
            <span class="badge {{ $dbOk ? 'badge-green' : 'badge-red' }}">
                {{ $dbOk ? '● Online' : '● Down' }}
            </span>
        </div>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">Port</span>
                <span class="font-medium text-xs">{{ request()->getPort() }}</span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">MySQL DB</span>
                <span class="badge {{ $dbOk ? 'badge-green' : 'badge-red' }} text-xs">
                    {{ $dbOk ? 'Connected' : 'Error' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1.5">
                <span class="text-gray-500 text-xs">Environment</span>
                <span class="text-xs font-medium">{{ app()->environment() }}</span>
            </div>
        </div>
    </div>

    {{-- AI Service --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🤖</span>
                <div>
                    <div class="font-semibold text-gray-800">AI / ML Service</div>
                    <div class="text-xs text-gray-400">FastAPI · Port 8001</div>
                </div>
            </div>
            <span class="badge {{ $aiOnline ? ($aiHealth['status'] === 'healthy' ? 'badge-green' : 'badge-yellow') : 'badge-red' }}">
                {{ $aiOnline ? ($aiHealth['status'] === 'healthy' ? '● Healthy' : '● Degraded') : '● Offline' }}
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">ClickHouse</span>
                <span class="badge {{ ($aiHealth['services']['clickhouse'] ?? false) ? 'badge-green' : 'badge-red' }} text-xs">
                    {{ ($aiHealth['services']['clickhouse'] ?? false) ? 'Connected' : 'Disconnected' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">Redis Cache</span>
                <span class="badge {{ ($aiHealth['services']['redis'] ?? false) ? 'badge-green' : 'badge-red' }} text-xs">
                    {{ ($aiHealth['services']['redis'] ?? false) ? 'Connected' : 'Disconnected' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">Cart Abandon Model</span>
                <span class="badge {{ ($aiHealth['models']['cart_abandonment'] ?? false) ? 'badge-green' : 'badge-yellow' }} text-xs">
                    {{ ($aiHealth['models']['cart_abandonment'] ?? false) ? 'Loaded' : 'Not Loaded' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1.5">
                <span class="text-gray-500 text-xs">Segmentation Model</span>
                <span class="badge {{ ($aiHealth['models']['user_segmentation'] ?? false) ? 'badge-green' : 'badge-yellow' }} text-xs">
                    {{ ($aiHealth['models']['user_segmentation'] ?? false) ? 'Loaded' : 'Not Loaded' }}
                </span>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100">
            <a href="{{ $aiServiceUrl }}/docs" target="_blank"
               class="btn-secondary w-full justify-center text-xs py-1.5">📖 API Docs →</a>
        </div>
    </div>

    {{-- Event Service --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📡</span>
                <div>
                    <div class="font-semibold text-gray-800">Event Service</div>
                    <div class="text-xs text-gray-400">FastAPI · Port 8000</div>
                </div>
            </div>
            <span class="badge {{ $eventOnline ? ($eventHealth['status'] === 'healthy' ? 'badge-green' : 'badge-yellow') : 'badge-red' }}">
                {{ $eventOnline ? ($eventHealth['status'] === 'healthy' ? '● Healthy' : '● Degraded') : '● Offline' }}
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">ClickHouse</span>
                <span class="badge {{ ($eventHealth['clickhouse'] ?? false) ? 'badge-green' : 'badge-red' }} text-xs">
                    {{ ($eventHealth['clickhouse'] ?? false) ? 'Connected' : 'Disconnected' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                <span class="text-gray-500 text-xs">Redis Queue</span>
                <span class="badge {{ ($eventHealth['redis'] ?? false) ? 'badge-green' : 'badge-yellow' }} text-xs">
                    {{ ($eventHealth['redis'] ?? false) ? 'Connected' : 'Not Connected' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1.5">
                <span class="text-gray-500 text-xs">Total Events Tracked</span>
                <span class="font-bold text-gray-800 text-sm">{{ number_format($totalEvents) }}</span>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100">
            <a href="{{ $eventServiceUrl }}/docs" target="_blank"
               class="btn-secondary w-full justify-center text-xs py-1.5">📖 API Docs →</a>
        </div>
    </div>
</div>

{{-- ── AI Models Detail ────────────────────────────────────────────────── --}}
@if($aiOnline && !empty($aiModels))
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    {{-- Recommendation Engines --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="font-semibold text-gray-800 mb-4">🎯 Recommendation Engines</div>
        @php $rec = $aiModels['recommendation_engine'] ?? []; @endphp
        <div class="space-y-2">
            @foreach([
                'collaborative_filtering' => ['name' => 'Collaborative Filtering', 'desc' => 'Based on similar users'],
                'content_based'           => ['name' => 'Content-Based',           'desc' => 'Based on product attributes'],
                'popular_products'        => ['name' => 'Popular Products',         'desc' => 'Based on views & purchases'],
                'trending_products'       => ['name' => 'Trending Products',        'desc' => 'Growth in last 7 days'],
            ] as $key => $info)
            <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                <div>
                    <div class="text-sm font-medium text-gray-700">{{ $info['name'] }}</div>
                    <div class="text-xs text-gray-400">{{ $info['desc'] }}</div>
                </div>
                <span class="badge {{ ($rec[$key] ?? '') === 'active' ? 'badge-green' : 'badge-red' }} text-xs">
                    {{ ($rec[$key] ?? '') === 'active' ? 'Active' : 'Inactive' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ML Models --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="font-semibold text-gray-800 mb-4">🧠 Trained ML Models</div>
        <div class="space-y-3">
            @php
                $ca  = $aiModels['cart_abandonment_model'] ?? [];
                $seg = $aiModels['user_segmentation_model'] ?? [];
                $df  = $aiModels['data_files'] ?? [];
            @endphp

            {{-- Cart Abandonment --}}
            <div class="rounded-lg bg-gray-50 p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-medium text-sm text-gray-700">Cart Abandonment (RandomForest)</span>
                    <span class="badge {{ ($ca['status'] ?? '') === 'active' ? 'badge-green' : 'badge-gray' }} text-xs">
                        {{ ($ca['status'] ?? '') === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex gap-3 text-xs text-gray-500">
                    <span>Model: {{ ($ca['loaded'] ?? false) ? '✓ Loaded' : '✗ Not Loaded' }}</span>
                    <span>Scaler: {{ ($ca['scaler_loaded'] ?? false) ? '✓ Loaded' : '✗ Not Loaded' }}</span>
                </div>
            </div>

            {{-- User Segmentation --}}
            <div class="rounded-lg bg-gray-50 p-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-medium text-sm text-gray-700">User Segmentation (KMeans)</span>
                    <span class="badge {{ ($seg['status'] ?? '') === 'active' ? 'badge-green' : 'badge-gray' }} text-xs">
                        {{ ($seg['status'] ?? '') === 'active' ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex gap-3 text-xs text-gray-500">
                    <span>Model: {{ ($seg['loaded'] ?? false) ? '✓ Loaded' : '✗ Not Loaded' }}</span>
                    <span>Segments: {{ $seg['segments'] ?? '—' }}</span>
                </div>
            </div>

            {{-- Data Files --}}
            <div class="rounded-lg bg-gray-50 p-3">
                <div class="font-medium text-sm text-gray-700 mb-2">Data Files</div>
                <div class="flex gap-3 text-xs text-gray-500 flex-wrap">
                    <span class="{{ ($df['popular_products'] ?? false) ? 'text-green-600' : 'text-red-500' }}">
                        {{ ($df['popular_products'] ?? false) ? '✓' : '✗' }} Popular Products
                    </span>
                    <span class="{{ ($df['user_product_matrix'] ?? false) ? 'text-green-600' : 'text-red-500' }}">
                        {{ ($df['user_product_matrix'] ?? false) ? '✓' : '✗' }} Interaction Matrix
                    </span>
                    <span class="{{ ($df['user_segments'] ?? false) ? 'text-green-600' : 'text-red-500' }}">
                        {{ ($df['user_segments'] ?? false) ? '✓' : '✗' }} User Segments
                    </span>
                </div>
            </div>
        </div>

        {{-- Retrain button --}}
        <div class="mt-4 pt-3 border-t border-gray-100">
            <form method="POST" action="{{ route('admin.ai-retrain') }}">
                @csrf
                <button type="submit"
                    class="btn-primary w-full justify-center text-xs py-2"
                    onclick="return confirm('Retrain all models on real ClickHouse data? This runs in background.')">
                    🔁 Retrain Models on Real Data
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── Event Stats ─────────────────────────────────────────────────────── --}}
@if(!empty($eventStats))
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">📊 ClickHouse Event Statistics (Last 90 Days)</h2>
        <p class="text-xs text-gray-400 mt-0.5">Real user behavioral data tracked from the e-commerce frontend</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-3 text-left font-medium">Event Type</th>
                    <th class="px-5 py-3 text-right font-medium">Total Events</th>
                    <th class="px-5 py-3 text-right font-medium">Unique Users</th>
                    <th class="px-5 py-3 text-right font-medium">Unique Sessions</th>
                    <th class="px-5 py-3 text-left font-medium">Visual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php $maxCount = collect($eventStats)->max('count') ?: 1; @endphp
                @foreach($eventStats as $stat)
                @php
                    $icons = ['product_view' => '👁️', 'add_to_cart' => '🛒', 'purchase' => '💳'];
                    $icon  = $icons[$stat['event_type']] ?? '📌';
                    $pct   = round($stat['count'] / $maxCount * 100);
                @endphp
                <tr class="table-row">
                    <td class="px-5 py-3">
                        <span class="text-base mr-1">{{ $icon }}</span>
                        <span class="font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $stat['event_type'])) }}</span>
                    </td>
                    <td class="px-5 py-3 text-right font-bold text-gray-800">{{ number_format($stat['count']) }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($stat['unique_users']) }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ number_format($stat['unique_sessions']) }}</td>
                    <td class="px-5 py-3 w-40">
                        <div class="bg-gray-100 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-400 to-purple-500 h-2 rounded-full"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Setup Instructions (if any service is down) ────────────────────── --}}
@if(!$aiOnline || !$eventOnline)
<div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
    <div class="font-semibold text-blue-800 mb-3">🚀 Start Missing Services</div>
    <p class="text-xs text-blue-700 mb-4">Run the following commands to start the services (or use <strong>start-all.bat</strong> in the project root):</p>
    <div class="space-y-3">
        @if(!$eventOnline)
        <div>
            <div class="text-xs font-semibold text-gray-600 mb-1">Event Service (Port 8000):</div>
            <pre class="bg-gray-800 text-green-400 text-xs p-3 rounded-lg overflow-x-auto">cd D:\AI-driven-personalized-ecommerce\services\event-service
.venv\Scripts\uvicorn main:app --host 0.0.0.0 --port 8000</pre>
        </div>
        @endif
        @if(!$aiOnline)
        <div>
            <div class="text-xs font-semibold text-gray-600 mb-1">AI Service (Port 8001):</div>
            <pre class="bg-gray-800 text-green-400 text-xs p-3 rounded-lg overflow-x-auto">cd D:\AI-driven-personalized-ecommerce\services\ai-service
.venv\Scripts\uvicorn main:app --host 0.0.0.0 --port 8001</pre>
        </div>
        @endif
    </div>
</div>
@endif

@endsection
