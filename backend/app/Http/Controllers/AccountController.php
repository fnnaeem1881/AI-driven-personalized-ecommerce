<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AIService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function dashboard()
    {
        $user = auth()->user();
        $recentOrders = $user->orders()->with('items')->limit(5)->get();

        $stats = [
            'total_orders'  => $user->orders()->count(),
            'total_spent'   => $user->orders()->where('payment_status', 'paid')->sum('total'),
            'wishlist_count' => $user->wishlists()->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
        ];

        $recommendations = collect();
        try {
            $recs = $this->aiService->getRecommendations($user->id, 4);
            if (!empty($recs)) {
                $recommendations = Product::whereIn('id', array_column($recs, 'product_id'))->get();
            }
        } catch (\Exception $e) {}

        return view('account.dashboard', compact('user', 'recentOrders', 'stats', 'recommendations'));
    }

    public function profile()
    {
        return view('account.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }
}
