@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('breadcrumb', 'Store configuration & theme')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl space-y-6">
    @csrf

    {{-- Store Info --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-5 pb-3 border-b border-gray-100">🏪 Store Information</h3>
        <div class="form-group">
            <label class="form-label">Store Name</label>
            <input type="text" name="store_name" value="{{ $settings['store_name'] ?? 'TechNova Store' }}"
                   required class="form-input" placeholder="TechNova Store">
        </div>
    </div>

    {{-- Theme Switcher --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-2 pb-3 border-b border-gray-100">🎨 Frontend Theme</h3>
        <p class="text-sm text-gray-500 mb-5">Choose how your store appears to customers. Changes take effect immediately after saving.</p>

        <div class="grid grid-cols-2 gap-4" x-data="{ theme: '{{ $settings['theme'] ?? 'dark' }}' }">
            {{-- Dark Theme Card --}}
            <label class="cursor-pointer">
                <input type="radio" name="theme" value="dark" x-model="theme" class="sr-only">
                <div :class="theme === 'dark' ? 'ring-2 ring-blue-500 border-blue-300' : 'border-gray-200'"
                     class="border-2 rounded-xl overflow-hidden transition-all">
                    {{-- Preview --}}
                    <div class="h-32 relative" style="background:#060b14;">
                        <div style="background:#0d1526;height:28px;display:flex;align-items:center;padding:0 10px;gap:6px;">
                            <div style="width:50px;height:8px;background:#3B82F6;border-radius:4px;"></div>
                            <div style="flex:1;height:8px;background:#111d35;border-radius:4px;margin:0 8px;"></div>
                            <div style="width:28px;height:16px;background:linear-gradient(135deg,#3B82F6,#8B5CF6);border-radius:4px;"></div>
                        </div>
                        <div style="padding:8px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                            @for($i=0;$i<3;$i++)
                            <div style="background:#111d35;border-radius:6px;height:44px;border:1px solid rgba(59,130,246,.2);"></div>
                            @endfor
                        </div>
                    </div>
                    <div class="p-3 flex items-center justify-between" :class="theme === 'dark' ? 'bg-blue-50' : 'bg-gray-50'">
                        <div>
                            <div class="font-semibold text-sm text-gray-800">🌑 Dark Mode</div>
                            <div class="text-xs text-gray-400">Deep navy + neon glow</div>
                        </div>
                        <div :class="theme === 'dark' ? 'bg-blue-500' : 'bg-gray-200'"
                             class="w-5 h-5 rounded-full flex items-center justify-center transition-colors">
                            <svg x-show="theme === 'dark'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </label>

            {{-- Light Theme Card --}}
            <label class="cursor-pointer">
                <input type="radio" name="theme" value="light" x-model="theme" class="sr-only">
                <div :class="theme === 'light' ? 'ring-2 ring-blue-500 border-blue-300' : 'border-gray-200'"
                     class="border-2 rounded-xl overflow-hidden transition-all">
                    {{-- Preview --}}
                    <div class="h-32 relative" style="background:#F1F5F9;">
                        <div style="background:#fff;height:28px;display:flex;align-items:center;padding:0 10px;gap:6px;border-bottom:1px solid #E2E8F0;">
                            <div style="width:50px;height:8px;background:#3B82F6;border-radius:4px;"></div>
                            <div style="flex:1;height:8px;background:#F1F5F9;border-radius:4px;margin:0 8px;border:1px solid #E2E8F0;"></div>
                            <div style="width:28px;height:16px;background:linear-gradient(135deg,#3B82F6,#8B5CF6);border-radius:4px;"></div>
                        </div>
                        <div style="padding:8px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                            @for($i=0;$i<3;$i++)
                            <div style="background:#fff;border-radius:6px;height:44px;border:1px solid #E2E8F0;box-shadow:0 1px 3px rgba(0,0,0,.06);"></div>
                            @endfor
                        </div>
                    </div>
                    <div class="p-3 flex items-center justify-between" :class="theme === 'light' ? 'bg-blue-50' : 'bg-gray-50'">
                        <div>
                            <div class="font-semibold text-sm text-gray-800">☀️ Light Mode</div>
                            <div class="text-xs text-gray-400">Clean white & crisp</div>
                        </div>
                        <div :class="theme === 'light' ? 'bg-blue-500' : 'bg-gray-200'"
                             class="w-5 h-5 rounded-full flex items-center justify-center transition-colors">
                            <svg x-show="theme === 'light'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <p class="text-xs text-gray-400 mt-3">
            💡 The admin panel always stays in light mode. Only the public storefront changes.
        </p>
    </div>

    {{-- Save --}}
    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary py-3 px-8 text-base">
            💾 Save Settings
        </button>
        <span class="text-sm text-gray-400">Changes apply to the store immediately</span>
    </div>
</form>
@endsection
