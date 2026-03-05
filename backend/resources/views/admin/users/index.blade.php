@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')
@section('breadcrumb', 'Manage registered users')

@section('content')
{{-- Search / Filter --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…" class="form-input">
    </div>
    <div class="min-w-36">
        <label class="form-label">Role</label>
        <select name="role" class="form-input">
            <option value="">All roles</option>
            <option value="user"  {{ request('role') === 'user'  ? 'selected' : '' }}>Customers</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
        </select>
    </div>
    <button type="submit" class="btn-primary">🔍 Search</button>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">✕ Clear</a>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <span class="text-sm text-gray-500">{{ $users->total() }} user(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left font-medium">User</th>
                    <th class="px-5 py-3 text-left font-medium">Email</th>
                    <th class="px-5 py-3 text-left font-medium">Role</th>
                    <th class="px-5 py-3 text-right font-medium">Orders</th>
                    <th class="px-5 py-3 text-left font-medium">Joined</th>
                    <th class="px-5 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="table-row">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white text-xs flex items-center justify-center font-bold flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                    <td class="px-5 py-3">
                        <span class="badge {{ $user->role === 'admin' ? 'badge-purple' : 'badge-gray' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ $user->orders_count }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3 text-center">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.role', $user) }}"
                              onsubmit="return confirm('Change role for {{ $user->name }}?')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="text-xs px-3 py-1 rounded-md border font-medium transition-all
                                       {{ $user->role === 'admin'
                                          ? 'border-red-200 text-red-600 hover:bg-red-50'
                                          : 'border-purple-200 text-purple-600 hover:bg-purple-50' }}">
                                {{ $user->role === 'admin' ? '↓ Make Customer' : '↑ Make Admin' }}
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">You</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>
@endsection
