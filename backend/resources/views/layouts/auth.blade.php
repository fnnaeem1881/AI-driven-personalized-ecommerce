<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auth') — TechNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color:#060b14; min-height:100vh; display:flex; flex-direction:column;">
    <a href="{{ route('home') }}" class="absolute top-6 left-8 flex items-center gap-2 z-10">
        <span style="font-size:1.5rem; font-weight:900; background:linear-gradient(135deg,#3B82F6,#8B5CF6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">⚡ TechNova</span>
    </a>
    <div class="flex flex-1" style="min-height:100vh;">
        {{-- Left Brand Panel --}}
        <div class="hidden lg:flex flex-col justify-center items-start px-16 relative overflow-hidden" style="width:45%; background:linear-gradient(135deg,#0d1526 0%,#111d35 50%,#0a1020 100%);">
            <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(59,130,246,0.12),transparent 70%);"></div>
            <div style="position:relative;z-index:1;">
                <div style="font-size:4rem; margin-bottom:1.5rem;">⚡</div>
                <h2 style="font-size:2.5rem; font-weight:900; line-height:1.1; color:#F1F5F9; margin-bottom:1rem;">
                    Shop Smarter<br>
                    <span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">with AI</span>
                </h2>
                <p style="color:#64748B; font-size:1rem; line-height:1.7; max-width:380px;">
                    Get personalized product recommendations powered by machine learning. Discover the best electronics tailored just for you.
                </p>
                <div style="margin-top:2.5rem; display:flex; flex-direction:column; gap:1rem;">
                    @foreach(['🤖 AI-powered recommendations', '🚚 Free shipping over $50', '⭐ 30-day easy returns', '🔒 Secure checkout'] as $feature)
                    <div style="display:flex; align-items:center; gap:0.75rem; color:#94A3B8; font-size:0.9rem;">
                        <span>{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right Form Panel --}}
        <div class="flex flex-1 items-center justify-center p-8">
            <div style="width:100%; max-width:440px;">
                @yield('content')
            </div>
        </div>
    </div>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
