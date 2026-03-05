@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div style="max-width:900px;margin:0 auto;padding:2rem 1rem;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
        <div>
            <a href="{{ route('orders.index') }}" style="color:#64748B;text-decoration:none;font-size:0.875rem;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">← Back to Orders</a>
            <h1 style="font-size:2rem;font-weight:800;color:#F1F5F9;margin-top:0.5rem;">Order {{ $order->order_number }}</h1>
            <p style="color:#64748B;font-size:0.875rem;">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        @php $colors = ['pending'=>'yellow','processing'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red']; @endphp
        <span class="badge badge-{{ $colors[$order->status] ?? 'gray' }}" style="padding:0.5rem 1.25rem;font-size:0.875rem;">{{ ucfirst($order->status) }}</span>
    </div>

    {{-- Tracking Timeline --}}
    @if($order->status !== 'cancelled')
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:2rem;margin-bottom:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:2rem;">📍 Order Tracking</h3>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            @foreach($steps as $i => $step)
            <div class="tracking-step {{ $i <= $statusIndex ? 'done' : '' }} {{ $i === $statusIndex ? 'current' : '' }}">
                <div class="step-icon">{{ $step['icon'] }}</div>
                <div style="margin-top:0.75rem;text-align:center;">
                    <div style="font-size:0.8rem;font-weight:700;color:{{ $i <= $statusIndex ? '#F1F5F9' : '#64748B' }};">{{ $step['label'] }}</div>
                    @if($i === 0)<div style="font-size:0.7rem;color:#64748B;margin-top:0.25rem;">{{ $order->created_at->format('M d') }}</div>@endif
                    @if($i === 2 && $order->shipped_at)<div style="font-size:0.7rem;color:#64748B;margin-top:0.25rem;">{{ $order->shipped_at->format('M d') }}</div>@endif
                    @if($i === 3 && $order->delivered_at)<div style="font-size:0.7rem;color:#64748B;margin-top:0.25rem;">{{ $order->delivered_at->format('M d') }}</div>@endif
                </div>
            </div>
            @endforeach
        </div>

        @if($order->tracking_number)
        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:0.75rem;font-size:0.875rem;">
            <span style="color:#64748B;">Tracking #:</span>
            <span style="font-weight:700;color:#3B82F6;font-family:monospace;">{{ $order->tracking_number }}</span>
        </div>
        @endif
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;">
        {{-- Items --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.5rem;">
            <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">Items</h3>
            @foreach($order->items as $item)
            <div style="display:flex;gap:1rem;padding:0.875rem 0;border-bottom:1px solid var(--border);">
                <img src="{{ $item->product_image }}" style="width:64px;height:64px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                <div style="flex:1;">
                    <div style="font-size:0.9rem;font-weight:600;color:#F1F5F9;margin-bottom:0.25rem;">{{ $item->product_name }}</div>
                    <div style="font-size:0.8rem;color:#64748B;">Qty: {{ $item->quantity }} × {{ format_currency($item->price) }}</div>
                </div>
                <div style="font-size:0.9rem;font-weight:700;color:#F1F5F9;">{{ format_currency($item->total) }}</div>
            </div>
            @endforeach
        </div>

        {{-- Summary + Address --}}
        <div style="display:flex;flex-direction:column;gap:1rem;">
            {{-- Price Summary --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:1.25rem;">
                <h4 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:1rem;">Order Summary</h4>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.5rem;"><span style="color:#64748B;">Subtotal</span><span style="color:#F1F5F9;">{{ format_currency($order->subtotal) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.5rem;"><span style="color:#64748B;">Shipping</span><span style="color:{{ $order->shipping == 0 ? '#10B981' : '#F1F5F9' }};">{{ $order->shipping == 0 ? 'FREE' : format_currency($order->shipping) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-bottom:0.875rem;padding-bottom:0.875rem;border-bottom:1px solid var(--border);"><span style="color:#64748B;">Tax</span><span style="color:#F1F5F9;">{{ format_currency($order->tax) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;"><span style="color:#F1F5F9;">Total</span><span style="color:#3B82F6;">{{ format_currency($order->total) }}</span></div>
            </div>

            {{-- Shipping Address --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:1.25rem;">
                <h4 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:0.875rem;">Shipping Address</h4>
                <p style="color:#94A3B8;font-size:0.85rem;line-height:1.7;">
                    {{ $order->shipping_address['first_name'] }} {{ $order->shipping_address['last_name'] }}<br>
                    {{ $order->shipping_address['address'] }}<br>
                    {{ $order->shipping_address['city'] }}, {{ $order->shipping_address['state'] }} {{ $order->shipping_address['zip'] }}<br>
                    {{ $order->shipping_address['country'] }}
                </p>
            </div>

            {{-- Payment --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:1.25rem;">
                <h4 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">Payment</h4>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;">
                    <span style="color:#64748B;">Method</span><span style="color:#F1F5F9;">{{ strtoupper($order->payment_method) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;margin-top:0.375rem;">
                    <span style="color:#64748B;">Status</span>
                    <span class="badge badge-{{ $order->payment_status === 'paid' ? 'green' : 'yellow' }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
