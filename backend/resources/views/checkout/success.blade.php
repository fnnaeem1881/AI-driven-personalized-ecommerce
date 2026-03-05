@extends('layouts.app')
@section('title', 'Order Confirmed!')

@section('content')
<div style="max-width:700px;margin:3rem auto;padding:0 1rem;text-align:center;">

    {{-- Success Animation --}}
    <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,rgba(16,185,129,0.2),rgba(59,130,246,0.2));border:2px solid rgba(16,185,129,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:3rem;animation:pulseCheck 2s ease infinite;">
        ✅
    </div>

    <h1 style="font-size:2.25rem;font-weight:900;color:#F1F5F9;margin-bottom:0.5rem;">Order Confirmed!</h1>
    <p style="color:#64748B;font-size:1rem;margin-bottom:2rem;">
        Thank you for your order. We'll send you a confirmation email shortly.
    </p>

    {{-- Order Card --}}
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:2rem;text-align:left;margin-bottom:1.5rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);margin-bottom:1.25rem;">
            <div>
                <div style="font-size:0.75rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">Order Number</div>
                <div style="font-size:1rem;font-weight:700;color:#3B82F6;">{{ $order->order_number }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">Est. Delivery</div>
                <div style="font-size:1rem;font-weight:700;color:#F1F5F9;">{{ now()->addDays(5)->format('M d') }} – {{ now()->addDays(8)->format('M d, Y') }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">Payment</div>
                <div style="font-size:1rem;font-weight:700;color:#F1F5F9;">{{ strtoupper($order->payment_method) }}</div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">Total</div>
                <div style="font-size:1rem;font-weight:700;color:#10B981;">{{ format_currency($order->total) }}</div>
            </div>
        </div>

        <h4 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:0.875rem;">Items Ordered</h4>
        @foreach($order->items as $item)
        <div style="display:flex;align-items:center;gap:0.875rem;padding:0.625rem 0;border-bottom:1px solid rgba(59,130,246,0.08);">
            <img src="{{ $item->product_image }}" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
            <div style="flex:1;">
                <div style="font-size:0.85rem;font-weight:600;color:#F1F5F9;">{{ $item->product_name }}</div>
                <div style="font-size:0.75rem;color:#64748B;">Qty: {{ $item->quantity }}</div>
            </div>
            <span style="font-size:0.875rem;font-weight:700;color:#F1F5F9;">{{ format_currency($item->total) }}</span>
        </div>
        @endforeach
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:1rem;justify-content:center;">
        <a href="{{ route('orders.show', $order->id) }}" class="btn-primary">📦 Track Order</a>
        <a href="{{ route('products.index') }}" class="btn-outline">Continue Shopping</a>
    </div>
</div>

<style>
@keyframes pulseCheck { 0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.3); } 50% { box-shadow: 0 0 0 16px rgba(16,185,129,0); } }
</style>
@endsection
