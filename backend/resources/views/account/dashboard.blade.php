@extends('layouts.app')
@section('title', 'My Dashboard')

@section('content')
<div style="max-width:1200px;margin:0 auto;padding:2rem 1rem;">
    <div style="display:grid;grid-template-columns:240px 1fr;gap:1.5rem;align-items:start;">

        {{-- Sidebar --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.25rem;position:sticky;top:80px;">
            {{-- Avatar --}}
            <div style="text-align:center;padding:1rem 0;border-bottom:1px solid var(--border);margin-bottom:1rem;">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#8B5CF6);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:white;margin:0 auto 0.75rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div style="font-size:0.9rem;font-weight:700;color:#F1F5F9;">{{ $user->name }}</div>
                <div style="font-size:0.75rem;color:#64748B;">{{ $user->email }}</div>
            </div>
            <a href="{{ route('account.dashboard') }}" class="account-nav-item active">📊 Dashboard</a>
            <a href="{{ route('orders.index') }}" class="account-nav-item">📦 My Orders</a>
            <a href="{{ route('wishlist.index') }}" class="account-nav-item">❤️ Wishlist</a>
            <a href="{{ route('account.profile') }}" class="account-nav-item">⚙️ Profile Settings</a>
            <div style="border-top:1px solid var(--border);margin-top:0.75rem;padding-top:0.75rem;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="width:100%;text-align:left;" class="account-nav-item" style="color:#EF4444;">🚪 Sign Out</button>
                </form>
            </div>
        </div>

        {{-- Main Content --}}
        <div>
            <h1 style="font-size:1.75rem;font-weight:800;color:#F1F5F9;margin-bottom:1.5rem;">Welcome back, {{ explode(' ', $user->name)[0] }}! 👋</h1>

            {{-- Stats --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
                @foreach([
                    ['📦','Total Orders', $stats['total_orders'],'blue'],
                    ['💰','Total Spent','$'.number_format($stats['total_spent'],2),'purple'],
                    ['❤️','Wishlist Items',$stats['wishlist_count'],'pink'],
                    ['⏳','Pending Orders',$stats['pending_orders'],'yellow'],
                ] as [$icon,$label,$val,$color])
                <div class="stat-card">
                    <div style="font-size:1.5rem;margin-bottom:0.75rem;">{{ $icon }}</div>
                    <div style="font-size:1.5rem;font-weight:900;color:#F1F5F9;margin-bottom:0.25rem;">{{ $val }}</div>
                    <div style="font-size:0.8rem;color:#64748B;">{{ $label }}</div>
                </div>
                @endforeach
            </div>

            {{-- Recent Orders --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.5rem;margin-bottom:1.5rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                    <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;">Recent Orders</h3>
                    <a href="{{ route('orders.index') }}" style="font-size:0.8rem;color:#3B82F6;text-decoration:none;">View All →</a>
                </div>
                @forelse($recentOrders as $order)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem 0;border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-size:0.85rem;font-weight:600;color:#3B82F6;">{{ $order->order_number }}</div>
                        <div style="font-size:0.75rem;color:#64748B;">{{ $order->created_at->format('M d, Y') }} · {{ $order->items->count() }} item(s)</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:0.875rem;font-weight:700;color:#F1F5F9;">${{ number_format($order->total, 2) }}</div>
                        @php $colors = ['pending'=>'yellow','processing'=>'blue','shipped'=>'purple','delivered'=>'green','cancelled'=>'red']; @endphp
                        <span class="badge badge-{{ $colors[$order->status] ?? 'gray' }}" style="font-size:0.65rem;">{{ ucfirst($order->status) }}</span>
                    </div>
                </div>
                @empty
                <p style="color:#64748B;font-size:0.875rem;text-align:center;padding:1.5rem 0;">No orders yet. <a href="{{ route('products.index') }}" style="color:#3B82F6;">Start shopping!</a></p>
                @endforelse
            </div>

            {{-- AI Recommendations --}}
            @if($recommendations->count() > 0)
            <div>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1.25rem;">
                    <span>🤖</span>
                    <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;">Recommended For You</h3>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
                    @foreach($recommendations as $p)
                        <x-product-card :product="$p" />
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
