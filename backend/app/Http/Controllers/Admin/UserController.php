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
        $query = User::withCount('orders')->withSum('orders', 'total')->with('roles')->latest();

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

    /**
     * Show full AI profile for a user in the admin panel.
     */
    public function show(User $user)
    {
        $user->loadCount('orders')->loadSum('orders', 'total');

        $aiProfile           = [];
        $recommendedProducts = collect();
        $interactedProducts  = collect();

        try {
            $aiService = new \App\Services\AIService();
            $aiProfile = $aiService->getUserProfile($user->id, 10);

            // Load recommended products from MySQL using product IDs from AI
            if (!empty($aiProfile['recommendations'])) {
                $recIds = array_column($aiProfile['recommendations'], 'product_id');
                $dbProds = \App\Models\Product::whereIn('id', $recIds)->with('category')->get()->keyBy('id');
                foreach ($aiProfile['recommendations'] as $rec) {
                    $pid = $rec['product_id'];
                    if (isset($dbProds[$pid])) {
                        $prod = clone $dbProds[$pid];
                        $prod->ai_score = $rec['score'] ?? null;
                        $recommendedProducts->push($prod);
                    }
                }
            }

            // Load interacted products from MySQL
            if (!empty($aiProfile['top_interactions'])) {
                $intIds = array_column($aiProfile['top_interactions'], 'product_id');
                $dbInts = \App\Models\Product::whereIn('id', $intIds)->with('category')->get()->keyBy('id');
                foreach ($aiProfile['top_interactions'] as $inter) {
                    $pid = $inter['product_id'];
                    if (isset($dbInts[$pid])) {
                        $prod = clone $dbInts[$pid];
                        $prod->interaction_score = $inter['interaction_score'];
                        $interactedProducts->push($prod);
                    }
                }
            }
        } catch (\Exception $e) {
            // AI service unavailable — show page with MySQL data only
        }

        return view('admin.users.show', compact('user', 'aiProfile', 'recommendedProducts', 'interactedProducts'));
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
