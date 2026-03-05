@extends('layouts.app')
@section('title', isset($category) ? $category->name : 'All Products')

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:2rem 1rem;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:#64748B;margin-bottom:1.5rem;">
        <a href="{{ route('home') }}" style="color:#64748B;text-decoration:none;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">Home</a>
        <span>›</span>
        <span style="color:#F1F5F9;">{{ isset($category) ? $category->name : 'All Products' }}</span>
    </div>

    <div style="display:grid;grid-template-columns:260px 1fr;gap:1.5rem;align-items:start;">

        {{-- LEFT: Filter Sidebar --}}
        <div class="glass-card" style="padding:1.25rem;position:sticky;top:80px;">
            <h3 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:1rem;">🔧 Filters</h3>

            <form method="GET" id="filter-form">
                {{-- Categories --}}
                <div class="filter-group">
                    <h4>Category</h4>
                    @foreach($categories as $cat)
                    <label class="filter-checkbox">
                        <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'checked' : '' }}>
                        <span>{{ $cat->icon }} {{ $cat->name }}</span>
                    </label>
                    @endforeach
                </div>

                {{-- Price Range --}}
                <div class="filter-group">
                    <h4>Price Range</h4>
                    <div style="display:flex;gap:0.5rem;margin-bottom:0.75rem;">
                        <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}" class="form-input" style="font-size:0.8rem;padding:0.5rem;">
                        <input type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}" class="form-input" style="font-size:0.8rem;padding:0.5rem;">
                    </div>
                </div>

                {{-- Brands --}}
                <div class="filter-group">
                    <h4>Brand</h4>
                    @foreach($brands->take(8) as $brand)
                    <label class="filter-checkbox">
                        <input type="checkbox" name="brand" value="{{ $brand }}" {{ request('brand') === $brand ? 'checked' : '' }}>
                        <span>{{ $brand }}</span>
                    </label>
                    @endforeach
                </div>

                {{-- Rating --}}
                <div class="filter-group">
                    <h4>Minimum Rating</h4>
                    @foreach([4, 3, 2] as $r)
                    <label class="filter-checkbox">
                        <input type="radio" name="rating" value="{{ $r }}" {{ request('rating') == $r ? 'checked' : '' }}>
                        <span class="stars" style="font-size:0.85rem;">{{ str_repeat('★', $r) }}{{ str_repeat('☆', 5 - $r) }}</span>
                        <span>& up</span>
                    </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem;">Apply Filters</button>
                <a href="{{ route('products.index') }}" class="btn-ghost" style="width:100%;text-align:center;margin-top:0.5rem;display:block;">Clear All</a>
            </form>
        </div>

        {{-- RIGHT: Products --}}
        <div>
            {{-- Sort Bar --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:0.75rem 1rem;">
                <span style="font-size:0.875rem;color:#64748B;">
                    <strong style="color:#F1F5F9;">{{ $products->total() }}</strong> products found
                </span>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <label style="font-size:0.8rem;color:#64748B;">Sort:</label>
                    <select name="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()"
                        style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:8px;padding:0.375rem 0.75rem;color:#F1F5F9;font-size:0.8rem;cursor:pointer;">
                        <option value="newest" {{ request('sort','newest')==='newest' ? 'selected' : '' }}>Newest</option>
                        <option value="popular" {{ request('sort')==='popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="rating" {{ request('sort')==='rating' ? 'selected' : '' }}>Top Rated</option>
                        <option value="price_asc" {{ request('sort')==='price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort')==='price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
            </div>

            {{-- Product Grid --}}
            @if($products->count() > 0)
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-bottom:2rem;">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div style="display:flex;justify-content:center;">
                {{ $products->links() }}
            </div>
            @else
            <div style="text-align:center;padding:5rem 2rem;">
                <div style="font-size:4rem;margin-bottom:1rem;">🔍</div>
                <h3 style="font-size:1.25rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">No products found</h3>
                <p style="color:#64748B;margin-bottom:1.5rem;">Try adjusting your filters or search term.</p>
                <a href="{{ route('products.index') }}" class="btn-primary">Browse All Products</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
