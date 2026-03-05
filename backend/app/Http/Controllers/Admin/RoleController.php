<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionGroups = $this->getGroupedPermissions();
        return view('admin.roles.create', compact('permissionGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" created with " . count($request->permissions ?? []) . " permissions.");
    }

    public function edit(Role $role)
    {
        $permissionGroups  = $this->getGroupedPermissions();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissionGroups', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['admin', 'customer'])) {
            return back()->with('error', "Cannot delete system role \"{$role->name}\".");
        }

        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', "Role deleted.");
    }

    /** Build grouped permission list using group_name column */
    private function getGroupedPermissions(): array
    {
        $allPermissions = Permission::orderBy('group_name')->orderBy('name')->get();
        $groups = [];

        foreach ($allPermissions as $permission) {
            $group = $permission->group_name
                  ?: (explode(' ', $permission->name, 2)[1] ?? 'general');
            $groups[$group][] = $permission;
        }

        ksort($groups);
        return $groups;
    }
}
