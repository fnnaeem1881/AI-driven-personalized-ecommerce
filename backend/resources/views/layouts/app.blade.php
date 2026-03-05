<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="cart-count" content="{{ \Cart::getTotalQuantity() }}">
    <title>@yield('title', 'TechNova') — AI-Powered Electronics Store</title>
    <meta name="description" content="@yield('meta_description', 'Shop the latest electronics, gadgets and tech at TechNova with AI-powered personalized recommendations.')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen" style="background-color:#060b14">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="toast toast-success" id="flash-toast">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="toast toast-error" id="flash-toast">✕ {{ session('error') }}</div>
    @endif

    {{-- Top Header --}}
    @include('components.top-header')

    {{-- Main Header --}}
    @include('components.main-header')

    {{-- Main Content --}}
    <main class="page-transition">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Axios (loaded before inline scripts so it's available globally) --}}
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Global JS --}}
    <script>
        // CSRF for AJAX
        window.Laravel = { csrfToken: '{{ csrf_token() }}' };
        axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;

        // Remove toast after 4s
        setTimeout(() => { const t = document.getElementById('flash-toast'); if(t) t.remove(); }, 4000);

        // Add to cart AJAX
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-add-cart]');
            if (!btn) return;
            e.preventDefault();
            const productId = btn.dataset.addCart;
            const qty = btn.dataset.qty || 1;
            try {
                btn.disabled = true;
                btn.innerHTML = '<span class="animate-spin">⟳</span>';
                const res = await axios.post('/cart/add', { product_id: productId, quantity: qty });
                document.querySelectorAll('.cart-count').forEach(el => el.textContent = res.data.cart_count);
                showToast(res.data.message || 'Added to cart!', 'success');
                btn.innerHTML = '✓ Added';
                setTimeout(() => { btn.innerHTML = '🛒 Add to Cart'; btn.disabled = false; }, 2000);
            } catch(err) { showToast('Failed to add to cart', 'error'); btn.disabled = false; btn.innerHTML = '🛒 Add to Cart'; }
        });

        // Wishlist toggle AJAX
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-wishlist]');
            if (!btn) return;
            e.preventDefault();
            @auth
            const productId = btn.dataset.wishlist;
            try {
                const res = await axios.post('/wishlist/toggle', { product_id: productId });
                btn.classList.toggle('active', res.data.in_wishlist);
                btn.querySelector('.wish-icon') && (btn.querySelector('.wish-icon').textContent = res.data.in_wishlist ? '❤️' : '🤍');
                showToast(res.data.message, 'success');
            } catch(err) { showToast('Please try again', 'error'); }
            @else
            window.location.href = '/login';
            @endauth
        });

        function showToast(msg, type = 'success') {
            const t = document.createElement('div');
            t.className = `toast toast-${type}`;
            t.textContent = (type === 'success' ? '✓ ' : '✕ ') + msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 4000);
        }
    </script>

    @stack('scripts')
</body>
</html>
