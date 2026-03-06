@extends('layouts.app')
@section('title', '⚡ Flash Deals — ' . store_setting('store_name', 'TechNova'))

@section('content')

{{-- ═══ FLASH DEALS HERO BANNER ═══ --}}
<section style="background:linear-gradient(135deg,rgba(239,68,68,0.12) 0%,rgba(251,146,60,0.08) 50%,rgba(6,11,20,0) 100%);border-bottom:1px solid rgba(239,68,68,0.15);padding:2.5rem 1rem 2rem;">
    <div class="max-w-7xl mx-auto">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
            <div>
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;">
                    <span style="font-size:2rem;animation:pulse 1.5s infinite;">⚡</span>
                    <h1 style="font-size:2rem;font-weight:900;color:#F1F5F9;">Flash Deals</h1>
                </div>
                <p style="color:#94A3B8;font-size:0.95rem;max-width:480px;">
                    Limited-time offers at unbeatable prices. Don't miss out — these deals expire soon!
                </p>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-top:1rem;">
                    <span style="font-size:0.8rem;color:#F87171;font-weight:600;">⏰ All deals end:</span>
                    <div style="display:flex;gap:0.375rem;align-items:center;" id="fd-countdown" data-end="{{ $closestEnd->timestamp }}">
                        <div class="countdown-box"><div class="num" id="fd-h">00</div><div class="label">hrs</div></div>
                        <span style="color:#F87171;font-weight:900;">:</span>
                        <div class="countdown-box"><div class="num" id="fd-m">00</div><div class="label">min</div></div>
                        <span style="color:#F87171;font-weight:900;">:</span>
                        <div class="countdown-box"><div class="num" id="fd-s">00</div><div class="label">sec</div></div>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                <a href="{{ route('products.index') }}" class="btn-outline" style="font-size:0.85rem;">All Products</a>
                <a href="{{ route('home') }}" class="btn-ghost" style="font-size:0.85rem;">← Back to Home</a>
            </div>
        </div>
    </div>
</section>

{{-- ═══ ACTIVE DEALS ═══ --}}
<div class="max-w-7xl mx-auto" style="padding:2rem 1rem;">

@if($activeDeals->isNotEmpty())
    @foreach($activeDeals as $deal)
    <div style="margin-bottom:3rem;">
        {{-- Deal Header --}}
        <div class="flash-sale-box" style="margin-bottom:1.5rem;border-radius:16px;">
            <div class="flash-sale-header">
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,rgba(239,68,68,0.2),rgba(251,146,60,0.2));display:flex;align-items:center;justify-content:center;font-size:1.5rem;border:1px solid rgba(239,68,68,0.25);">⚡</div>
                    <div>
                        <h2 class="flash-sale-title" style="font-size:1.25rem;">{{ $deal->title }}</h2>
                        <p class="flash-sale-sub">
                            @if($deal->description){{ $deal->description }}@else Up to <span style="color:#F87171;font-weight:700;">{{ $deal->discount_percent }}% OFF</span> — ends {{ $deal->ends_at->diffForHumans() }}@endif
                        </p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    <div style="text-align:center;padding:0.5rem 1rem;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);border-radius:12px;">
                        <div style="font-size:1.5rem;font-weight:900;color:#F87171;">{{ $deal->discount_percent }}%</div>
                        <div style="font-size:0.7rem;color:#94A3B8;text-transform:uppercase;letter-spacing:0.08em;">OFF</div>
                    </div>
                    <div style="font-size:0.78rem;color:#64748B;">
                        Ends {{ $deal->ends_at->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Grid --}}
        @if($deal->products->isNotEmpty())
        <div class="products-grid">
            @foreach($deal->products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:2rem;color:#64748B;background:rgba(15,23,42,0.4);border-radius:12px;border:1px dashed rgba(59,130,246,0.15);">
            <p>No products assigned to this deal yet.</p>
        </div>
        @endif
    </div>
    @endforeach

@elseif($fallbackProducts->isNotEmpty())
    {{-- Fallback: products with is_flash_deal flag --}}
    <div style="margin-bottom:3rem;">
        <div class="flash-sale-box" style="margin-bottom:1.5rem;border-radius:16px;">
            <div class="flash-sale-header">
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="font-size:1.5rem;">⚡</div>
                    <div>
                        <h2 class="flash-sale-title">Limited Time Offers</h2>
                        <p class="flash-sale-sub">Grab these deals before they're gone!</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="products-grid">
            @foreach($fallbackProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>

@else
    {{-- No active deals --}}
    <div style="text-align:center;padding:5rem 2rem;">
        <div style="font-size:4rem;margin-bottom:1rem;">⚡</div>
        <h2 style="font-size:1.5rem;font-weight:700;color:#F1F5F9;margin-bottom:0.75rem;">No Active Flash Deals Right Now</h2>
        <p style="color:#64748B;margin-bottom:2rem;max-width:400px;margin-left:auto;margin-right:auto;">
            Check back soon! Our flash deals change frequently. Browse all products in the meantime.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('products.index') }}" class="btn-primary">Browse All Products</a>
            <a href="{{ route('home') }}" class="btn-outline">Back to Home</a>
        </div>
    </div>
@endif

</div>

@endsection

@push('scripts')
<script>
(function(){
    const el = document.getElementById('fd-countdown');
    if (!el) return;
    const end = parseInt(el.dataset.end) * 1000;
    function tick() {
        const diff = end - Date.now();
        if (diff <= 0) { el.style.display = 'none'; return; }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        document.getElementById('fd-h').textContent = String(h).padStart(2,'0');
        document.getElementById('fd-m').textContent = String(m).padStart(2,'0');
        document.getElementById('fd-s').textContent = String(s).padStart(2,'0');
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
