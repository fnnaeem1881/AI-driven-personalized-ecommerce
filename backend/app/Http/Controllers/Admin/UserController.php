<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('orders')->with('roles')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => 'required|in:user,admin',
            'spatie_role' => 'nullable|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if ($request->spatie_role) {
            $user->syncRoles([$request->spatie_role]);
        } elseif ($request->role === 'admin') {
            $user->syncRoles(['admin']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    /** Toggle simple admin/user role flag */
    public function updateRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';
        $user->update(['role' => $newRole]);

        // Keep Spatie role in sync
        $user->syncRoles([$newRole === 'admin' ? 'admin' : 'customer']);

        return back()->with('success', "Role updated to " . ucfirst($newRole) . ".");
    }

    /** Assign a specific Spatie role to a user */
    public function assignSpatieRole(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own roles.');
        }

        $request->validate([
            'spatie_role' => 'required|exists:roles,name',
        ]);

        $user->syncRoles([$request->spatie_role]);

        return back()->with('success', "Role \"{$request->spatie_role}\" assigned to {$user->name}.");
    }
}
