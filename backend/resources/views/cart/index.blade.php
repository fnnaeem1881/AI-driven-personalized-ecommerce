@extends('layouts.app')
@section('title', 'Shopping Cart')

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:2rem 1rem;">
    <h1 style="font-size:2rem;font-weight:800;color:#F1F5F9;margin-bottom:2rem;">🛒 Shopping Cart
        <span style="font-size:1rem;font-weight:400;color:#64748B;margin-left:0.75rem;">{{ \Cart::getTotalQuantity() }} item(s)</span>
    </h1>

    @if($showDiscount)
    <div style="background:linear-gradient(135deg,rgba(139,92,246,0.1),rgba(59,130,246,0.1));border:1px solid rgba(139,92,246,0.3);border-radius:16px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;">
        <span style="font-size:2rem;">🎁</span>
        <div>
            <h4 style="font-size:0.9rem;font-weight:700;color:#A78BFA;">Special Offer Just For You!</h4>
            <p style="font-size:0.8rem;color:#64748B;">Use code <strong style="color:#3B82F6;">SAVE10</strong> at checkout for an extra 10% off your order!</p>
        </div>
        <span style="margin-left:auto;background:linear-gradient(135deg,#8B5CF6,#3B82F6);padding:0.375rem 1rem;border-radius:8px;color:white;font-size:0.8rem;font-weight:700;cursor:pointer;" onclick="navigator.clipboard.writeText('SAVE10');this.textContent='Copied!'">Copy Code</span>
    </div>
    @endif

    @if($cartItems->isEmpty())
    <div style="text-align:center;padding:5rem 2rem;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;">
        <div style="font-size:5rem;margin-bottom:1rem;">🛒</div>
        <h2 style="font-size:1.5rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">Your cart is empty</h2>
        <p style="color:#64748B;margin-bottom:2rem;">Add some awesome tech products to get started!</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Start Shopping</a>
    </div>
    @else
    <div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start;">

        {{-- Cart Items --}}
        <div style="display:flex;flex-direction:column;gap:0.875rem;">
            @foreach($cartItems as $item)
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:1.25rem;display:flex;gap:1.25rem;align-items:center;">
                <img src="{{ $item->attributes->image ?? 'https://images.unsplash.com/photo-1625948515-b3e10b35b68c?w=150' }}"
                     alt="{{ $item->name }}" style="width:90px;height:90px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                <div style="flex:1;">
                    <div style="font-size:0.7rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">{{ $item->attributes->brand ?? '' }}</div>
                    <h3 style="font-size:0.9rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">{{ $item->name }}</h3>
                    <span style="font-size:1rem;font-weight:800;color:#3B82F6;">${{ number_format($item->price, 2) }}</span>
                </div>

                {{-- Quantity --}}
                <div style="display:flex;align-items:center;background:var(--bg-elevated);border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                    <form method="POST" action="{{ route('cart.update') }}">
                        @csrf
                        <input type="hidden" name="rowId" value="{{ $item->rowId }}">
                        <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                        <button style="background:none;border:none;color:#F1F5F9;padding:0.5rem 0.75rem;cursor:pointer;font-size:1rem;">−</button>
                    </form>
                    <span style="padding:0.5rem 0.75rem;color:#F1F5F9;font-weight:700;min-width:36px;text-align:center;">{{ $item->quantity }}</span>
                    <form method="POST" action="{{ route('cart.update') }}">
                        @csrf
                        <input type="hidden" name="rowId" value="{{ $item->rowId }}">
                        <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                        <button style="background:none;border:none;color:#F1F5F9;padding:0.5rem 0.75rem;cursor:pointer;font-size:1rem;">+</button>
                    </form>
                </div>

                <div style="text-align:right;min-width:80px;">
                    <div style="font-size:1.1rem;font-weight:800;color:#F1F5F9;">${{ number_format($item->price * $item->quantity, 2) }}</div>
                    <form method="POST" action="{{ route('cart.remove') }}" style="margin-top:0.5rem;">
                        @csrf
                        <input type="hidden" name="rowId" value="{{ $item->rowId }}">
                        <button type="submit" style="background:none;border:none;color:#EF4444;font-size:0.75rem;cursor:pointer;padding:0;" onmouseover="this.style.color='#F87171'" onmouseout="this.style.color='#EF4444'">🗑 Remove</button>
                    </form>
                </div>
            </div>
            @endforeach

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:0.5rem;">
                <a href="{{ route('products.index') }}" class="btn-ghost">← Continue Shopping</a>
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button type="submit" class="btn-ghost" style="color:#EF4444;border-color:rgba(239,68,68,0.3);">🗑 Clear Cart</button>
                </form>
            </div>
        </div>

        {{-- Order Summary --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.5rem;position:sticky;top:80px;">
            <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">Order Summary</h3>

            <div style="display:flex;flex-direction:column;gap:0.75rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
                <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
                    <span style="color:#64748B;">Subtotal</span><span style="color:#F1F5F9;font-weight:600;">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
                    <span style="color:#64748B;">Shipping</span>
                    <span style="color:{{ $subtotal >= 50 ? '#10B981' : '#F1F5F9' }};font-weight:600;">
                        {{ $subtotal >= 50 ? '🎉 FREE' : '$9.99' }}
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.875rem;">
                    <span style="color:#64748B;">Tax (8%)</span>
                    <span style="color:#F1F5F9;font-weight:600;">${{ number_format($subtotal * 0.08, 2) }}</span>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;padding:1rem 0;border-bottom:1px solid var(--border);">
                <span style="font-weight:700;color:#F1F5F9;">Total</span>
                <span style="font-size:1.5rem;font-weight:900;background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    ${{ number_format($subtotal + ($subtotal >= 50 ? 0 : 9.99) + ($subtotal * 0.08), 2) }}
                </span>
            </div>

            @if($subtotal < 50)
            <div style="background:rgba(59,130,246,0.08);border-radius:10px;padding:0.875rem;margin:0.875rem 0;font-size:0.8rem;color:#60A5FA;border:1px solid rgba(59,130,246,0.2);">
                🚚 Add <strong>${{ number_format(50 - $subtotal, 2) }}</strong> more for FREE shipping!
            </div>
            @endif

            <a href="{{ route('checkout.index') }}" class="btn-primary" style="width:100%;justify-content:center;margin-top:1rem;font-size:0.95rem;padding:0.875rem;">
                🔒 Proceed to Checkout
            </a>
            <div style="text-align:center;margin-top:0.875rem;">
                <span style="font-size:0.75rem;color:#64748B;">Secure checkout with 256-bit SSL</span>
            </div>
        </div>
    </div>

    {{-- Upsells --}}
    @if($upsells->count() > 0)
    <div style="margin-top:3rem;">
        <h2 class="section-title" style="margin-bottom:1.25rem;">💡 You Might Also Like</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.25rem;">
            @foreach($upsells as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
