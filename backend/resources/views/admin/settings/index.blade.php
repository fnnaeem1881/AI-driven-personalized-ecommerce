@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Store Settings')
@section('breadcrumb', 'Configure your store')

@section('content')
@php $v = fn(string $k, $d = '') => $settings[$k] ?? $d; @endphp

<form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ tab: 'general' }">
    @csrf

    {{-- Tab Nav --}}
    <div class="flex gap-1 bg-white border border-gray-200 rounded-xl p-1.5 mb-6 overflow-x-auto">
        @foreach([
            ['general',  '🏪', 'General'],
            ['currency', '💰', 'Currency'],
            ['contact',  '📞', 'Contact'],
            ['social',   '🌐', 'Social'],
            ['seo',      '🔍', 'SEO'],
            ['shipping', '📦', 'Shipping'],
            ['homepage', '🏠', 'Homepage'],
            ['advanced', '⚙️', 'Advanced'],
        ] as [$id, $icon, $label])
        <button type="button" @click="tab = '{{ $id }}'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap"
                :class="tab === '{{ $id }}'
                    ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow'
                    : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50'">
            {{ $icon }} {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ① General --}}
    <div x-show="tab === 'general'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🏪 Store Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group mb-0">
                    <label class="form-label">Store Name <span class="text-red-500">*</span></label>
                    <input type="text" name="store_name" value="{{ $v('store_name','TechNova Store') }}"
                           required class="form-input" placeholder="TechNova Store">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Store Status <span class="text-red-500">*</span></label>
                    <select name="store_status" class="form-input">
                        <option value="open"        {{ $v('store_status','open')==='open'        ? 'selected' : '' }}>✅ Open for Business</option>
                        <option value="maintenance" {{ $v('store_status','open')==='maintenance' ? 'selected' : '' }}>🔧 Maintenance Mode</option>
                    </select>
                </div>
                <div class="form-group mb-0 sm:col-span-2">
                    <label class="form-label">Maintenance Message</label>
                    <input type="text" name="maintenance_message" value="{{ $v('maintenance_message') }}"
                           class="form-input" placeholder="We'll be back soon!">
                </div>
            </div>
        </div>

        {{-- Theme --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6" x-data="{ th: '{{ $v('theme','dark') }}' }">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🎨 Frontend Theme</h3>
            <input type="hidden" name="theme" :value="th">
            <div class="grid grid-cols-2 gap-4 max-w-xs">
                <label @click="th='dark'" class="cursor-pointer">
                    <div class="rounded-xl overflow-hidden border-2 transition-all"
                         :class="th==='dark' ? 'border-purple-500 shadow-md' : 'border-gray-200'">
                        <div style="background:#060b14; height:60px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:12px; font-weight:600;">🌑 Dark</div>
                    </div>
                    <div class="flex items-center gap-2 mt-2 text-sm font-medium" :class="th==='dark' ? 'text-purple-600' : 'text-gray-500'">
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                             :class="th==='dark' ? 'border-purple-500 bg-purple-500' : 'border-gray-300'">
                            <div x-show="th==='dark'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                        Dark Mode
                    </div>
                </label>
                <label @click="th='light'" class="cursor-pointer">
                    <div class="rounded-xl overflow-hidden border-2 transition-all"
                         :class="th==='light' ? 'border-blue-500 shadow-md' : 'border-gray-200'">
                        <div style="background:#F1F5F9; height:60px; display:flex; align-items:center; justify-content:center; color:#475569; font-size:12px; font-weight:600;">☀️ Light</div>
                    </div>
                    <div class="flex items-center gap-2 mt-2 text-sm font-medium" :class="th==='light' ? 'text-blue-600' : 'text-gray-500'">
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                             :class="th==='light' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                            <div x-show="th==='light'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                        Light Mode
                    </div>
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-3">Admin panel always stays light. Only the public storefront changes.</p>
        </div>
    </div>

    {{-- ② Currency --}}
    <div x-show="tab === 'currency'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">💰 Currency Settings</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                <div class="form-group mb-0">
                    <label class="form-label">Currency Code <span class="text-red-500">*</span></label>
                    <input type="text" name="currency" value="{{ $v('currency','BDT') }}"
                           required class="form-input" placeholder="BDT" maxlength="10">
                    <p class="text-xs text-gray-400 mt-1">e.g. BDT, USD, EUR, GBP</p>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Currency Symbol <span class="text-red-500">*</span></label>
                    <input type="text" name="currency_symbol" id="sym_input" value="{{ $v('currency_symbol','৳') }}"
                           required class="form-input" placeholder="৳" maxlength="5">
                    <p class="text-xs text-gray-400 mt-1">৳ $ € £ ¥ ﷼ ₹</p>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Symbol Position <span class="text-red-500">*</span></label>
                    <select name="currency_position" id="pos_input" class="form-input">
                        <option value="before" {{ $v('currency_position','before')==='before' ? 'selected' : '' }}>Before amount (৳1,299)</option>
                        <option value="after"  {{ $v('currency_position','before')==='after'  ? 'selected' : '' }}>After amount (1,299 ৳)</option>
                    </select>
                </div>
            </div>
            <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                <p class="text-xs text-blue-600 font-medium mb-1">Live Preview:</p>
                <p class="text-2xl font-bold text-blue-700" id="currency_preview">{{ $v('currency_symbol','৳') }}1,299</p>
                <script>
                    (function(){
                        var sym = document.getElementById('sym_input');
                        var pos = document.getElementById('pos_input');
                        var pre = document.getElementById('currency_preview');
                        function upd(){
                            pre.textContent = pos && pos.value==='after' ? '1,299 '+(sym?sym.value:'৳') : (sym?sym.value:'৳')+'1,299';
                        }
                        if(sym) sym.addEventListener('input', upd);
                        if(pos) pos.addEventListener('change', upd);
                    })();
                </script>
            </div>
        </div>
    </div>

    {{-- ③ Contact --}}
    <div x-show="tab === 'contact'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">📞 Contact Information</h3>
            <div class="space-y-4">
                <div class="form-group mb-0">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ $v('contact_email') }}"
                           class="form-input" placeholder="info@technova.com">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ $v('contact_phone') }}"
                           class="form-input" placeholder="+880 1700-000000">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Address</label>
                    <textarea name="contact_address" rows="2" class="form-input"
                              placeholder="Dhaka, Bangladesh">{{ $v('contact_address') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ④ Social --}}
    <div x-show="tab === 'social'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🌐 Social Media</h3>
            <div class="space-y-4">
                @foreach([
                    ['social_facebook',  '👤', 'Facebook',  'https://facebook.com/yourpage'],
                    ['social_instagram', '📸', 'Instagram', 'https://instagram.com/yourpage'],
                    ['social_twitter',   '🐦', 'Twitter/X', 'https://x.com/yourpage'],
                    ['social_youtube',   '▶️', 'YouTube',   'https://youtube.com/@yourchannel'],
                ] as [$name, $icon, $label, $ph])
                <div class="form-group mb-0">
                    <label class="form-label">{{ $icon }} {{ $label }}</label>
                    <input type="url" name="{{ $name }}" value="{{ $v($name) }}"
                           class="form-input" placeholder="{{ $ph }}">
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ⑤ SEO --}}
    <div x-show="tab === 'seo'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🔍 SEO Settings</h3>
            <div class="space-y-4">
                <div class="form-group mb-0">
                    <label class="form-label">Meta Title <span class="text-gray-400 text-xs font-normal">(max 160)</span></label>
                    <input type="text" name="meta_title" value="{{ $v('meta_title') }}"
                           class="form-input" maxlength="160" placeholder="TechNova — Electronics & Gadgets">
                    <p class="text-xs text-gray-400 mt-1">Shown in browser tab and search engine results.</p>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Meta Description <span class="text-gray-400 text-xs font-normal">(max 300)</span></label>
                    <textarea name="meta_description" rows="3" maxlength="300"
                              class="form-input" placeholder="Shop the latest electronics...">{{ $v('meta_description') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Shown in Google/Bing results below the title link.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ⑥ Shipping --}}
    <div x-show="tab === 'shipping'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">📦 Shipping Rules</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="form-group mb-0">
                    <label class="form-label">Flat Shipping Cost ({{ $v('currency_symbol','৳') }})</label>
                    <input type="number" name="shipping_cost" value="{{ $v('shipping_cost','60') }}"
                           class="form-input" min="0" step="1" placeholder="60">
                    <p class="text-xs text-gray-400 mt-1">Charged below threshold.</p>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Free Shipping Threshold ({{ $v('currency_symbol','৳') }})</label>
                    <input type="number" name="free_shipping_threshold" value="{{ $v('free_shipping_threshold','2000') }}"
                           class="form-input" min="0" step="1" placeholder="2000">
                    <p class="text-xs text-gray-400 mt-1">0 = always paid shipping.</p>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Minimum Order ({{ $v('currency_symbol','৳') }})</label>
                    <input type="number" name="min_order_amount" value="{{ $v('min_order_amount','100') }}"
                           class="form-input" min="0" step="1" placeholder="100">
                    <p class="text-xs text-gray-400 mt-1">Blocks checkout below this.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ⑦ Homepage --}}
    <div x-show="tab === 'homepage'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🏠 Hero Banner</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group mb-0">
                    <label class="form-label">Badge Text</label>
                    <input type="text" name="hero_badge" value="{{ $v('hero_badge','🚀 New Season Sale') }}"
                           class="form-input" placeholder="🚀 New Season Sale">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">CTA Button Text</label>
                    <input type="text" name="hero_cta" value="{{ $v('hero_cta','Shop Now') }}"
                           class="form-input" placeholder="Shop Now">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Main Title</label>
                    <input type="text" name="hero_title" value="{{ $v('hero_title','Next-Gen Tech') }}"
                           class="form-input" placeholder="Next-Gen Tech">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="hero_subtitle" value="{{ $v('hero_subtitle','at Unbeatable Prices') }}"
                           class="form-input" placeholder="at Unbeatable Prices">
                </div>
            </div>
        </div>
    </div>

    {{-- ⑧ Advanced --}}
    <div x-show="tab === 'advanced'" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">⚙️ System Info</h3>
            <dl class="divide-y divide-gray-50 text-sm">
                @foreach([
                    ['PHP Version',    phpversion()],
                    ['Laravel',        app()->version()],
                    ['Environment',    app()->environment()],
                    ['App URL',        config('app.url')],
                    ['Settings Keys',  count($settings)],
                ] as [$label, $val])
                <div class="flex justify-between py-2.5">
                    <dt class="text-gray-500">{{ $label }}</dt>
                    <dd class="font-mono font-semibold text-gray-800">{{ $val }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>

    {{-- Sticky save bar --}}
    <div class="sticky bottom-0 bg-white border-t border-gray-200 mt-6 px-6 py-3 -mx-0 flex items-center justify-between rounded-b-xl shadow-lg">
        <p class="text-sm text-gray-400">Changes apply immediately after saving.</p>
        <button type="submit" class="btn-primary py-2.5 px-10 text-base">💾 Save All Settings</button>
    </div>
</form>
@endsection
