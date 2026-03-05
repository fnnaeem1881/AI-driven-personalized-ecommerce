<header class="main-header py-3 px-4" x-data="{ mobileOpen: false, userOpen: false }">
    <div class="max-w-7xl mx-auto flex items-center gap-4">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="header-logo flex items-center gap-2 shrink-0" style="text-decoration:none;">
            @php $logoUrl = \App\Models\Setting::get('logo_url'); $storeName = \App\Models\Setting::get('store_name', 'TechNova'); @endphp
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $storeName }}" style="height:36px;width:auto;object-fit:contain;">
            @else
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#3B82F6,#8B5CF6);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;box-shadow:0 0 20px rgba(59,130,246,0.4);">⚡</div>
                <span style="font-size:1.4rem;font-weight:900;background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ $storeName }}</span>
            @endif
        </a>

        {{-- Search Bar --}}
        <div class="search-bar flex-1" style="max-width:600px; margin: 0 auto;">
            <form action="{{ route('search') }}" method="GET" style="display:flex;width:100%;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search for phones, laptops, audio gear..." autocomplete="off">
                <button type="submit" class="search-btn">🔍 Search</button>
            </form>
        </div>

        {{-- Right Icons --}}
        <div class="flex items-center gap-2 shrink-0">

            {{-- Wishlist --}}
            @auth
            <a href="{{ route('wishlist.index') }}" class="icon-btn" title="Wishlist">
                <span>🤍</span>
            </a>
            @endauth

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}" class="icon-btn" title="Cart">
                <span>🛒</span>
                <span class="cart-badge cart-count">{{ \Cart::getTotalQuantity() }}</span>
            </a>

            {{-- User --}}
            @auth
            <div style="position:relative;" x-data="{ open: false }">
                <button @click="open = !open" class="icon-btn" title="Account" style="width:auto;padding:0 0.75rem;gap:0.5rem;">
                    <span>👤</span>
                    <span style="font-size:0.8rem;color:#94A3B8;max-width:80px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</span>
                    <span style="font-size:0.6rem;color:#64748B;">▼</span>
                </button>
                <div x-show="open" @click.away="open = false" x-transition
                    style="position:absolute;top:calc(100% + 8px);right:0;background:#0d1526;border:1px solid rgba(59,130,246,0.2);border-radius:12px;padding:0.5rem;min-width:180px;z-index:200;box-shadow:0 20px 60px rgba(0,0,0,0.5);">
                    <a href="{{ route('account.dashboard') }}" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0.875rem;border-radius:8px;color:#94A3B8;font-size:0.875rem;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(59,130,246,0.1)';this.style.color='#F1F5F9'" onmouseout="this.style.background='';this.style.color='#94A3B8'">📊 Dashboard</a>
                    <a href="{{ route('orders.index') }}" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0.875rem;border-radius:8px;color:#94A3B8;font-size:0.875rem;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(59,130,246,0.1)';this.style.color='#F1F5F9'" onmouseout="this.style.background='';this.style.color='#94A3B8'">📦 My Orders</a>
                    <a href="{{ route('wishlist.index') }}" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0.875rem;border-radius:8px;color:#94A3B8;font-size:0.875rem;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(59,130,246,0.1)';this.style.color='#F1F5F9'" onmouseout="this.style.background='';this.style.color='#94A3B8'">❤️ Wishlist</a>
                    <a href="{{ route('account.profile') }}" style="display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0.875rem;border-radius:8px;color:#94A3B8;font-size:0.875rem;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.background='rgba(59,130,246,0.1)';this.style.color='#F1F5F9'" onmouseout="this.style.background='';this.style.color='#94A3B8'">⚙️ Profile</a>
                    <div style="border-top:1px solid rgba(59,130,246,0.15);margin:0.375rem 0;"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="width:100%;display:flex;align-items:center;gap:0.625rem;padding:0.625rem 0.875rem;border-radius:8px;color:#F87171;font-size:0.875rem;background:none;border:none;cursor:pointer;transition:all 0.2s;text-align:left;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background=''">🚪 Sign Out</button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn-primary" style="padding:0.5rem 1.25rem; font-size:0.8rem;">Sign In</a>
            @endauth

            {{-- Mobile menu btn --}}
            <button @click="mobileOpen = !mobileOpen" class="icon-btn md:hidden" id="mobile-menu-btn">
                <span>☰</span>
            </button>
        </div>
    </div>

    {{-- Category Nav Bar --}}
    <div class="max-w-7xl mx-auto mt-2 hidden md:flex items-center gap-1" style="padding:0 0.25rem;">
        @php $navCats = App\Models\Category::where('is_active', true)->orderBy('sort_order')->take(8)->get(); @endphp
        @foreach($navCats as $cat)
        <a href="{{ route('category.show', $cat->slug) }}"
           style="display:flex;align-items:center;gap:0.375rem;padding:0.375rem 0.875rem;border-radius:8px;font-size:0.8rem;color:#64748B;text-decoration:none;transition:all 0.2s;white-space:nowrap;"
           onmouseover="this.style.background='rgba(59,130,246,0.1)';this.style.color='#3B82F6'"
           onmouseout="this.style.background='';this.style.color='#64748B'">
            {{ $cat->icon }} {{ $cat->name }}
        </a>
        @endforeach
        <a href="{{ route('products.index') }}" style="display:flex;align-items:center;gap:0.375rem;padding:0.375rem 0.875rem;border-radius:8px;font-size:0.8rem;color:#3B82F6;text-decoration:none;margin-left:auto;font-weight:600;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">All Products →</a>
    </div>
</header>
