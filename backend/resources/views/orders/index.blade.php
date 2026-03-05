@extends('layouts.app')
@section('title', 'My Orders')

@section('content')
<div style="max-width:1000px;margin:0 auto;padding:2rem 1rem;">
    <h1 style="font-size:2rem;font-weight:800;color:#F1F5F9;margin-bottom:2rem;">📦 My Orders</h1>

    @if($orders->isEmpty())
    <div style="text-align:center;padding:5rem 2rem;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;">
        <div style="font-size:4rem;margin-bottom:1rem;">📦</div>
        <h2 style="font-size:1.25rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">No orders yet</h2>
        <p style="color:#64748B;margin-bottom:1.5rem;">Your order history will appear here after your first purchase.</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Start Shopping</a>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:1rem;">
        @foreach($orders as $order)
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;transition:all 0.3s;" onmouseover="this.style.borderColor='rgba(59,130,246,0.3)'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                <div>
                    <div style="font-size:0.75rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">Order #</div>
                    <div style="font-size:1rem;font-weight:700;color:#3B82F6;">{{ $order->order_number }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:#64748B;margin-bottom:0.25rem;">Date</div>
                    <div style="font-size:0.875rem;color:#F1F5F9;">{{ $order->created_at->format('M d, Y') }}</div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:#64748B;margin-bottom:0.25rem;">Items</div>
                    <div style="font-size:0.875rem;color:#F1F5F9;">{{ $order->items->count() }} item(s)</div>
                </div>
                <div>
                    <div style="font-size:0.75rem;color:#64748B;margin-bottom:0.25rem;">Total</div>
                    <div style="font-size:1rem;font-weight:800;color:#F1F5F9;">{{ format_currency($order->total) }}</div>
                </div>
                <div>
                    @php
                    $colors = ['pending'=>'yellow','processing'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red'];
                    $c = $colors[$order->status] ?? 'gray';
                    @endphp
                    <span class="badge badge-{{ $c }}" style="padding:0.375rem 0.875rem;font-size:0.75rem;">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <a href="{{ route('orders.show', $order->id) }}" class="btn-ghost" style="font-size:0.8rem;">View Details →</a>
            </div>

            {{-- Item Previews --}}
            <div style="display:flex;gap:0.625rem;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
                @foreach($order->items->take(4) as $item)
                <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}"
                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border);"
                     title="{{ $item->product_name }}">
                @endforeach
                @if($order->items->count() > 4)
                <div style="width:48px;height:48px;border-radius:8px;background:var(--bg-elevated);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.75rem;color:#64748B;">+{{ $order->items->count() - 4 }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top:2rem;">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
