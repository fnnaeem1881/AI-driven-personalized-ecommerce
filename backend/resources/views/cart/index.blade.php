@extends('layouts.app')
@section('title', 'Shopping Cart')

@section('content')
<div class="cart-page">
    <h1 class="cart-title">🛒 Shopping Cart
        <span class="cart-count-label">{{ \Cart::getTotalQuantity() }} item(s)</span>
    </h1>

    @if($showDiscount)
    <div class="discount-banner">
        <span class="discount-icon">🎁</span>
        <div>
            <h4 class="discount-heading">Special Offer Just For You!</h4>
            <p class="discount-body">Use code <strong class="code-highlight">SAVE10</strong> at checkout for an extra 10% off your order!</p>
        </div>
        <button class="copy-code-btn" onclick="navigator.clipboard.writeText('SAVE10');this.textContent='Copied! ✓'">Copy Code</button>
    </div>
    @endif

    @if($cartItems->isEmpty())
    <div class="empty-cart">
        <div class="empty-cart-icon">🛒</div>
        <h2 class="empty-cart-title">Your cart is empty</h2>
        <p class="empty-cart-body">Add some awesome tech products to get started!</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Start Shopping</a>
    </div>
    @else
    <div class="cart-layout">

        {{-- Cart Items --}}
        <div class="cart-items-col">
            @foreach($cartItems as $item)
            <div class="cart-item">
                <img src="{{ $item->attributes->image ?? 'https://images.unsplash.com/photo-1625948515-b3e10b35b68c?w=150' }}"
                     alt="{{ $item->name }}" class="cart-item-img">
                <div class="cart-item-info">
                    <div class="cart-item-brand">{{ $item->attributes->brand ?? '' }}</div>
                    <h3 class="cart-item-name">{{ $item->name }}</h3>
                    <span class="cart-item-price">{{ format_currency($item->price) }}</span>
                </div>

                {{-- Quantity Controls (AJAX) --}}
                <div class="qty-control">
                    <button type="button" class="qty-btn"
                            onclick="cartQty('{{ $item->id }}', {{ max(1, $item->quantity - 1) }}, this)">−</button>
                    <span class="qty-value" id="qv-{{ $item->id }}">{{ $item->quantity }}</span>
                    <button type="button" class="qty-btn"
                            onclick="cartQty('{{ $item->id }}', {{ $item->quantity + 1 }}, this)">+</button>
                </div>

                <div class="cart-item-total-col">
                    <div class="cart-item-total">{{ format_currency($item->price * $item->quantity) }}</div>
                    <form method="POST" action="{{ route('cart.remove') }}" style="margin-top:0.5rem;">
                        @csrf
                        <input type="hidden" name="rowId" value="{{ $item->id }}">
                        <button type="submit" class="remove-btn">🗑 Remove</button>
                    </form>
                </div>
            </div>
            @endforeach

            <div class="cart-actions">
                <a href="{{ route('products.index') }}" class="btn-ghost">← Continue Shopping</a>
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button type="submit" class="btn-ghost clear-btn">🗑 Clear Cart</button>
                </form>
            </div>
        </div>

        {{-- Order Summary --}}
        <div class="order-summary">
            <h3 class="summary-title">Order Summary</h3>

            @php
                $freeShipThreshold = (float) store_setting('free_shipping_threshold', 1000);
                $shipCost = (float) store_setting('shipping_cost', 60);
                $tax = $subtotal * 0.08;
                $shipping = $subtotal >= $freeShipThreshold ? 0 : $shipCost;
                $total = $subtotal + $shipping + $tax;
            @endphp

            <div class="summary-lines">
                <div class="summary-line">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">{{ format_currency($subtotal) }}</span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value {{ $subtotal >= $freeShipThreshold ? 'text-success' : '' }}">
                        {{ $subtotal >= $freeShipThreshold ? '🎉 FREE' : format_currency($shipCost) }}
                    </span>
                </div>
                <div class="summary-line">
                    <span class="summary-label">Tax (8%)</span>
                    <span class="summary-value">{{ format_currency($tax) }}</span>
                </div>
            </div>

            <div class="summary-total-row">
                <span class="summary-total-label">Total</span>
                <span class="summary-total-value gradient-text">{{ format_currency($total) }}</span>
            </div>

            @if($subtotal < $freeShipThreshold)
            <div class="free-ship-nudge">
                🚚 Add <strong>{{ format_currency($freeShipThreshold - $subtotal) }}</strong> more for FREE shipping!
            </div>
            @endif

            <a href="{{ route('checkout.index') }}" class="btn-primary checkout-btn">
                🔒 Proceed to Checkout
            </a>
            <p class="secure-note">Secure checkout with 256-bit SSL</p>
        </div>
    </div>

    {{-- Upsells --}}
    @if($upsells->count() > 0)
    <div class="upsells-section">
        <h2 class="section-title" style="margin-bottom:1.25rem;">💡 You Might Also Like</h2>
        <div class="upsells-grid">
            @foreach($upsells as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </div>
    @endif
    @endif
</div>

@push('styles')
<style>
/* ── Cart Page ─────────────────────────────────────── */
.cart-page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
.cart-title {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 2rem;
}
.cart-count-label {
  font-size: 1rem;
  font-weight: 400;
  color: var(--text-muted);
  margin-left: 0.75rem;
}

/* Discount banner */
.discount-banner {
  background: linear-gradient(135deg,rgba(139,92,246,0.1),rgba(59,130,246,0.1));
  border: 1px solid rgba(139,92,246,0.3);
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}
.discount-icon { font-size: 2rem; }
.discount-heading { font-size: 0.9rem; font-weight: 700; color: #A78BFA; }
.discount-body { font-size: 0.8rem; color: var(--text-muted); }
.code-highlight { color: var(--primary); }
.copy-code-btn {
  margin-left: auto;
  background: linear-gradient(135deg,#8B5CF6,#3B82F6);
  padding: 0.375rem 1rem;
  border-radius: 8px;
  color: white;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  border: none;
}

/* Empty cart */
.empty-cart {
  text-align: center;
  padding: 5rem 2rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 20px;
}
.empty-cart-icon { font-size: 5rem; margin-bottom: 1rem; }
.empty-cart-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
.empty-cart-body { color: var(--text-muted); margin-bottom: 2rem; }

/* Cart layout */
.cart-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1.5rem;
  align-items: start;
}
@media (max-width: 900px) {
  .cart-layout { grid-template-columns: 1fr; }
}

/* Cart items column */
.cart-items-col { display: flex; flex-direction: column; gap: 0.875rem; }

/* Cart item card */
.cart-item {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 1.25rem;
  display: flex;
  gap: 1.25rem;
  align-items: center;
  flex-wrap: wrap;
}
.cart-item-img {
  width: 90px;
  height: 90px;
  object-fit: cover;
  border-radius: 10px;
  border: 1px solid var(--border);
  flex-shrink: 0;
}
.cart-item-info { flex: 1; min-width: 140px; }
.cart-item-brand {
  font-size: 0.7rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 0.25rem;
}
.cart-item-name {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}
.cart-item-price { font-size: 1rem; font-weight: 800; color: var(--primary); }

/* Quantity control */
.qty-control {
  display: flex;
  align-items: center;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
}
.qty-btn {
  background: none;
  border: none;
  color: var(--text-primary);
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
}
.qty-btn:hover { color: var(--primary); }
.qty-value {
  padding: 0.5rem 0.75rem;
  color: var(--text-primary);
  font-weight: 700;
  min-width: 36px;
  text-align: center;
}

/* Item total column */
.cart-item-total-col { text-align: right; min-width: 80px; }
.cart-item-total { font-size: 1.1rem; font-weight: 800; color: var(--text-primary); }
.remove-btn {
  background: none;
  border: none;
  color: var(--danger);
  font-size: 0.75rem;
  cursor: pointer;
  padding: 0;
  margin-top: 0.5rem;
}
.remove-btn:hover { opacity: 0.8; }

/* Cart bottom actions */
.cart-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; flex-wrap: wrap; }
.clear-btn { color: var(--danger) !important; border-color: rgba(239,68,68,0.3) !important; }

/* Order Summary */
.order-summary {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 1.5rem;
  position: sticky;
  top: 80px;
}
.summary-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.25rem; }
.summary-lines { display: flex; flex-direction: column; gap: 0.75rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
.summary-line { display: flex; justify-content: space-between; font-size: 0.875rem; }
.summary-label { color: var(--text-muted); }
.summary-value { color: var(--text-primary); font-weight: 600; }
.text-success { color: var(--success) !important; }

.summary-total-row {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  border-bottom: 1px solid var(--border);
}
.summary-total-label { font-weight: 700; color: var(--text-primary); }
.summary-total-value {
  font-size: 1.5rem;
  font-weight: 900;
}
.gradient-text {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.free-ship-nudge {
  background: rgba(59,130,246,0.08);
  border-radius: 10px;
  padding: 0.875rem;
  margin: 0.875rem 0;
  font-size: 0.8rem;
  color: #60A5FA;
  border: 1px solid rgba(59,130,246,0.2);
}
.checkout-btn {
  width: 100%;
  justify-content: center;
  margin-top: 1rem;
  font-size: 0.95rem;
  padding: 0.875rem;
}
.secure-note { text-align: center; margin-top: 0.875rem; font-size: 0.75rem; color: var(--text-muted); }

/* Upsells */
.upsells-section { margin-top: 3rem; }
.upsells-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(200px,1fr)); gap: 1.25rem; }

/* Light theme overrides for cart */
[data-theme="light"] .free-ship-nudge { color: #1D4ED8; background: rgba(59,130,246,0.06); }
[data-theme="light"] .discount-heading { color: #7C3AED; }
</style>
@endpush

@push('scripts')
<script>
const CART_UPDATE_URL = '{{ route("cart.update") }}';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

function cartQty(itemId, newQty, btn) {
    if (newQty < 1) return;
    // Optimistic update
    const display = document.getElementById('qv-' + itemId);
    if (display) display.textContent = newQty;
    btn.disabled = true;

    fetch(CART_UPDATE_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: 'rowId=' + encodeURIComponent(itemId) + '&quantity=' + newQty
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            const badge = document.querySelector('.cart-badge');
            if (badge && data.cart_count !== undefined) badge.textContent = data.cart_count;
            window.location.reload();
        } else {
            btn.disabled = false;
        }
    })
    .catch(() => { btn.disabled = false; window.location.reload(); });
}
</script>
@endpush
@endsection
