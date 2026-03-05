<div class="top-header py-2 px-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        {{-- Left: Promo message --}}
        <div class="flex items-center gap-2" style="color:#94A3B8; font-size:0.75rem;">
            <span style="color:#F59E0B;">🔥</span>
            <span>Free shipping on orders over <strong style="color:#F1F5F9;">{{ format_currency(store_setting('free_shipping_threshold', 1000)) }}</strong></span>
            <span style="color:#3B82F6; margin-left:1rem;">● SUMMER25 — Extra 15% off</span>
        </div>

        {{-- Center: Marquee --}}
        <div class="hidden md:block" style="font-size:0.7rem; color:#64748B;">
            ⚡ Same-day dispatch on orders before 3PM &nbsp;|&nbsp; 30-day free returns &nbsp;|&nbsp; 2-year warranty
        </div>

        {{-- Right: Links --}}
        <div class="flex items-center gap-4" style="font-size:0.75rem; color:#64748B;">
            @auth
                <span style="color:#94A3B8;">Hi, {{ auth()->user()->name }}</span>
                <a href="{{ route('orders.index') }}" style="color:#64748B; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">My Orders</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" style="color:#8B5CF6; text-decoration:none; font-weight:600;" onmouseover="this.style.color='#A78BFA'" onmouseout="this.style.color='#8B5CF6'">⚙ Admin</a>
                @endif
            @else
                <a href="{{ route('login') }}" style="color:#64748B; text-decoration:none;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">Sign In</a>
                <a href="{{ route('register') }}" style="color:#64748B; text-decoration:none;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">Register</a>
            @endauth
            <span>{{ store_setting('currency', 'BDT') }} {{ store_setting('currency_symbol', '৳') }}</span>
        </div>
    </div>
</div>
