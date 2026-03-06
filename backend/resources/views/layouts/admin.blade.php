<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — TechNova Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        /* CSS variables so pagination template renders correctly in admin */
        :root {
            --bg-elevated:#F8FAFC; --bg-card:#FFFFFF; --bg-dark:#F1F5F9;
            --border:#E2E8F0; --border-hover:rgba(59,130,246,0.4);
            --text-primary:#0F172A; --text-subtle:#475569; --text-muted:#94A3B8;
            --primary:#3B82F6; --secondary:#8B5CF6;
            --primary-glow:rgba(59,130,246,0.2); --secondary-glow:rgba(139,92,246,0.2);
            --success:#10B981; --danger:#EF4444; --warning:#F59E0B;
        }
        /* Select2 custom styling */
        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            border: 1px solid #CBD5E1 !important;
            border-radius: 8px !important;
            background: #F8FAFC !important;
            min-height: 40px !important;
            font-size: 14px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            color: #0F172A !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #DBEAFE !important;
            border: none !important;
            color: #1D4ED8 !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            padding: 2px 8px !important;
            margin: 4px 4px 0 0 !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #1D4ED8 !important;
            margin-right: 4px !important;
        }
        .select2-container--default .select2-results__option--highlighted {
            background: #3B82F6 !important;
        }
        .select2-dropdown {
            border: 1px solid #CBD5E1 !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1) !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #CBD5E1 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            font-size: 13px !important;
        }
        .sidebar-link { display:flex; align-items:center; gap:10px; padding:10px 16px; border-radius:8px; color:#475569; font-size:14px; font-weight:500; text-decoration:none; transition:all .15s; }
        .sidebar-link:hover { background:#EFF6FF; color:#1D4ED8; }
        .sidebar-link.active { background:#EFF6FF; color:#1D4ED8; font-weight:600; }
        .badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600; }
        .badge-green  { background:#DCFCE7; color:#15803D; }
        .badge-yellow { background:#FEF9C3; color:#A16207; }
        .badge-blue   { background:#DBEAFE; color:#1D4ED8; }
        .badge-red    { background:#FEE2E2; color:#DC2626; }
        .badge-purple { background:#F3E8FF; color:#7E22CE; }
        .badge-gray   { background:#F1F5F9; color:#475569; }
        .stat-card { background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:20px; }
        .btn-primary { background:linear-gradient(135deg,#3B82F6,#8B5CF6); color:#fff; padding:8px 18px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:opacity .15s; }
        .btn-primary:hover { opacity:.9; }
        .btn-danger { background:#EF4444; color:#fff; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; border:none; cursor:pointer; text-decoration:none; }
        .btn-secondary { background:#F1F5F9; color:#374151; padding:7px 14px; border-radius:7px; font-size:13px; font-weight:500; border:1px solid #E2E8F0; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
        .btn-secondary:hover { background:#E2E8F0; }
        .form-input { width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:14px; color:#0F172A; background:#F8FAFC; outline:none; transition:border-color .15s; }
        .form-input:focus { border-color:#3B82F6; background:#fff; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
        .form-label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .form-group { margin-bottom:18px; }
        .table-row:hover { background:#F8FAFC; }
        .flash-success { background:#DCFCE7; border:1px solid #86EFAC; color:#15803D; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
        .flash-error   { background:#FEE2E2; border:1px solid #FCA5A5; color:#DC2626; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">

{{-- Sidebar --}}
<aside class="w-64 bg-white border-r border-gray-200 flex flex-col min-h-screen fixed top-0 left-0 z-30">
    {{-- Logo --}}
    <div class="p-5 border-b border-gray-100">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-decoration-none">
            <span class="text-2xl">⚡</span>
            <div>
                <div class="font-bold text-gray-900" style="font-size:15px;">TechNova</div>
                <div class="text-xs text-purple-600 font-medium">Admin Panel</div>
            </div>
        </a>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            📊 <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.products.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
            📦 <span>Products</span>
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            🏷️ <span>Categories</span>
        </a>
        <a href="{{ route('admin.orders.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
            📋 <span>Orders</span>
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            👥 <span>Users</span>
        </a>
        <a href="{{ route('admin.flash-deals.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.flash-deals*') ? 'active' : '' }}">
            ⚡ <span>Flash Deals</span>
        </a>
        <a href="{{ route('admin.slides.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.slides*') ? 'active' : '' }}">
            🖼️ <span>Hero Slides</span>
        </a>
        <a href="{{ route('admin.settings') }}"
           class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
            ⚙️ <span>Settings</span>
        </a>

        {{-- AI & Analytics --}}
        <div class="pt-3 mt-3 border-t border-gray-100">
            <div class="px-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">AI & Analytics</div>
            <a href="{{ route('admin.analytics') }}"
               class="sidebar-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                📊 <span>ClickHouse Analytics</span>
            </a>
            <a href="{{ route('admin.ai-health') }}"
               class="sidebar-link {{ request()->routeIs('admin.ai-health') ? 'active' : '' }}">
                🩺 <span>Services Health</span>
            </a>
        </div>

        {{-- Access Control --}}
        <div class="pt-3 mt-3 border-t border-gray-100">
            <div class="px-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Access Control</div>
            <a href="{{ route('admin.roles.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                🛡️ <span>Roles</span>
            </a>
            <a href="{{ route('admin.permissions.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.permissions*') ? 'active' : '' }}">
                🔐 <span>Permissions</span>
            </a>
        </div>

        <div class="pt-3 mt-2 border-t border-gray-100">
            <a href="{{ route('home') }}" class="sidebar-link text-blue-600">
                🏪 <span>View Store</span>
            </a>
        </div>
    </nav>

    {{-- User info + Profile link --}}
    <div class="p-4 border-t border-gray-100 bg-gray-50">
        <a href="{{ route('admin.profile') }}"
           class="flex items-center gap-3 group hover:bg-white rounded-lg px-2 py-1.5 -mx-2 transition-colors mb-2">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-gray-800 truncate group-hover:text-blue-600 transition-colors">{{ auth()->user()->name }}</div>
                <div class="text-xs text-purple-500">Edit Profile →</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left text-xs text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1.5 px-1 py-1">
                <span>⏻</span> Sign Out
            </button>
        </form>
    </div>
</aside>

{{-- Main content --}}
<div class="flex-1 ml-64 flex flex-col min-h-screen">
    {{-- Top bar --}}
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-20">
        <div>
            <h1 class="text-lg font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
            @hasSection('breadcrumb')
                <div class="text-xs text-gray-400 mt-0.5">@yield('breadcrumb')</div>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @yield('header-actions')
        </div>
    </header>

    {{-- Flash messages --}}
    <div class="px-6 pt-4">
        @if(session('success'))
            <div class="flash-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-error">✕ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="flash-error">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Page content --}}
    <main class="flex-1 p-6">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-4 border-t border-gray-100">
        TechNova Admin Panel © {{ date('Y') }}
    </footer>
</div>

<script>
$(document).ready(function () {
    // Init all selects with Select2 (except those marked no-select2)
    $('select:not(.no-select2):not([multiple])').each(function () {
        $(this).select2({
            width: '100%',
            placeholder: $(this).data('placeholder') || 'Select...',
            allowClear: !$(this).prop('required'),
            minimumResultsForSearch: 6,
        });
    });
    // Multi-select with search (e.g. product assignment)
    $('select[multiple]:not(.no-select2)').each(function () {
        $(this).select2({
            width: '100%',
            placeholder: $(this).data('placeholder') || 'Search and select...',
            allowClear: true,
        });
    });
    // Re-init after Alpine.js DOM updates (for dynamically shown selects)
    document.addEventListener('alpine:initialized', function () {
        setTimeout(function () {
            $('select:not(.no-select2):not(.select2-hidden-accessible)').select2({ width: '100%' });
        }, 100);
    });
});
</script>

@stack("scripts")
</body>
</html>
