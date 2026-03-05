@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:2rem 1rem;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:#64748B;margin-bottom:2rem;">
        <a href="{{ route('home') }}" style="color:#64748B;text-decoration:none;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">Home</a>
        <span>›</span>
        <a href="{{ route('category.show', $product->category->slug) }}" style="color:#64748B;text-decoration:none;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">{{ $product->category->name }}</a>
        <span>›</span>
        <span style="color:#F1F5F9;">{{ Str::limit($product->name, 40) }}</span>
    </div>

    {{-- Product Detail --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:start;margin-bottom:3rem;">

        {{-- LEFT: Image Gallery --}}
        <div>
            <div class="swiper product-gallery" style="border-radius:16px;overflow:hidden;border:1px solid var(--border);background:var(--bg-card);margin-bottom:0.75rem;">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1;object-fit:cover;">
                    </div>
                    @foreach($product->images as $img)
                    <div class="swiper-slide">
                        <img src="{{ $img->image_path }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1;object-fit:cover;">
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
            {{-- Thumbnails --}}
            <div class="swiper gallery-thumbs">
                <div class="swiper-wrapper">
                    @foreach([$product->image, ...$product->images->pluck('image_path')->toArray()] as $img)
                    <div class="swiper-slide" style="width:70px;">
                        <img src="{{ $img }}" style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:2px solid var(--border);cursor:pointer;transition:border-color 0.2s;">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT: Product Info --}}
        <div>
            {{-- Brand + Category --}}
            <div style="display:flex;gap:0.5rem;margin-bottom:0.75rem;">
                <span class="badge badge-blue">{{ $product->brand }}</span>
                <span class="badge badge-purple">{{ $product->category->name }}</span>
                @if($product->is_featured)<span class="badge badge-yellow">⭐ Featured</span>@endif
            </div>

            <h1 style="font-size:1.875rem;font-weight:800;color:#F1F5F9;line-height:1.2;margin-bottom:1rem;">{{ $product->name }}</h1>

            {{-- Rating --}}
            <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:1.25rem;">
                <div style="display:flex;align-items:center;gap:0.375rem;">
                    <div class="stars" style="font-size:1.1rem;">
                        @for($i=1;$i<=5;$i++) {{ $i <= round($product->rating) ? '★' : '☆' }} @endfor
                    </div>
                    <span style="font-size:1rem;font-weight:700;color:#F1F5F9;">{{ number_format($product->rating, 1) }}</span>
                </div>
                <span style="color:#64748B;font-size:0.875rem;">({{ $product->reviews_count }} reviews)</span>
                <span style="color:#10B981;font-size:0.875rem;font-weight:600;">{{ $product->is_in_stock ? '✓ In Stock' : '✗ Out of Stock' }}</span>
            </div>

            {{-- Price --}}
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1.25rem;background:var(--bg-elevated);border-radius:14px;border:1px solid var(--border);">
                <span style="font-size:2.5rem;font-weight:900;background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">${{ number_format($product->price, 2) }}</span>
                @if($product->compare_price)
                <div>
                    <span style="font-size:1rem;color:#64748B;text-decoration:line-through;display:block;">${{ number_format($product->compare_price, 2) }}</span>
                    <span class="badge badge-red">Save {{ $product->discount_percent }}%</span>
                </div>
                @endif
            </div>

            {{-- Short Description --}}
            <p style="color:#94A3B8;font-size:0.9rem;line-height:1.7;margin-bottom:1.5rem;">{{ $product->short_description }}</p>

            {{-- Add to Cart --}}
            <form action="{{ route('cart.add') }}" method="POST" style="display:flex;gap:0.75rem;margin-bottom:1rem;" id="atc-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div style="display:flex;align-items:center;background:var(--bg-elevated);border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                    <button type="button" onclick="changeQty(-1)" style="background:none;border:none;color:#F1F5F9;padding:0.625rem 0.875rem;font-size:1.1rem;cursor:pointer;">−</button>
                    <input type="number" name="quantity" id="qty" value="1" min="1" max="{{ $product->stock }}" style="background:none;border:none;width:50px;text-align:center;color:#F1F5F9;font-weight:700;outline:none;">
                    <button type="button" onclick="changeQty(1)" style="background:none;border:none;color:#F1F5F9;padding:0.625rem 0.875rem;font-size:1.1rem;cursor:pointer;">+</button>
                </div>
                @if($product->is_in_stock)
                    <button type="submit" class="btn-primary" style="flex:1;justify-content:center;">🛒 Add to Cart</button>
                @else
                    <button disabled style="flex:1;background:var(--bg-elevated);border:1px solid var(--border);border-radius:10px;color:#64748B;padding:0.625rem 1.5rem;cursor:not-allowed;">Out of Stock</button>
                @endif
            </form>

            {{-- Wishlist --}}
            <button data-wishlist="{{ $product->id }}"
                style="width:100%;background:{{ $inWishlist ? 'rgba(139,92,246,0.15)' : 'transparent' }};border:1px solid {{ $inWishlist ? 'rgba(139,92,246,0.4)' : 'var(--border)' }};border-radius:10px;padding:0.625rem;color:{{ $inWishlist ? '#A78BFA' : '#64748B' }};font-size:0.875rem;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.5rem;">
                <span class="wish-icon">{{ $inWishlist ? '❤️' : '🤍' }}</span>
                <span>{{ $inWishlist ? 'In Wishlist' : 'Add to Wishlist' }}</span>
            </button>

            {{-- Trust badges --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-top:1.5rem;">
                @foreach([['🚚','Free Shipping','Over $50'],['🔄','Free Returns','30 days'],['🛡️','Warranty','1 year']] as $b)
                <div style="text-align:center;padding:0.875rem 0.5rem;background:var(--bg-elevated);border-radius:10px;border:1px solid var(--border);">
                    <div style="font-size:1.25rem;">{{ $b[0] }}</div>
                    <div style="font-size:0.75rem;font-weight:700;color:#F1F5F9;margin:0.25rem 0 0.125rem;">{{ $b[1] }}</div>
                    <div style="font-size:0.7rem;color:#64748B;">{{ $b[2] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tabs: Description / Specs / Reviews --}}
    <div x-data="{ tab: 'description' }" style="margin-bottom:3rem;">
        <div style="display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:1.5rem;">
            @foreach([['description','📋 Description'],['specs','⚙️ Specs'],['reviews','⭐ Reviews ('.$product->reviews->count().')']] as [$key,$label])
            <button @click="tab = '{{ $key }}'"
                :style="tab === '{{ $key }}' ? 'border-bottom:2px solid #3B82F6;color:#3B82F6;background:rgba(59,130,246,0.06);' : 'border-bottom:2px solid transparent;color:#64748B;'"
                style="padding:0.875rem 1.5rem;font-size:0.875rem;font-weight:600;background:none;border-left:none;border-right:none;border-top:none;cursor:pointer;transition:all 0.2s;">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Description --}}
        <div x-show="tab === 'description'" style="color:#94A3B8;font-size:0.9rem;line-height:1.9;">
            {!! nl2br(e($product->description)) !!}
        </div>

        {{-- Specs --}}
        <div x-show="tab === 'specs'">
            @if($product->specs)
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.75rem;">
                @foreach($product->specs as $key => $val)
                <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:0.875rem;display:flex;justify-content:space-between;">
                    <span style="font-size:0.8rem;color:#64748B;font-weight:600;">{{ $key }}</span>
                    <span style="font-size:0.8rem;color:#F1F5F9;font-weight:500;">{{ is_array($val) ? implode(', ',$val) : $val }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Reviews --}}
        <div x-show="tab === 'reviews'">
            @forelse($product->reviews as $review)
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:1.25rem;margin-bottom:0.875rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#8B5CF6);display:flex;align-items:center;justify-content:center;font-weight:700;color:white;font-size:0.875rem;">{{ substr($review->user->name,0,1) }}</div>
                        <div>
                            <div style="font-size:0.875rem;font-weight:600;color:#F1F5F9;">{{ $review->user->name }}</div>
                            <div style="font-size:0.7rem;color:#64748B;">{{ $review->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="stars" style="font-size:0.875rem;">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</div>
                </div>
                @if($review->title)<h4 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:0.375rem;">{{ $review->title }}</h4>@endif
                <p style="font-size:0.875rem;color:#94A3B8;line-height:1.6;">{{ $review->body }}</p>
            </div>
            @empty
            <p style="color:#64748B;text-align:center;padding:2rem;">No reviews yet. Be the first to review this product!</p>
            @endforelse
        </div>
    </div>

    {{-- Similar Products --}}
    @if($similar->count() > 0)
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <h2 class="section-title">🔗 Similar Products</h2>
            <a href="{{ route('category.show', $product->category->slug) }}" class="btn-ghost">View Category →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.25rem;">
            @foreach($similar as $sim)
                <x-product-card :product="$sim" />
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
function changeQty(delta) {
    const input = document.getElementById('qty');
    const newVal = Math.max(1, Math.min({{ $product->stock }}, parseInt(input.value) + delta));
    input.value = newVal;
}
</script>
@push('scripts')
<script>
const gallery = new Swiper('.product-gallery', {
    thumbs: { swiper: new Swiper('.gallery-thumbs', { slidesPerView: 'auto', spaceBetween: 8, freeMode: true }) },
    pagination: { el: '.product-gallery .swiper-pagination', clickable: true },
});
</script>
@endpush
@endsection
