<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /** List all permissions grouped by group_name */
    public function index()
    {
        $permissions = Permission::orderBy('group_name')->orderBy('name')->get();

        // Group by group_name (fallback: derive from name)
        $groups = $permissions->groupBy(function ($perm) {
            return $perm->group_name ?: (explode(' ', $perm->name, 2)[1] ?? 'general');
        })->sortKeys();

        $roles = Role::withCount('permissions')->get();

        $availableGuards = array_keys(config('auth.guards', ['web' => []]));

        return view('admin.permissions.index', compact('groups', 'roles', 'availableGuards'));
    }

    /** Show create form */
    public function create()
    {
        $existingGroups  = Permission::allGroups();
        $availableGuards = array_keys(config('auth.guards', ['web' => []]));
        $predefinedActions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'approve', 'publish'];

        return view('admin.permissions.create', compact('existingGroups', 'availableGuards', 'predefinedActions'));
    }

    /**
     * Store one or more permissions for a group (bulk create).
     * Creates: "{action} {group}" for each action, with group_name and guard_name stored explicitly.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:100|regex:/^[a-z0-9\-_]+$/',
            'guard_name' => 'required|string|in:' . implode(',', array_keys(config('auth.guards', ['web' => []]))),
            'actions'    => 'required|array|min:1',
            'actions.*'  => 'required|string|max:50|regex:/^[a-z0-9\-_]+$/',
        ]);

        $group      = strtolower(trim($request->group_name));
        $guard      = $request->guard_name;
        $created    = 0;
        $skipped    = 0;

        foreach ($request->actions as $action) {
            $action = strtolower(trim($action));
            if (empty($action)) continue;

            $name   = "{$action} {$group}";
            $exists = Permission::where('name', $name)->where('guard_name', $guard)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Permission::create([
                'name'       => $name,
                'guard_name' => $guard,
                'group_name' => $group,
            ]);
            $created++;
        }

        // Auto-assign new permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $msg = "Created {$created} permission(s) in group \"{$group}\" (guard: {$guard}).";
        if ($skipped) $msg .= " {$skipped} already existed.";

        return redirect()->route('admin.permissions.index')->with('success', $msg);
    }

    /** Delete a single permission */
    public function destroy(Permission $permission)
    {
        $name = $permission->name;
        $permission->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission \"{$name}\" deleted.");
    }

    /** Assign / sync permissions to a role */
    public function assignToRole(Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($request->permissions ?? []);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', "Permissions updated for role \"{$role->name}\".");
    }
}
