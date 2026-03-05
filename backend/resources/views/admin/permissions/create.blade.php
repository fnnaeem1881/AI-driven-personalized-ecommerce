@extends('layouts.admin')

@section('title', 'Add Permissions')
@section('page-title', 'Add Permissions')
@section('breadcrumb', 'Admin / Permissions / Add')

@section('header-actions')
    <a href="{{ route('admin.permissions.index') }}" class="btn-secondary">← Back to Permissions</a>
@endsection

@section('content')
{{-- Pre-compute old values in PHP so we never put PHP inside JS strings --}}
@php
    $oldGroup   = old('group_name', request('group', ''));
    $oldGuard   = old('guard_name', 'web');
    $oldActions = old('actions', ['view','create','edit','delete']);
    // Encode for JS - safe to embed in <script> block
    $jsDefault  = json_encode([
        'group'           => $oldGroup,
        'selectedActions' => $oldActions,
        'predefined'      => $predefinedActions,
    ]);
@endphp

<div class="max-w-2xl" x-data="permForm({{ $jsDefault }})">

    <form method="POST" action="{{ route('admin.permissions.store') }}">
        @csrf

        {{-- ① Group Name + Guard Name --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">📁 Group &amp; Guard</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Group Name --}}
                <div class="form-group mb-0">
                    <label class="form-label">
                        Group Name <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal text-xs ml-1">(e.g. products, reports)</span>
                    </label>
                    <input type="text"
                           name="group_name"
                           x-model="group"
                           value="{{ $oldGroup }}"
                           required
                           placeholder="products"
                           list="existing-groups"
                           class="form-input">
                    <datalist id="existing-groups">
                        @foreach($existingGroups as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </datalist>
                    <p class="text-xs text-gray-400 mt-1">Lowercase · no spaces · use <code>_</code> or <code>-</code></p>
                </div>

                {{-- Guard Name --}}
                <div class="form-group mb-0">
                    <label class="form-label">
                        Guard Name <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal text-xs ml-1">(usually web)</span>
                    </label>
                    <select name="guard_name" class="form-input">
                        @foreach($availableGuards as $guard)
                            <option value="{{ $guard }}"
                                {{ $oldGuard === $guard ? 'selected' : '' }}>
                                {{ $guard }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ② Actions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-gray-800">⚡ Actions to Create</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Each action = one permission name, e.g. <code class="bg-gray-100 px-1 rounded">view products</code>
                    </p>
                </div>
                <button type="button" @click="toggleAll()"
                        class="text-xs text-blue-600 hover:underline font-medium shrink-0"
                        x-text="allSelected ? 'Deselect All' : 'Select All'">
                </button>
            </div>

            {{-- Pre-defined action cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                @foreach($predefinedActions as $action)
                @php
                    $cardColor = match($action) {
                        'view'    => 'border-blue-300   bg-blue-50   text-blue-700',
                        'create'  => 'border-green-300  bg-green-50  text-green-700',
                        'edit'    => 'border-yellow-300 bg-yellow-50 text-yellow-700',
                        'delete'  => 'border-red-300    bg-red-50    text-red-700',
                        'export'  => 'border-cyan-300   bg-cyan-50   text-cyan-700',
                        'import'  => 'border-teal-300   bg-teal-50   text-teal-700',
                        'approve' => 'border-violet-300 bg-violet-50 text-violet-700',
                        'publish' => 'border-orange-300 bg-orange-50 text-orange-700',
                        default   => 'border-gray-200   bg-gray-50   text-gray-600',
                    };
                    $jsAction = e($action); // HTML-safe, used in :class Alpine expression
                @endphp
                <label class="flex items-center gap-2 cursor-pointer border-2 rounded-lg px-3 py-2
                              transition-all select-none {{ $cardColor }}"
                       :class="selectedActions.includes('{{ $jsAction }}')
                                   ? 'ring-2 ring-offset-1 ring-current shadow-sm'
                                   : 'opacity-60'">
                    <input type="checkbox"
                           name="actions[]"
                           value="{{ $action }}"
                           x-model="selectedActions"
                           class="w-4 h-4 rounded shrink-0">
                    <span class="text-sm font-semibold capitalize">{{ $action }}</span>
                </label>
                @endforeach
            </div>

            {{-- Custom action --}}
            <div class="border-t border-gray-100 pt-4">
                <label class="form-label text-gray-500 mb-2">+ Custom Action</label>
                <div class="flex gap-2">
                    <input type="text"
                           x-model="customAction"
                           placeholder="e.g. export, restore, bulk-delete"
                           class="form-input"
                           @keydown.enter.prevent="addCustom()">
                    <button type="button" @click="addCustom()"
                            class="btn-secondary whitespace-nowrap px-4">Add</button>
                </div>

                <div class="flex flex-wrap gap-2 mt-3" x-show="customActions.length > 0">
                    <template x-for="(act, idx) in customActions" :key="act">
                        <span class="inline-flex items-center gap-1 bg-purple-50 border border-purple-200
                                     text-purple-700 px-3 py-1 rounded-full text-xs font-semibold">
                            <input type="hidden" name="actions[]" :value="act">
                            <span x-text="act"></span>
                            <button type="button" @click="removeCustom(idx)"
                                    class="ml-1.5 opacity-50 hover:opacity-100 font-bold">×</button>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        {{-- ③ Live Preview --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5"
             x-show="selectedActions.length > 0 || customActions.length > 0">
            <p class="text-sm font-semibold text-blue-700 mb-2">📋 Will create these permissions:</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="act in [...selectedActions, ...customActions]" :key="act">
                    <code class="text-xs bg-white border border-blue-200 text-blue-700 px-2 py-1 rounded font-mono"
                          x-text="act + ' ' + (group.trim() || '{group_name}')">
                    </code>
                </template>
            </div>
            <p class="text-xs text-blue-500 mt-3">
                ✓ <strong>group_name</strong> = <span class="font-mono" x-text="group || '{group_name}'"></span>
                &nbsp;|&nbsp;
                ✓ All new permissions are auto-assigned to the <strong>admin</strong> role.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary py-3 px-8">🔐 Create Permissions</button>
            <a href="{{ route('admin.permissions.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
/**
 * permForm — Alpine.js data function for the permission create page.
 * All dynamic values are passed in as a single JSON object (no Blade inside JS strings).
 *
 * @param {object} cfg - { group, selectedActions, predefined }
 */
function permForm(cfg) {
    return {
        group:           cfg.group           || '',
        customAction:    '',
        selectedActions: cfg.selectedActions || [],
        customActions:   [],
        predefined:      cfg.predefined      || [],

        get allSelected() {
            return this.predefined.length > 0
                && this.predefined.every(a => this.selectedActions.includes(a));
        },

        toggleAll() {
            if (this.allSelected) {
                this.selectedActions = [];
            } else {
                this.selectedActions = [...this.predefined];
            }
        },

        addCustom() {
            const val = this.customAction.toLowerCase().trim().replace(/[\s]+/g, '_');
            if (val && !this.customActions.includes(val) && !this.selectedActions.includes(val)) {
                this.customActions.push(val);
            }
            this.customAction = '';
        },

        removeCustom(idx) {
            this.customActions.splice(idx, 1);
        }
    };
}
</script>
@endsection
