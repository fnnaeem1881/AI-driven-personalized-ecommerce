@extends('layouts.app')
@section('title', 'Search: ' . $query)

@section('content')
<div style="max-width:1280px;margin:0 auto;padding:2rem 1rem;">

    <div style="margin-bottom:2rem;">
        <h1 style="font-size:1.75rem;font-weight:800;color:#F1F5F9;">
            @if($query)
                🔍 Results for "<span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ $query }}</span>"
            @else
                🔍 All Products
            @endif
        </h1>
        <p style="color:#64748B;margin-top:0.375rem;font-size:0.875rem;">
            Found <strong style="color:#F1F5F9;">{{ $products->total() }}</strong> results
        </p>
    </div>

    <div style="display:grid;grid-template-columns:260px 1fr;gap:1.5rem;align-items:start;">

        {{-- Filter Sidebar --}}
        <div class="glass-card" style="padding:1.25rem;position:sticky;top:80px;">
            <h3 style="font-size:0.875rem;font-weight:700;color:#F1F5F9;margin-bottom:1rem;">🔧 Refine Results</h3>
            <form method="GET" action="{{ route('search') }}">
                <input type="hidden" name="q" value="{{ $query }}">

                <div class="filter-group">
                    <h4>Category</h4>
                    @foreach($categories as $cat)
                    <label class="filter-checkbox">
                        <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'checked' : '' }}>
                        <span>{{ $cat->icon }} {{ $cat->name }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h4>Price Range</h4>
                    <div style="display:flex;gap:0.5rem;">
                        <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}" class="form-input" style="font-size:0.8rem;padding:0.5rem;">
                        <input type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}" class="form-input" style="font-size:0.8rem;padding:0.5rem;">
                    </div>
                </div>

                <div class="filter-group">
                    <h4>Brand</h4>
                    @foreach($brands->take(8) as $brand)
                    <label class="filter-checkbox">
                        <input type="radio" name="brand" value="{{ $brand }}" {{ request('brand') === $brand ? 'checked' : '' }}>
                        <span>{{ $brand }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="filter-group">
                    <h4>Sort By</h4>
                    @foreach([['relevance','Most Relevant'],['price_asc','Price: Low-High'],['price_desc','Price: High-Low'],['rating','Top Rated'],['newest','Newest']] as [$val,$label])
                    <label class="filter-checkbox">
                        <input type="radio" name="sort" value="{{ $val }}" {{ request('sort','relevance') === $val ? 'checked' : '' }}>
                        <span>{{ $label }}</span>
                    </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Apply</button>
                <a href="{{ route('search', ['q' => $query]) }}" class="btn-ghost" style="width:100%;text-align:center;display:block;margin-top:0.5rem;">Clear Filters</a>
            </form>
        </div>

        {{-- Results --}}
        <div>
            @if($products->isEmpty())
            <div style="text-align:center;padding:5rem 2rem;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;">
                <div style="font-size:4rem;margin-bottom:1rem;">🔍</div>
                <h3 style="font-size:1.25rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">No results found</h3>
                <p style="color:#64748B;margin-bottom:1.5rem;">Try different keywords or browse our categories.</p>
                <a href="{{ route('products.index') }}" class="btn-primary">Browse All Products</a>
            </div>
            @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-bottom:2rem;">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div style="display:flex;justify-content:center;">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
