<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = auth()->user()->wishlists()->with('product.category')->get();
        return view('account.wishlist', compact('wishlistItems'));
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)->first();

        if ($existing) {
            $existing->delete();
            $inWishlist = false;
            $message = 'Removed from wishlist.';
        } else {
            Wishlist::create(['user_id' => auth()->id(), 'product_id' => $request->product_id]);
            $inWishlist = true;
            $message = 'Added to wishlist!';
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'in_wishlist' => $inWishlist, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
