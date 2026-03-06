@props(['product'])
<div class="product-card">
    {{-- Image --}}
    <div class="product-img-wrap" style="height:200px;">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $product->image ?? 'https://images.unsplash.com/photo-1625948515-b3e10b35b68c?w=400' }}"
                 alt="{{ $product->name }}" loading="lazy">
        </a>

        {{-- Badges --}}
        <div style="position:absolute;top:10px;left:10px;display:flex;flex-direction:column;gap:4px;">
            @if($product->is_flash_deal)
                <span class="badge badge-red">⚡ Flash Deal</span>
            @elseif($product->discount_percent > 0)
                <span class="badge badge-red">-{{ $product->flash_deal_discount ?? $product->discount_percent }}%</span>
            @endif
            @if($product->is_featured)
                <span class="badge badge-purple">⭐ Featured</span>
            @endif
            @if($product->stock <= 5 && $product->stock > 0)
                <span class="badge badge-yellow">Only {{ $product->stock }} left</span>
            @elseif($product->stock == 0)
                <span class="badge badge-red">Out of Stock</span>
            @endif
        </div>

        {{-- Wishlist btn --}}
        <button data-wishlist="{{ $product->id }}" class="card-wish-btn">
            <span class="wish-icon">🤍</span>
        </button>

        {{-- Overlay add to cart --}}
        <div class="overlay-actions">
            <button data-add-cart="{{ $product->id }}" data-qty="1" class="card-cart-btn">
                🛒 Add to Cart
            </button>
            <a href="{{ route('products.show', $product->slug) }}" class="card-view-btn">👁</a>
        </div>
    </div>

    {{-- Info --}}
    <div class="pc-info">
        <div class="pc-brand">{{ $product->brand }}</div>

        <a href="{{ route('products.show', $product->slug) }}" class="pc-name-link">
            <h3 class="pc-name">{{ $product->name }}</h3>
        </a>

        <div class="pc-stars">
            <div class="stars" style="font-size:0.75rem;">
                @for($i = 1; $i <= 5; $i++)
                    {{ $i <= round($product->rating) ? '★' : '☆' }}
                @endfor
            </div>
            <span class="pc-reviews">({{ $product->reviews_count }})</span>
        </div>

        <div class="pc-price-row">
            <span class="pc-price">{{ format_currency($product->price) }}</span>
            @if($product->compare_price)
                <span class="pc-compare">{{ format_currency($product->compare_price) }}</span>
            @endif
        </div>
    </div>
</div>

<style>
.card-wish-btn {
    position: absolute; top: 10px; right: 10px;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(13,21,38,0.8);
    border: 1px solid rgba(59,130,246,0.2);
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; font-size: 0.9rem; transition: all 0.2s;
}
.card-wish-btn:hover { background: rgba(59,130,246,0.2); }
[data-theme="light"] .card-wish-btn { background: rgba(255,255,255,0.9); border-color: #E2E8F0; }

.card-cart-btn {
    flex: 1;
    background: linear-gradient(135deg,#3B82F6,#8B5CF6);
    border: none; border-radius: 8px; padding: 0.5rem;
    color: white; font-size: 0.8rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
}
.card-view-btn {
    width: 36px; height: 36px;
    background: rgba(59,130,246,0.2);
    border: 1px solid rgba(59,130,246,0.3);
    border-radius: 8px; display: flex;
    align-items: center; justify-content: center;
    color: #3B82F6; text-decoration: none; font-size: 0.875rem;
}

/* Product card info */
.pc-info { padding: 1rem; }
.pc-brand {
    font-size: 0.7rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.pc-name-link { text-decoration: none; }
.pc-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.4;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.pc-stars { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.625rem; }
.pc-reviews { font-size: 0.7rem; color: var(--text-muted); }
.pc-price-row { display: flex; align-items: center; gap: 0.625rem; }
.pc-price { font-size: 1.1rem; font-weight: 800; color: var(--primary); }
.pc-compare { font-size: 0.8rem; color: var(--text-muted); text-decoration: line-through; }

/* Light theme overrides */
[data-theme="light"] .pc-name { color: #0F172A; }
[data-theme="light"] .pc-brand { color: #64748B; }
[data-theme="light"] .pc-reviews { color: #94A3B8; }
</style>
