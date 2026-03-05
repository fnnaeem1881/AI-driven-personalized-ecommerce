@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('breadcrumb', 'Manage customer orders')

@section('content')
{{-- Status tabs --}}
<div class="flex gap-2 mb-5 flex-wrap">
    @foreach(['all','pending','processing','shipped','delivered','cancelled'] as $s)
    <a href="{{ route('admin.orders.index', array_merge(request()->query(), ['status' => $s])) }}"
       class="px-4 py-2 rounded-lg text-sm font-medium border transition-all
              {{ request('status', 'all') === $s
                 ? 'bg-blue-600 text-white border-blue-600'
                 : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300' }}">
        {{ ucfirst($s) }}
        <span class="ml-1 text-xs {{ request('status','all') === $s ? 'text-blue-100' : 'text-gray-400' }}">
            ({{ $counts[$s] }})
        </span>
    </a>
    @endforeach
</div>

{{-- Search --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex gap-3 items-end">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <div class="flex-1">
        <label class="form-label">Search order / customer</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Order #, name, email…" class="form-input">
    </div>
    <button type="submit" class="btn-primary">🔍 Search</button>
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary">✕ Clear</a>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left font-medium">Order</th>
                    <th class="px-5 py-3 text-left font-medium">Customer</th>
                    <th class="px-5 py-3 text-left font-medium">Date</th>
                    <th class="px-5 py-3 text-center font-medium">Items</th>
                    <th class="px-5 py-3 text-right font-medium">Total</th>
                    <th class="px-5 py-3 text-left font-medium">Status</th>
                    <th class="px-5 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
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
                <tr class="table-row">
                    <td class="px-5 py-3 font-mono text-xs text-blue-600 font-medium">
                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">#{{ $order->order_number }}</a>
                    </td>
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-800">{{ $order->user?->name ?? 'Guest' }}</div>
                        <div class="text-xs text-gray-400">{{ $order->user?->email }}</div>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-5 py-3 text-center text-gray-600">{{ $order->items->count() }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ format_currency($order->total) }}</td>
                    <td class="px-5 py-3"><span class="badge {{ $cls }}">{{ ucfirst($order->status) }}</span></td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn-secondary text-xs py-1 px-3">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
