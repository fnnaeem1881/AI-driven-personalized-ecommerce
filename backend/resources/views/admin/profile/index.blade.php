@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('breadcrumb', 'Admin / Profile')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Profile Info --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Update Info --}}
        <form method="POST" action="{{ route('admin.profile.update') }}" class="bg-white rounded-xl border border-gray-200 p-6">
            @csrf
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">👤 Profile Information</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group mb-0">
                        <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               required class="form-input">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               required class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group mb-0">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="form-input" placeholder="+880 1700-000000">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Avatar URL</label>
                        <input type="url" name="avatar" value="{{ old('avatar', $user->avatar) }}"
                               class="form-input" placeholder="https://...">
                    </div>
                </div>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex gap-3">
                <button type="submit" class="btn-primary py-2 px-6">💾 Save Profile</button>
            </div>
        </form>

        {{-- Change Password --}}
        <form method="POST" action="{{ route('admin.profile.password') }}" class="bg-white rounded-xl border border-gray-200 p-6">
            @csrf
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🔑 Change Password</h3>
            <div class="space-y-4">
                <div class="form-group mb-0">
                    <label class="form-label">Current Password <span class="text-red-500">*</span></label>
                    <input type="password" name="current_password" required class="form-input"
                           placeholder="Enter your current password">
                    @error('current_password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group mb-0">
                        <label class="form-label">New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required class="form-input"
                               placeholder="Min 8 characters">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required class="form-input"
                               placeholder="Repeat new password">
                    </div>
                </div>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-secondary py-2 px-6">🔐 Update Password</button>
            </div>
        </form>
    </div>

    {{-- Right: Avatar + Account Summary --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
            {{-- Avatar --}}
            <div class="mb-4">
                @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                     class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-blue-100">
                @else
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-purple-600
                            flex items-center justify-center text-white text-3xl font-bold mx-auto">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                @endif
            </div>
            <h4 class="font-bold text-gray-900 text-lg">{{ $user->name }}</h4>
            <p class="text-gray-500 text-sm">{{ $user->email }}</p>
            <div class="mt-2">
                <span class="badge badge-purple text-xs">{{ ucfirst($user->role) }}</span>
                @foreach($user->getRoleNames() as $role)
                    <span class="badge badge-blue text-xs ml-1">{{ $role }}</span>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h4 class="font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-100">Account Details</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Member since</dt>
                    <dd class="font-medium text-gray-700">{{ $user->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">System Role</dt>
                    <dd><span class="badge badge-{{ $user->role === 'admin' ? 'purple' : 'gray' }} text-xs">{{ ucfirst($user->role) }}</span></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Permissions</dt>
                    <dd class="font-medium text-gray-700">{{ $user->getAllPermissions()->count() }}</dd>
                </div>
            </dl>
        </div>

        <a href="{{ route('admin.dashboard') }}" class="btn-secondary w-full justify-center py-2.5">← Back to Dashboard</a>
    </div>
</div>
@endsection
