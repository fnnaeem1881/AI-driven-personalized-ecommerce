@extends('layouts.admin')

@section('title', 'Edit Role: ' . $role->name)
@section('page-title', 'Edit Role: ' . ucfirst($role->name))
@section('breadcrumb', 'Admin / Roles / Edit')

@section('header-actions')
    <a href="{{ route('admin.roles.index') }}" class="btn-secondary">← Back to Roles</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.roles.update', $role) }}" class="max-w-3xl">
    @csrf @method('PUT')

    {{-- Role Name --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
        <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🛡️ Role Details</h3>
        <div class="form-group">
            <label class="form-label">Role Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                   class="form-input max-w-sm"
                   {{ in_array($role->name, ['admin','customer']) ? 'readonly' : '' }}>
            @if(in_array($role->name, ['admin','customer']))
                <p class="text-xs text-amber-500 mt-1">⚠ System role name cannot be changed.</p>
            @endif
        </div>
    </div>

    {{-- Permissions grouped --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">🔐 Permissions</h3>
            <button type="button" onclick="toggleAll(this)"
                    class="text-xs text-blue-600 hover:underline font-medium">Select All</button>
        </div>

        <div class="space-y-5">
            @foreach($permissionGroups as $group => $permissions)
            <div class="border border-gray-100 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 flex items-center justify-between border-b border-gray-100">
                    <span class="text-sm font-semibold text-gray-700 capitalize">{{ $group }}</span>
                    <button type="button" onclick="toggleGroup('{{ $group }}')"
                            class="text-xs text-blue-500 hover:underline">Toggle group</button>
                </div>
                <div class="px-4 py-3 grid grid-cols-2 sm:grid-cols-4 gap-3" data-group="{{ $group }}">
                    @foreach($permissions as $permission)
                    @php
                        $action = explode(' ', $permission->name, 2)[0];
                        $color = match($action) {
                            'view'   => 'text-blue-700',
                            'create' => 'text-green-700',
                            'edit'   => 'text-yellow-700',
                            'delete' => 'text-red-700',
                            default  => 'text-gray-700',
                        };
                    @endphp
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                               class="w-4 h-4 rounded perm-checkbox"
                               {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}>
                        <span class="text-sm font-medium {{ $color }} capitalize">{{ $action }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary py-3 px-8">💾 Save Changes</button>
        <a href="{{ route('admin.roles.index') }}" class="btn-secondary">Cancel</a>
    </div>
</form>

<script>
function toggleAll(btn) {
    const boxes = document.querySelectorAll('.perm-checkbox');
    const anyUnchecked = Array.from(boxes).some(b => !b.checked);
    boxes.forEach(b => b.checked = anyUnchecked);
    btn.textContent = anyUnchecked ? 'Deselect All' : 'Select All';
}
function toggleGroup(group) {
    const container = document.querySelector('[data-group="' + group + '"]');
    const boxes = container.querySelectorAll('.perm-checkbox');
    const anyUnchecked = Array.from(boxes).some(b => !b.checked);
    boxes.forEach(b => b.checked = anyUnchecked);
}
</script>
@endsection
