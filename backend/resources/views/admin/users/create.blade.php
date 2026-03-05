@extends('layouts.admin')

@section('title', 'Create User')
@section('page-title', 'Create User')
@section('breadcrumb', 'Admin / Users / Create')

@section('header-actions')
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">← Back to Users</a>
@endsection

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
        @csrf

        {{-- Account Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">👤 Account Information</h3>
            <div class="space-y-4">
                <div class="form-group mb-0">
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           required class="form-input" placeholder="John Doe">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           required class="form-input" placeholder="john@example.com">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group mb-0">
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                               class="form-input" placeholder="Min 8 characters">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="form-input" placeholder="Repeat password">
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Assignment --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🛡️ Role Assignment</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group mb-0">
                    <label class="form-label">System Role <span class="text-red-500">*</span></label>
                    <select name="role" class="form-input" required>
                        <option value="user"  {{ old('role','user') === 'user'  ? 'selected' : '' }}>👤 Customer</option>
                        <option value="admin" {{ old('role','user') === 'admin' ? 'selected' : '' }}>👑 Admin</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Controls backend access.</p>
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Permission Role</label>
                    <select name="spatie_role" class="form-input">
                        <option value="">— None —</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}"
                                {{ old('spatie_role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                            ({{ $role->permissions_count ?? 0 }} perms)
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Spatie permission role (optional).</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary py-3 px-8">✅ Create User</button>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
