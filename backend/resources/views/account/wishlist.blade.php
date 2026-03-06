@extends('layouts.app')
@section('title', 'My Wishlist')

@section('content')
<div style="max-width:1100px;margin:0 auto;padding:2rem 1rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
        <h1 style="font-size:2rem;font-weight:800;color:var(--text-primary);">❤️ My Wishlist
            <span style="font-size:1rem;font-weight:400;color:#64748B;margin-left:0.75rem;">{{ $wishlistItems->count() }} item(s)</span>
        </h1>
        <a href="{{ route('products.index') }}" class="btn-ghost">+ Add More Products</a>
    </div>

    @if($wishlistItems->isEmpty())
    <div style="text-align:center;padding:5rem 2rem;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;">
        <div style="font-size:4rem;margin-bottom:1rem;">❤️</div>
        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-primary);margin-bottom:0.5rem;">Your wishlist is empty</h2>
        <p style="color:#64748B;margin-bottom:1.5rem;">Save products you love by clicking the ❤️ button on any product.</p>
        <a href="{{ route('products.index') }}" class="btn-primary">Browse Products</a>
    </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
        @foreach($wishlistItems as $item)
        <div class="product-card" style="position:relative;">
            <form method="POST" action="{{ route('wishlist.toggle') }}" style="position:absolute;top:10px;right:10px;z-index:5;">
                @csrf
                <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                <button type="submit" style="width:32px;height:32px;border-radius:50%;background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.4);cursor:pointer;font-size:0.9rem;">❤️</button>
            </form>

            <div class="product-img-wrap" style="height:200px;">
                <a href="{{ route('products.show', $item->product->slug) }}">
                    <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}" loading="lazy">
                </a>
            </div>

            <div style="padding:1rem;">
                <div style="font-size:0.7rem;color:#64748B;font-weight:600;text-transform:uppercase;margin-bottom:0.25rem;">{{ $item->product->brand }}</div>
                <h3 style="font-size:0.875rem;font-weight:600;color:var(--text-primary);margin-bottom:0.75rem;">{{ Str::limit($item->product->name, 45) }}</h3>
                <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.875rem;">
                    <span style="font-size:1rem;font-weight:800;color:#3B82F6;">{{ format_currency($item->product->price) }}</span>
                </div>
                <button data-add-cart="{{ $item->product->id }}" data-qty="1" class="btn-primary" style="width:100%;justify-content:center;font-size:0.8rem;padding:0.5rem;">
                    🛒 Add to Cart
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
