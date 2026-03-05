<footer class="footer mt-20 pt-12 pb-6 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 pb-10" style="border-bottom:1px solid rgba(59,130,246,0.1);">

            {{-- Brand --}}
            <div class="lg:col-span-2">
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                    <div style="width:40px;height:40px;background:linear-gradient(135deg,#3B82F6,#8B5CF6);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">⚡</div>
                    <span style="font-size:1.5rem;font-weight:900;background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">TechNova</span>
                </div>
                <p style="color:#64748B;font-size:0.875rem;line-height:1.7;max-width:300px;">
                    Your AI-powered electronics destination. Get personalized recommendations and shop the latest tech with confidence.
                </p>
                <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                    @foreach(['𝕏', '📘', '📸', '▶'] as $social)
                    <a href="#" style="width:36px;height:36px;border-radius:10px;background:rgba(13,21,38,1);border:1px solid rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;font-size:0.875rem;color:#64748B;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.borderColor='#3B82F6';this.style.color='#3B82F6'" onmouseout="this.style.borderColor='rgba(59,130,246,0.15)';this.style.color='#64748B'">{{ $social }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Shop --}}
            <div>
                <h4 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#94A3B8;margin-bottom:1.25rem;">Shop</h4>
                @foreach(['Smartphones' => 'smartphones', 'Laptops' => 'laptops', 'Audio' => 'audio', 'Gaming' => 'gaming', 'Cameras' => 'cameras'] as $name => $slug)
                <a href="{{ route('category.show', $slug) }}" class="footer-link">{{ $name }}</a>
                @endforeach
            </div>

            {{-- Account --}}
            <div>
                <h4 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#94A3B8;margin-bottom:1.25rem;">Account</h4>
                <a href="{{ route('account.dashboard') }}" class="footer-link">My Dashboard</a>
                <a href="{{ route('orders.index') }}" class="footer-link">My Orders</a>
                <a href="{{ route('wishlist.index') }}" class="footer-link">Wishlist</a>
                <a href="{{ route('account.profile') }}" class="footer-link">Profile Settings</a>
                <a href="{{ route('cart.index') }}" class="footer-link">Shopping Cart</a>
            </div>

            {{-- Support --}}
            <div>
                <h4 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#94A3B8;margin-bottom:1.25rem;">Support</h4>
                <a href="{{ route('support.help') }}" class="footer-link">Help Center</a>
                <a href="{{ route('support.shipping') }}" class="footer-link">Shipping Info</a>
                <a href="{{ route('support.returns') }}" class="footer-link">Returns Policy</a>
                <a href="{{ route('support.track') }}" class="footer-link">Track Order</a>
                <a href="{{ route('support.contact') }}" class="footer-link">Contact Us</a>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="flex flex-col md:flex-row items-center justify-between pt-6 gap-4">
            <p style="color:#374151;font-size:0.8rem;">© {{ date('Y') }} TechNova. All rights reserved. AI-Powered by TechNova ML.</p>
            <div style="display:flex;gap:1.5rem;">
                <span style="font-size:1.5rem;" title="Visa">💳</span>
                <span style="font-size:1.5rem;" title="Mastercard">💳</span>
                <span style="font-size:1.5rem;" title="PayPal">🅿️</span>
                <span style="font-size:1.5rem;" title="Apple Pay">🍎</span>
            </div>
        </div>
    </div>
</footer>
