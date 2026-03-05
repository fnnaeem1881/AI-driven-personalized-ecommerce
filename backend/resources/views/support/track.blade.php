@extends('layouts.app')
@section('title', 'Track Order')
@section('content')
<div style="max-width:700px;margin:0 auto;padding:2rem 1rem;">
    <div style="text-align:center;margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;margin-bottom:0.75rem;">📍 Track Your Order</h1>
        <p style="color:#64748B;">Enter your order number to get real-time status updates</p>
    </div>
    <div class="glass-card" style="padding:2rem;margin-bottom:2rem;">
        @auth
        <p style="color:#94A3B8;font-size:0.875rem;margin-bottom:1.5rem;">
            You're logged in. <a href="{{ route('orders.index') }}" class="text-blue-400 hover:underline">View all your orders →</a>
        </p>
        @endauth
        <form action="{{ route('orders.index') }}" method="GET" style="display:flex;gap:0.75rem;">
            <input type="text" name="search" placeholder="Enter order number (e.g. TN-2024-001)" class="form-input" style="flex:1;" required>
            <button type="submit" class="btn-primary" style="white-space:nowrap;">🔍 Track</button>
        </form>
    </div>
    <div class="glass-card" style="padding:1.75rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.5rem;">📊 Order Status Guide</h3>
        @foreach([
            ['badge-yellow','Pending','Your order has been received and is awaiting processing.'],
            ['badge-blue','Processing','We are preparing your order and verifying payment.'],
            ['badge-purple','Shipped','Your order is on its way! Track with the provided tracking number.'],
            ['badge-green','Delivered','Your order has been delivered. Enjoy your purchase!'],
            ['badge-red','Cancelled','The order has been cancelled. If a payment was made, a refund will be issued.'],
        ] as $status)
        <div style="display:flex;align-items:flex-start;gap:1rem;padding:0.875rem 0;border-bottom:1px solid rgba(59,130,246,0.08);">
            <span class="badge {{ $status[0] }}">{{ $status[1] }}</span>
            <p style="font-size:0.875rem;color:#94A3B8;line-height:1.5;margin:0;">{{ $status[2] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
