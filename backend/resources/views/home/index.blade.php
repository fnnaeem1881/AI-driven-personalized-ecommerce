@extends('layouts.app')
@section('title', store_setting('store_name', 'TechNova') . ' — AI-Powered Electronics')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     HERO SECTION — Category Sidebar + Hero Slider
════════════════════════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:1.5rem 1rem;">
    <div style="display:grid;grid-template-columns:240px 1fr;gap:1.25rem;align-items:start;">

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
        <div>
            <div class="swiper hero-swiper" style="border-radius:16px;overflow:hidden;">
                <div class="swiper-wrapper">
                    @if($heroSlides->count() > 0)
                        @foreach($heroSlides as $slide)
                        <div class="swiper-slide">
                            <div class="hero-slide" style="min-height:420px;">
                                @if($slide->image)
                                <div class="hero-slide-bg" style="background-image:url('{{ $slide->image }}');"></div>
                                @else
                                <div class="hero-slide-bg" style="background-image:url('https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=1200');"></div>
                                @endif
                                <div class="hero-slide-content">
                                    @if($slide->badge)
                                    <span class="badge {{ $slide->badge_color ?? 'badge-blue' }}" style="margin-bottom:0.75rem;align-self:flex-start;">{{ $slide->badge }}</span>
                                    @endif
                                    <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">
                                        {{ $slide->title }}
                                        @if($slide->subtitle)
                                        <br><span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ $slide->subtitle }}</span>
                                        @endif
                                    </h1>
                                    @if($slide->description)
                                    <p style="color:#94A3B8;font-size:1rem;margin-bottom:1.5rem;max-width:500px;">{{ $slide->description }}</p>
                                    @endif
                                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
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
                    {{-- Fallback slides when no DB slides exist --}}
                    <div class="swiper-slide">
                        <div class="hero-slide" style="min-height:420px;">
                            <div class="hero-slide-bg" style="background-image:url('https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=1200');"></div>
                            <div class="hero-slide-content">
                                <span class="badge badge-blue" style="margin-bottom:0.75rem;align-self:flex-start;">{{ store_setting('hero_badge', '🔥 New Arrival') }}</span>
                                <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">
                                    {{ store_setting('hero_title', 'Next-Gen Tech') }}<br>
                                    <span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ store_setting('hero_subtitle', 'At Your Fingertips') }}</span>
                                </h1>
                                <p style="color:#94A3B8;font-size:1rem;margin-bottom:1.5rem;max-width:500px;">{{ store_setting('meta_description', 'Explore AI-curated electronics, gadgets & more.') }}</p>
                                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                                    <a href="{{ route('products.index') }}" class="btn-primary">{{ store_setting('hero_cta', 'Shop Now') }}</a>
                                    <a href="{{ route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">Explore All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="hero-slide" style="min-height:420px;">
                            <div class="hero-slide-bg" style="background-image:url('https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200');"></div>
                            <div class="hero-slide-content">
                                <span class="badge badge-purple" style="margin-bottom:0.75rem;align-self:flex-start;">📱 Flagship</span>
                                <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">Samsung Galaxy S24 Ultra<br><span style="background:linear-gradient(135deg,#8B5CF6,#06B6D4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">200MP. AI-Powered.</span></h1>
                                <p style="color:#94A3B8;font-size:1rem;margin-bottom:1.5rem;max-width:500px;">Built-in S Pen, Snapdragon 8 Gen 3. The most capable Galaxy smartphone.</p>
                                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                                    <a href="{{ route('category.show', 'smartphones') }}" class="btn-primary">Shop Smartphones</a>
                                    <a href="{{ route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">Learn More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="hero-slide" style="min-height:420px;">
                            <div class="hero-slide-bg" style="background-image:url('https://images.unsplash.com/photo-1607853202273-797f1c22a38e?w=1200');"></div>
                            <div class="hero-slide-content">
                                <span class="badge badge-cyan" style="margin-bottom:0.75rem;align-self:flex-start;">🎮 Gaming</span>
                                <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;line-height:1.1;margin-bottom:0.75rem;">PlayStation 5 Slim<br><span style="background:linear-gradient(135deg,#06B6D4,#3B82F6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Play Has No Limits.</span></h1>
                                <p style="color:#94A3B8;font-size:1rem;margin-bottom:1.5rem;max-width:500px;">Ultra-high speed SSD. Haptic feedback. Adaptive triggers. 4K gaming at 120fps.</p>
                                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                                    <a href="{{ route('category.show', 'gaming') }}" class="btn-primary">Shop Gaming</a>
                                    <a href="{{ route('products.index') }}" class="btn-outline" style="color:#94A3B8;border-color:rgba(148,163,184,0.3);">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="swiper-pagination" style="bottom:16px;"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>

            {{-- Quick Banner Row --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-top:0.75rem;">
                @foreach([
                    ['icon'=>'🚚','title'=>'Free Shipping','sub'=>'On orders over '.format_currency(store_setting('free_shipping_threshold', 1000)),'color'=>'#3B82F6'],
                    ['icon'=>'🔄','title'=>'Easy Returns','sub'=>'30-day no-hassle','color'=>'#8B5CF6'],
                    ['icon'=>'🔒','title'=>'Secure Pay','sub'=>'256-bit encryption','color'=>'#06B6D4'],
                ] as $item)
                <div style="background:rgba(13,21,38,0.8);border:1px solid rgba(59,130,246,0.12);border-radius:12px;padding:0.875rem;display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size:1.4rem;">{{ $item['icon'] }}</span>
                    <div>
                        <div style="font-size:0.8rem;font-weight:700;color:#F1F5F9;">{{ $item['title'] }}</div>
                        <div style="font-size:0.7rem;color:#64748B;">{{ $item['sub'] }}</div>
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
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="background:linear-gradient(135deg,rgba(239,68,68,0.08),rgba(245,158,11,0.08));border:1px solid rgba(239,68,68,0.2);border-radius:20px;padding:1.5rem 2rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="font-size:1.5rem;">⚡</div>
                <div>
                    <h2 style="font-size:1.5rem;font-weight:900;color:#F1F5F9;">Flash Sale</h2>
                    <p style="font-size:0.8rem;color:#64748B;">Limited time deals — grab them before they're gone!</p>
                </div>
            </div>
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
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.25rem;">
                <span style="font-size:1rem;">🤖</span>
                <span style="font-size:0.75rem;font-weight:600;color:#3B82F6;text-transform:uppercase;letter-spacing:0.08em;">AI Powered</span>
            </div>
            <h2 class="section-title">Recommended For You</h2>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
        @foreach($recommendations->take(8) as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     CATEGORY SHOWCASE
════════════════════════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <h2 class="section-title">Shop by Category</h2>
        <a href="{{ route('products.index') }}" class="btn-ghost">View All →</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;">
        @foreach($categories->take(8) as $cat)
        <a href="{{ route('category.show', $cat->slug) }}"
           style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;text-decoration:none;transition:all 0.3s;display:block;aspect-ratio:1;"
           onmouseover="this.style.borderColor='rgba(59,130,246,0.4)';this.style.transform='translateY(-4px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform=''">
            <div style="position:relative;height:70%;overflow:hidden;">
                <img src="{{ $cat->image }}" alt="{{ $cat->name }}" style="width:100%;height:100%;object-fit:cover;filter:brightness(0.5);transition:transform 0.5s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(6,11,20,1) 0%,transparent 60%);">
                </div>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:2.5rem;">{{ $cat->icon }}</div>
            </div>
            <div style="padding:0.875rem;border-top:1px solid var(--border);">
                <h3 style="font-size:0.9rem;font-weight:700;color:#F1F5F9;">{{ $cat->name }}</h3>
                <p style="font-size:0.75rem;color:#3B82F6;margin-top:0.25rem;">Shop Now →</p>
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     FEATURED PRODUCTS
════════════════════════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <h2 class="section-title">⭐ Featured Products</h2>
            <p style="font-size:0.875rem;color:#64748B;margin-top:0.25rem;">Handpicked by our team — top quality, best value</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
        @foreach($featured as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     TRENDING PRODUCTS
════════════════════════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <h2 class="section-title">🔥 Trending Now</h2>
            <p style="font-size:0.875rem;color:#64748B;margin-top:0.25rem;">Most loved products this week</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div class="swiper trending-swiper">
        <div class="swiper-wrapper">
            @foreach($trending as $product)
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
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <h2 class="section-title">🆕 New Arrivals</h2>
        <a href="{{ route('products.index') }}" class="btn-ghost">See All →</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
        @foreach($newArrivals->take(8) as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     BRANDS BANNER
════════════════════════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:2rem;">
        <h3 style="text-align:center;font-size:0.8rem;color:#64748B;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:1.5rem;">Trusted Brands</h3>
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:2.5rem;align-items:center;">
            @foreach(['Apple','Samsung','Sony','Google','Microsoft','ASUS','Dell','Razer','Logitech','Bose'] as $brand)
            <span style="font-size:1rem;font-weight:700;color:#374151;letter-spacing:0.05em;transition:color 0.2s;cursor:default;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#374151'">{{ $brand }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════
     NEWSLETTER
════════════════════════════════════════════════════════════ --}}
<section style="max-width:1280px;margin:0 auto;padding:0 1rem 2rem;">
    <div style="background:linear-gradient(135deg,rgba(59,130,246,0.08),rgba(139,92,246,0.08));border:1px solid rgba(59,130,246,0.2);border-radius:24px;padding:3rem 2rem;text-align:center;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:radial-gradient(circle,rgba(59,130,246,0.1),transparent 70%);"></div>
        <div style="position:absolute;bottom:-50px;left:-50px;width:200px;height:200px;background:radial-gradient(circle,rgba(139,92,246,0.1),transparent 70%);"></div>
        <div style="position:relative;z-index:1;">
            <span style="font-size:2rem;">📧</span>
            <h2 style="font-size:2rem;font-weight:900;color:#F1F5F9;margin:0.75rem 0 0.5rem;">Stay in the Loop</h2>
            <p style="color:#64748B;font-size:1rem;margin-bottom:2rem;">Get exclusive deals, new product alerts, and AI-curated picks delivered to your inbox.</p>
            <form style="display:flex;max-width:480px;margin:0 auto;gap:0.75rem;" onsubmit="this.querySelector('button').textContent='✓ Subscribed!';event.preventDefault();">
                <input type="email" placeholder="your@email.com" class="form-input" style="flex:1;" required>
                <button type="submit" class="btn-primary" style="white-space:nowrap;">Subscribe</button>
            </form>
            <p style="font-size:0.75rem;color:#374151;margin-top:0.875rem;">No spam, unsubscribe anytime. We respect your privacy.</p>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// Hero Swiper
new Swiper('.hero-swiper', {
    loop: true, autoplay: { delay: 5000, disableOnInteraction: false },
    pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
    navigation: { nextEl: '.hero-swiper .swiper-button-next', prevEl: '.hero-swiper .swiper-button-prev' },
    effect: 'fade', fadeEffect: { crossFade: true },
});

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
