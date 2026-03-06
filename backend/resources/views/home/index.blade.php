@extends('layouts.app')
@section('title', store_setting('store_name', 'TechNova') . ' — AI-Powered Electronics')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     HERO SECTION — Category Sidebar + Hero Slider
════════════════════════════════════════════════════════════ --}}
<section class="hero-section">
    <div class="hero-grid">

        {{-- LEFT: Category Sidebar --}}
        <div class="cat-sidebar hidden lg:block">
            <div style="padding:0.875rem 1rem;border-bottom:1px solid rgba(59,130,246,0.12);">
                <h3 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#64748B;">All Categories</h3>
            </div>
            @foreach($categories as $cat)
            <a href="{{ route('category.show', $cat->slug) }}" class="cat-item">
                <span class="cat-icon">{{ $cat->icon }}</span>
                <span>{{ $cat->name }}</span>
                <span class="cat-arrow" style="color:#3B82F6;font-size:0.75rem;">›</span>
            </a>
            @endforeach
            <div style="padding:0.75rem 1rem;border-top:1px solid rgba(59,130,246,0.12);">
                <a href="{{ route('products.index') }}" class="btn-primary" style="width:100%;justify-content:center;font-size:0.8rem;padding:0.5rem;">
                    <span>View All Products</span>
                </a>
            </div>
        </div>

        {{-- RIGHT: Hero Slider --}}
        <div style="min-width:0;">
            <style>
            .hs-wrap{border-radius:16px;overflow:hidden;position:relative;}
            .hs-inner{position:relative;height:420px;overflow:hidden;}
            .hs-bg{position:absolute;inset:0;background-size:cover;background-position:center;transform:scale(1.08);transition:transform 8s ease-out;}
            .swiper-slide-active .hs-bg{transform:scale(1);}
            .hs-overlay{position:absolute;inset:0;background:linear-gradient(110deg,rgba(6,11,20,0.92) 0%,rgba(6,11,20,0.6) 50%,rgba(6,11,20,0.18) 100%);}
            .hs-content{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:2.5rem 2rem;}
            .hs-badge,.hs-title,.hs-desc,.hs-actions{opacity:0;transform:translateY(24px);}
            .swiper-slide-active .hs-badge{animation:hsUp 0.55s cubic-bezier(.22,.68,0,1.2) 0.08s forwards;}
            .swiper-slide-active .hs-title{animation:hsUp 0.55s cubic-bezier(.22,.68,0,1.2) 0.26s forwards;}
            .swiper-slide-active .hs-desc{animation:hsUp 0.5s cubic-bezier(.22,.68,0,1.2) 0.42s forwards;}
            .swiper-slide-active .hs-actions{animation:hsUp 0.5s cubic-bezier(.22,.68,0,1.2) 0.56s forwards;}
            @keyframes hsUp{to{opacity:1;transform:none;}}
            .hs-controls{display:flex;align-items:center;gap:0.625rem;padding:0.625rem 1rem;background:rgba(13,21,38,0.75);backdrop-filter:blur(12px);}
            .hs-arrow{width:34px;height:34px;border-radius:50%;border:1px solid rgba(59,130,246,0.3);background:rgba(59,130,246,0.08);color:#94A3B8;font-size:1.1rem;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0;}
            .hs-arrow:hover{background:rgba(59,130,246,0.25);color:#F1F5F9;border-color:#3B82F6;box-shadow:0 0 12px rgba(59,130,246,0.3);}
            .hs-dots-wrap{flex:1;display:flex;justify-content:center;}
            .hs-dots .swiper-pagination-bullet{background:rgba(148,163,184,0.35);opacity:1;transition:all 0.35s;width:8px;height:8px;}
            .hs-dots .swiper-pagination-bullet-active{background:#3B82F6;width:22px;border-radius:4px;box-shadow:0 0 8px rgba(59,130,246,0.5);}
            .hs-counter{font-size:0.72rem;color:#64748B;font-weight:700;letter-spacing:0.05em;white-space:nowrap;}
            .hs-progress{height:3px;background:rgba(59,130,246,0.12);}
            .hs-fill{height:3px;background:linear-gradient(90deg,#3B82F6,#8B5CF6);width:0%;transition:none;}
            </style>

            <div class="hs-wrap">
                <div class="swiper hero-swiper">
                    <div class="swiper-wrapper">
                        @if($heroSlides->count() > 0)
                            @foreach($heroSlides as $slide)
                            <div class="swiper-slide">
                                <div class="hs-inner">
                                    <div class="hs-bg" style="background-image:url('{{ $slide->image ?: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=1200' }}');"></div>
                                    <div class="hs-overlay"></div>
                                    <div class="hs-content">
                                        @if($slide->badge)
                                        <div class="hs-badge" style="margin-bottom:0.875rem;">
                                            <span class="badge {{ $slide->badge_color ?? 'badge-blue' }}">{{ $slide->badge }}</span>
                                        </div>
                                        @endif
                                        <h1 class="hs-title" style="font-size:2.4rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">
                                            {{ $slide->title }}
                                            @if($slide->subtitle)
                                            <br><span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ $slide->subtitle }}</span>
                                            @endif
                                        </h1>
                                        @if($slide->description)
                                        <p class="hs-desc" style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;max-width:480px;line-height:1.65;">{{ $slide->description }}</p>
                                        @endif
                                        <div class="hs-actions" style="display:flex;gap:1rem;flex-wrap:wrap;">
                                            <a href="{{ $slide->cta_link }}" class="btn-primary">{{ $slide->cta_text }}</a>
                                            @if($slide->cta_secondary_text)
                                            <a href="{{ $slide->cta_secondary_link ?? route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">{{ $slide->cta_secondary_text }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                        {{-- Fallback slides --}}
                        <div class="swiper-slide">
                            <div class="hs-inner">
                                <div class="hs-bg" style="background-image:url('https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=1200');"></div>
                                <div class="hs-overlay"></div>
                                <div class="hs-content">
                                    <div class="hs-badge" style="margin-bottom:0.875rem;"><span class="badge badge-blue">{{ store_setting('hero_badge', '🔥 New Arrival') }}</span></div>
                                    <h1 class="hs-title" style="font-size:2.4rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">
                                        {{ store_setting('hero_title', 'Next-Gen Tech') }}<br>
                                        <span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ store_setting('hero_subtitle', 'At Your Fingertips') }}</span>
                                    </h1>
                                    <p class="hs-desc" style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;max-width:480px;line-height:1.65;">{{ store_setting('meta_description', 'Explore AI-curated electronics, gadgets & more.') }}</p>
                                    <div class="hs-actions" style="display:flex;gap:1rem;flex-wrap:wrap;">
                                        <a href="{{ route('products.index') }}" class="btn-primary">{{ store_setting('hero_cta', 'Shop Now') }}</a>
                                        <a href="{{ route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">Explore All</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="hs-inner">
                                <div class="hs-bg" style="background-image:url('https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200');"></div>
                                <div class="hs-overlay"></div>
                                <div class="hs-content">
                                    <div class="hs-badge" style="margin-bottom:0.875rem;"><span class="badge badge-purple">📱 Flagship</span></div>
                                    <h1 class="hs-title" style="font-size:2.4rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">
                                        Samsung Galaxy S24 Ultra<br><span style="background:linear-gradient(135deg,#8B5CF6,#06B6D4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">200MP. AI-Powered.</span>
                                    </h1>
                                    <p class="hs-desc" style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;max-width:480px;line-height:1.65;">Built-in S Pen, Snapdragon 8 Gen 3. The most capable Galaxy smartphone ever made.</p>
                                    <div class="hs-actions" style="display:flex;gap:1rem;flex-wrap:wrap;">
                                        <a href="{{ route('category.show', 'smartphones') }}" class="btn-primary">Shop Smartphones</a>
                                        <a href="{{ route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">Learn More</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="hs-inner">
                                <div class="hs-bg" style="background-image:url('https://images.unsplash.com/photo-1607853202273-797f1c22a38e?w=1200');"></div>
                                <div class="hs-overlay"></div>
                                <div class="hs-content">
                                    <div class="hs-badge" style="margin-bottom:0.875rem;"><span class="badge badge-cyan">🎮 Gaming</span></div>
                                    <h1 class="hs-title" style="font-size:2.4rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">
                                        PlayStation 5 Slim<br><span style="background:linear-gradient(135deg,#06B6D4,#3B82F6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Play Has No Limits.</span>
                                    </h1>
                                    <p class="hs-desc" style="color:#94A3B8;font-size:0.95rem;margin-bottom:1.5rem;max-width:480px;line-height:1.65;">Ultra-high speed SSD. Haptic feedback. Adaptive triggers. 4K gaming at 120fps.</p>
                                    <div class="hs-actions" style="display:flex;gap:1rem;flex-wrap:wrap;">
                                        <a href="{{ route('category.show', 'gaming') }}" class="btn-primary">Shop Gaming</a>
                                        <a href="{{ route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">View All</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                {{-- Progress bar --}}
                <div class="hs-progress"><div class="hs-fill" id="hsFill"></div></div>
                {{-- Controls bar --}}
                <div class="hs-controls">
                    <button class="hs-arrow hs-prev" aria-label="Previous slide">‹</button>
                    <div class="hs-dots-wrap"><div class="swiper-pagination hs-dots"></div></div>
                    <span class="hs-counter" id="hsCounter">01 / 01</span>
                    <button class="hs-arrow hs-next" aria-label="Next slide">›</button>
                </div>
            </div>

            {{-- Quick Banner Row --}}
            <div class="quick-banners">
                @foreach([
                    ['icon'=>'🚚','title'=>'Free Shipping','sub'=>'On orders over '.format_currency(store_setting('free_shipping_threshold', 1000))],
                    ['icon'=>'🔄','title'=>'Easy Returns','sub'=>'30-day no-hassle'],
                    ['icon'=>'🔒','title'=>'Secure Pay','sub'=>'256-bit encryption'],
                ] as $item)
                <div class="quick-banner-card">
                    <span style="font-size:1.4rem;">{{ $item['icon'] }}</span>
                    <div>
                        <div class="qb-title">{{ $item['title'] }}</div>
                        <div class="qb-sub">{{ $item['sub'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     FLASH SALE
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="flash-sale-box">
        <div class="flash-sale-header">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="font-size:1.5rem;">⚡</div>
                <div>
                    <h2 class="flash-sale-title">{{ $activeDeal ? $activeDeal->title : 'Flash Sale' }}</h2>
                    <p class="flash-sale-sub">{{ $activeDeal && $activeDeal->description ? $activeDeal->description : 'Limited time deals — grab them before they\'re gone!' }}</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            <a href="{{ route('flash-deals.index') }}" style="font-size:0.8rem;color:#F87171;font-weight:700;text-decoration:none;white-space:nowrap;border:1px solid rgba(248,113,113,0.3);padding:0.35rem 0.875rem;border-radius:20px;transition:all 0.2s;" onmouseover="this.style.background='rgba(248,113,113,0.12)'" onmouseout="this.style.background=''">View All Deals →</a>
            {{-- Countdown --}}
            <div style="display:flex;align-items:center;gap:0.5rem;" id="countdown" data-end="{{ $flashSaleEnds }}">
                <span style="font-size:0.8rem;color:#64748B;margin-right:0.25rem;">Ends in:</span>
                <div class="countdown-box"><div class="num" id="cd-h">00</div><div class="label">hrs</div></div>
                <span style="color:#F87171;font-size:1.25rem;font-weight:900;">:</span>
                <div class="countdown-box"><div class="num" id="cd-m">00</div><div class="label">min</div></div>
                <span style="color:#F87171;font-size:1.25rem;font-weight:900;">:</span>
                <div class="countdown-box"><div class="num" id="cd-s">00</div><div class="label">sec</div></div>
            </div>
            </div>
        </div>

        <div class="swiper flash-swiper">
            <div class="swiper-wrapper">
                @foreach($flashSale as $product)
                <div class="swiper-slide" style="width:220px;">
                    <x-product-card :product="$product" />
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     AI RECOMMENDATIONS
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="section-header">
        <div>
            <div class="section-badge" style="{{ $isPersonalized ? 'background:rgba(139,92,246,0.12);border-color:rgba(139,92,246,0.25);' : '' }}">
                <span>{{ $isPersonalized ? '🧠' : '🔥' }}</span>
                <span style="{{ $isPersonalized ? 'color:#8B5CF6;' : '' }}">
                    {{ $isPersonalized ? 'Personalized for You' : 'Popular Right Now' }}
                </span>
                @if(!$isPersonalized)
                <span style="font-size:0.65rem;color:#06B6D4;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);border-radius:4px;padding:1px 5px;margin-left:4px;">ClickHouse Live</span>
                @endif
            </div>
            <h2 class="section-title">
                {{ $isPersonalized ? 'Recommended For You' : 'Most Popular Products' }}
            </h2>
            @if($isPersonalized)
            <p class="section-sub" style="font-size:0.78rem;color:#64748B;margin-top:2px;">
                🧠 Based on your browsing &amp; purchase history
            </p>
            @else
            <p class="section-sub" style="font-size:0.78rem;color:#64748B;margin-top:2px;">
                📊 Trending across our store — updated in real-time
                @auth
                <span style="color:#F59E0B;"> · Browse more to get personalized picks!</span>
                @endauth
            </p>
            @endif
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div class="products-grid">
        @foreach($recommendations->take(8) as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     CATEGORY SHOWCASE
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="section-header">
        <h2 class="section-title">Shop by Category</h2>
        <a href="{{ route('products.index') }}" class="btn-ghost">View All →</a>
    </div>
    <div class="cat-showcase-grid">
        @foreach($categories->take(8) as $cat)
        <a href="{{ route('category.show', $cat->slug) }}" class="cat-showcase-card">
            <div class="cat-showcase-img-wrap">
                <img src="{{ $cat->image }}" alt="{{ $cat->name }}" class="cat-showcase-img">
                <div class="cat-showcase-overlay"></div>
                <div class="cat-showcase-icon">{{ $cat->icon }}</div>
            </div>
            <div class="cat-showcase-info">
                <h3 class="cat-showcase-name">{{ $cat->name }}</h3>
                <p class="cat-showcase-cta">Shop Now →</p>
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     FEATURED PRODUCTS
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="section-header">
        <div>
            <h2 class="section-title">⭐ Featured Products</h2>
            <p class="section-sub">Handpicked by our team — top quality, best value</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div class="products-grid">
        @foreach($featured as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     TRENDING PRODUCTS (ClickHouse-powered)
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="section-header">
        <div>
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;">
                <h2 class="section-title" style="margin:0;">🔥 Trending Now</h2>
                @if($aiTrendingIsLive)
                <span style="font-size:0.65rem;color:#06B6D4;background:rgba(6,182,212,0.1);border:1px solid rgba(6,182,212,0.2);border-radius:4px;padding:2px 6px;font-weight:600;">📡 Live Data</span>
                @endif
            </div>
            <p class="section-sub">Most viewed &amp; interacted products this week</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div class="swiper trending-swiper">
        <div class="swiper-wrapper">
            @foreach($aiTrending as $product)
            <div class="swiper-slide" style="width:240px;">
                <x-product-card :product="$product" />
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     NEW ARRIVALS
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="section-header">
        <h2 class="section-title">🆕 New Arrivals</h2>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div class="products-grid">
        @foreach($newArrivals->take(8) as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     BRANDS BANNER
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="brands-box">
        <h3 class="brands-label">Trusted Brands</h3>
        <div class="brands-row">
            @foreach(['Apple','Samsung','Sony','Google','Microsoft','ASUS','Dell','Razer','Logitech','Bose'] as $brand)
            <span class="brand-name">{{ $brand }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     NEWSLETTER
════════════════════════════════════════════════════════════ --}}
<section class="section-container">
    <div class="newsletter-box">
        <div class="newsletter-glow newsletter-glow-tr"></div>
        <div class="newsletter-glow newsletter-glow-bl"></div>
        <div style="position:relative;z-index:1;">
            <span style="font-size:2rem;">📧</span>
            <h2 class="newsletter-title">Stay in the Loop</h2>
            <p class="newsletter-sub">Get exclusive deals, new product alerts, and AI-curated picks delivered to your inbox.</p>
            <form class="newsletter-form" onsubmit="this.querySelector('button').textContent='✓ Subscribed!';event.preventDefault();">
                <input type="email" placeholder="your@email.com" class="form-input newsletter-input" required>
                <button type="submit" class="btn-primary" style="white-space:nowrap;">Subscribe</button>
            </form>
            <p class="newsletter-note">No spam, unsubscribe anytime. We respect your privacy.</p>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// Hero Swiper — Ken Burns + staggered text animations
(function(){
    const DELAY = 5000;
    const fill = document.getElementById('hsFill');
    const counter = document.getElementById('hsCounter');
    let realTotal = 1;

    function startFill() {
        if (!fill) return;
        fill.style.transition = 'none';
        fill.style.width = '0%';
        requestAnimationFrame(() => requestAnimationFrame(() => {
            fill.style.transition = 'width ' + DELAY + 'ms linear';
            fill.style.width = '100%';
        }));
    }

    function updateCounter(swiper) {
        if (!counter) return;
        counter.textContent =
            String(swiper.realIndex + 1).padStart(2,'0') + ' / ' +
            String(realTotal).padStart(2,'0');
    }

    const heroSwiper = new Swiper('.hero-swiper', {
        loop: true,
        speed: 750,
        autoplay: { delay: DELAY, disableOnInteraction: false },
        pagination: { el: '.hs-dots', clickable: true },
        navigation: { nextEl: '.hs-next', prevEl: '.hs-prev' },
        on: {
            init(swiper) {
                realTotal = swiper.slides.filter(s => !s.classList.contains('swiper-slide-duplicate')).length;
                startFill();
                updateCounter(swiper);
            },
            slideChangeTransitionStart(swiper) {
                startFill();
                updateCounter(swiper);
            }
        }
    });
})();

// Flash Sale Swiper
new Swiper('.flash-swiper', {
    slidesPerView: 'auto', spaceBetween: 16, freeMode: true,
    grabCursor: true,
});

// Trending Swiper
new Swiper('.trending-swiper', {
    slidesPerView: 'auto', spaceBetween: 20, freeMode: true, grabCursor: true,
});

// Flash Sale Countdown
function updateCountdown() {
    const el = document.getElementById('countdown');
    if (!el) return;
    const end = parseInt(el.dataset.end) * 1000;
    const now = Date.now();
    const diff = end - now;
    if (diff <= 0) return;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    document.getElementById('cd-h').textContent = String(h).padStart(2,'0');
    document.getElementById('cd-m').textContent = String(m).padStart(2,'0');
    document.getElementById('cd-s').textContent = String(s).padStart(2,'0');
}
updateCountdown();
setInterval(updateCountdown, 1000);
</script>
@endpush
