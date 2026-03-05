@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')
@section('breadcrumb', 'Manage customer accounts & roles')

@section('header-actions')
    <a href="{{ route('admin.users.create') }}" class="btn-primary">+ Create User</a>
@endsection

@section('content')
{{-- Search & Filter --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-input" placeholder="Name or email...">
    </div>
    <div>
        <label class="form-label">System Role</label>
        <select name="role" class="form-input">
            <option value="">All</option>
            <option value="admin" {{ request('role')==='admin' ? 'selected' : '' }}>Admin</option>
            <option value="user"  {{ request('role')==='user'  ? 'selected' : '' }}>Customer</option>
        </select>
    </div>
    <button type="submit" class="btn-primary py-2 px-4">🔍 Filter</button>
    @if(request()->hasAny(['search','role']))
        <a href="{{ route('admin.users.index') }}" class="btn-secondary py-2 px-4">✕ Clear</a>
    @endif
</form>

{{-- Users Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
        <span class="text-sm text-gray-500">{{ $users->total() }} user(s) found</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-3 text-left font-medium">User</th>
                    <th class="px-5 py-3 text-left font-medium">Email</th>
                    <th class="px-5 py-3 text-left font-medium">System Role</th>
                    <th class="px-5 py-3 text-left font-medium">Assign Permission Role</th>
                    <th class="px-5 py-3 text-center font-medium">Orders</th>
                    <th class="px-5 py-3 text-left font-medium">Joined</th>
                    <th class="px-5 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="table-row">
                    {{-- Avatar + Name --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-purple-500
                                        flex items-center justify-center text-white text-sm font-bold shrink-0">
                                {{ strtoupper(substr($user->name,0,1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $user->name }}</div>
                                @if($user->id === auth()->id())
                                    <span class="text-xs text-blue-400">(you)</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td class="px-5 py-3 text-gray-600 text-xs">{{ $user->email }}</td>

                    {{-- System role badge --}}
                    <td class="px-5 py-3">
                        <span class="badge {{ $user->role === 'admin' ? 'badge-purple' : 'badge-gray' }} text-xs">
                            {{ $user->role === 'admin' ? '👑 Admin' : '👤 Customer' }}
                        </span>
                    </td>

                    {{-- Spatie role assign dropdown --}}
                    <td class="px-5 py-3">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.spatie-role', $user) }}" class="flex items-center gap-1.5">
                            @csrf @method('PATCH')
                            <select name="spatie_role" class="form-input text-xs py-1 pl-2 pr-6" style="min-width:110px;">
                                @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                        {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-secondary text-xs py-1 px-2">Set</button>
                        </form>
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($user->roles as $sr)
                                <span class="badge badge-blue" style="font-size:10px;">{{ $sr->name }}</span>
                            @endforeach
                        </div>
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>

                    {{-- Orders --}}
                    <td class="px-5 py-3 text-center">
                        <span class="badge badge-blue">{{ $user->orders_count }}</span>
                    </td>

                    {{-- Joined --}}
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>

                    {{-- Toggle role --}}
                    <td class="px-5 py-3 text-center">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="btn-secondary text-xs py-1 px-3 {{ $user->role==='admin' ? 'text-red-600 border-red-200 hover:bg-red-50' : '' }}"
                                    onclick="return confirm('Change role for {{ addslashes($user->name) }}?')">
                                {{ $user->role==='admin' ? '↓ Demote' : '↑ Make Admin' }}
                            </button>
                        </form>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <div class="text-4xl mb-2">👥</div>
                        No users found.
                        @if(request()->hasAny(['search','role']))
                            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline ml-1">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>
@endsection
