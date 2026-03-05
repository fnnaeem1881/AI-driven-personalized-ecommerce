@extends('layouts.admin')

@section('title', 'Permissions')
@section('page-title', 'Permissions')
@section('breadcrumb', 'Manage permissions — grouped by module')

@section('header-actions')
    <a href="{{ route('admin.permissions.create') }}" class="btn-primary">+ Add Permissions</a>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ══════════════════════════════════════════════════════════
         Left 2/3 — Permission Groups
    ══════════════════════════════════════════════════════════ --}}
    <div class="xl:col-span-2 space-y-4">

        @forelse($groups as $group => $permissions)
        @php
            $icon = match($group) {
                'dashboard'   => '📊',
                'products'    => '📦',
                'categories'  => '🏷️',
                'orders'      => '📋',
                'users'       => '👥',
                'settings'    => '⚙️',
                'roles'       => '🛡️',
                'permissions' => '🔐',
                default       => '🔹',
            };
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {{-- Group header --}}
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">{{ $icon }}</span>
                    <span class="font-semibold text-gray-800 capitalize">{{ $group }}</span>
                    <span class="badge badge-gray text-xs">{{ count($permissions) }}</span>
                </div>
                <a href="{{ route('admin.permissions.create') }}?group={{ $group }}"
                   class="text-xs text-blue-600 hover:underline font-medium">+ Add to group</a>
            </div>

            {{-- Permission table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-2 text-left font-medium">Permission Name</th>
                            <th class="px-5 py-2 text-left font-medium">Group</th>
                            <th class="px-5 py-2 text-left font-medium">Guard</th>
                            <th class="px-5 py-2 text-left font-medium">Action</th>
                            <th class="px-5 py-2 text-center font-medium">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($permissions as $perm)
                        @php
                            $action = explode(' ', $perm->name, 2)[0];
                            [$pillBg, $pillText] = match($action) {
                                'view'    => ['bg-blue-50',   'text-blue-700'],
                                'create'  => ['bg-green-50',  'text-green-700'],
                                'edit'    => ['bg-yellow-50', 'text-yellow-700'],
                                'delete'  => ['bg-red-50',    'text-red-700'],
                                'export'  => ['bg-cyan-50',   'text-cyan-700'],
                                'import'  => ['bg-teal-50',   'text-teal-700'],
                                'approve' => ['bg-violet-50', 'text-violet-700'],
                                'publish' => ['bg-orange-50', 'text-orange-700'],
                                default   => ['bg-gray-50',   'text-gray-600'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Full permission name --}}
                            <td class="px-5 py-2.5">
                                <code class="text-xs text-gray-800 bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $perm->name }}</code>
                            </td>
                            {{-- group_name --}}
                            <td class="px-5 py-2.5">
                                <span class="text-xs text-gray-500">{{ $perm->group_name ?? '—' }}</span>
                            </td>
                            {{-- guard_name --}}
                            <td class="px-5 py-2.5">
                                <span class="badge badge-blue text-xs">{{ $perm->guard_name }}</span>
                            </td>
                            {{-- action pill --}}
                            <td class="px-5 py-2.5">
                                <span class="text-xs font-semibold capitalize px-2 py-0.5 rounded-full {{ $pillBg }} {{ $pillText }}">
                                    {{ $action }}
                                </span>
                            </td>
                            {{-- delete --}}
                            <td class="px-5 py-2.5 text-center">
                                <form method="POST"
                                      action="{{ route('admin.permissions.destroy', $perm) }}"
                                      onsubmit="return confirm('Delete permission: {{ $perm->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-gray-300 hover:text-red-500 transition-colors text-lg leading-none"
                                            title="Delete">×</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
            <div class="text-4xl mb-3">🔐</div>
            <p class="font-medium mb-2">No permissions found.</p>
            <a href="{{ route('admin.permissions.create') }}" class="btn-primary">Create your first group</a>
        </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════════════════════
         Right 1/3 — Role Summary + Stats
    ══════════════════════════════════════════════════════════ --}}
    <div class="space-y-4">

        {{-- Role summary cards --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🛡️ Role Summary</h3>

            @php $totalPerms = array_sum(array_map('count', $groups->toArray())); @endphp

            @foreach($roles as $role)
            <div class="mb-5 last:mb-0">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800 capitalize text-sm">{{ $role->name }}</span>
                        <span class="badge badge-gray text-xs">{{ $role->permissions_count }}</span>
                    </div>
                    <a href="{{ route('admin.roles.edit', $role) }}"
                       class="text-xs text-blue-600 hover:underline">Edit →</a>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php $pct = $totalPerms > 0 ? min(100, round($role->permissions_count / $totalPerms * 100)) : 0; @endphp
                    <div class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all"
                         style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $pct }}% of all permissions</p>
            </div>
            @endforeach
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 pb-3 border-b border-gray-100">📊 Stats</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Groups</dt>
                    <dd class="font-bold text-gray-800">{{ count($groups) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Total Permissions</dt>
                    <dd class="font-bold text-gray-800">{{ $totalPerms }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Guards in use</dt>
                    <dd class="font-bold text-gray-800">
                        {{ collect($groups->flatten())->pluck('guard_name')->unique()->implode(', ') }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Roles</dt>
                    <dd class="font-bold text-gray-800">{{ $roles->count() }}</dd>
                </div>
            </dl>
        </div>

        {{-- Quick link --}}
        <a href="{{ route('admin.permissions.create') }}"
           class="btn-primary w-full justify-center py-3">
            + Add New Permission Group
        </a>
    </div>
</div>
@endsection
