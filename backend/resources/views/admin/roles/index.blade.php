@extends('layouts.admin')

@section('title', 'Roles')
@section('page-title', 'Roles')
@section('breadcrumb', 'Manage roles & their permissions')

@section('header-actions')
    <a href="{{ route('admin.roles.create') }}" class="btn-primary">+ New Role</a>
@endsection

@section('content')
<div class="space-y-4">
    @foreach($roles as $role)
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Role header --}}
        <div class="px-5 py-4 flex items-center justify-between border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="text-2xl">{{ $role->name === 'admin' ? '👑' : ($role->name === 'customer' ? '👤' : '🛡️') }}</span>
                <div>
                    <div class="font-bold text-gray-900 capitalize">{{ $role->name }}</div>
                    <div class="text-xs text-gray-400">{{ $role->permissions_count }} permission(s) assigned</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn-secondary text-xs py-1 px-3">✏️ Edit</a>
                @if(!in_array($role->name, ['admin','customer']))
                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                      onsubmit="return confirm('Delete role {{ $role->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger text-xs py-1 px-3">🗑 Delete</button>
                </form>
                @else
                    <span class="text-xs text-gray-400 px-2">System role</span>
                @endif
            </div>
        </div>

        {{-- Permission pills grouped --}}
        @if($role->permissions->count())
        @php
            $grouped = $role->permissions->groupBy(fn($p) => explode(' ', $p->name, 2)[1] ?? 'general');
        @endphp
        <div class="px-5 py-3 flex flex-wrap gap-3">
            @foreach($grouped->sortKeys() as $group => $perms)
            <div class="flex items-center gap-1 flex-wrap">
                <span class="text-xs font-semibold text-gray-500 uppercase mr-1">{{ $group }}:</span>
                @foreach($perms as $perm)
                    @php
                        $action = explode(' ', $perm->name, 2)[0];
                        $color = match($action) {
                            'view'   => 'bg-blue-50 text-blue-700 border-blue-200',
                            'create' => 'bg-green-50 text-green-700 border-green-200',
                            'edit'   => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            'delete' => 'bg-red-50 text-red-700 border-red-200',
                            default  => 'bg-gray-50 text-gray-700 border-gray-200',
                        };
                    @endphp
                    <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $color }}">{{ $action }}</span>
                @endforeach
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-3 text-sm text-gray-400 italic">No permissions assigned.</div>
        @endif
    </div>
    @endforeach

    @if($roles->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
        No roles found. <a href="{{ route('admin.roles.create') }}" class="text-blue-600">Create one</a>.
    </div>
    @endif
</div>
@endsection
